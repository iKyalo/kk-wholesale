<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Branch;
class StoresController extends Controller
{
    public function index()
    {
        $stores = Store::all();
        $branches = Branch::all();
        return view('stores.index', compact('stores', 'branches'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('stores.create', compact('branches'));
    }
    
    
}
