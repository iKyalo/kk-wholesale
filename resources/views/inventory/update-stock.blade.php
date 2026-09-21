@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0">Update Stock</h1>
            <p class="text-muted small mb-0">Adjust the stock quantity for a product at a specific store.</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
            Back to Inventory
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            Please fix the errors below and try again.
        </div>
    @endif

    <div class="row g-4">

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form
                        method="POST"
                        action="{{ route('inventory.update-stock') }}"
                        onsubmit="return confirm('Are you sure you want to apply this stock adjustment?');"
                        novalidate
                    >
                        @csrf

                        @isset($inventory)
                            <input type="hidden" name="inventory_id" value="{{ $inventory->id }}">
                        @endisset

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="branch_id" class="form-label">Branch</label>
                                <select
                                    class="form-select @error('branch_id') is-invalid @enderror"
                                    id="branch_id"
                                    name="branch_id"
                                    required
                                >
                                    <option value="" disabled {{ old('branch_id', $inventory->store->branch_id ?? '') ? '' : 'selected' }}>
                                        Select a branch
                                    </option>
                                    @foreach ($branches as $branch)
                                        <option
                                            value="{{ $branch->id }}"
                                            {{ (string) old('branch_id', $inventory->store->branch_id ?? '') === (string) $branch->id ? 'selected' : '' }}
                                        >
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="store_id" class="form-label">Store</label>
                                <select
                                    class="form-select @error('store_id') is-invalid @enderror"
                                    id="store_id"
                                    name="store_id"
                                    required
                                >
                                    <option value="" disabled {{ old('store_id', $inventory->store_id ?? '') ? '' : 'selected' }}>
                                        Select a store
                                    </option>
                                    @foreach ($stores as $store)
                                        <option
                                            value="{{ $store->id }}"
                                            {{ (string) old('store_id', $inventory->store_id ?? '') === (string) $store->id ? 'selected' : '' }}
                                        >
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('store_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="product_id" class="form-label">Product</label>
                                <select
                                    class="form-select @error('product_id') is-invalid @enderror"
                                    id="product_id"
                                    name="product_id"
                                    required
                                >
                                    <option value="" disabled {{ old('product_id', $inventory->product_id ?? '') ? '' : 'selected' }}>
                                        Select a product
                                    </option>
                                    @foreach ($products as $product)
                                        <option
                                            value="{{ $product->id }}"
                                            {{ (string) old('product_id', $inventory->product_id ?? '') === (string) $product->id ? 'selected' : '' }}
                                        >
                                            {{ $product->name }} ({{ $product->sku }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12"><hr></div>

                            <div class="col-md-4">
                                <label for="adjustment_type" class="form-label">Adjustment Type</label>
                                <select
                                    class="form-select @error('adjustment_type') is-invalid @enderror"
                                    id="adjustment_type"
                                    name="adjustment_type"
                                    required
                                >
                                    <option value="stock_in" {{ old('adjustment_type') === 'stock_in' ? 'selected' : '' }}>Stock In</option>
                                    <option value="stock_out" {{ old('adjustment_type') === 'stock_out' ? 'selected' : '' }}>Stock Out</option>
                                    <option value="set_exact" {{ old('adjustment_type') === 'set_exact' ? 'selected' : '' }}>Set Exact Quantity</option>
                                </select>
                                @error('adjustment_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input
                                    type="number"
                                    min="0"
                                    class="form-control @error('quantity') is-invalid @enderror"
                                    id="quantity"
                                    name="quantity"
                                    value="{{ old('quantity') }}"
                                    placeholder="0"
                                    required
                                >
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="reference" class="form-label">Reference Number <span class="text-muted fw-normal">(optional)</span></label>
                                <input
                                    type="text"
                                    class="form-control @error('reference') is-invalid @enderror"
                                    id="reference"
                                    name="reference"
                                    value="{{ old('reference') }}"
                                    placeholder="e.g. GRN-00231"
                                >
                                @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="notes" class="form-label">Notes <span class="text-muted fw-normal">(optional)</span></label>
                                <textarea
                                    class="form-control @error('notes') is-invalid @enderror"
                                    id="notes"
                                    name="notes"
                                    rows="3"
                                    placeholder="Reason for this adjustment"
                                >{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary px-4">Update Stock</button>
                            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Current Stock Display --}}
        <div class="col-lg-4">
            @isset($inventory)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-3">
                        <h2 class="h6 fw-bold mb-0">Current Stock</h2>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-1">Product</p>
                        <p class="fw-semibold mb-3">{{ $inventory->product->name ?? '—' }}</p>

                        <p class="text-muted small mb-1">SKU</p>
                        <p class="fw-semibold mb-3">{{ $inventory->product->sku ?? '—' }}</p>

                        <p class="text-muted small mb-1">Store</p>
                        <p class="fw-semibold mb-3">{{ $inventory->store->name ?? '—' }}</p>

                        <p class="text-muted small mb-1">Current Quantity</p>
                        <p class="fw-bold fs-4 mb-0">{{ number_format($inventory->quantity) }}</p>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-0">
                            Select a branch, store, and product to view the current stock level here.
                        </p>
                    </div>
                </div>
            @endisset
        </div>

    </div>

</div>
</div>
@endsection
