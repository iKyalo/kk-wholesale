<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Validation\Rule;

class BranchesController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::withCount('users');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $branches = $query
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'code'      => ['required', 'string', 'max:50', 'unique:branches,code'],
            'location'  => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'email'     => ['nullable', 'email', 'max:255'],
            'address'   => ['nullable', 'string', 'max:1000'],
        ]);

        Branch::create($validated);

        return redirect()
            ->route('branches.index')
            ->with('success', 'Branch created successfully.');
    }

    public function show(Branch $branch)
    {
        return view('branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('branches', 'code')->ignore($branch->id),
            ],
            'location'  => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'email'     => ['nullable', 'email', 'max:255'],
            'address'   => ['nullable', 'string', 'max:1000'],
        ]);     

        $branch->update($validated);

        return redirect()
            ->route('branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->stores()->exists()) {
            return redirect()
                ->route('branches.index')
                ->with('error', 'Cannot delete this branch because it has stores.');
        }

        if ($branch->users()->exists()) {
            return redirect()
                ->route('branches.index')
                ->with('error', 'Cannot delete this branch because users are assigned to it.');
        }

        $branch->delete();

        return redirect()
            ->route('branches.index')
            ->with('success', 'Branch deleted successfully.');
    }

    public function editUser(Branch $branch) 
    {
        $users = User::where('role_id', 2)->get();

        return view('branches.edit-users', compact('branch', 'users'));
    }

    public function updateUser() 
    {

    }

}
