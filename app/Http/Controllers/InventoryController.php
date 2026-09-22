<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        $stores = Store::all();
        $inventories = Inventory::all();
        
        return view('inventory.index', compact('branches', 'stores', 'inventories'));
    }

    public function editStock() 
    {
        $branches = Branch::all();
        $stores = Store::all();
        $inventories = Inventory::all();
        $products = Product::all();

        return view('inventory.update-stock', compact('branches', 'stores', 'inventories', 'products'));
    }

    public function byStore() 
    {
        $branches = Branch::all();
        $stores = Store::all();
        $inventories = Inventory::all();
        $products = Product::all();

        return view('inventory.by-store', compact('branches', 'stores', 'inventories', 'products'));
    }
}
