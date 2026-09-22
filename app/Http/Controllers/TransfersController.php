<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockTransfer;
use App\Models\Store;
use Illuminate\Http\Request;

class TransfersController extends Controller
{
    public function index()
    {
        $transfers = StockTransfer::all();

        return view('transfers.index', compact('transfers'));
    }

    public function create() {
        $sales = Sale::all();
        $branches = Branch::all();
        $stores = Store::all();
        $products = Product::all();

        return view('transfers.create', compact('sales', 'branches', 'stores', 'products'));

    }
}
