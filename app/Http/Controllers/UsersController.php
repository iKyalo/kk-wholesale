<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    /**
     * Display all users.
     */
    public function index()
    {
        $roles = Role::all();

        $branches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get();

        $users = User::with(['branches', 'stores'])
            ->latest()
            ->paginate(15);

        $stores = Store::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('users.index', compact('users', 'roles', 'branches', 'stores'));
    }

    /**
     * Show the create user form.
     */
    public function create()
    {
        $branches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get();

        $stores = Store::where('is_active', true)
            ->orderBy('name')
            ->get();

        $roles = Role::all();
        // dd($roles);

        $user = Auth::user();

        return view('users.create', compact('branches', 'stores', 'roles', 'user'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            'role_id' => ['required', 'exists:roles,id'],

            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['exists:branches,id'],

            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['exists:stores,id'],
        ]);

        DB::transaction(function () use ($validated) {

            $role = Role::findOrFail($validated['role_id']);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role_id' => $role->id,
            ]);

            // Assign branches to Branch Managers.
            if ($role->slug === 'branch_manager') {
                $user->branches()->sync(
                    $validated['branch_ids'] ?? []
                );
            }

            // Assign stores to Store Managers.
            if ($role->slug === 'store_manager') {
                $user->stores()->sync(
                    $validated['store_ids'] ?? []
                );
            }
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display a specific user.
     */
    public function show(User $user)
    {
        $user->load(['branches', 'stores']);

        return view('users.show', compact('user'));
    }

    /**
     * Show the edit user form.
     */
    public function edit(User $user)
    {
        $user->load(['branches', 'stores']);

        $branches = Branch::orderBy('name')->get();
        $stores = Store::orderBy('name')->get();

        return view(
            'users.edit',
            compact('user', 'branches', 'stores')
        );
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],

            'password' => ['nullable', 'string', 'min:8', 'confirmed'],

            'role' => [
                'required',
                Rule::in([
                    'administrator',
                    'branch_manager',
                    'store_manager',
                ]),
            ],

            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['exists:branches,id'],

            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['exists:stores,id'],
        ]);

        DB::transaction(function () use ($validated, $user) {
            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'role' => $validated['role'],
            ];

            // Only update password if provided.
            if (!empty($validated['password'])) {
                $data['password'] = Hash::make(
                    $validated['password']
                );
            }

            $user->update($data);

            // Sync branch assignments.
            $user->branches()->sync(
                $user->role === 'branch_manager'
                    ? ($validated['branch_ids'] ?? [])
                    : []
            );

            // Sync store assignments.
            $user->stores()->sync(
                $user->role === 'store_manager'
                    ? ($validated['store_ids'] ?? [])
                    : []
            );
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user)
    {
        // Prevent deleting the currently authenticated user.
        if (auth()->id() === $user->id) {
            return redirect()
                ->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        DB::transaction(function () use ($user) {
            $user->branches()->detach();
            $user->stores()->detach();

            $user->delete();
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}