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
            ->where('status', 'open')
            ->orderByDesc('order_date')
            ->get();

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
        $user = Auth::user();

        $data = $request->validate([
            'order_type'   => 'required|in:dine_in,take_away,delivery',
            'table_id'     => 'nullable|exists:cafe_tables,id',

            'customer_name'  => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:150',

            'cart'                => 'required|array|min:1',
            'cart.*.menu_item_id' => 'required|exists:menu_items,id',
            'cart.*.name'         => 'required|string',
            'cart.*.qty'          => 'required|integer|min:1',
            'cart.*.price'        => 'required|numeric|min:0',

            'promotion_id'        => 'nullable|exists:promotions,id',
        ]);

        // 1. Customer (INSERT baru, supaya pasti ada)
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

        // 2. Ambil promo (kalau ada)
        $promo = !empty($data['promotion_id'])
            ? Promotion::find($data['promotion_id'])
            : null;

        // 3. Hitung subtotal, diskon, dan items (dengan discount per item)
        $calc = $this->calculateItemsWithDiscount($data['cart'], $promo);

        DB::transaction(function () use ($user, $data, $customerId, $promo, $calc, &$order) {

            // 4. Insert order (header)
            $order = Order::create([
                'order_code'       => 'ORD-' . now()->format('Ymd-His'),
                'outlet_id'        => $user->outlet_id,
                'table_id'         => $data['order_type'] === 'dine_in' ? $data['table_id'] : null,
                'customer_id'      => $customerId,
                'promotion_id'     => $promo?->id,
                'cashier_id'       => $user->id,
                'order_type'       => $data['order_type'],
                'order_date'       => now(),
                'status'           => 'open',
                'subtotal'         => $calc['subtotal'],
                'discount_total'   => $calc['discount_total'],
                'grand_total'      => $calc['grand_total'],
                'payment_status'   => 'unpaid',
                
               
            ]);

            // 5. Insert detail ke order_items (diskon sudah dihitung)
            $itemsData = [];
            foreach ($calc['items'] as $itemRow) {
                $itemsData[] = array_merge($itemRow, [
                    'order_id'   => $order->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            // boleh pakai insert() atau createMany()
            $order->items()->insert($itemsData);

            // 6. Update status meja kalau dine-in
            if ($order->order_type === 'dine_in' && $order->table_id) {
                $table = CafeTable::find($order->table_id);
                if ($table) {
                    $table->status = 'occupied';
                    $table->save();
                }
            }
        });

        return redirect()
            // ->route('kasir.orders.index')
            ->route('kasir.orders.show', $order)
            ->with('success', 'Order berhasil dibuat dengan diskon per item.');
    }


    public function show(Order $order)
    {
        $this->authorizeOrderForKasir($order);

        $order->load(['items.menuItem', 'customer', 'table', 'promotion']);

        return view('kasir.orders.show', compact('order'));
    }

    /**
     * FORM EDIT – Edit header + customer + cart
     */
    // public function edit(Order $order)
    // {
    //     $this->authorizeOrderForKasir($order);

    //     if ($order->status !== 'open') {
    //         return redirect()->route('kasir.orders.show', $order)
    //             ->with('error', 'Order sudah tidak bisa diubah (bukan status OPEN).');
    //     }

    //     $user = Auth::user();

    //     $tables = CafeTable::where('outlet_id', $user->outlet_id ?? null)
    //         ->orderBy('name')
    //         ->get();

    //     $order->load(['customer', 'items.menuItem', 'table', 'promotion']);

    //     $categories = Category::with(['menuItems' => function ($q) {
    //             $q->where('is_active', 1)->orderBy('name');
    //         }])
    //         ->orderBy('name')
    //         ->get();

    //     $today = now()->toDateString();
    //     $promotions = Promotion::where('is_active', 1)
    //         ->whereDate('start_date', '<=', $today)
    //         ->whereDate('end_date', '>=', $today)
    //         ->where(function ($q) use ($user) {
    //             $q->whereNull('outlet_id')
    //               ->orWhere('outlet_id', $user->outlet_id);
    //         })
    //         ->orderBy('name')
    //         ->get();

    //     $promos = $promotions->map(fn ($p) => [
    //         'id'         => $p->id,
    //         'type'       => $p->type,
    //         'value'      => $p->value,
    //         'min_amount' => $p->min_amount,
    //     ])->values();

    //     return view('kasir.orders.edit', compact(
    //         'order',
    //         'tables',
    //         'categories',
    //         'promotions',
    //         'promos'
    //     ));
    // }


    public function edit(Order $order)
    {
        $this->authorizeOrderForKasir($order);

        $order->load([
            'customer',
            'items.menuItem',
            'table',
            'promotion',
        ]);

        $outletId = $order->outlet_id ?? Auth::user()->outlet_id;

        // Ambil kategori + menu per kategori untuk outlet ini
        $categories = Category::with(['menuItems' => function ($q) use ($outletId) {
                $q->where('is_active', true)
                ->where('outlet_id', $outletId)
                ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        // Meja dine-in
        $tables = CafeTable::where('outlet_id', $outletId)
            ->orderBy('name')
            ->get();

        // Promo aktif
        $promos = Promotion::where('outlet_id', $outletId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Data cart & promo untuk JS (supaya tidak ribet di Blade)
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

        return view('kasir.orders.edit', compact(
            'order',
            'categories',
            'tables',
            'promos',
            'initialCart',
            'promosData'
        ));
    }

    /**
     * UPDATE – Edit header + customer + cart + promo
     */
    public function update(Request $request, Order $order)
    {
        $this->authorizeOrderForKasir($order);

        $data = $request->validate([
            'order_type'   => 'required|in:dine_in,take_away,delivery',
            'table_id'     => 'nullable|exists:cafe_tables,id',

            'customer_name'  => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:150',

            'cart'                => 'required|array|min:1',
            'cart.*.menu_item_id' => 'required|exists:menu_items,id',
            'cart.*.name'         => 'required|string',
            'cart.*.qty'          => 'required|integer|min:1',
            'cart.*.price'        => 'required|numeric|min:0',

            'promotion_id'        => 'nullable|exists:promotions,id',
        ]);

        // 1. Hitung kembali diskon per item
        $promo = !empty($data['promotion_id'])
            ? Promotion::find($data['promotion_id'])
            : null;

        $calc = $this->calculateItemsWithDiscount($data['cart'], $promo);

        DB::transaction(function () use ($order, $data, $promo, $calc) {

            $oldTableId   = $order->table_id;
            $oldOrderType = $order->order_type;

            // 2. Update / buat customer
            $customerId = $order->customer_id;

            if (
                !empty($data['customer_name']) ||
                !empty($data['customer_phone']) ||
                !empty($data['customer_email'])
            ) {
                if ($customerId && $order->customer) {
                    // update customer existing
                    $order->customer->update([
                        'name'  => $data['customer_name'] ?: $order->customer->name,
                        'phone' => $data['customer_phone'] ?: $order->customer->phone,
                        'email' => $data['customer_email'] ?: $order->customer->email,
                    ]);
                } else {
                    $customer = Customer::create([
                        'name'  => $data['customer_name'] ?: 'Customer',
                        'phone' => $data['customer_phone'] ?: null,
                        'email' => $data['customer_email'] ?: null,
                    ]);
                    $customerId = $customer->id;
                }
            } else {
                $customerId = null;
            }

            // 3. Update header order
            $order->update([
                'customer_id'    => $customerId,
                'order_type'     => $data['order_type'],
                'table_id'       => $data['order_type'] === 'dine_in' ? $data['table_id'] : null,
                'promotion_id'   => $promo?->id,
                'subtotal'       => $calc['subtotal'],
                'discount_total' => $calc['discount_total'],
                'grand_total'    => $calc['grand_total'],
            ]);

            // 4. Reset detail lama
            $order->items()->delete();

            // 5. Insert ulang detail baru dengan discount per item
            $itemsData = [];
            foreach ($calc['items'] as $itemRow) {
                $itemsData[] = array_merge($itemRow, [
                    'order_id'   => $order->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $order->items()->insert($itemsData);

            // 6. Update status meja jika tipe/order meja berubah
            if ($oldOrderType === 'dine_in' && $oldTableId && $order->order_type !== 'dine_in') {
                $oldTable = CafeTable::find($oldTableId);
                if ($oldTable) {
                    $oldTable->status = 'available';
                    $oldTable->save();
                }
            }
            if ($order->order_type === 'dine_in' && $order->table_id) {
                $newTable = CafeTable::find($order->table_id);
                if ($newTable) {
                    $newTable->status = 'occupied';
                    $newTable->save();
                }
            }
        });

        return redirect()
            ->route('kasir.orders.show', $order)
            ->with('success', 'Order berhasil diupdate dengan diskon per item.');
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
            } elseif ($promo->type === 'fixed') {
                $discountTotal = min($promo->value, $subtotal);
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
                'discount'     => $discountTotal,
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
        if ($order->cashier_id !== $user->id) {
            abort(403, 'Tidak boleh mengakses order kasir lain.');
        }
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

            // -------- 5) kalau dine in, meja jadi available ----
            if ($order->order_type === 'dine_in' && $order->table_id) {
                $table = $order->table;
                if ($table) {
                    $table->status = 'available';
                    $table->save();
                }
            }
        });

        return redirect()
            ->route('kasir.orders.show', $order)
            ->with('success', 'Pembayaran berhasil disimpan. Poin loyalty ditambahkan.');
    }

}
