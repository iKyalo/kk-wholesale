@extends('layouts.app')

@section('content')
    <div class="bg-light min-vh-100 py-4">
        <div class="container-fluid px-3 px-md-4">

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
                <div>
                    <h1 class="h3 fw-bold mb-0">{{ $store->name }}</h1>
                    <p class="text-muted small mb-0">
                        {{ $store->code }} &middot; {{ $store->branch->name ?? '—' }}
                        @if ($store->is_active)
                            <span class="badge text-bg-success ms-2">Active</span>
                        @else
                            <span class="badge text-bg-secondary ms-2">Inactive</span>
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('stores.edit', $store) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i> Edit Store
                    </a>
                    <a href="{{ route('stores.users.edit', $store) }}" class="btn btn-outline-info">
                        <i class="bi bi-people me-1"></i> Assign Users
                    </a>
                    <a href="{{ route('stores.index') }}" class="btn btn-outline-secondary">
                        Back to Stores
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Store Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-3">
                    <h2 class="h6 fw-bold mb-0">Store Information</h2>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Store Name</p>
                            <p class="fw-semibold mb-0">{{ $store->name }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Store Code</p>
                            <p class="fw-semibold mb-0">{{ $store->code }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Branch</p>
                            <p class="fw-semibold mb-0">{{ $store->branch->name ?? '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Status</p>
                            @if ($store->is_active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-secondary">Inactive</span>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Location</p>
                            <p class="fw-semibold mb-0">{{ $store->location }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Phone</p>
                            <p class="fw-semibold mb-0">{{ $store->phone ?? '—' }}</p>
                        </div>
                        <div class="col-md-8">
                            <p class="text-muted small mb-1">Address</p>
                            <p class="fw-semibold mb-0">{{ $store->address ?? '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Email</p>
                            <p class="fw-semibold mb-0">{{ $store->email ?? '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Created At</p>
                            <p class="fw-semibold mb-0">{{ $store->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Last Updated</p>
                            <p class="fw-semibold mb-0">{{ $store->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Store Statistics --}}
            <div class="row g-3 mb-4">
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Users</p>
                            <h5 class="fw-bold mb-0">{{ number_format($store->users_count ?? 0) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Products</p>
                            <h5 class="fw-bold mb-0">{{ number_format($store->products_count ?? 0) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Units in Stock</p>
                            <h5 class="fw-bold mb-0">{{ number_format($store->total_units ?? 0) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Inventory Value</p>
                            <h5 class="fw-bold mb-0">KSh {{ number_format($store->inventory_value ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Sales Today</p>
                            <h5 class="fw-bold mb-0">KSh {{ number_format($store->sales_today ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Sales This Month</p>
                            <h5 class="fw-bold mb-0">KSh {{ number_format($store->sales_this_month ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Current Stock --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Current Stock</h5>
                            <small class="text-muted">
                                Products currently available in {{ $store->name }}
                            </small>
                        </div>

                        <span class="badge bg-primary">
                            {{ $storeInventory->count() }}
                            {{ $storeInventory->count() === 1 ? 'Product' : 'Products' }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if ($storeInventory->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-box-seam fs-1 text-muted"></i>
                            <h6 class="mt-3 mb-1">No stock available</h6>
                            <p class="text-muted mb-0">
                                This store currently has no inventory.
                            </p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4">#</th>
                                        <th class="px-4">Product</th>
                                        <th>SKU</th>
                                        <th class="text-end">Quantity</th>
                                        <th class="text-end">Cost Price</th>
                                        <th class="text-end">Stock Value</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($storeInventory as $inventory)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="px-4">
                                                <div class="fw-semibold">
                                                    {{ $inventory->product->name }}
                                                </div>
                                            </td>

                                            <td>
                                                <span class="text-muted">
                                                    {{ $inventory->product->sku }}
                                                </span>
                                            </td>

                                            <td class="text-end">
                                                <span class="badge bg-success">
                                                    {{ number_format($inventory->quantity) }}
                                                </span>
                                            </td>

                                            <td class="text-end">
                                                KSh {{ number_format($inventory->product->cost_price, 2) }}
                                            </td>

                                            <td class="text-end fw-semibold">
                                                KSh
                                                {{ number_format($inventory->quantity * $inventory->product->cost_price, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                                <tfoot class="table-light">
                                    <tr>
                                        <th></th>

                                        <th colspan="2" class="px-4">
                                            Total
                                        </th>

                                        <th class="text-end">
                                            {{ number_format($store->total_units) }}
                                        </th>

                                        <th></th>

                                        <th class="text-end">
                                            KSh {{ number_format($store->inventory_value, 2) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Store Sales --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Sales</h5>
                            <small class="text-muted">
                                Sales made from this store
                            </small>
                        </div>

                        <span class="badge bg-primary">
                            {{ $sales->count() }} Sales
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if ($sales->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-receipt fs-1 text-muted"></i>
                            <h6 class="mt-3">No sales found</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th class="px-4">Sale #</th>

                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($sales as $sale)
                                        <tr>
                                            <td>
                                                {{ $loop->iteration }}
                                            </td>

                                            <td class="px-4 fw-semibold">
                                                {{ $sale->sale_number }}
                                            </td>

                                            <td>
                                                {{ $sale->created_at->format('d M Y H:i') }}
                                            </td>

                                            <td>
                                                {{ $sale->customer_name ?? 'Walk-in Customer' }}
                                            </td>

                                            <td>
                                                {{ ucfirst($sale->payment_method) }}
                                            </td>

                                            <td>
                                                <span class="badge bg-success">
                                                    {{ ucfirst($sale->status ?? 'completed') }}
                                                </span>
                                            </td>

                                            <td class="text-end fw-semibold">
                                                KSh {{ number_format($sale->total, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Stock Movements
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Stock Movements</h5>
                            <small class="text-muted">
                                Inventory movements for this store
                            </small>
                        </div>

                        <span class="badge bg-secondary">
                            {{ $stockMovements->count() }} Movements
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if ($stockMovements->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-arrow-left-right fs-1 text-muted"></i>
                            <h6 class="mt-3">No stock movements found</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4">Product</th>
                                        <th>Type</th>
                                        <th class="text-end">Quantity</th>
                                        <th>Reference</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($stockMovements as $movement)
                                        <tr>
                                            <td class="px-4 fw-semibold">
                                                {{ $movement->product->name }}
                                            </td>

                                            <td>
                                                @php
                                                    $type = strtolower($movement->type);
                                                @endphp

                                                @if (in_array($type, ['in', 'purchase', 'received', 'addition']))
                                                    <span class="badge bg-success">
                                                        {{ ucfirst($movement->type) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        {{ ucfirst($movement->type) }}
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="text-end fw-semibold">
                                                {{ number_format($movement->quantity) }}
                                            </td>

                                            <td>
                                                {{ $movement->reference ?? '-' }}
                                            </td>

                                            <td>
                                                {{ $movement->created_at->format('d M Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div> --}}

            {{-- Stock Transfers --}}

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Stock Transfers</h5>
                            <small class="text-muted">
                                Stock transferred into and out of this store
                            </small>
                        </div>

                        {{-- <span class="badge bg-warning text-dark">
                            {{ $stockTransfers->count() }} Transfers
                        </span> --}}
                    </div>
                </div>

                <div class="card-body p-0">
                    @if ($stockTransfers->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-truck fs-1 text-muted"></i>

                            <h6 class="mt-3">
                                No stock transfers found
                            </h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        {{-- <th class="px-4">#</th> --}}
                                        <th>Source</th>
                                        <th>Destination</th>
                                        <th>Direction</th>
                                        <th>Product</th>
                                        <th class="text-end">Quantity</th>
                                        <th>Transferred By</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($stockTransfers as $transfer)
                                        @foreach ($transfer->items as $item)
                                            <tr>
                                                {{-- <td>
                                                    {{ $loop->iteration }}
                                                </td> --}}

                                                {{-- Source --}}
                                                <td>
                                                    @if ($transfer->fromStore)
                                                        <div class="fw-semibold">
                                                            {{ $transfer->fromStore->name }}
                                                        </div>

                                                        @if ($transfer->fromStore->branch)
                                                            <small class="text-muted">
                                                                {{ $transfer->fromStore->branch->name }}
                                                            </small>
                                                        @endif
                                                    @elseif ($transfer->fromBranch)
                                                        <div class="fw-semibold">
                                                            {{ $transfer->fromBranch->name }}
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                </td>

                                                {{-- Destination --}}
                                                <td>
                                                    @if ($transfer->toStore)
                                                        <div class="fw-semibold">
                                                            {{ $transfer->toStore->name }}
                                                        </div>

                                                        @if ($transfer->toStore->branch)
                                                            <small class="text-muted">
                                                                {{ $transfer->toStore->branch->name }}
                                                            </small>
                                                        @endif
                                                    @elseif ($transfer->toBranch)
                                                        <div class="fw-semibold">
                                                            {{ $transfer->toBranch->name }}
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                </td>

                                                {{-- Direction --}}
                                                <td>
                                                    @if ($transfer->direction === 'Incoming')
                                                        <span class="badge bg-success">
                                                            Incoming
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger">
                                                            Outgoing
                                                        </span>
                                                    @endif
                                                </td>

                                                {{-- Product --}}
                                                <td>
                                                    {{ $item->product->name }}
                                                </td>

                                                {{-- Quantity --}}
                                                <td class="text-end fw-semibold">
                                                    {{ number_format($item->quantity) }}
                                                </td>

                                                {{-- User --}}
                                                <td>
                                                    {{ $transfer->user?->name ?? '-' }}
                                                </td>

                                                {{-- Status --}}
                                                <td>
                                                    @php
                                                        $statusClass = match ($transfer->status) {
                                                            'completed' => 'bg-success',
                                                            'cancelled' => 'bg-danger',
                                                            'pending' => 'bg-warning text-dark',
                                                            default => 'bg-secondary',
                                                        };
                                                    @endphp

                                                    <span class="badge {{ $statusClass }}">
                                                        {{ ucfirst($transfer->status) }}
                                                    </span>
                                                </td>

                                                {{-- Date --}}
                                                <td>
                                                    {{ $transfer->transferred_at
                                                        ? $transfer->transferred_at->format('d M Y H:i')
                                                        : $transfer->created_at->format('d M Y H:i') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>



            {{-- Assigned Users --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3 d-flex align-items-center justify-content-between">
                    <h2 class="h6 fw-bold mb-0">Assigned Users</h2>
                    <a href="{{ route('stores.users.edit', $store) }}" class="btn btn-sm btn-outline-primary">
                        Assign Users
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Assigned At</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($store->users as $user)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-semibold">{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->role->name ?? '—' }}</td>
                                        <td>
                                            @if ($user->is_active ?? true)
                                                <span class="badge text-bg-success">Active</span>
                                            @else
                                                <span class="badge text-bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ optional($user->pivot->created_at ?? null)->format('d M Y') ?? '—' }}</td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="{{ route('users.show', $user) }}"
                                                    class="btn btn-sm btn-outline-secondary" title="View User">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    title="Remove User" data-bs-toggle="modal"
                                                    data-bs-target="#removeUserModal{{ $user->id }}">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>

                                            <div class="modal fade" id="removeUserModal{{ $user->id }}"
                                                tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Remove User</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Remove <strong>{{ $user->name }}</strong> from
                                                            {{ $store->name }}?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                data-bs-dismiss="modal">Cancel</button>
                                                            <form method="POST"
                                                                action="{{ route('stores.users.remove', [$store, $user]) }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn btn-danger">Remove</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No users assigned to this
                                            store yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
