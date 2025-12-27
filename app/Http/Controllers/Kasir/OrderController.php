<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Customer;
use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Promotion;
use App\Models\Payment;
use App\Models\MenuItem;
use App\Models\Reserved;
use App\Models\StockMovement;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\LoyaltyPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class OrderController extends Controller
{
    public function index(Request $request)
    {
        
        $user  = Auth::user();
        $today = now()->toDateString();

        // Filter periode untuk RIWAYAT
        $from = $request->input('from_date', $today);
        $to   = $request->input('to_date', $today);

        // -------- OPEN BILL (status open) --------
        $openOrders = Order::with(['customer', 'table','payments'])
            ->where('cashier_id', $user->id)
            // ->where('status', 'open')
            ->orderByDesc('order_date')
            ->get();
        // dd($openOrders);
        // -------- RIWAYAT (status paid) + filter tanggal --------
        $historyQuery = Order::with(['customer', 'table','payments'])
            ->where('cashier_id', $user->id)
            ->where('status', 'paid')
            ->whereBetween('order_date', [
                $from . ' 00:00:00',
                $to   . ' 23:59:59',
            ]);

        $historyOrders = $historyQuery
            ->orderByDesc('order_date')
            ->paginate(20)
            ->withQueryString(); // biar from/to tetap di query string

        $totalTransactions = (clone $historyQuery)->count();
        $totalRevenue      = (clone $historyQuery)->sum('grand_total');

        // Tab aktif (bisa dikirim via ?tab=history)
        $activeTab = $request->input('tab', 'open');

        return view('kasir.orders.index', compact(
            'openOrders',
            'historyOrders',
            'from',
            'to',
            'totalTransactions',
            'totalRevenue',
            'activeTab'
        ));
    }


    /**
     * FORM CREATE – POS / Transaksi baru
     */
    public function create()
    {
        $user = Auth::user();

        $outlet = Outlet::find($user->outlet_id);

       

        $tables = CafeTable::where('outlet_id', $user->outlet_id ?? null)
            ->orderBy('name')
            ->get();
        
        

        $categories = Category::with(['menuItems' => function ($q) {
                $q->where('is_active', 1)->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $today = now()->toDateString();

        $promotions = Promotion::where('is_active', 1)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->where(function ($q) use ($user) {
                $q->whereNull('outlet_id')
                  ->orWhere('outlet_id', $user->outlet_id);
            })
            ->orderBy('name')
            ->get();
        
        $promos = $promotions->map(fn ($p) => [
            'id'         => $p->id,
            'type'       => $p->type,
            'value'      => $p->value,
            'min_amount' => $p->min_amount,
        ])->values();

        return view('kasir.orders.create', compact(
            'outlet',
            'tables',
            'categories',
            'promotions',
            'promos'
        ));
    }

    /**
     * SIMPAN ORDER BARU (Create)
        */
        public function store(Request $request)
        {

            if ($request->filled('nominal_dp')) {
                $request->merge([
                    'nominal_dp' => preg_replace('/[^0-9]/', '', $request->nominal_dp),
                ]);
            }
            
            /**
             * ==========================================================
             * 1️⃣ AUTH USER
             * ==========================================================
             */
            $user = Auth::user();

            /**
             * ==========================================================
             * 2️⃣ CEK RESERVED (SUMBER DARI CHECKBOX)
             * ==========================================================
             */
            $isReserved = $request->input('is_reserved') === 'reserved';

            /**
             * ==========================================================
             * 3️⃣ BASE VALIDATION
             * ==========================================================
             */
            $rules = [
                'order_type'   => 'required|in:dine_in,take_away,delivery',
                'table_id'     => 'nullable|exists:cafe_tables,id',

                'customer_name'  => 'nullable|string|max:150',
                'customer_phone' => 'nullable|string|max:50',
                'customer_email' => 'nullable|email|max:150',

                'promotion_id'   => 'nullable|exists:promotions,id',
            ];

            /**
             * ==========================================================
             * 4️⃣ VALIDASI KHUSUS RESERVED / NON-RESERVED
             * ==========================================================
             */
            if ($isReserved) {
                $rules = array_merge($rules, [
                    'table_id'   => 'required|exists:cafe_tables,id',
                    'nominal_dp' => 'required|string|numeric|min:0',
                    'start_date' => 'required|date',
                    'end_date'   => 'required|date|after:start_date',
                ]);
            } else {
                $rules = array_merge($rules, [
                    'cart'                => 'required|array|min:1',
                    'cart.*.menu_item_id' => 'required|exists:menu_items,id',
                    'cart.*.name'         => 'required|string',
                    'cart.*.qty'          => 'required|integer|min:1',
                    'cart.*.price'        => 'required|numeric|min:0',
                ]);
            }

            /**
             * ==========================================================
             * 5️⃣ VALIDATE REQUEST
             * ==========================================================
             */
            $data = $request->validate($rules);

            /**
             * ==========================================================
             * 6️⃣ VALIDASI & LOCK MEJA
             * ==========================================================
             */
            $table = null;

            if ($data['order_type'] === 'dine_in' || $isReserved) {

                $table = CafeTable::lockForUpdate()->find($data['table_id']);

                if (!$table) {
                    return back()->withErrors([
                        'table_id' => 'Meja tidak ditemukan.'
                    ]);
                }

                if ($table->status === 'occupied') {
                    return back()->withErrors([
                        'table_id' => 'Meja sedang digunakan.'
                    ]);
                }

                if ($table->status === 'reserved' && !$isReserved) {
                    return back()->withErrors([
                        'table_id' => 'Meja sedang reserved.'
                    ]);
                }
            }

            /**
             * ==========================================================
             * 7️⃣ CUSTOMER (OPTIONAL)
             * ==========================================================
             */
            $customerId = null;

            if (
                !empty($data['customer_name']) ||
                !empty($data['customer_phone']) ||
                !empty($data['customer_email'])
            ) {
                $customer = Customer::create([
                    'name'  => $data['customer_name'] ?: 'Customer',
                    'phone' => $data['customer_phone'] ?: null,
                    'email' => $data['customer_email'] ?: null,
                ]);

                $customerId = $customer->id;
            }

            /**
             * ==========================================================
             * 8️⃣ PROMOTION (OPTIONAL)
             * ==========================================================
             */
            $promo = !empty($data['promotion_id'])
                ? Promotion::find($data['promotion_id'])
                : null;

            /**
             * ==========================================================
             * 9️⃣ HITUNG CART
             * ==========================================================
             */
            $calc = $this->calculateItemsWithDiscount(
                $data['cart'] ?? [],
                $promo
            );

            /**
             * ==========================================================
             * 🔟 DATABASE TRANSACTION
             * ==========================================================
             */
            DB::transaction(function () use (
                $user,
                $data,
                $customerId,
                $promo,
                $calc,
                $table,
                $isReserved,
                $request,
                &$order
            ) {

                /**
                 * 10.1 INSERT ORDER
                 */
                $order = Order::create([
                    'order_code'     => 'ORD-' . now()->format('Ymd-His'),
                    'outlet_id'      => $user->outlet_id,
                    'table_id'       => $table?->id,
                    'customer_id'    => $customerId,
                    'promotion_id'   => $promo?->id,
                    'cashier_id'     => $user->id,
                    'order_type'     => $data['order_type'],
                    'order_date'     => now(),
                    'status'         => 'open',
                    'subtotal'       => $calc['subtotal'],
                    'discount_total' => $calc['discount_total'],
                    'grand_total'    => $calc['grand_total'],
                    'payment_status' => 'unpaid',
                ]);

                /**
                 * 10.2 INSERT ORDER ITEMS (JIKA ADA)
                 */
                if (!$isReserved && !empty($calc['items'])) {
                    $itemsData = [];

                    foreach ($calc['items'] as $item) {
                        $itemsData[] = array_merge($item, [
                            'order_id'   => $order->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                            'cafe_tables_id' => $table->id,
                        ]);
                    }

                    $order->items()->insert($itemsData);
                }

                /**
                 * 10.3 INSERT ORDER RESERVED
                 */
                if ($isReserved) {
                    Reserved::create([
                        'order_id'       => $order->id,
                        'cafe_tables_id' => $table->id,
                        'total_dp'       => $request->nominal_dp,
                        'start_date'     => Carbon::parse($request->start_date),
                        'end_date'       => Carbon::parse($request->end_date),
                        'statu'           => 1,
                    ]);

                    $table->update([
                        'status'      => 'reserved',
                    ]);
                }

                /**
                 * 10.4 UPDATE STATUS MEJA NON-RESERVED
                 */
                if (!$isReserved && $table) {
                    $table->update([
                        'status' => 'occupied',
                    ]);
                }
            });

            /**
             * ==========================================================
             * 🔚 REDIRECT
             * ==========================================================
             */
            return redirect()
                ->route('kasir.orders.show', $order)
                ->with('success', 'Order berhasil dibuat.');
        }



    public function show(Order $order)
    {
        $this->authorizeOrderForKasir($order);

        $order->load([
            'items.menuItem',
            'customer',
            'table',
            'promotion',
            'reserved', // ⬅️ PENTING
        ]);

        return view('kasir.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $this->authorizeOrderForKasir($order);

        $order->load([
            'customer',
            'items.menuItem',
            'table',
            'promotion',
            'reservation', // ⬅️ PENTING
        ]);

        $outletId = $order->outlet_id ?? Auth::user()->outlet_id;

        // kategori & menu
        $categories = Category::with(['menuItems' => function ($q) use ($outletId) {
                $q->where('is_active', true)
                ->where('outlet_id', $outletId)
                ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $tables = CafeTable::where('outlet_id', $outletId)
            ->orderBy('name')
            ->get();

        $promos = Promotion::where('outlet_id', $outletId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // cart awal
        $initialCart = $order->items->map(function ($item) {
            return [
                'menu_item_id' => (string) $item->menu_item_id,
                'name'         => $item->menuItem->name ?? 'Menu',
                'qty'          => (int) $item->qty,
                'price'        => (float) $item->price,
            ];
        })->values()->all();

        $promosData = $promos->map(function ($p) {
            return [
                'id'         => $p->id,
                'name'       => $p->name,
                'type'       => $p->type,
                'value'      => $p->value,
                'min_amount' => $p->min_amount,
            ];
        })->values()->all();

        // ⬇️ FLAG & DATA RESERVED
        $isReserved = $order->reservation !== null;

        return view('kasir.orders.edit', compact(
            'order',
            'categories',
            'tables',
            'promos',
            'initialCart',
            'promosData',
            'isReserved'
        ));
    }

    public function editreserved(Order $order)
    {
        $this->authorizeOrderForKasir($order);

        $order->load([
            'customer',
            'items.menuItem',
            'table',
            'promotion',
            'reservation', // ⬅️ PENTING
        ]);

        $outletId = $order->outlet_id ?? Auth::user()->outlet_id;

        // kategori & menu
        $categories = Category::with(['menuItems' => function ($q) use ($outletId) {
                $q->where('is_active', true)
                ->where('outlet_id', $outletId)
                ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $tables = CafeTable::where('outlet_id', $outletId)
            ->orderBy('name')
            ->get();

        $promos = Promotion::where('outlet_id', $outletId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // cart awal
        $initialCart = $order->items->map(function ($item) {
            return [
                'menu_item_id' => (string) $item->menu_item_id,
                'name'         => $item->menuItem->name ?? 'Menu',
                'qty'          => (int) $item->qty,
                'price'        => (float) $item->price,
            ];
        })->values()->all();

        $promosData = $promos->map(function ($p) {
            return [
                'id'         => $p->id,
                'name'       => $p->name,
                'type'       => $p->type,
                'value'      => $p->value,
                'min_amount' => $p->min_amount,
            ];
        })->values()->all();

        // ⬇️ FLAG & DATA RESERVED
        $isReserved = $order->reservation !== null;

    
        
        return view('kasir.orders.editreserved', compact(
            'order',
            'categories',
            'tables',
            'promos',
            'initialCart',
            'promosData',
            'isReserved'
        ));
    }


    /**
     * UPDATE – Edit header + customer + cart + promo
     */
        // public function update(Request $request, Order $order)
            // {
                
            //     if ($request->filled('nominal_dp')) {
            //         $request->merge([
            //             'nominal_dp' => preg_replace('/[^0-9]/', '', $request->nominal_dp),
            //         ]);
            //     }
                

            //     $this->authorizeOrderForKasir($order);

            //     $data = $request->validate([
            //         'order_type'   => 'required|in:dine_in,take_away,delivery',
            //         'table_id'     => 'nullable|exists:cafe_tables,id',

            //         'customer_name'  => 'nullable|string|max:150',
            //         'customer_phone' => 'nullable|string|max:50',
            //         'customer_email' => 'nullable|email|max:150',


            //         'nominal_dp' => 'nullable|string|numeric|min:0',
            //         'start_date' => 'nullable|date',
            //         'end_date'   => 'nullable|date|after:start_date',
            //          'table_id'   => 'required|exists:cafe_tables,id',

            //         'cart'                => 'required|array|min:1',
            //         'cart.*.menu_item_id' => 'required|exists:menu_items,id',
            //         'cart.*.name'         => 'required|string',
            //         'cart.*.qty'          => 'required|integer|min:1',
            //         'cart.*.price'        => 'required|numeric|min:0',

            //         'promotion_id'        => 'nullable|exists:promotions,id',
            //     ]);

            //     // 1. Hitung kembali diskon per item
            //     $promo = !empty($data['promotion_id'])
            //         ? Promotion::find($data['promotion_id'])
            //         : null;

            //     $calc = $this->calculateItemsWithDiscount($data['cart'], $promo);

            //     DB::transaction(function () use ($order, $data, $promo, $calc) {

            //         $oldTableId   = $order->table_id;
            //         $oldOrderType = $order->order_type;

            //         // 2. Update / buat customer
            //         $customerId = $order->customer_id;

            //         if (
            //             !empty($data['customer_name']) ||
            //             !empty($data['customer_phone']) ||
            //             !empty($data['customer_email'])
            //         ) {
            //             if ($customerId && $order->customer) {
            //                 // update customer existing
            //                 $order->customer->update([
            //                     'name'  => $data['customer_name'] ?: $order->customer->name,
            //                     'phone' => $data['customer_phone'] ?: $order->customer->phone,
            //                     'email' => $data['customer_email'] ?: $order->customer->email,
            //                 ]);
            //             } else {
            //                 $customer = Customer::create([
            //                     'name'  => $data['customer_name'] ?: 'Customer',
            //                     'phone' => $data['customer_phone'] ?: null,
            //                     'email' => $data['customer_email'] ?: null,
            //                 ]);
            //                 $customerId = $customer->id;
            //             }
            //         } else {
            //             $customerId = null;
            //         }



            //         // 3. Update header order
            //         $order->update([
            //             'customer_id'    => $customerId,
            //             'order_type'     => $data['order_type'],
            //             'table_id'       => $data['order_type'] === 'dine_in' ? $data['table_id'] : null,
            //             'promotion_id'   => $promo?->id,
            //             'subtotal'       => $calc['subtotal'],
            //             'discount_total' => $calc['discount_total'],
            //             'grand_total'    => $calc['grand_total'],
            //         ]);

            //         // 4. Reset detail lama
            //         $order->items()->delete();

            //         // 5. Insert ulang detail baru dengan discount per item
            //         $itemsData = [];
            //         foreach ($calc['items'] as $itemRow) {
            //             $itemsData[] = array_merge($itemRow, [
            //                 'order_id'   => $order->id,
            //                 'created_at' => now(),
            //                 'updated_at' => now(),
            //             ]);
            //         }
            //         $order->items()->insert($itemsData);


            //         if (empty($request->is_reserved)) {
            //             // jika tidak reserved → hapus data reserved
            //             Reserved::where('order_id', $order->id)->delete();
            //         } else {
            //             // validasi minimal untuk reserved
            //             if (
            //                 !empty($request->is_reserved) &&
            //                 !empty($data['table_id']) &&
            //                 !empty($request->start_date) &&
            //                 !empty($request->end_date)
            //             ) {
            //                 Reserved::updateOrCreate(
            //                     ['order_id' => $order->id],
            //                     [
            //                         'cafe_tables_id' => $data['table_id'],
            //                         'total_dp'       => $request->nominal_dp ?? 0,
            //                         'start_date'     => $request->start_date,
            //                         'end_date'       => $request->end_date,
            //                     ]
            //                 );

            //                 CafeTable::where('id', $data['table_id'])
            //                     ->update(['status' => 'reserved']);
            //             }
            //         }

                    
            //         // 6. Update status meja jika tipe/order meja berubah
            //         if ($oldOrderType === 'dine_in' && $oldTableId && $order->order_type !== 'dine_in') {
            //             $oldTable = CafeTable::find($oldTableId);
            //             if ($oldTable) {
            //                 $oldTable->status = 'available';
            //                 $oldTable->save();
            //             }
            //         }
            //         if ($order->order_type === 'dine_in' && $order->table_id) {
            //             $newTable = CafeTable::find($order->table_id);
            //             if ($newTable) {
            //                 $newTable->status = 'occupied';
            //                 $newTable->save();
            //             }
            //         }
            //     });

            //     return redirect()
            //         ->route('kasir.orders.show', $order)
            //         ->with('success', 'Order berhasil diupdate dengan diskon per item.');
        // }

        public function update(Request $request, Order $order)
        {
           

            $this->authorizeOrderForKasir($order);

            // Bersihkan DP
            if ($request->filled('nominal_dp')) {
                $request->merge([
                    'nominal_dp' => preg_replace('/[^0-9]/', '', $request->nominal_dp),
                ]);
            }

            $data = $request->validate([
                'order_type'   => 'required|in:dine_in,take_away,delivery',
                'table_id'     => 'nullable|exists:cafe_tables,id',

                'customer_name'  => 'nullable|string|max:150',
                'customer_phone' => 'nullable|string|max:50',
                'customer_email' => 'nullable|email|max:150',

                'nominal_dp' => 'nullable|numeric|min:0',

                'cart'                => 'required|array|min:1',
                'cart.*.menu_item_id' => 'required|exists:menu_items,id',
                'cart.*.qty'          => 'required|integer|min:1',
                'cart.*.price'        => 'required|numeric|min:0',

                'promotion_id'        => 'nullable|exists:promotions,id',
            ]);

            // 🔥 HITUNG ULANG DI BACKEND (SUMBER KEBENARAN)
            $promo = !empty($data['promotion_id'])
                ? Promotion::find($data['promotion_id'])
                : null;

                
            $calc = $this->calculateItemsWithDiscount($data['cart'], $promo);
            
            DB::transaction(function () use ($order, $data, $promo, $calc, $request) {

                // Update order header
                $order->update([
                    'order_type'     => $data['order_type'],
                    'table_id'       => $data['order_type'] === 'dine_in' ? $data['table_id'] : null,
                    'promotion_id'   => $promo?->id,
                    'subtotal'       => $calc['subtotal'],
                    'discount_total' => $calc['discount_total'], // ✅ DARI BACKEND
                    'grand_total'    => $calc['grand_total'],
                ]);

                // Reset items
                $order->items()->delete();

                // Insert ulang items
                foreach ($calc['items'] as $row) {
                    $order->items()->create($row);
                }

                // Reserved
                if ($request->is_reserved) {
                    Reserved::updateOrCreate(
                        ['order_id' => $order->id], // kondisi
                        [
                            'cafe_tables_id' => $data['table_id'],
                            'total_dp'       => $request->nominal_dp ?? 0,
                            'start_date'     => $request->start_date,
                            'end_date'       => $request->end_date,
                            'status'         => 1
                        ]
                    );
                        CafeTable::where('id', $data['table_id'])
                            ->update(['status' => 'reserved']);
                } else {
                    Reserved::where('order_id', $order->id)->delete();
                }

            });

            return redirect()
                ->route('kasir.orders.show', $order)
                ->with('success', 'Order berhasil diupdate.');
        }

    /**
     * Helper: hitung diskon dari promo
     */
    
        protected function calculateItemsWithDiscount(array $cart, ?Promotion $promo): array
        {
            // 1. Hitung subtotal & line subtotal
            $subtotal = 0;
            $lineSubtotals = [];

            foreach ($cart as $index => $row) {
                $qty   = (int) $row['qty'];
                $price = (float) $row['price'];

                $lineSubtotal = $qty * $price;
                $lineSubtotals[$index] = $lineSubtotal;
                $subtotal += $lineSubtotal;
            }

            // Tidak ada item, return kosong
            if ($subtotal <= 0) {
                return [
                    'subtotal'       => 0,
                    'discount_total' => 0,
                    'grand_total'    => 0,
                    'items'          => [],
                ];
            }

            // 2. Hitung total discount dari promo (order-level)
            $discountTotal = 0;

            if ($promo) {
                if ($promo->type === 'percent') {
                    $discountTotal = $subtotal * ($promo->value / 100);
                } elseif (in_array($promo->type, ['fixed', 'nominal'])) {
                    $discountTotal = min((float) $promo->value, $subtotal);
                }
            }

            // 3. Distribusi discountTotal ke tiap item
            $discountPerItem = [];
            $allocated = 0;
            $lastIndex = array_key_last($lineSubtotals);

            if ($discountTotal > 0) {
                foreach ($lineSubtotals as $index => $lineSubtotal) {
                    if ($index === $lastIndex) {
                        // Sisa diskon taruh di item terakhir supaya pas
                        $discount = $discountTotal - $allocated;
                    } else {
                        $proporsi = $lineSubtotal / $subtotal;
                        $discount = round($discountTotal * $proporsi);
                        $allocated += $discount;
                    }
                    $discountPerItem[$index] = max(0, min($discount, $lineSubtotal));
                }
            } else {
                // Tidak ada diskon, semua 0
                foreach ($lineSubtotals as $index => $lineSubtotal) {
                    $discountPerItem[$index] = 0;
                }
            }

            // 4. Bentuk ulang array items lengkap (untuk insert ke order_items)
            $items = [];
            foreach ($cart as $index => $row) {
                $qty   = (int) $row['qty'];
                $price = (float) $row['price'];
                $lineSubtotal = $lineSubtotals[$index];
                $disc = $discountPerItem[$index] ?? 0;
                $total = $lineSubtotal - $disc;

                $items[] = [
                    'menu_item_id' => $row['menu_item_id'],
                    'qty'          => $qty,
                    'price'        => $price,
                    'discount'     => $disc,   // ✅ BENAR
                    'total'        => $total,
                ];
            }

            $grandTotal = $subtotal - $discountTotal;

            return [
                'subtotal'       => $subtotal,
                'discount_total' => $discountTotal,
                'grand_total'    => $grandTotal,
                'items'          => $items,
            ];
        }

    /**
     * Helper: pastikan kasir hanya akses order miliknya
     */
    protected function authorizeOrderForKasir(Order $order): void
    {
        $user = Auth::user();
        // if ($order->cashier_id !== $user->id) {
        //     abort(403, 'Tidak boleh mengakses order kasir lain.');
        // }
    }


    public function pay(Request $request, Order $order)
    {
        
        $this->authorizeOrderForKasir($order);

        if ($order->payment_status === 'paid') {
            return redirect()->route('kasir.orders.show', $order)
                ->with('error', 'Order ini sudah dibayar.');
        }

        $data = $request->validate([
            'payment_method' => 'required|in:cash,qris,transfer',
            'paid_amount'    => 'required|numeric|min:0',
            'reference_no'   => 'nullable|string|max:100',
            'is_reserved'   => 'nullable|string|max:100',
        ]);
       
        DB::transaction(function () use ($order, $data) {
            
            // -------- 1) buat nomor resi auto -----------
            $referenceNo = $data['reference_no'] ?: (
                'PAY-' . now()->format('YmdHis') . '-' . $order->id
            );

            // -------- 2) insert ke payments -------------
            Payment::create([
                'order_id'     => $order->id,
                'payment_method'       => $data['payment_method'],
                'amount'       => $data['paid_amount'],
                'ref_no' => $referenceNo,
                'paid_at'      => now(),
            ]);

            // -------- 3) update summary di orders -------
            $order->update([
                'status'         => 'paid',
                'payment_status' => 'paid',
            ]);

            // -------- 4) loyalty point (earn) -----------
            if ($order->customer_id) {
                $points = (int) floor($order->grand_total / 10000); // 1 poin per 10rb

                if ($points > 0) {
                    LoyaltyPoint::create([
                        'customer_id' => $order->customer_id,
                        'order_id'    => $order->id,
                        'points'      => $points,
                        'type'        => 'earn',
                        'description' => 'Pembelian order ' . $order->order_code,
                    ]);
                }
            }
            // -------- 5) kalau dine in atau reseved, meja jadi available ----
            $table = $order->table;
            if ($order->order_type === 'dine_in' && $order->table_id &&  $data['is_reserved'] !="reserved" ) {
                
                if ($table) {
                    $table->status = 'occupied';
                    $table->save();
                }
            }
            // ambil semua item dalam order
            $orderItems = OrderItem::where('order_id', $order->id)->get();

            foreach ($orderItems as $item) {

                // ambil menu
                $menu = MenuItem::find($item->menu_item_id);

                if ($menu && $menu->stock_id !== null) {

                    // ambil stock
                    $stock = StockMovement::find($menu->stock_id);

                    if ($stock) {

                        // hitung sisa stock
                        $sisaQty = $stock->qty - $item->qty;

                        // update stock
                        $stock->update([
                            'qty' => $sisaQty
                        ]);

                        if ($table) {
                            $table->status = 'occupied';
                            $table->save();
                        }
                    }
                }
            }

        });

        return redirect()
        ->route('kasir.orders.index', $order)
        ->with('success', 'Pembayaran berhasil. Struk dicetak.');
    }

    public function afterPay(Order $order)
    {
        return view('kasir.orders.after_pay', compact('order'));
    }

    public function print(Order $order)
    {
        $this->authorizeOrderForKasir($order);

        $order->load([
            'items.menuItem',
            'customer',
            'table',
            'promotion',
            'reserved',
            'outlet',
        ]);

        $pdf = Pdf::loadView('kasir.orders.print', compact('order'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream('struk-'.$order->order_code.'.pdf');
    }

    
}
