<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        $totalSales = Sale::count();

        $totalRevenue = Sale::sum('total');

        $totalProducts = Product::count();

        $totalStock = Inventory::sum('quantity');

        $lowStockProducts = Product::whereHas('inventories', function ($query) {
            $query->whereColumn(
                'quantity',
                '<=',
                'products.minimum_stock'
            );
        })->count();

        $pendingTransfers = StockTransfer::where('status', 'pending')->count();

        return view('reports.index', compact(
            'totalSales',
            'totalRevenue',
            'totalProducts',
            'totalStock',
            'lowStockProducts',
            'pendingTransfers'
        ));
    }

    public function sales(Request $request)
    {
        $query = Sale::with([
            'store',
            'items.product',
        ]);

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sales = $query
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $stores = Store::orderBy('name')->get();

        $totalRevenue = (clone $query)->sum('total');

        $totalSales = (clone $query)->count();

        $storePerformance = [];

        return view('reports.sales', compact(
            'sales',
            'stores',
            'totalRevenue',
            'totalSales',
            'storePerformance'
        ));
    }

    public function inventory(Request $request)
    {
        $query = Inventory::with([
            'product',
            'store',
        ]);

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'low') {
                $query->whereHas('product', function ($q) {
                    $q->whereColumn(
                        'inventories.quantity',
                        '<=',
                        'products.minimum_stock'
                    );
                });
            }

            if ($request->status === 'out') {
                $query->where('quantity', 0);
            }

            if ($request->status === 'available') {
                $query->where('quantity', '>', 0);
            }
        }

        $inventory = $query
            ->orderBy('store_id')
            ->paginate(25)
            ->withQueryString();

        $stores = Store::orderBy('name')->get();

        $inventoryRecords = [];

        return view('reports.inventory', compact(
            'inventory',
            'stores',
            'inventoryRecords'
        ));
    }

    public function stockMovements(Request $request)
    {
        $query = StockMovement::with([
            'product',
            'store',
        ]);

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $stockMovements = [];

        $stores = Store::orderBy('name')->get();

        $products = Product::orderBy('name')->get();

        return view('reports.stock-movements', compact(
            'movements',
            'stockMovements',
            'stores',
            'products'
        ));
    }

    public function transfers(Request $request)
    {
        $query = StockTransfer::with([
            'fromStore',
            'toStore',
            'items.product',
        ]);

        if ($request->filled('from_store_id')) {
            $query->where(
                'from_store_id',
                $request->from_store_id
            );
        }

        if ($request->filled('to_store_id')) {
            $query->where(
                'to_store_id',
                $request->to_store_id
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->date_to
            );
        }

        $transfers = $query
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $stores = Store::orderBy('name')->get();

        return view('reports.transfers', compact(
            'transfers',
            'stores'
        ));
    }

    public function products() 
    {
        $products = [];

        $productPerformance = [];

        return view('reports.products', compact(
            'products',
            'productPerformance'
        ));
    }

    public function branches() 
    {
        $branches = [];

        $branchPerformance = [];

        return view('reports.branches', compact(
            'branches',
            'branchPerformance'
        ));
    }

    public function stores() 
    {
        $stores = [];

        $storePerformance = [];

        return view('reports.stores', compact(
            'stores',
            'storePerformance'
        ));
    }
}