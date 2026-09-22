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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'code'      => ['required', 'string', 'max:50', 'unique:stores,code'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'is_active' => ['required', 'boolean'],
            'location'  => ['required', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'email'     => ['nullable', 'email', 'max:255'],
            'address'   => ['nullable', 'string', 'max:1000'],
        ]);

        Store::create($validated);

        return redirect()
            ->route('stores.index')
            ->with('success', 'Store created successfully.');
    }
    
    
}
