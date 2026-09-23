@extends('layouts.app')

@section('content')
    <div class="bg-light min-vh-100 py-4">
        <div class="container-fluid px-3 px-md-4">

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
                <div>
                    <h1 class="h3 fw-bold mb-0">Products</h1>
                    <p class="text-muted small mb-0">Manage your product catalog.</p>
                </div>
                <a href="{{ route('products.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create Product
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
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted text-uppercase small fw-semibold mb-1">Total Products</p>
                                <h4 class="fw-bold mb-0">{{ number_format($totalProducts ?? 0) }}</h4>
                            </div>
                            <span
                                class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center"
                                style="width:44px;height:44px;">
                                <i class="bi bi-box-seam fs-5"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted text-uppercase small fw-semibold mb-1">Active Products</p>
                                <h4 class="fw-bold mb-0">{{ number_format($activeProducts ?? 0) }}</h4>
                            </div>
                            <span
                                class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center"
                                style="width:44px;height:44px;">
                                <i class="bi bi-check-circle fs-5"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted text-uppercase small fw-semibold mb-1">Inactive Products</p>
                                <h4 class="fw-bold mb-0">{{ number_format($inactiveProducts ?? 0) }}</h4>
                            </div>
                            <span
                                class="bg-secondary bg-opacity-10 text-secondary rounded-circle p-2 d-flex align-items-center justify-content-center"
                                style="width:44px;height:44px;">
                                <i class="bi bi-pause-circle fs-5"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted text-uppercase small fw-semibold mb-1">Low-Stock Products</p>
                                <h4 class="fw-bold mb-0">{{ number_format($lowStockProducts ?? 0) }}</h4>
                            </div>
                            <span
                                class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 d-flex align-items-center justify-content-center"
                                style="width:44px;height:44px;">
                                <i class="bi bi-exclamation-triangle fs-5"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search and Filters --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-center">
                        <div class="col-md-4 col-lg-4">
                            <input type="text" name="search" class="form-control" placeholder="Search by name or SKU..."
                                value="{{ request('search') }}">
                        </div>

                        @isset($categories)
                            <div class="col-md-3 col-lg-3">
                                <select name="category_id" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endisset

                        <div class="col-md-3 col-lg-3">
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive
                                </option>
                            </select>
                        </div>

                        <div class="col-md-1 col-lg-1">
                            <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                        </div>

                        @if (request('search') || request('category_id') || request('status'))
                            <div class="col-md-1 col-lg-1">
                                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Products Table --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    @isset($categories)
                                        <th>Category</th>
                                    @endisset
                                    <th>Selling Price</th>
                                    <th>Cost Price</th>
                                    <th>Total Stock</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if ($product->image_url ?? false)
                                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                        class="rounded"
                                                        style="width: 36px; height: 36px; object-fit: cover;">
                                                @else
                                                    <span
                                                        class="bg-light border rounded d-flex align-items-center justify-content-center text-muted"
                                                        style="width: 36px; height: 36px;">
                                                        <i class="bi bi-box-seam"></i>
                                                    </span>
                                                @endif
                                                <span class="fw-semibold">{{ $product->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $product->sku }}</td>
                                        @isset($categories)
                                            <td>{{ $product->category->name ?? '—' }}</td>
                                        @endisset
                                        <td>KSh {{ number_format($product->selling_price, 2) }}</td>
                                        <td>KSh {{ number_format($product->cost_price, 2) }}</td>
                                        <td>{{ number_format($product->total_stock ?? 0) }}</td>
                                        <td>
                                            @if ($product->is_active)
                                                <span class="badge text-bg-success">Active</span>
                                            @else
                                                <span class="badge text-bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="{{ route('products.show', $product) }}"
                                                    class="btn btn-sm btn-outline-secondary" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('products.edit', $product) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    title="Delete" data-bs-toggle="modal"
                                                    data-bs-target="#deleteProductModal{{ $product->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>

                                            {{-- Delete confirmation modal --}}
                                            <div class="modal fade" id="deleteProductModal{{ $product->id }}"
                                                tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Delete Product</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Are you sure you want to delete
                                                            <strong>{{ $product->name }}</strong>? This action cannot be
                                                            undone. Products with existing sales or stock history may not be
                                                            deletable.
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                data-bs-dismiss="modal">Cancel</button>
                                                            <form method="POST"
                                                                action="{{ route('products.destroy', $product) }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">Delete
                                                                    Product</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ isset($categories) ? 9 : 8 }}"
                                            class="text-center text-muted py-5">
                                            <i class="bi bi-box-seam fs-2 d-block mb-2"></i>
                                            No products found. Try adjusting your filters or create a new product.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (
                        $products instanceof \Illuminate\Contracts\Pagination\Paginator ||
                            $products instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="mt-3">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
