@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2 no-print">
        <div>
            <h1 class="h3 fw-bold mb-0">Inventory Report</h1>
            <p class="text-muted small mb-0">Current stock levels and value across your stores.</p>
        </div>
        <div class="d-flex gap-2">
            @if (Route::has('reports.inventory.export'))
                <a href="{{ route('reports.inventory.export', request()->query()) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-download me-1"></i> Export CSV
                </a>
            @endif
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Report
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.inventory') }}" class="row g-2 align-items-end">
                @isset($branches)
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Branch</label>
                        <select name="branch_id" class="form-select">
                            <option value="">All Branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) request('branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                @isset($stores)
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Store</label>
                        <select name="store_id" class="form-select">
                            <option value="">All Stores</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}" {{ (string) request('store_id') === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                @isset($products)
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Product</label>
                        <select name="product_id" class="form-select">
                            <option value="">All Products</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" {{ (string) request('product_id') === (string) $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Stock Status</label>
                    <select name="stock_status" class="form-select">
                        <option value="">All</option>
                        <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary">Apply</button>
                </div>
                <div class="col-12">
                    <a href="{{ route('reports.inventory') }}" class="btn btn-sm btn-outline-secondary">Reset Filters</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Products</p>
                    <h5 class="fw-bold mb-0">{{ number_format($summary['total_products'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Units in Stock</p>
                    <h5 class="fw-bold mb-0">{{ number_format($summary['total_units'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Inventory Value</p>
                    <h5 class="fw-bold mb-0">KSh {{ number_format($summary['total_inventory_value'] ?? 0, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Low-Stock Products</p>
                    <h5 class="fw-bold mb-0 text-warning">{{ number_format($summary['low_stock_count'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Out-of-Stock Products</p>
                    <h5 class="fw-bold mb-0 text-danger">{{ number_format($summary['out_of_stock_count'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
    </div>

    {{-- Inventory Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Branch</th>
                            <th>Store</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Minimum Stock Level</th>
                            <th class="text-end">Cost Price</th>
                            <th class="text-end">Stock Value</th>
                            <th>Stock Status</th>
                            <th class="text-end no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inventoryRecords as $record)
                            @php
                                $minLevel = $record->minimum_stock_level ?? 0;
                                $stockValue = $record->quantity * ($record->cost_price ?? 0);
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $record->product_name }}</td>
                                <td>{{ $record->sku }}</td>
                                <td>{{ $record->branch_name }}</td>
                                <td>{{ $record->store_name }}</td>
                                <td class="text-end">{{ number_format($record->quantity) }}</td>
                                <td class="text-end">{{ number_format($minLevel) }}</td>
                                <td class="text-end">KSh {{ number_format($record->cost_price ?? 0, 2) }}</td>
                                <td class="text-end">KSh {{ number_format($stockValue, 2) }}</td>
                                <td>
                                    @if ($record->quantity <= 0)
                                        <span class="badge text-bg-danger">Out of Stock</span>
                                    @elseif ($record->quantity <= $minLevel)
                                        <span class="badge text-bg-warning">Low Stock</span>
                                    @else
                                        <span class="badge text-bg-success">In Stock</span>
                                    @endif
                                </td>
                                <td class="text-end no-print">
                                    @if (Route::has('inventory.show') && isset($record->inventory_id))
                                        <a href="{{ route('inventory.show', $record->inventory_id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">
                                    <i class="bi bi-boxes fs-2 d-block mb-2"></i>
                                    No inventory records found for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>

@push('styles')
<style>
    @media print {
        .no-print, nav, .btn { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .bg-light { background: #fff !important; }
    }
</style>
@endpush
@endsection
