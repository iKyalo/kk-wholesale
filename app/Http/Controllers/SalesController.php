<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesController extends Controller
{

    public function index()
    {
        $today        = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth   = Carbon::now()->endOfMonth();

        // Base query: completed sales only
        $completedSales = Sale::where('status', 'completed');

        // Sales today
        $salesToday = (clone $completedSales)
            ->whereDate('sold_at', $today)
            ->sum('total');

        // Sales this month
        $salesThisMonth = (clone $completedSales)
            ->whereBetween('sold_at', [$startOfMonth, $endOfMonth])
            ->sum('total');

        // Total completed transactions
        $totalTransactions = (clone $completedSales)->count();

        // Average completed sale value
        $averageSaleValue = (clone $completedSales)->avg('total') ?? 0;

        // Retrieve sales for the table
        $sales = Sale::with(['branch', 'store', 'user'])
            ->latest('sold_at')
            ->get();

        return view('sales.index', compact(
            'sales',
            'salesToday',
            'salesThisMonth',
            'totalTransactions',
            'averageSaleValue'
        ));
    }

    public function create()
    {
        $sales    = Sale::all();
        $branches = Branch::all();
        $stores   = Store::all();
        $products = Product::all();

        return view('sales.create', compact('sales', 'branches', 'stores', 'products'));
    }

    public function byStore(Request $request)
    {
        $branches = Branch::orderBy('name')->get();

        $branchId = $request->input('branch_id');
        $storeId  = $request->input('store_id');

        // Only show stores belonging to the selected branch.
        $stores = Store::when($branchId, function ($query) use ($branchId) {
            $query->where('branch_id', $branchId);
        })
            ->orderBy('name')
            ->get();

        $store = null;
        $sales = collect();

        $storeTotalSales       = 0;
        $storeTransactionCount = 0;
        $storeAverageSaleValue = 0;

        if ($storeId) {

            $store = Store::with('branch')->find($storeId);

            if ($store) {

                $salesQuery = Sale::with(['cashier', 'items'])
                    ->where('store_id', $storeId);

                if ($request->filled('date_from')) {
                    $salesQuery->whereDate(
                        'created_at',
                        '>=',
                        $request->date_from
                    );
                }

                if ($request->filled('date_to')) {
                    $salesQuery->whereDate(
                        'created_at',
                        '<=',
                        $request->date_to
                    );
                }

                $storeTotalSales = (clone $salesQuery)->sum('total');

                $storeTransactionCount = (clone $salesQuery)->count();

                $storeAverageSaleValue = $storeTransactionCount > 0
                    ? $storeTotalSales / $storeTransactionCount
                    : 0;

                $sales = $salesQuery
                    ->latest()
                    ->paginate(20)
                    ->withQueryString();
            }
        }

        $products = Product::orderBy('name')->get();

        return view('sales.by-store', compact(
            'sales',
            'branches',
            'stores',
            'products',
            'store',
            'storeTotalSales',
            'storeTransactionCount',
            'storeAverageSaleValue'
        ));
    }

    public function productStock(Request $request)
    {
        $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
        ]);

        $stock = Inventory::where('store_id', $request->store_id)
            ->get()
            ->keyBy('product_id')
            ->map(function ($inventory) {
                return (int) $inventory->quantity;
            });

        return response()->json($stock);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id'          => [
                'required',
                'integer',
                'exists:branches,id',
            ],

            'store_id'           => [
                'required',
                'integer',
                'exists:stores,id',
            ],

            'payment_method'     => [
                'required',
                'in:cash,mpesa,card,bank_transfer',
            ],

            'sale_date'          => [
                'required',
                'date',
            ],

            'customer_name'      => [
                'nullable',
                'string',
                'max:255',
            ],

            'customer_phone'     => [
                'nullable',
                'string',
                'max:30',
            ],

            'items'              => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'items.*.quantity'   => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.discount'   => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        // Make sure the selected store belongs to the selected branch.
        $store = Store::where('id', $validated['store_id'])
            ->where('branch_id', $validated['branch_id'])
            ->first();

        if (! $store) {
            return back()
                ->withInput()
                ->withErrors([
                    'store_id' => 'The selected store does not belong to the selected branch.',
                ]);
        }

        try {
            $sale = DB::transaction(function () use ($validated) {

                $saleSubtotal = 0;
                $saleDiscount = 0;

                /*
                * Prevent the same product from appearing multiple times
                * in the submitted sale.
                */
                $items = collect($validated['items'])
                    ->groupBy('product_id')
                    ->map(function ($productItems) {
                        return [
                            'product_id' => $productItems->first()['product_id'],
                            'quantity'   => $productItems->sum('quantity'),
                            'discount'   => $productItems->sum(function ($item) {
                                return (float) ($item['discount'] ?? 0);
                            }),
                        ];
                    })
                    ->values();

                $itemsToCreate = [];

                /*
                * Validate stock and calculate totals.
                */
                foreach ($items as $item) {

                    $product = Product::findOrFail($item['product_id']);

                    $inventory = Inventory::where('store_id', $validated['store_id'])
                        ->where('product_id', $product->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $inventory) {
                        throw new \Exception(
                            "No inventory record exists for {$product->name} in the selected store."
                        );
                    }

                    $availableStock = (int) $inventory->quantity;
                    $quantity       = (int) $item['quantity'];

                    if ($quantity > $availableStock) {
                        throw new \Exception(
                            "Insufficient stock for {$product->name}. "
                            . "Available: {$availableStock}, requested: {$quantity}."
                        );
                    }

                    $unitPrice = (float) $product->selling_price;
                    $discount  = (float) ($item['discount'] ?? 0);

                    $lineSubtotal = ($unitPrice * $quantity) - $discount;

                    if ($lineSubtotal < 0) {
                        throw new \Exception(
                            "Discount for {$product->name} cannot exceed the line value."
                        );
                    }

                    $saleSubtotal += $unitPrice * $quantity;
                    $saleDiscount += $discount;

                    $itemsToCreate[] = [
                        'product_id'   => $product->id,
                        'quantity'     => $quantity,
                        'unit_price'   => $unitPrice,
                        'discount'     => $discount,
                        'subtotal'     => $lineSubtotal,
                        'inventory_id' => $inventory->id,
                    ];
                }

                /*
                * Tax is currently zero.
                */
                $tax = 0;

                $total = $saleSubtotal - $saleDiscount + $tax;

                /*
                * Generate a unique sale number.
                */
                do {
                    $saleNumber = 'SALE-'
                    . now()->format('YmdHis')
                    . '-'
                    . strtoupper(Str::random(5));
                } while (Sale::where('sale_number', $saleNumber)->exists());

                /*
                * Create sale.
                */
                $sale = Sale::create([
                    'sale_number'    => $saleNumber,
                    'branch_id'      => $validated['branch_id'],
                    'store_id'       => $validated['store_id'],
                    'user_id'        => Auth::id(),

                    'subtotal'       => $saleSubtotal,
                    'discount'       => $saleDiscount,
                    'tax'            => $tax,
                    'total'          => $total,

                    'payment_method' => $validated['payment_method'],
                    'status'         => 'completed',
                    'sold_at'        => $validated['sale_date'],
                ]);

                /*
                * Create sale items and deduct inventory ONCE.
                */
                foreach ($itemsToCreate as $item) {

                    $sale->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount'   => $item['discount'],
                        'subtotal'   => $item['subtotal'],
                    ]);

                    /*
                    * The inventory row was already locked during validation.
                    * Fetch it again by ID to make the deduction explicit.
                    */
                    $inventory = Inventory::where('id', $item['inventory_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    /*
                    * Deduct stock exactly once.
                    */
                    $inventory->decrement(
                        'quantity',
                        $item['quantity']
                    );

                    /*
                    * Record the stock movement.
                    */
                    StockMovement::create([
                        'product_id'     => $item['product_id'],
                        'store_id'       => $validated['store_id'],
                        'user_id'        => Auth::id(),

                        'type'           => 'sale',
                        'quantity'       => -$item['quantity'],

                        'reference_type' => Sale::class,
                        'reference_id'   => $sale->id,

                        'notes'          => "Stock deducted for {$sale->sale_number}",
                    ]);
                }

                return $sale;
            });

            return redirect()
                ->route('sales.index')
                ->with('success', 'Sale completed successfully.');

        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->withErrors([
                    'sale' => $e->getMessage(),
                ]);
        }
    }

    public function show(Sale $sale)
    {
        $sale->load([
            'store.branch',
            'cashier',
            'items.product',
        ]);

        return view('sales.show', compact('sale'));
    }

}
