<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Store;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransfersController extends Controller
{
    public function index(Request $request)
    {
        $query = StockTransfer::query()
            ->with([
                'fromBranch',
                'toBranch',
                'sourceStore',
                'destinationStore',
                'user',
                'items.product',
            ])
            ->withCount('items');

        // Search by transfer number
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where('transfer_number', 'like', '%' . $search . '%');
        }

        // From branch
        if ($request->filled('from_branch_id')) {
            $query->where('from_branch_id', $request->from_branch_id);
        }

        // From store
        if ($request->filled('from_store_id')) {
            $query->where('from_store_id', $request->from_store_id);
        }

        // To branch
        if ($request->filled('to_branch_id')) {
            $query->where('to_branch_id', $request->to_branch_id);
        }

        // To store
        if ($request->filled('to_store_id')) {
            $query->where('to_store_id', $request->to_store_id);
        }

        // Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date from
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // Date to
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        /*
        |--------------------------------------------------------------------------
        | Summary counts
        |--------------------------------------------------------------------------
        | Clone the filtered query before pagination/execution.
        */
        $totalTransfers = (clone $query)->count();

        $pendingTransfers = (clone $query)
            ->where('status', 'pending')
            ->count();

        $inTransitTransfers = (clone $query)
            ->where('status', 'in_transit')
            ->count();

        $completedTransfers = (clone $query)
            ->where('status', 'completed')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Transfers
        |--------------------------------------------------------------------------
        */
        $transfers = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Filter dropdown data
        |--------------------------------------------------------------------------
        */
        $branches = Branch::orderBy('name')->get();

        $stores = Store::orderBy('name')->get();

        return view('transfers.index', compact(
            'transfers',
            'branches',
            'stores',
            'totalTransfers',
            'pendingTransfers',
            'inTransitTransfers',
            'completedTransfers'
        ));
    }

    public function create()
    {
        $sales = Sale::all();
        $branches = Branch::all();
        $stores = Store::all();

        $products = Product::where('is_active', true)
            ->with(['inventories' => function ($query) {
                $query->select(
                    'id',
                    'product_id',
                    'store_id',
                    'quantity'
                );
            }])
            ->get()
            ->map(function ($product) {

                $product->stock_by_store = $product->inventories
                    ->groupBy('store_id')
                    ->map(function ($inventories) {
                        return $inventories->sum('quantity');
                    })
                    ->toArray();

                return $product;
            });

        return view('transfers.create', compact(
            'sales',
            'branches',
            'stores',
            'products'
        ));
    }

    public function store(Request $request)
    {
        // dd($request);
        $validated = $request->validate([
            'from_store_id' => [
                'required',
                'integer',
                'exists:stores,id',
                'different:to_store_id',
            ],

            'to_store_id' => [
                'required',
                'integer',
                'exists:stores,id',
            ],

            'transfer_date' => [
                'required',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        DB::transaction(function () use ($validated) {

            $transfer = StockTransfer::create([
                'transfer_number' => null,

                'from_branch_id' => Store::findOrFail(
                    $validated['from_store_id']
                )->branch_id,

                'to_branch_id' => Store::findOrFail(
                    $validated['to_store_id']
                )->branch_id,

                'from_store_id' => $validated['from_store_id'],
                'to_store_id' => $validated['to_store_id'],

                'transfered_at' => $validated['transfer_date'],

                'notes' => $validated['notes'] ?? null,

                'status' => 'completed',

                'user_id' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {

                // Lock source inventory row
                $sourceInventory = Inventory::where('store_id', $validated['from_store_id'])
                    ->where('product_id', $item['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$sourceInventory) {
                    abort(
                        422,
                        'No inventory record exists for the selected product at the source store.'
                    );
                }

                if ($sourceInventory->quantity < $item['quantity']) {
                    abort(
                        422,
                        "Insufficient stock for {$sourceInventory->product->name}. Available: {$sourceInventory->quantity}."
                    );
                }

                // Create transfer item
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);

                // Remove stock from source store
                $sourceInventory->decrement(
                    'quantity',
                    $item['quantity']
                );

                // Lock destination inventory if it exists
                $destinationInventory = Inventory::where('store_id', $validated['to_store_id'])
                    ->where('product_id', $item['product_id'])
                    ->lockForUpdate()
                    ->first();

                if ($destinationInventory) {
                    $destinationInventory->increment(
                        'quantity',
                        $item['quantity']
                    );
                } else {
                    Inventory::create([
                        'store_id' => $validated['to_store_id'],
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }
            }
        });

        return redirect()
            ->route('transfers.index')
            ->with('success', 'Stock transfer completed successfully.');
    }

    public function show(StockTransfer $transfer)
    {
        $transfer->load([
            'fromBranch',
            'toBranch',
            'user',
            'items.product',
        ]);

        return view('transfers.show', compact('transfer'));
    }

    public function edit(StockTransfer $transfer)
    {
        $stores = Store::all();
        $products = Product::where('is_active', true)->get();

        $transfer->load('items.product');

        return view('transfers.edit', compact(
            'transfer',
            'stores',
            'products'
        ));
    }

    public function update(Request $request, StockTransfer $transfer)
    {
        $validated = $request->validate([
            'from_store_id' => [
                'required',
                'integer',
                'exists:stores,id',
                'different:to_store_id',
            ],
            'to_store_id' => [
                'required',
                'integer',
                'exists:stores,id',
            ],
            'transfer_date' => [
                'required',
                'date',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $transfer->update($validated);

        return redirect()
            ->route('transfers.show', $transfer)
            ->with('success', 'Transfer updated successfully.');
    }

    public function destroy(StockTransfer $transfer)
    {
        DB::transaction(function () use ($transfer) {

            $transfer->load('items');

            foreach ($transfer->items as $item) {

                // Return stock to source store
                Inventory::where('store_id', $transfer->from_store_id)
                    ->where('product_id', $item->product_id)
                    ->increment('quantity', $item->quantity);

                // Remove transferred stock from destination store
                Inventory::where('store_id', $transfer->to_store_id)
                    ->where('product_id', $item->product_id)
                    ->decrement('quantity', $item->quantity);
            }

            $transfer->items()->delete();
            $transfer->delete();
        });

        return redirect()
            ->route('transfers.index')
            ->with('success', 'Transfer deleted successfully.');
    }
}