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

    public function byStore() 
    {
        $sales = Sale::all();
        $branches = Branch::all();
        $stores = Store::all();
        $products = Product::all();

        return view('sales.by-store', compact('sales', 'branches', 'stores', 'products'));
    }
}
