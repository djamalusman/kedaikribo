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
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class OrderController extends Controller
{
    // public function index(Request $request)
    // {
        
    //     $user  = Auth::user();
    //     $today = now()->toDateString();

    //     // Filter periode untuk RIWAYAT
    //     $from = $request->input('from_date', $today);
    //     $to   = $request->input('to_date', $today);

    //     // -------- OPEN BILL (status open) --------
    //     $openOrders = Order::with(['customer', 'table','payments'])
    //         ->where('cashier_id', $user->id)
    //         // ->where('status', 'open')
    //         ->orderByDesc('order_date')
    //         ->get();
    //     // dd($openOrders);
    //     // -------- RIWAYAT (status paid) + filter tanggal --------
    //     $historyQuery = Order::with(['customer', 'table','payments'])
    //         ->where('cashier_id', $user->id)
    //         ->where('status', 'paid')
    //         ->whereBetween('order_date', [
    //             $from . ' 00:00:00',
    //             $to   . ' 23:59:59',
    //         ]);

    //     $historyOrders = $historyQuery
    //         ->orderByDesc('order_date')
    //         ->paginate(20)
    //         ->withQueryString(); // biar from/to tetap di query string

    //     $totalTransactions = (clone $historyQuery)->count();
    //     $totalRevenue      = (clone $historyQuery)->sum('grand_total');

    //     // Tab aktif (bisa dikirim via ?tab=history)
    //     $activeTab = $request->input('tab', 'open');

    //     return view('kasir.orders.index', compact(
    //         'openOrders',
    //         'historyOrders',
    //         'from',
    //         'to',
    //         'totalTransactions',
    //         'totalRevenue',
    //         'activeTab',
    //     ));
    // }


    public function index(Request $request)
    {
        $today = now()->toDateString();

        return view('kasir.orders.index', [
            'from'      => $request->from_date ?? $today,
            'to'        => $request->to_date ?? $today,
            'activeTab' => $request->tab ?? 'open',
        ]);
    }

        /**
     * OPEN BILL (SERVER SIDE)
     */
    public function openData(Request $request)
    {
        $user = Auth::user();

        $query = Order::with(['customer', 'table'])
            ->where('cashier_id', $user->id)
            ->orderByDesc('order_date');

        return DataTables::of($query)
            ->addColumn('tanggal', fn ($o) =>
                optional($o->order_date)->format('d/m/Y H:i')
            )
            ->addColumn('kode', fn ($o) => $o->order_code)
            ->addColumn('customer', function ($o) {
                if (!$o->customer) return '-';
                $phone = $o->customer->phone
                    ? "<br><small class='text-muted'>{$o->customer->phone}</small>"
                    : '';
                return $o->customer->name . $phone;
            })
            ->addColumn('tipe_meja', function ($o) {
                $type = strtoupper(str_replace('_', ' ', $o->order_type));
                return $o->table
                    ? "$type<br><small class='text-muted'>Meja: {$o->table->name}</small>"
                    : $type;
            })
            ->addColumn('total', fn ($o) =>
                rupiah($o->grand_total ?? $o->subtotal)
            )
            ->addColumn('status', fn ($o) => strtoupper($o->status))
            ->addColumn('aksi', function ($o) {
                $detail = route('kasir.orders.show', $o);
                $edit   = route('kasir.orders.edit', $o);

                // 🔥 IF / ELSE PINDAH KE CONTROLLER
                if ($o->status === 'paid') {
                    $print = route('kasir.orders.print', $o);
                    return "
                        <a href='$detail' class='btn btn-sm btn-outline-primary'>Detail</a>
                        <a href='$print' target='_blank'
                           class='btn btn-sm btn-outline-secondary'>Cetak</a>
                    ";
                }

                return "
                    <a href='$detail' class='btn btn-sm btn-outline-primary'>Detail / Bayar</a>
                    <a href='$edit' class='btn btn-sm btn-outline-secondary'>Edit</a>
                ";
            })
            ->rawColumns(['customer', 'tipe_meja', 'aksi'])
            ->make(true);
    }


    /**
     * RIWAYAT (SERVER SIDE)
     */
    public function historyData(Request $request)
    {
        $user = Auth::user();

        $query = Order::with(['customer', 'table', 'payments'])
            ->where('cashier_id', $user->id)
            ->where('status', 'paid');

        // 🔥 FILTER DARI FORM
        if ($request->from_date && $request->to_date) {
            $query->whereBetween('order_date', [
                $request->from_date . ' 00:00:00',
                $request->to_date   . ' 23:59:59',
            ]);
        }

        return DataTables::of($query)
            ->addColumn('tanggal', fn ($o) =>
                optional($o->order_date)->format('d/m/Y H:i')
            )
            ->addColumn('kode', fn ($o) => $o->order_code)
            ->addColumn('customer', function ($o) {
                if (!$o->customer) return '-';
                $phone = $o->customer->phone
                    ? "<br><small class='text-muted'>{$o->customer->phone}</small>"
                    : '';
                return $o->customer->name . $phone;
            })
            ->addColumn('tipe_meja', function ($o) {
                $type = strtoupper(str_replace('_', ' ', $o->order_type));
                return $o->table
                    ? "$type<br><small class='text-muted'>Meja: {$o->table->name}</small>"
                    : $type;
            })
            ->addColumn('metode', fn ($o) =>
                strtoupper($o->payments->first()->payment_method ?? '-')
            )
            ->addColumn('total', fn ($o) => rupiah($o->grand_total))
            ->addColumn('aksi', function ($o) {
                $detail = route('kasir.orders.show', $o);
                return "<a href='$detail' class='btn btn-sm btn-outline-primary'>
                            Detail / Bayar
                        </a>";
            })
            ->rawColumns(['customer', 'tipe_meja', 'aksi'])
            ->make(true);
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
                            'cafe_tables_id' => $data['order_type'] === 'dine_in' ? $table->id : null,
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
            'payments',
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
            return response()->json([
                'success' => false,
                'message' => 'Order ini sudah dibayar'
            ], 400);
        }

        $data = $request->validate([
            'payment_method' => 'required|in:cash,qris',
            'paid_amount'    => 'required|numeric|min:0',
            'reference_no'   => 'nullable|string|max:100',
            'is_reserved'    => 'nullable|string',
        ]);

        DB::transaction(function () use ($order, $data) {

            $referenceNo = $data['reference_no'] ?? (
                'PAY-' . now()->format('YmdHis') . '-' . $order->id
            );

            Payment::create([
                'order_id'       => $order->id,
                'payment_method' => $data['payment_method'],
                'amount'         => $data['paid_amount'],
                'ref_no'         => $referenceNo,
                'paid_at'        => now(),
            ]);

            $order->update([
                'status'         => 'paid',
                'payment_status' => 'paid',
            ]);
        });

        // 🔥 INI YANG PENTING
        return response()->json([
            'success'   => true,
            'message'    => 'Pembayaran berhasil. Struk dicetak',
            'print_url'=> route('kasir.orders.print', $order),
        ]);
    }



    public function afterPay(Order $order)
    {
        return view('kasir.orders.after_pay', compact('order'));
    }

    


    public function printIndex(Order $order)
    {
        $order->load([
            'items.menuItem',
            'customer',
            'table',
            'promotion',
            'reserved',
            'outlet',
        ]);

        // ================= WIDTH =================
        $paperWidth = 164; // 58mm

        // ================= HEIGHT =================
        $baseHeight = 180; // header + logo + footer
        $lineHeight = 16;  // tinggi 1 baris teks

        $lines = 0;

        foreach ($order->items as $item) {
            // nama menu bisa 1–2 baris
            $nameLines = ceil(strlen($item->menuItem->name) / 18);
            $lines += max(1, $nameLines);
        }

        // subtotal, diskon, total, metode
        $lines += 8;

        $paperHeight = $baseHeight + ($lines * $lineHeight);

        // safety net 🔥
        if ($paperHeight < 400) {
            $paperHeight = 400;
        }

        $pdf = Pdf::loadView('kasir.orders.printindex', compact('order'))
            ->setPaper([0, 0, $paperWidth, $paperHeight]);

        return $pdf->stream('struk-'.$order->order_code.'.pdf');
    }




    public function print(Order $order)
    {
        // ===============================
        // 1️⃣ VALIDASI
        // ===============================
        if ($order->payment_status !== 'paid') {
            abort(403, 'Order belum dibayar');
        }

        // ===============================
        // 2️⃣ LOAD RELATION
        // ===============================
        $order->load([
            'items.menuItem',
            'customer',
            'table',
            'promotion',
            'reserved',
            'outlet',
            'payments',
        ]);

        // ===============================
        // 3️⃣ LOGO → BASE64 (WAJIB)
        // ===============================
        $logoPath = public_path('assets/compiled/svg/logov1.png');

        if (!file_exists($logoPath)) {
            abort(500, 'File logo tidak ditemukan');
        }

        $logoBase64 = base64_encode(file_get_contents($logoPath));

        // ===============================
        // 4️⃣ HITUNG TINGGI KERTAS (AUTO)
        // ===============================
        $paperWidth = 164; // 58mm
        $baseHeight = 220;
        $lineHeight = 16;
        $lines = 0;

        foreach ($order->items as $item) {
            $nameLines = ceil(strlen($item->menuItem->name) / 18);
            $lines += max(1, $nameLines);
        }

        $lines += 12;
        $paperHeight = max(400, $baseHeight + ($lines * $lineHeight));

        // ===============================
        // 5️⃣ GENERATE PDF
        // ===============================
        $pdf = Pdf::loadView(
            'kasir.orders.print',
            compact('order', 'logoBase64')
        )->setPaper([0, 0, $paperWidth, $paperHeight]);

        // ===============================
        // 6️⃣ SIMPAN PDF KE PUBLIC STORAGE
        // ===============================
        $fileName = 'struk-' . $order->order_code . '.pdf';
        $documentRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
        $storagePath  = $documentRoot . '/storage/struk';

        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $fullPath = $storagePath . '/' . $fileName;
        file_put_contents($fullPath, $pdf->output());

        // ===============================
        // 7️⃣ REDIRECT KE PDF
        // ===============================
        return redirect()->to(asset('storage/struk/' . $fileName));
    }
    
}