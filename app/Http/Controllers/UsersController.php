<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
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
    public function index(Request $request)
    {
        $roles = Role::orderBy('name')
            ->get()
            ->keyBy('id');

        $branches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get();

        $stores = Store::where('is_active', true)
            ->orderBy('name')
            ->get();

        $users = User::query()
            ->with(['role', 'branches', 'stores'])

        // Search by name, email, or phone
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })

        // Filter by role
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->where('role_id', $request->role);
            })

        // Filter by status
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })

        // Filter by assigned branch
            ->when($request->filled('branch_id'), function ($query) use ($request) {
                $query->whereHas('branches', function ($q) use ($request) {
                    $q->where('branches.id', $request->branch_id);
                });
            })

        // Filter by assigned store
            ->when($request->filled('store_id'), function ($query) use ($request) {
                $query->whereHas('stores', function ($q) use ($request) {
                    $q->where('stores.id', $request->store_id);
                });
            })

            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact(
            'users',
            'roles',
            'branches',
            'stores'
        ));
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

        return view('users.create', compact('branches', 'stores', 'roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'        => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9+()\s-]+$/',
                'unique:users,phone',
            ],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],

            'role'         => ['required', 'exists:roles,id'],
            'status'       => ['required', 'in:active,inactive'],

            'branch_ids'   => ['nullable', 'array'],
            'branch_ids.*' => ['exists:branches,id'],

            'store_ids'    => ['nullable', 'array'],
            'store_ids.*'  => ['exists:stores,id'],
        ]);

        DB::transaction(function () use ($validated) {

            $role = Role::findOrFail($validated['role']);

            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'phone'    => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role_id'  => $role->id,
                'status'   => $validated['status'],
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
        $stores   = Store::orderBy('name')->get();

        $roles = Role::all();

        return view(
            'users.edit',
            compact('user', 'branches', 'stores', 'roles')
        );
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, User $user)
    {
        // dd($request);
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],

            'email'        => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'phone'        => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9+()\s-]+$/',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],

            'password'     => ['nullable', 'string', 'min:8', 'confirmed'],

            'role'         => ['required', 'exists:roles,id'],
            'status'       => ['required', 'in:active,inactive'],

            'branch_ids'   => ['nullable', 'array'],
            'branch_ids.*' => ['exists:branches,id'],

            'store_ids'    => ['nullable', 'array'],
            'store_ids.*'  => ['exists:stores,id'],
        ]);

        DB::transaction(function () use ($validated, $user) {
            $data = [
                'name'    => $validated['name'],
                'email'   => $validated['email'],
                'phone'   => $validated['phone'],
                'role_id' => $validated['role'],
                'status'  => $validated['status'],
            ];

            // Only update password if provided.
            if (! empty($validated['password'])) {
                $data['password'] = Hash::make(
                    $validated['password']
                );
            }

            $user->update($data);

            // Sync branch assignments.
            $user->branches()->sync(
                $user->role === 2
                    ? ($validated['branch_ids'] ?? [])
                    : []
            );

            // Sync store assignments.
            $user->stores()->sync(
                $user->role === 3
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
