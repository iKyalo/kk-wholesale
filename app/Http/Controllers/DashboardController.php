<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $lowStockCount = 0;
        $pendingTransfersCount = 0;
        $totalProducts = 0;
        $totalUnits = 0;
        $totalInventoryValue = 0;
        $totalBranches = 0;
        $totalStores = 0;
        $recentSales = [];
        $salesToday = 0;
        $salesTodayTransactions = 0;
        $salesThisMonth = 0;
        $salesThisMonthTransactions = 0;
        $lowStockProducts = 0;
        $pendingStockTransfers = 0;
        $lowStockProducts = [];
        $pendingStockTransfers = [];
        $pendingTransfers = [];
        
        return view('dashboard.index', compact(
            'lowStockCount',
            'pendingTransfersCount',
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
            'pendingStockTransfers',
            'lowStockProducts',
            'pendingStockTransfers',
            'pendingTransfers'
        ));
    }
}
