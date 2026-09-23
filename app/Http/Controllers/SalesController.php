<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index()
    {
        $sales = Sale::all();
        return view('sales.index', compact('sales'));
    }

    public function create() 
    {
        $sales = Sale::all();
        $branches = Branch::all();
        $stores = Store::all();
        $products = Product::all();

        return view('sales.create', compact('sales', 'branches', 'stores', 'products'));
    }

    public function byStore(Request $request)
    {
        $branches = Branch::orderBy('name')->get();

        $branchId = $request->input('branch_id');
        $storeId = $request->input('store_id');

        // Only show stores belonging to the selected branch.
        $stores = Store::when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->orderBy('name')
            ->get();

        $store = null;
        $sales = collect();

        $storeTotalSales = 0;
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
}
