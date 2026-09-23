@extends('layouts.app')

@section('content')
    <div class="bg-light min-vh-100 py-4">
        <div class="container-fluid px-3 px-md-4">

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
                <div>
                    <h1 class="h3 fw-bold mb-0">Inventory</h1>
                    <p class="text-muted small mb-0">Monitor and manage stock across branches and stores.</p>
                </div>
                <a href="{{ route('inventory.edit-stock') }}" class="btn btn-primary">
                    <i class="bi bi-pencil-square me-1"></i> Update Stock
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Summary Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-xl col-md-4 col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Total Products</p>
                            <h5 class="fw-bold mb-0">{{ number_format($totalProducts ?? 0) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-md-4 col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Total Units</p>
                            <h5 class="fw-bold mb-0">{{ number_format($totalUnits ?? 0) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-md-4 col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Low-Stock Items</p>
                            <h5 class="fw-bold mb-0 text-warning">{{ number_format($lowStockCount ?? 0) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-md-4 col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Out-of-Stock Items</p>
                            <h5 class="fw-bold mb-0 text-danger">{{ number_format($outOfStockCount ?? 0) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-xl col-md-4 col-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Inventory Value</p>
                            <h5 class="fw-bold mb-0">KSh {{ number_format($totalInventoryValue ?? 0, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search and Filters --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('inventory.index') }}" class="row g-2 align-items-center">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control"
                                placeholder="Search by product name or SKU..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="branch_id" class="form-select">
                                <option value="">All Branches</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ (string) request('branch_id') === (string) $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="store_id" class="form-select">
                                <option value="">All Stores</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}"
                                        {{ (string) request('store_id') === (string) $store->id ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="in_stock" {{ request('status') === 'in_stock' ? 'selected' : '' }}>In Stock
                                </option>
                                <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>Low
                                    Stock</option>
                                <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>
                                    Out of Stock</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-grid">
                            <button type="submit" class="btn btn-outline-primary">Filter</button>
                        </div>
                        @if (request('search') || request('branch_id') || request('store_id') || request('status'))
                            <div class="col-12">
                                <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-secondary">Clear
                                    Filters</a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Inventory Table --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Branch</th>
                                    <th>Store</th>
                                    <th>Quantity Available</th>
                                    <th>Minimum Stock Level</th>
                                    <th>Cost Price</th>
                                    <th>Inventory Value</th>
                                    <th>Stock Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inventories as $inventory)
                                    @php
                                        $minLevel = $inventory->product->minimum_stock_level ?? 0;
                                        $value = $inventory->quantity * ($inventory->product->cost_price ?? 0);
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-semibold">{{ $inventory->product->name ?? '—' }}</td>
                                        <td>{{ $inventory->product->sku ?? '—' }}</td>
                                        <td>{{ $inventory->store->branch->name ?? '—' }}</td>
                                        <td>{{ $inventory->store->name ?? '—' }}</td>
                                        <td>{{ number_format($inventory->quantity) }}</td>
                                        <td>{{ number_format($minLevel) }}</td>
                                        <td>KSh {{ number_format($inventory->product->cost_price ?? 0, 2) }}</td>
                                        <td>KSh {{ number_format($value, 2) }}</td>
                                        <td>
                                            @if ($inventory->quantity <= 0)
                                                <span class="badge text-bg-danger">Out of Stock</span>
                                            @elseif ($inventory->quantity <= $minLevel)
                                                <span class="badge text-bg-warning">Low Stock</span>
                                            @else
                                                <span class="badge text-bg-success">In Stock</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="{{ route('inventory.show', $inventory) }}"
                                                    class="btn btn-sm btn-outline-secondary" title="View Inventory">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                {{-- <a href="{{ route('inventory.edit-stock', ['inventory' => $inventory->id]) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Update Stock">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a> --}}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-5">
                                            <i class="bi bi-boxes fs-2 d-block mb-2"></i>
                                            No inventory records found. Try adjusting your filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (
                        $inventories instanceof \Illuminate\Contracts\Pagination\Paginator ||
                            $inventories instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="mt-3">
                            {{ $inventories->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
