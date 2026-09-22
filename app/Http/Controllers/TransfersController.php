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
use Illuminate\Support\Facades\DB;

class TransfersController extends Controller
{
    public function index()
    {
        $transfers = StockTransfer::with([
            'fromStore',
            'toStore',
            'items.product',
        ])->latest()->get();

        return view('transfers.index', compact('transfers'));
    }

    public function create()
    {
        $sales = Sale::all();
        $branches = Branch::all();
        $stores = Store::all();
        $products = Product::where('is_active', true)->get();

        return view('transfers.create', compact(
            'sales',
            'branches',
            'stores',
            'products'
        ));
    }

    public function store(Request $request)
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
                'from_store_id' => $validated['from_store_id'],
                'to_store_id' => $validated['to_store_id'],
                'transfer_date' => $validated['transfer_date'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'completed',
            ]);

            foreach ($validated['items'] as $item) {

                $inventory = Inventory::where('store_id', $validated['from_store_id'])
                    ->where('product_id', $item['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$inventory || $inventory->quantity < $item['quantity']) {
                    abort(
                        422,
                        'Insufficient stock for the selected product.'
                    );
                }

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);

                // Remove stock from source store
                $inventory->decrement('quantity', $item['quantity']);

                // Add stock to destination store
                Inventory::updateOrCreate(
                    [
                        'store_id' => $validated['to_store_id'],
                        'product_id' => $item['product_id'],
                    ],
                    [
                        'quantity' => DB::raw(
                            'quantity + ' . (int) $item['quantity']
                        ),
                    ]
                );
            }
        });

        return redirect()
            ->route('transfers.index')
            ->with('success', 'Stock transfer completed successfully.');
    }

    public function show(StockTransfer $transfer)
    {
        $transfer->load([
            'fromStore',
            'toStore',
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