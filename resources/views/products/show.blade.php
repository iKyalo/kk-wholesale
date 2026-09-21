@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-0">{{ $product->name }}</h1>
            <p class="text-muted small mb-0">
                SKU: {{ $product->sku }}
                @if ($product->is_active)
                    <span class="badge text-bg-success ms-2">Active</span>
                @else
                    <span class="badge text-bg-secondary ms-2">Inactive</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i> Edit Product
            </a>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                Back to Products
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Product Information --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-3">
            <h2 class="h6 fw-bold mb-0">Product Information</h2>
        </div>
        <div class="card-body">
            <div class="row g-4">

                @if ($product->image_url ?? false)
                    <div class="col-md-3 text-center">
                        <img
                            src="{{ $product->image_url }}"
                            alt="{{ $product->name }}"
                            class="img-fluid rounded border"
                        >
                    </div>
                @endif

                <div class="{{ ($product->image_url ?? false) ? 'col-md-9' : 'col-12' }}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Product Name</p>
                            <p class="fw-semibold mb-0">{{ $product->name }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">SKU</p>
                            <p class="fw-semibold mb-0">{{ $product->sku }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Barcode</p>
                            <p class="fw-semibold mb-0">{{ $product->barcode ?? '—' }}</p>
                        </div>

                        @if ($product->category ?? false)
                            <div class="col-md-4">
                                <p class="text-muted small mb-1">Category</p>
                                <p class="fw-semibold mb-0">{{ $product->category->name }}</p>
                            </div>
                        @endif

                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Cost Price</p>
                            <p class="fw-semibold mb-0">KSh {{ number_format($product->cost_price, 2) }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Selling Price</p>
                            <p class="fw-semibold mb-0">KSh {{ number_format($product->selling_price, 2) }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Minimum Stock Level</p>
                            <p class="fw-semibold mb-0">{{ number_format($product->minimum_stock) }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Status</p>
                            @if ($product->is_active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-secondary">Inactive</span>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Created At</p>
                            <p class="fw-semibold mb-0">{{ $product->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Last Updated</p>
                            <p class="fw-semibold mb-0">{{ $product->updated_at->format('d M Y, H:i') }}</p>
                        </div>

                        <div class="col-12">
                            <p class="text-muted small mb-1">Description</p>
                            <p class="mb-0">{{ $product->description ?? '—' }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Inventory Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Units in Stock</p>
                    <h4 class="fw-bold mb-0">{{ number_format($totalUnits ?? 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Branches Holding Stock</p>
                    <h4 class="fw-bold mb-0">{{ number_format($branchesHoldingStock ?? 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Stores Holding Stock</p>
                    <h4 class="fw-bold mb-0">{{ number_format($storesHoldingStock ?? 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Inventory Value</p>
                    <h4 class="fw-bold mb-0">KSh {{ number_format($totalInventoryValue ?? 0, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Stock by Store --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-3">
            <h2 class="h6 fw-bold mb-0">Stock by Store</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th>Branch</th>
                            <th>Store</th>
                            <th>Quantity Available</th>
                            <th>Minimum Stock Level</th>
                            <th>Stock Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($storeInventory as $inventory)
                            <tr>
                                <td>{{ $inventory->store->branch->name ?? '—' }}</td>
                                <td class="fw-semibold">{{ $inventory->store->name ?? '—' }}</td>
                                <td>{{ number_format($inventory->quantity) }}</td>
                                <td>{{ number_format($inventory->minimum_stock ?? $product->minimum_stock) }}</td>
                                <td>
                                    @if ($inventory->quantity <= 0)
                                        <span class="badge text-bg-danger">Out of Stock</span>
                                    @elseif ($inventory->quantity <= ($inventory->minimum_stock ?? $product->minimum_stock))
                                        <span class="badge text-bg-warning">Low Stock</span>
                                    @else
                                        <span class="badge text-bg-success">In Stock</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No inventory records found for this product yet.
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
