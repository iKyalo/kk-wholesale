<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class InventoryController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        $stores = Store::all();
        $inventories = Inventory::all();
        
        return view('inventory.index', compact('branches', 'stores', 'inventories'));
    }

    public function show(Inventory $inventory) 
    {
        $stockMovements = [];

        return view('inventory.show', compact('inventory', 'stockMovements'));
    }

    public function editStock() 
    {
        $branches = Branch::all();
        $stores = Store::all();
        $inventories = Inventory::all();
        $products = Product::all();

        return view('inventory.update-stock', compact('branches', 'stores', 'inventories', 'products'));
    }

    public function updateStock(Request $request)
    {
        $validated = $request->validate([
            'inventory_id' => ['nullable', 'integer', 'exists:inventories,id'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'adjustment_type' => ['required', 'in:stock_in,stock_out,set_exact'],
            'quantity' => ['required', 'integer', 'min:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        // Make sure the selected store belongs to the selected branch.
        $store = Store::where('id', $validated['store_id'])
            ->where('branch_id', $validated['branch_id'])
            ->first();

        if (!$store) {
            throw ValidationException::withMessages([
                'store_id' => 'The selected store does not belong to the selected branch.',
            ]);
        }

        return DB::transaction(function () use ($validated, $request) {

            /*
            * Find the inventory record.
            *
            * If inventory_id was supplied, use it.
            * Otherwise find the inventory record using the
            * branch, store and product combination.
            */
            if (!empty($validated['inventory_id'])) {

                $inventory = Inventory::where('id', $validated['inventory_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // Prevent changing the inventory record to another location/product.
                if (
                    $inventory->branch_id != $validated['branch_id'] ||
                    $inventory->store_id != $validated['store_id'] ||
                    $inventory->product_id != $validated['product_id']
                ) {
                    throw ValidationException::withMessages([
                        'product_id' => 'The selected inventory record does not match the selected branch, store, and product.',
                    ]);
                }

            } else {

                $inventory = Inventory::where('branch_id', $validated['branch_id'])
                    ->where('store_id', $validated['store_id'])
                    ->where('product_id', $validated['product_id'])
                    ->lockForUpdate()
                    ->first();

                // Create inventory record if one does not exist.
                if (!$inventory) {
                    $inventory = Inventory::create([
                        'branch_id' => $validated['branch_id'],
                        'store_id' => $validated['store_id'],
                        'product_id' => $validated['product_id'],
                        'quantity' => 0,
                    ]);

                    // Lock the newly created record.
                    $inventory->refresh();
                }
            }

            $quantityBefore = $inventory->quantity;

            /*
            * Calculate the new quantity.
            */
            switch ($validated['adjustment_type']) {

                case 'stock_in':
                    $quantityAfter = $quantityBefore + $validated['quantity'];
                    break;

                case 'stock_out':
                    $quantityAfter = $quantityBefore - $validated['quantity'];

                    if ($quantityAfter < 0) {
                        throw ValidationException::withMessages([
                            'quantity' => "Insufficient stock. Current stock is {$quantityBefore}.",
                        ]);
                    }

                    break;

                case 'set_exact':
                    $quantityAfter = $validated['quantity'];
                    break;

                default:
                    throw ValidationException::withMessages([
                        'adjustment_type' => 'Invalid adjustment type.',
                    ]);
            }

            /*
            * Update inventory.
            */
            $inventory->update([
                'quantity' => $quantityAfter,
            ]);

            /*
            * Record the stock movement.
            */
            StockMovement::create([
                'branch_id' => $inventory->branch_id,
                'product_id' => $inventory->product_id,
                'user_id' => $request->user()->id,
                'type' => $validated['adjustment_type'],
                'quantity' => $validated['adjustment_type'] === 'stock_out'
                    ? -$validated['quantity']
                    : (
                        $validated['adjustment_type'] === 'stock_in'
                            ? $validated['quantity']
                            : $quantityAfter - $quantityBefore
                    ),
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reference_type' => $validated['reference'] ? 'manual_adjustment' : null,
                'reference_id' => null,
                'notes' => $validated['notes'] ?? null,
            ]);

            return redirect()
                ->route('inventory.index')
                ->with('success', 'Stock updated successfully.');
        });
    }

    public function byStore(Request $request)
    {
        $branches = Branch::all();

        $stores = Store::all();

        $store = null;
        $inventories = collect();

        $storeTotalProducts = 0;
        $storeTotalUnits = 0;
        $storeLowStockCount = 0;
        $storeOutOfStockCount = 0;
        $storeInventoryValue = 0;

        if ($request->filled('store_id')) {

            $request->validate([
                'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
                'store_id' => ['required', 'integer', 'exists:stores,id'],
            ]);

            $store = Store::with('branch')->findOrFail($request->store_id);

            // Make sure the selected store belongs to the selected branch.
            if ($request->filled('branch_id') && $store->branch_id != $request->branch_id) {
                abort(404);
            }

            $inventories = Inventory::with('product')
                ->where('store_id', $store->id)
                ->when($request->filled('branch_id'), function ($query) use ($request) {
                    $query->where('branch_id', $request->branch_id);
                })
                ->get();

            $storeTotalProducts = $inventories->count();

            $storeTotalUnits = $inventories->sum('quantity');

            $storeLowStockCount = $inventories->filter(function ($inventory) {
                $minimumStockLevel = $inventory->product->minimum_stock_level ?? 0;

                return $inventory->quantity > 0 &&
                    $inventory->quantity <= $minimumStockLevel;
            })->count();

            $storeOutOfStockCount = $inventories->filter(function ($inventory) {
                return $inventory->quantity <= 0;
            })->count();

            $storeInventoryValue = $inventories->sum(function ($inventory) {
                return $inventory->quantity * ($inventory->product->cost_price ?? 0);
            });
        }

        return view('inventory.by-store', compact(
            'branches',
            'stores',
            'inventories',
            'store',
            'storeTotalProducts',
            'storeTotalUnits',
            'storeLowStockCount',
            'storeOutOfStockCount',
            'storeInventoryValue'
        ));
    }
}
