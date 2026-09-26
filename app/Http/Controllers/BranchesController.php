<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'phone'     => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9+()\s-]+$/',
                'unique:branches,phone',
            ],
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
        // Load branch users and stores.
        $branch->load(['users', 'stores']);

        $storeIds = $branch->stores->pluck('id');

        // 1. Total users assigned to this branch.
        $usersCount = $branch->users()->count();

        // 2. Total stores in this branch.
        $storesCount = $storeIds->count();

        // 3. Unique products stocked in this branch.
        $productsCount = DB::table('inventories')
            ->whereIn('store_id', $storeIds)
            ->distinct('product_id')
            ->count('product_id');

        // 4. Total inventory value using cost price.
        $inventoryValue = DB::table('inventories')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->whereIn('inventories.store_id', $storeIds)
            ->selectRaw('COALESCE(SUM(inventories.quantity * products.cost_price), 0) as total')
            ->value('total');

        // 5. Total sales today for this branch.
        $salesToday = DB::table('sales')
            ->whereIn('store_id', $storeIds)
            ->whereDate('created_at', today())
            ->sum('total');

        return view('branches.show', compact(
            'branch',
            'usersCount',
            'storesCount',
            'productsCount',
            'inventoryValue',
            'salesToday'
        ));
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'code'      => [
                'required',
                'string',
                'max:50',
                Rule::unique('branches', 'code')->ignore($branch->id),
            ],
            'location'  => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'phone'     => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9+()\s-]+$/',
                Rule::unique('branches', 'phone')->ignore($branch->id),
            ],
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

    public function updateUser(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'users'   => ['nullable', 'array'],
            'users.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);

        DB::transaction(function () use ($validated, $branch) {
            // Assign selected users and remove users that were unselected.
            $branch->users()->sync($validated['users'] ?? []);
        });

        return redirect()
            ->route('branches.show', $branch)
            ->with('success', 'Branch users updated successfully.');
    }

    public function removeUser(Branch $branch, User $user)
    {
        // Remove the user from this branch.
        $branch->users()->detach($user->id);

        return redirect()
            ->route('branches.show', $branch)
            ->with('success', 'User removed from branch successfully.');
    }

}
