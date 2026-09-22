@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2 no-print">
        <div>
            <h1 class="h3 fw-bold mb-0">Stock Movement Report</h1>
            <p class="text-muted small mb-0">Track every stock in, stock out, and adjustment.</p>
        </div>
        <div class="d-flex gap-2">
            @if (Route::has('reports.stock-movements.export'))
                <a href="{{ route('reports.stock-movements.export', request()->query()) }}" class="btn btn-outline-secondary">
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
            <form method="GET" action="{{ route('reports.stock-movements') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                @isset($branches)
                    <div class="col-md-2">
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
                    <div class="col-md-2">
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
                    <div class="col-md-2">
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
                    <label class="form-label small text-muted mb-1">Movement Type</label>
                    <select name="movement_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="stock_in" {{ request('movement_type') === 'stock_in' ? 'selected' : '' }}>Stock In</option>
                        <option value="stock_out" {{ request('movement_type') === 'stock_out' ? 'selected' : '' }}>Stock Out</option>
                        <option value="adjustment" {{ request('movement_type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                        <option value="transfer_in" {{ request('movement_type') === 'transfer_in' ? 'selected' : '' }}>Transfer In</option>
                        <option value="transfer_out" {{ request('movement_type') === 'transfer_out' ? 'selected' : '' }}>Transfer Out</option>
                        <option value="sale" {{ request('movement_type') === 'sale' ? 'selected' : '' }}>Sale</option>
                    </select>
                </div>
                @isset($users)
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Performed By</label>
                        <select name="performed_by" class="form-select">
                            <option value="">Anyone</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ (string) request('performed_by') === (string) $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
                <div class="col-12">
                    <a href="{{ route('reports.stock-movements') }}" class="btn btn-sm btn-outline-secondary">Reset Filters</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Stock In</p>
                    <h5 class="fw-bold mb-0 text-success">+{{ number_format($summary['total_stock_in'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Stock Out</p>
                    <h5 class="fw-bold mb-0 text-danger">-{{ number_format($summary['total_stock_out'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Adjustments</p>
                    <h5 class="fw-bold mb-0">{{ number_format($summary['total_adjustments'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Movement Records</p>
                    <h5 class="fw-bold mb-0">{{ number_format($summary['total_records'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
    </div>

    {{-- Stock Movement Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th>Date</th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Store</th>
                            <th>Movement Type</th>
                            <th class="text-end">Quantity</th>
                            <th>Reference</th>
                            <th>Performed By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stockMovements as $movement)
                            <tr>
                                <td>{{ $movement->created_at->format('d M Y, H:i') }}</td>
                                <td class="fw-semibold">{{ $movement->product_name }}</td>
                                <td>{{ $movement->sku }}</td>
                                <td>{{ $movement->store_name }}</td>
                                <td>
                                    @switch($movement->type)
                                        @case('stock_in')
                                            <span class="badge text-bg-success">Stock In</span>
                                            @break
                                        @case('stock_out')
                                            <span class="badge text-bg-danger">Stock Out</span>
                                            @break
                                        @case('adjustment')
                                            <span class="badge text-bg-info">Adjustment</span>
                                            @break
                                        @case('transfer_in')
                                            <span class="badge text-bg-primary">Transfer In</span>
                                            @break
                                        @case('transfer_out')
                                            <span class="badge text-bg-warning">Transfer Out</span>
                                            @break
                                        @case('sale')
                                            <span class="badge text-bg-secondary">Sale</span>
                                            @break
                                        @default
                                            <span class="badge text-bg-secondary">{{ ucfirst(str_replace('_', ' ', $movement->type)) }}</span>
                                    @endswitch
                                </td>
                                <td class="text-end {{ $movement->quantity < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity) }}
                                </td>
                                <td>{{ $movement->reference ?? '—' }}</td>
                                <td>{{ $movement->performed_by_name ?? '—' }}</td>
                                <td>{{ $movement->notes ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-arrow-down-up fs-2 d-block mb-2"></i>
                                    No stock movement records found for the selected filters.
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
