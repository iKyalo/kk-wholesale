<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoresController extends Controller
{
    public function index()
    {
        $stores = Store::with('branch')->get();
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

    public function show(Store $store)
    {
        $store->load([
            'branch',
            'users',
            'inventories.product',
        ]);

        return view('stores.show', compact('store'));
    }

    public function edit(Store $store)
    {
        $branches = Branch::all();

        return view('stores.edit', compact('store', 'branches'));
    }

    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'code'      => [
                'required',
                'string',
                'max:50',
                Rule::unique('stores', 'code')->ignore($store->id),
            ],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'is_active' => ['required', 'boolean'],
            'location'  => ['required', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'email'     => ['nullable', 'email', 'max:255'],
            'address'   => ['nullable', 'string', 'max:1000'],
        ]);

        $store->update($validated);

        return redirect()
            ->route('stores.index')
            ->with('success', 'Store updated successfully.');
    }

    public function destroy(Store $store)
    {
        $store->delete();

        return redirect()
            ->route('stores.index')
            ->with('success', 'Store deleted successfully.');
    }

    public function editUser(Store $store) 
    {
        $users = User::where('role_id', 3)->get();

        return view('stores.edit-users', compact('store', 'users'));
    }

    public function updateUser() 
    {

    }
}