<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockTransfer;
use App\Models\Store;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today           = Carbon::today();
        $startOfMonth    = Carbon::now()->startOfMonth();
        $startOfTomorrow = $today->copy()->addDay();

        /*
        |--------------------------------------------------------------------------
        | Sales Summary
        |--------------------------------------------------------------------------
        | Only completed sales are included in revenue.
        */

        $salesTodayQuery = Sale::where('status', 'completed')
            ->where('created_at', '>=', $today)
            ->where('created_at', '<', $startOfTomorrow);

        $salesToday = (clone $salesTodayQuery)->sum('total');

        $salesTodayTransactions = (clone $salesTodayQuery)->count();

        $salesThisMonthQuery = Sale::where('status', 'completed')
            ->where('created_at', '>=', $startOfMonth)
            ->where('created_at', '<', $startOfTomorrow);

        $salesThisMonth = (clone $salesThisMonthQuery)->sum('total');

        $salesThisMonthTransactions = (clone $salesThisMonthQuery)->count();

        /*
        |--------------------------------------------------------------------------
        | Inventory Summary
        |--------------------------------------------------------------------------
        */

        $totalProducts = Product::count();

        $totalUnits = Inventory::sum('quantity');

        $totalInventoryValue = Inventory::join(
            'products',
            'inventories.product_id',
            '=',
            'products.id'
        )
            ->selectRaw('COALESCE(SUM(inventories.quantity * products.cost_price), 0) as total')
            ->value('total');

        $totalBranches = Branch::count();

        $totalStores = Store::count();

        /*
        |--------------------------------------------------------------------------
        | Low Stock Products
        |--------------------------------------------------------------------------
        | Stock is checked per store against the product's minimum stock.
        */

        $lowStockQuery = Inventory::query()
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->whereRaw('inventories.quantity <= products.minimum_stock')
            ->with(['store', 'product'])
            ->select([
                'inventories.*',
                'products.name as name',
                'products.sku as sku',
                'products.minimum_stock as minimum_stock',
                'inventories.quantity as current_stock',
            ]);

        $lowStockCount = (clone $lowStockQuery)->count();

        $lowStockProducts = $lowStockQuery
            ->orderBy('inventories.quantity')
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Stock Transfers
        |--------------------------------------------------------------------------
        | Pending, approved, and in-transit transfers still require action
        | or have not yet been completed.
        */

        $transferStatuses = [
            'pending',
            'approved',
            'in_transit',
            'completed',
        ];

        $transferQuery = StockTransfer::whereIn(
            'status',
            $transferStatuses
        );

        $transfersCount = (clone $transferQuery)->count();

        $transfers = (clone $transferQuery)
            ->with([
                'fromStore',
                'toStore',
                'requestedBy',
            ])
            ->withCount('items')
            ->latest()
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent Sales
        |--------------------------------------------------------------------------
        */

        $recentSales = Sale::with([
            'store',
        ])
            ->withCount('items')
            ->latest()
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Dashboard View
        |--------------------------------------------------------------------------
        */

        return view('dashboard.index', compact(
            'lowStockCount',
            'transfersCount',
            'totalProducts',
            'totalUnits',
            'totalInventoryValue',
            'totalBranches',
            'totalStores',
            'recentSales',
            'salesToday',
            'salesTodayTransactions',
            'salesThisMonth',
            'salesThisMonthTransactions',
            'lowStockProducts',
            'transfers'
        ));
    }
}
