@extends('layouts.app')

@section('content')
    <div class="bg-light min-vh-100 py-4">
        <div class="container-fluid px-3 px-md-4">

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-0">Create Sale</h1>
                    <p class="text-muted small mb-0">Add products and complete a new sale.</p>
                </div>
                <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                    Back to Sales
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    Please fix the errors below and try again.
                </div>
            @endif

            <form id="saleForm" method="POST" action="{{ route('sales.store') }}" novalidate>
                @csrf

                <div class="row g-4">

                    <div class="col-lg-8">

                        {{-- Sale Information --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 pt-3">
                                <h2 class="h6 fw-bold mb-0">Sale Information</h2>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="branch_id" class="form-label">Branch</label>
                                        <select class="form-select @error('branch_id') is-invalid @enderror" id="branch_id"
                                            name="branch_id" required>
                                            <option value="" disabled {{ old('branch_id') ? '' : 'selected' }}>Select
                                                a branch</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}"
                                                    {{ (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>
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

                                        <select class="form-select @error('store_id') is-invalid @enderror" id="store_id"
                                            name="store_id" required>

                                            <option value="" disabled {{ old('store_id') ? '' : 'selected' }}>
                                                Select a store
                                            </option>

                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}" data-branch-id="{{ $store->branch_id }}"
                                                    {{ (string) old('store_id') === (string) $store->id ? 'selected' : '' }}>
                                                    {{ $store->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <div id="stockLoading" class="small text-muted mt-1 d-none">
                                            Loading stock...
                                        </div>

                                        @error('store_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="customer_name" class="form-label">Customer Name <span
                                                class="text-muted fw-normal">(optional)</span></label>
                                        <input type="text"
                                            class="form-control @error('customer_name') is-invalid @enderror"
                                            id="customer_name" name="customer_name" value="{{ old('customer_name') }}"
                                            placeholder="Walk-in customer">
                                        @error('customer_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="customer_phone" class="form-label">Customer Phone <span
                                                class="text-muted fw-normal">(optional)</span></label>
                                        <input type="tel"
                                            class="form-control @error('customer_phone') is-invalid @enderror"
                                            id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}"
                                            placeholder="+254 700 000000">
                                        @error('customer_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="payment_method" class="form-label">Payment Method</label>
                                        <select class="form-select @error('payment_method') is-invalid @enderror"
                                            id="payment_method" name="payment_method" required>
                                            <option value="cash"
                                                {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                                            <option value="mpesa"
                                                {{ old('payment_method') === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                                            <option value="card"
                                                {{ old('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                                            <option value="bank_transfer"
                                                {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank
                                                Transfer</option>
                                        </select>
                                        @error('payment_method')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="sale_date" class="form-label">Sale Date</label>
                                        <input type="datetime-local"
                                            class="form-control @error('sale_date') is-invalid @enderror" id="sale_date"
                                            name="sale_date" value="{{ old('sale_date', now()->format('Y-m-d\TH:i')) }}"
                                            required>
                                        @error('sale_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Product Selection --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 pt-3">
                                <h2 class="h6 fw-bold mb-0">Add Products</h2>
                            </div>
                            <div class="card-body">
                                <input type="text" id="productSearchInput" class="form-control mb-3"
                                    placeholder="Search products by name or SKU...">

                                <div class="table-responsive" style="max-height: 360px; overflow-y: auto;">
                                    <table class="table table-hover align-middle mb-0" id="productCatalogTable">
                                        <thead>
                                            <tr class="text-muted small text-uppercase">
                                                <th>Product</th>
                                                <th>SKU</th>
                                                <th class="text-end">Price</th>
                                                <th class="text-end">Available Stock</th>
                                                <th style="width: 90px;">Qty</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($products as $product)
                                                <tr class="product-row" data-id="{{ $product->id }}"
                                                    data-name="{{ $product->name }}" data-sku="{{ $product->sku }}"
                                                    data-price="{{ $product->selling_price }}" data-stock="0">

                                                    <td class="product-name fw-semibold">
                                                        {{ $product->name }}
                                                    </td>

                                                    <td class="product-sku">
                                                        {{ $product->sku }}
                                                    </td>

                                                    <td class="text-end">
                                                        KSh {{ number_format($product->selling_price, 2) }}
                                                    </td>

                                                    <td class="text-end available-stock">
                                                        0
                                                    </td>

                                                    <td>
                                                        <input type="number"
                                                            class="form-control form-control-sm product-qty-input"
                                                            min="1" max="0" value="1" disabled>
                                                    </td>

                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary add-item-btn" disabled>
                                                            Add
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        No products available.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Sale Items --}}
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 pt-3">
                                <h2 class="h6 fw-bold mb-0">Sale Items</h2>
                            </div>
                            <div class="card-body">
                                <div id="saleItemsHiddenInputs"></div>

                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr class="text-muted small text-uppercase">
                                                <th>Product</th>
                                                <th>SKU</th>
                                                <th class="text-end">Unit Price</th>
                                                <th style="width: 90px;">Quantity</th>
                                                <th style="width: 110px;">Discount</th>
                                                <th class="text-end">Line Total</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="saleItemsTableBody">
                                            <tr id="noItemsRow">
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    No items added yet. Use "Add Products" above to build this sale.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Payment Summary --}}
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm" style="position: sticky; top: 1rem;">
                            <div class="card-header bg-white border-0 pt-3">
                                <h2 class="h6 fw-bold mb-0">Payment Summary</h2>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Subtotal</span>
                                    <span id="summarySubtotal" class="fw-semibold">KSh 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Discount</span>
                                    <span id="summaryDiscount" class="fw-semibold">KSh 0.00</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="fw-bold">Total Amount</span>
                                    <span id="summaryTotal" class="fw-bold fs-5">KSh 0.00</span>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary py-2">Complete Sale</button>
                                    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </form>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let items = [];
            let nextRowId = 1;

            const catalogRows = document.querySelectorAll('.product-row');
            const itemsBody = document.getElementById('saleItemsTableBody');
            const hiddenInputsContainer = document.getElementById('saleItemsHiddenInputs');
            const noItemsRow = document.getElementById('noItemsRow');
            const productSearchInput = document.getElementById('productSearchInput');
            const saleForm = document.getElementById('saleForm');

            function formatMoney(amount) {
                return 'KSh ' + Number(amount || 0).toLocaleString('en-KE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function calculateLineTotal(item) {
                const total = (Number(item.unitPrice) * Number(item.quantity)) -
                    Number(item.discount || 0);

                return total > 0 ? total : 0;
            }

            function updateSummary() {
                let subtotal = 0;
                let discount = 0;

                items.forEach(function(item) {
                    subtotal += Number(item.unitPrice) * Number(item.quantity);
                    discount += Number(item.discount || 0);
                });

                const total = Math.max(subtotal - discount, 0);

                document.getElementById('summarySubtotal').textContent =
                    formatMoney(subtotal);

                document.getElementById('summaryDiscount').textContent =
                    formatMoney(discount);

                document.getElementById('summaryTotal').textContent =
                    formatMoney(total);
            }

            function updateCatalogButtons() {
                catalogRows.forEach(function(row) {
                    const productId = String(row.dataset.id);
                    const stock = parseInt(row.dataset.stock, 10) || 0;
                    const addButton = row.querySelector('.add-item-btn');

                    if (!addButton) {
                        return;
                    }

                    const item = items.find(function(item) {
                        return String(item.productId) === productId;
                    });

                    const currentQuantity = item ? Number(item.quantity) : 0;
                    const remainingStock = stock - currentQuantity;

                    if (remainingStock <= 0) {
                        addButton.disabled = true;
                        addButton.textContent = 'Added';
                    } else {
                        addButton.disabled = false;
                        addButton.textContent = 'Add';
                    }
                });
            }

            function bindItemRowEvents() {
                document.querySelectorAll('.item-qty-input').forEach(function(input) {
                    input.addEventListener('change', function() {
                        const rowId = Number(input.dataset.rowId);

                        const item = items.find(function(item) {
                            return item.rowId === rowId;
                        });

                        if (!item) {
                            return;
                        }

                        let quantity = parseInt(input.value, 10) || 1;

                        quantity = Math.max(1, quantity);
                        quantity = Math.min(quantity, item.stock);

                        item.quantity = quantity;

                        render();
                    });
                });

                document.querySelectorAll('.item-discount-input').forEach(function(input) {
                    input.addEventListener('change', function() {
                        const rowId = Number(input.dataset.rowId);

                        const item = items.find(function(item) {
                            return item.rowId === rowId;
                        });

                        if (!item) {
                            return;
                        }

                        let discount = parseFloat(input.value) || 0;

                        const maxDiscount =
                            Number(item.unitPrice) * Number(item.quantity);

                        discount = Math.max(0, discount);
                        discount = Math.min(discount, maxDiscount);

                        item.discount = discount;

                        render();
                    });
                });

                document.querySelectorAll('.remove-item-btn').forEach(function(button) {
                    button.addEventListener('click', function() {
                        const rowId = Number(button.dataset.rowId);

                        items = items.filter(function(item) {
                            return item.rowId !== rowId;
                        });

                        render();
                    });
                });
            }

            const branchSelect = document.getElementById('branch_id');
            const storeSelect = document.getElementById('store_id');

            function filterStoresByBranch() {
                const branchId = branchSelect.value;
                const currentStoreId = storeSelect.value;

                // Reset store selection
                storeSelect.value = '';

                let hasVisibleStores = false;

                Array.from(storeSelect.options).forEach(function(option) {
                    // Always show the placeholder
                    if (!option.value) {
                        option.hidden = false;
                        return;
                    }

                    const storeBranchId = option.dataset.branchId;

                    if (storeBranchId === branchId) {
                        option.hidden = false;
                        hasVisibleStores = true;
                    } else {
                        option.hidden = true;
                    }
                });

                // If the previously selected store belongs to this branch,
                // keep it selected (useful after validation errors).
                if (currentStoreId) {
                    const selectedOption = Array.from(storeSelect.options).find(function(option) {
                        return option.value === currentStoreId &&
                            option.dataset.branchId === branchId;
                    });

                    if (selectedOption) {
                        storeSelect.value = currentStoreId;
                    }
                }

                // Disable store selection until a branch is selected
                storeSelect.disabled = !branchId || !hasVisibleStores;
            }

            branchSelect.addEventListener('change', function() {
                filterStoresByBranch();
            });

            // Run once when the page loads
            filterStoresByBranch();

            function render() {
                itemsBody.innerHTML = '';
                hiddenInputsContainer.innerHTML = '';

                if (items.length === 0) {
                    itemsBody.appendChild(noItemsRow);
                } else {
                    items.forEach(function(item, index) {
                        const row = document.createElement('tr');

                        row.innerHTML = `
                        <td class="fw-semibold">${item.name}</td>

                        <td>${item.sku}</td>

                        <td class="text-end">
                            ${formatMoney(item.unitPrice)}
                        </td>

                        <td>
                            <input
                                type="number"
                                class="form-control form-control-sm item-qty-input"
                                min="1"
                                max="${item.stock}"
                                value="${item.quantity}"
                                data-row-id="${item.rowId}"
                            >
                        </td>

                        <td>
                            <input
                                type="number"
                                class="form-control form-control-sm item-discount-input"
                                min="0"
                                step="0.01"
                                value="${item.discount}"
                                data-row-id="${item.rowId}"
                            >
                        </td>

                        <td class="text-end fw-semibold">
                            ${formatMoney(calculateLineTotal(item))}
                        </td>

                        <td>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger remove-item-btn"
                                data-row-id="${item.rowId}"
                            >
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </td>
                    `;

                        itemsBody.appendChild(row);

                        // Hidden inputs submitted to Laravel
                        const fields = {
                            product_id: item.productId,
                            quantity: item.quantity,
                            unit_price: item.unitPrice,
                            discount: item.discount
                        };

                        Object.keys(fields).forEach(function(field) {
                            const input = document.createElement('input');

                            input.type = 'hidden';
                            input.name = `items[${index}][${field}]`;
                            input.value = fields[field];

                            hiddenInputsContainer.appendChild(input);
                        });
                    });
                }

                updateSummary();
                updateCatalogButtons();
                bindItemRowEvents();
            }

            // Add product buttons
            catalogRows.forEach(function(row) {
                const addButton = row.querySelector('.add-item-btn');

                if (!addButton) {
                    return;
                }

                addButton.addEventListener('click', function(event) {
                    event.preventDefault();

                    const productId = String(row.dataset.id);
                    const productName = row.dataset.name;
                    const productSku = row.dataset.sku;

                    const unitPrice =
                        parseFloat(row.dataset.price) || 0;

                    const stock =
                        parseInt(row.dataset.stock, 10) || 0;

                    const qtyInput =
                        row.querySelector('.product-qty-input');

                    if (stock <= 0) {
                        alert('This product is out of stock.');
                        return;
                    }

                    let quantity =
                        parseInt(qtyInput.value, 10) || 1;

                    quantity = Math.max(1, quantity);
                    quantity = Math.min(quantity, stock);

                    // Check if product already exists
                    const existingItem = items.find(function(item) {
                        return String(item.productId) === productId;
                    });

                    if (existingItem) {
                        existingItem.quantity = Math.min(
                            Number(existingItem.quantity) + quantity,
                            existingItem.stock
                        );
                    } else {
                        items.push({
                            rowId: nextRowId++,
                            productId: productId,
                            name: productName,
                            sku: productSku,
                            unitPrice: unitPrice,
                            quantity: quantity,
                            discount: 0,
                            stock: stock
                        });
                    }

                    // Reset quantity
                    qtyInput.value = 1;

                    // Re-render sale items
                    render();
                });
            });

            // Product search
            if (productSearchInput) {
                productSearchInput.addEventListener('input', function() {
                    const term =
                        productSearchInput.value.trim().toLowerCase();

                    catalogRows.forEach(function(row) {
                        const name =
                            row.querySelector('.product-name')
                            .textContent
                            .toLowerCase();

                        const sku =
                            row.querySelector('.product-sku')
                            .textContent
                            .toLowerCase();

                        row.style.display =
                            name.includes(term) || sku.includes(term) ?
                            '' :
                            'none';
                    });
                });
            }

            // Prevent submitting an empty sale
            saleForm.addEventListener('submit', function(event) {
                if (items.length === 0) {
                    event.preventDefault();

                    alert(
                        'Please add at least one product to the sale before completing it.'
                    );
                }
            });

            // Initial render
            render();
        });

        const branchSelect = document.getElementById('branch_id');
        const storeSelect = document.getElementById('store_id');
        const stockLoading = document.getElementById('stockLoading');

        /**
         * Filter stores according to selected branch.
         */
        function filterStores() {

            const branchId = branchSelect.value;

            Array.from(storeSelect.options).forEach(option => {

                if (!option.value) {
                    option.hidden = false;
                    return;
                }

                option.hidden = option.dataset.branchId !== branchId;
            });

            // Reset store selection
            storeSelect.value = '';

            // Reset all product stock
            resetProductStock();
        }


        /**
         * Reset product stock to zero.
         */
        function resetProductStock() {

            document.querySelectorAll('.product-row').forEach(row => {

                const stockCell = row.querySelector('.available-stock');
                const qtyInput = row.querySelector('.product-qty-input');
                const addButton = row.querySelector('.add-item-btn');

                row.dataset.stock = 0;

                stockCell.textContent = '0';

                qtyInput.max = 0;
                qtyInput.value = 1;
                qtyInput.disabled = true;

                addButton.disabled = true;
            });
        }


        /**
         * Load inventory for selected store.
         */
        async function loadStoreStock() {

            const storeId = storeSelect.value;

            if (!storeId) {
                resetProductStock();
                return;
            }

            stockLoading.classList.remove('d-none');

            try {

                const url = new URL(
                    "{{ route('sales.products.stock') }}",
                    window.location.origin
                );

                url.searchParams.set('store_id', storeId);

                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to load product stock.');
                }

                const stock = await response.json();

                updateProductStock(stock);

            } catch (error) {

                console.error(error);

                alert('Unable to load product stock. Please try again.');

                resetProductStock();

            } finally {

                stockLoading.classList.add('d-none');
            }
        }


        /**
         * Update every product row with the stock
         * belonging to the selected store.
         */
        function updateProductStock(stock) {

            document.querySelectorAll('.product-row').forEach(row => {

                const productId = row.dataset.id;

                const availableStock = parseInt(
                    stock[productId] ?? 0
                );

                const stockCell = row.querySelector('.available-stock');
                const qtyInput = row.querySelector('.product-qty-input');
                const addButton = row.querySelector('.add-item-btn');

                // Store stock on row
                row.dataset.stock = availableStock;

                // Display stock
                stockCell.textContent = availableStock.toLocaleString();

                // Update quantity input
                qtyInput.max = availableStock;
                qtyInput.value = availableStock > 0 ? 1 : 0;
                qtyInput.disabled = availableStock <= 0;

                // Enable/disable Add button
                addButton.disabled = availableStock <= 0;
            });
        }


        /**
         * Branch changed.
         */
        branchSelect.addEventListener('change', function() {
            filterStores();
        });


        /**
         * Store changed.
         */
        storeSelect.addEventListener('change', function() {
            loadStoreStock();
        });


        /**
         * Prevent quantity from exceeding available stock.
         */
        document.addEventListener('input', function(event) {

            if (!event.target.classList.contains('product-qty-input')) {
                return;
            }

            const input = event.target;

            const max = parseInt(input.max || 0);
            let value = parseInt(input.value || 0);

            if (value < 1) {
                value = 1;
            }

            if (value > max) {
                value = max;
            }

            input.value = value;
        });


        /**
         * Load stock automatically if an old store exists
         * after validation failure.
         */
        if (storeSelect.value) {
            loadStoreStock();
        }
    </script>
@endsection
