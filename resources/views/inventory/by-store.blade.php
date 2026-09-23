@extends('layouts.app')

@section('content')
    <div class="bg-light min-vh-100 py-4">
        <div class="container-fluid px-3 px-md-4">

            <div class="mb-4">
                <h1 class="h3 fw-bold mb-0">Inventory by Store</h1>
                <p class="text-muted small mb-0">View stock levels for a specific store.</p>
            </div>

            {{-- Store Selection --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('inventory.by-store') }}" class="row g-2 align-items-center">
                        @isset($branches)
                            <div class="col-md-4">
                                <select name="branch_id" class="form-select">
                                    <option value="">Select a Branch</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ (string) request('branch_id') === (string) $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endisset
                        @isset($stores)
                            <div class="col-md-4">
                                <select name="store_id" class="form-select">
                                    <option value="">Select a Store</option>
                                    @foreach ($stores as $storeOption)
                                        <option value="{{ $storeOption->id }}" data-branch-id="{{ $storeOption->branch_id }}"
                                            {{ isset($store) && $store->id === $storeOption->id ? 'selected' : '' }}>
                                            {{ $storeOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endisset
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary w-100">View Store</button>
                        </div>
                    </form>
                </div>
            </div>

            @isset($store)
                {{-- Store Summary --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div>
                                <h2 class="h5 fw-bold mb-0">{{ $store->name }}</h2>
                                <p class="text-muted small mb-0">{{ $store->branch->name ?? '—' }}</p>
                            </div>
                        </div>

                        <div class="row g-3 text-center">
                            <div class="col-6 col-md-3">
                                <p class="text-muted small mb-1">Total Products</p>
                                <p class="fw-bold fs-5 mb-0">{{ number_format($storeTotalProducts ?? 0) }}</p>
                            </div>
                            <div class="col-6 col-md-3">
                                <p class="text-muted small mb-1">Total Units</p>
                                <p class="fw-bold fs-5 mb-0">{{ number_format($storeTotalUnits ?? 0) }}</p>
                            </div>
                            <div class="col-6 col-md-3">
                                <p class="text-muted small mb-1">Low-Stock Items</p>
                                <p class="fw-bold fs-5 mb-0 text-warning">{{ number_format($storeLowStockCount ?? 0) }}</p>
                            </div>
                            <div class="col-6 col-md-3">
                                <p class="text-muted small mb-1">Out-of-Stock Items</p>
                                <p class="fw-bold fs-5 mb-0 text-danger">{{ number_format($storeOutOfStockCount ?? 0) }}</p>
                            </div>
                            <div class="col-12">
                                <p class="text-muted small mb-1">Total Inventory Value</p>
                                <p class="fw-bold fs-5 mb-0">KSh {{ number_format($storeInventoryValue ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Store Inventory Table --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small text-uppercase">
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Quantity Available</th>
                                        <th>Minimum Stock Level</th>
                                        <th>Cost Price</th>
                                        <th>Selling Price</th>
                                        <th>Stock Value</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($inventories as $inventory)
                                        @php
                                            $minLevel = $inventory->product->minimum_stock ?? 0;
                                            $stockValue = $inventory->quantity * ($inventory->product->cost_price ?? 0);
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="fw-semibold">{{ $inventory->product->name ?? '—' }}</td>
                                            <td>{{ $inventory->product->sku ?? '—' }}</td>
                                            <td>{{ number_format($inventory->quantity) }}</td>
                                            <td>{{ number_format($minLevel) }}</td>
                                            <td>KSh {{ number_format($inventory->product->cost_price ?? 0, 2) }}</td>
                                            <td>KSh {{ number_format($inventory->product->selling_price ?? 0, 2) }}</td>
                                            <td>KSh {{ number_format($stockValue, 2) }}</td>
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
                                            <td colspan="9" class="text-center text-muted py-5">
                                                <i class="bi bi-boxes fs-2 d-block mb-2"></i>
                                                No inventory records found for this store.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-shop fs-2 d-block mb-2"></i>
                        Select a branch and store above to view its inventory.
                    </div>
                </div>
            @endisset

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const branchSelect = document.querySelector('select[name="branch_id"]');
            const storeSelect = document.querySelector('select[name="store_id"]');

            if (!branchSelect || !storeSelect) {
                return;
            }

            const allStores = Array.from(storeSelect.options)
                .filter(option => option.value !== '')
                .map(option => ({
                    value: option.value,
                    text: option.text,
                    branchId: option.dataset.branchId
                }));

            function filterStores() {
                const branchId = branchSelect.value;
                const currentStoreId = storeSelect.value;

                storeSelect.innerHTML = '<option value="">Select a Store</option>';

                if (!branchId) {
                    storeSelect.disabled = true;
                    return;
                }

                allStores
                    .filter(store => String(store.branchId) === String(branchId))
                    .forEach(store => {
                        const option = document.createElement('option');

                        option.value = store.value;
                        option.textContent = store.text;

                        if (String(store.value) === String(currentStoreId)) {
                            option.selected = true;
                        }

                        storeSelect.appendChild(option);
                    });

                storeSelect.disabled = storeSelect.options.length === 1;
            }

            branchSelect.addEventListener('change', function() {
                storeSelect.value = '';
                filterStores();
            });

            filterStores();
        });
    </script>
@endsection
