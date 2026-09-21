@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    @php
        $minLevel = $inventory->product->minimum_stock_level ?? 0;
        $stockValue = $inventory->quantity * ($inventory->product->cost_price ?? 0);
    @endphp

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-0">{{ $inventory->product->name ?? '—' }}</h1>
            <p class="text-muted small mb-0">
                SKU: {{ $inventory->product->sku ?? '—' }}
                &middot; {{ $inventory->store->name ?? '—' }} ({{ $inventory->store->branch->name ?? '—' }})
                @if ($inventory->quantity <= 0)
                    <span class="badge text-bg-danger ms-2">Out of Stock</span>
                @elseif ($inventory->quantity <= $minLevel)
                    <span class="badge text-bg-warning ms-2">Low Stock</span>
                @else
                    <span class="badge text-bg-success ms-2">In Stock</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.edit-stock', ['inventory' => $inventory->id]) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil-square me-1"></i> Update Stock
            </a>
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
                Back to Inventory
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Inventory Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Current Quantity</p>
                    <h4 class="fw-bold mb-0">{{ number_format($inventory->quantity) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Minimum Stock Level</p>
                    <h4 class="fw-bold mb-0">{{ number_format($minLevel) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Cost Price</p>
                    <h4 class="fw-bold mb-0">KSh {{ number_format($inventory->product->cost_price ?? 0, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Stock Value</p>
                    <h4 class="fw-bold mb-0">KSh {{ number_format($stockValue, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">

        {{-- Product Information --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3">
                    <h2 class="h6 fw-bold mb-0">Product Information</h2>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <p class="text-muted small mb-1">Product Name</p>
                            <p class="fw-semibold mb-0">{{ $inventory->product->name ?? '—' }}</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted small mb-1">SKU</p>
                            <p class="fw-semibold mb-0">{{ $inventory->product->sku ?? '—' }}</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted small mb-1">Barcode</p>
                            <p class="fw-semibold mb-0">{{ $inventory->product->barcode ?? '—' }}</p>
                        </div>
                        @if ($inventory->product->category ?? false)
                            <div class="col-6">
                                <p class="text-muted small mb-1">Category</p>
                                <p class="fw-semibold mb-0">{{ $inventory->product->category->name }}</p>
                            </div>
                        @endif
                        <div class="col-6">
                            <p class="text-muted small mb-1">Cost Price</p>
                            <p class="fw-semibold mb-0">KSh {{ number_format($inventory->product->cost_price ?? 0, 2) }}</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted small mb-1">Selling Price</p>
                            <p class="fw-semibold mb-0">KSh {{ number_format($inventory->product->selling_price ?? 0, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Store Information --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3">
                    <h2 class="h6 fw-bold mb-0">Store Information</h2>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <p class="text-muted small mb-1">Store Name</p>
                            <p class="fw-semibold mb-0">{{ $inventory->store->name ?? '—' }}</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted small mb-1">Store Code</p>
                            <p class="fw-semibold mb-0">{{ $inventory->store->code ?? '—' }}</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted small mb-1">Branch</p>
                            <p class="fw-semibold mb-0">{{ $inventory->store->branch->name ?? '—' }}</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted small mb-1">Store Location</p>
                            <p class="fw-semibold mb-0">{{ $inventory->store->location ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Stock Movement History --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-3">
            <h2 class="h6 fw-bold mb-0">Stock Movement History</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th>Date</th>
                            <th>Movement Type</th>
                            <th>Quantity</th>
                            <th>Reference</th>
                            <th>Performed By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stockMovements as $movement)
                            <tr>
                                <td>{{ $movement->created_at->format('d M Y, H:i') }}</td>
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
                                        @default
                                            <span class="badge text-bg-secondary">{{ ucfirst(str_replace('_', ' ', $movement->type)) }}</span>
                                    @endswitch
                                </td>
                                <td>{{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity) }}</td>
                                <td>{{ $movement->reference ?? '—' }}</td>
                                <td>{{ $movement->performedBy->name ?? '—' }}</td>
                                <td>{{ $movement->notes ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No stock movement records for this item yet.
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
@endsection
