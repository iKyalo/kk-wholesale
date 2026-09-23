<div class="row g-4">

    {{-- Transfer Information --}}
    <div class="col-12">
        <h2 class="h6 fw-bold mb-3">Transfer Information</h2>
        <div class="row g-3">

            <div class="col-md-3">
                <label for="source_branch_id" class="form-label">Source Branch</label>
                <select class="form-select @error('source_branch_id') is-invalid @enderror" id="source_branch_id"
                    name="source_branch_id" required>
                    <option value="" disabled
                        {{ old('source_branch_id', $transfer->sourceStore->branch_id ?? '') ? '' : 'selected' }}>Select
                        branch</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}"
                            {{ (string) old('source_branch_id', $transfer->sourceStore->branch_id ?? '') === (string) $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                @error('source_branch_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="source_store_id" class="form-label">Source Store</label>
                <select class="form-select @error('source_store_id') is-invalid @enderror" id="source_store_id"
                    name="source_store_id" required>
                    <option value="" disabled
                        {{ old('source_store_id', $transfer->source_store_id ?? '') ? '' : 'selected' }}>Select store
                    </option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->id }}" data-branch-id="{{ $store->branch_id }}"
                            {{ (string) old('source_store_id', $transfer->source_store_id ?? '') === (string) $store->id ? 'selected' : '' }}>
                            {{ $store->name }}
                        </option>
                    @endforeach
                </select>
                @error('source_store_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="destination_branch_id" class="form-label">Destination Branch</label>
                <select class="form-select @error('destination_branch_id') is-invalid @enderror"
                    id="destination_branch_id" name="destination_branch_id" required>
                    <option value="" disabled
                        {{ old('destination_branch_id', $transfer->destinationStore->branch_id ?? '') ? '' : 'selected' }}>
                        Select branch</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}"
                            {{ (string) old('destination_branch_id', $transfer->destinationStore->branch_id ?? '') === (string) $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                @error('destination_branch_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="destination_store_id" class="form-label">Destination Store</label>
                <select class="form-select @error('destination_store_id') is-invalid @enderror"
                    id="destination_store_id" name="destination_store_id" required>
                    <option value="" disabled
                        {{ old('destination_store_id', $transfer->destination_store_id ?? '') ? '' : 'selected' }}>
                        Select store</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->id }}" data-branch-id="{{ $store->branch_id }}"
                            {{ (string) old('destination_store_id', $transfer->destination_store_id ?? '') === (string) $store->id ? 'selected' : '' }}>
                            {{ $store->name }}
                        </option>
                    @endforeach
                </select>
                @error('destination_store_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div id="sameStoreWarning" class="form-text text-danger d-none">Source and destination stores must be
                    different.</div>
            </div>

            <div class="col-md-4">
                <label for="transfer_date" class="form-label">Transfer Date</label>
                <input type="date" class="form-control @error('transfer_date') is-invalid @enderror"
                    id="transfer_date" name="transfer_date"
                    value="{{ old('transfer_date', isset($transfer) ? $transfer->transfer_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                    required>
                @error('transfer_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-8">
                <label for="notes" class="form-label">Notes <span
                        class="text-muted fw-normal">(optional)</span></label>
                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="1"
                    placeholder="Reason for transfer or handling instructions">{{ old('notes', $transfer->notes ?? '') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

        </div>
    </div>

    <div class="col-12">
        <hr>
    </div>

    {{-- Product Selection --}}
    <div class="col-12">
        <h2 class="h6 fw-bold mb-3">Add Products</h2>

        <input type="text" id="productSearchInput" class="form-control mb-3"
            placeholder="Search products by name or SKU...">

        <div class="table-responsive mb-4" style="max-height: 320px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0" id="productCatalogTable">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th>Product</th>
                        <th>SKU</th>
                        <th class="text-end">Available at Source</th>
                        <th style="width: 100px;">Qty</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="product-row" data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                            data-sku="{{ $product->sku }}"
                            data-stock-map='{{ json_encode($product->stock_by_store ?? []) }}'>
                            <td class="product-name fw-semibold">{{ $product->name }}</td>
                            <td class="product-sku">{{ $product->sku }}</td>
                            <td class="text-end product-available-stock">—</td>
                            <td>
                                <input type="number" class="form-control form-control-sm product-qty-input"
                                    min="1" value="1">
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary add-item-btn">Add</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No products available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Transfer Items --}}
        <div id="transferItemsHiddenInputs"></div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th>Product</th>
                        <th>SKU</th>
                        <th style="width: 100px;">Quantity</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="transferItemsTableBody">
                    <tr id="noTransferItemsRow">
                        <td colspan="4" class="text-center text-muted py-4">No products added yet.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-12">
        <hr>
    </div>

    {{-- Transfer Summary --}}
    <div class="col-12">
        <div class="card bg-light border-0">
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-6 col-md-3">
                        <p class="text-muted small mb-1">Number of Products</p>
                        <p class="fw-bold fs-5 mb-0" id="summaryProductCount">0</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="text-muted small mb-1">Total Units to Transfer</p>
                        <p class="fw-bold fs-5 mb-0" id="summaryTotalUnits">0</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="text-muted small mb-1">Source Store</p>
                        <p class="fw-bold fs-6 mb-0" id="summarySourceStore">—</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="text-muted small mb-1">Destination Store</p>
                        <p class="fw-bold fs-6 mb-0" id="summaryDestinationStore">—</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Bootstrap data for existing items when editing --}}
<script id="initialTransferItemsData" type="application/json">
    @isset($transfer)
        [
            @foreach ($transfer->items as $item)
                {
                    "productId": "{{ $item->product_id }}",
                    "name": {{ Js::from($item->product->name ?? '') }},
                    "sku": {{ Js::from($item->product->sku ?? '') }},
                    "quantity": {{ (int) $item->quantity }}
                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ]
    @else
        []
    @endisset
</script>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let items = [];
            let nextRowId = 1;

            const sourceBranchSelect = document.getElementById('source_branch_id');
            const sourceStoreSelect = document.getElementById('source_store_id');
            const destinationBranchSelect = document.getElementById('destination_branch_id');
            const destinationStoreSelect = document.getElementById('destination_store_id');
            const sameStoreWarning = document.getElementById('sameStoreWarning');

            const catalogRows = document.querySelectorAll('.product-row');
            const itemsBody = document.getElementById('transferItemsTableBody');
            const hiddenInputsContainer = document.getElementById('transferItemsHiddenInputs');
            const noItemsRow = document.getElementById('noTransferItemsRow');
            const productSearchInput = document.getElementById('productSearchInput');

            function filterStoresByBranch(branchSelect, storeSelect) {
                const branchId = branchSelect.value;
                Array.from(storeSelect.options).forEach(function(option) {
                    if (!option.value) return;
                    const matches = !branchId || option.dataset.branchId === branchId;
                    option.hidden = !matches;
                    option.disabled = !matches;
                });
                if (storeSelect.selectedOptions[0] && storeSelect.selectedOptions[0].hidden) {
                    storeSelect.value = '';
                }
            }

            function currentSourceStoreId() {
                return sourceStoreSelect.value;
            }

            function stockForProduct(row) {
                const stockMap = JSON.parse(row.dataset.stockMap || '{}');
                const storeId = currentSourceStoreId();
                return storeId && stockMap[storeId] !== undefined ? parseInt(stockMap[storeId], 10) : 0;
            }

            function refreshCatalogStockDisplay() {
                catalogRows.forEach(function(row) {
                    const stock = stockForProduct(row);
                    const cell = row.querySelector('.product-available-stock');
                    const qtyInput = row.querySelector('.product-qty-input');
                    const addButton = row.querySelector('.add-item-btn');

                    cell.textContent = currentSourceStoreId() ? stock.toLocaleString() : '—';
                    qtyInput.max = stock;
                    addButton.disabled = !currentSourceStoreId() || stock <= 0;
                });
            }

            function checkSameStore() {
                const same = sourceStoreSelect.value && sourceStoreSelect.value === destinationStoreSelect.value;
                sameStoreWarning.classList.toggle('d-none', !same);
                return same;
            }

            function updateSummary() {
                document.getElementById('summaryProductCount').textContent = items.length;
                document.getElementById('summaryTotalUnits').textContent = items.reduce(function(sum, i) {
                    return sum + i.quantity;
                }, 0);

                const sourceOption = sourceStoreSelect.selectedOptions[0];
                const destOption = destinationStoreSelect.selectedOptions[0];
                document.getElementById('summarySourceStore').textContent = sourceOption && sourceOption.value ?
                    sourceOption.textContent : '—';
                document.getElementById('summaryDestinationStore').textContent = destOption && destOption.value ?
                    destOption.textContent : '—';
            }

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
                    <td>
                        <input type="number" class="form-control form-control-sm item-qty-input" min="1" value="${item.quantity}" data-row-id="${item.rowId}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" data-row-id="${item.rowId}">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </td>
                `;
                        itemsBody.appendChild(row);

                        ['product_id', 'quantity'].forEach(function(field) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = `items[${index}][${field}]`;
                            input.value = field === 'product_id' ? item.productId : item.quantity;
                            hiddenInputsContainer.appendChild(input);
                        });
                    });
                }

                updateSummary();
                bindItemRowEvents();
            }

            function bindItemRowEvents() {
                document.querySelectorAll('.item-qty-input').forEach(function(input) {
                    input.addEventListener('change', function() {
                        const rowId = Number(input.dataset.rowId);
                        const item = items.find(function(i) {
                            return i.rowId === rowId;
                        });
                        if (!item) return;
                        let qty = parseInt(input.value, 10) || 1;
                        if (qty < 1) qty = 1;
                        item.quantity = qty;
                        render();
                    });
                });

                document.querySelectorAll('.remove-item-btn').forEach(function(button) {
                    button.addEventListener('click', function() {
                        const rowId = Number(button.dataset.rowId);
                        items = items.filter(function(i) {
                            return i.rowId !== rowId;
                        });
                        render();
                    });
                });
            }

            catalogRows.forEach(function(row) {
                const addButton = row.querySelector('.add-item-btn');
                addButton.addEventListener('click', function() {
                    const stock = stockForProduct(row);
                    const qtyInput = row.querySelector('.product-qty-input');
                    let qty = parseInt(qtyInput.value, 10) || 1;
                    if (stock <= 0) return;
                    if (qty > stock) qty = stock;
                    if (qty < 1) qty = 1;

                    const productId = row.dataset.id;
                    const existing = items.find(function(i) {
                        return i.productId === productId;
                    });

                    if (existing) {
                        existing.quantity = Math.min(existing.quantity + qty, stock);
                    } else {
                        items.push({
                            rowId: nextRowId++,
                            productId: productId,
                            name: row.dataset.name,
                            sku: row.dataset.sku,
                            quantity: qty,
                        });
                    }
                    render();
                });
            });

            if (productSearchInput) {
                productSearchInput.addEventListener('input', function() {
                    const term = productSearchInput.value.trim().toLowerCase();
                    catalogRows.forEach(function(row) {
                        const name = row.querySelector('.product-name').textContent.toLowerCase();
                        const sku = row.querySelector('.product-sku').textContent.toLowerCase();
                        row.style.display = (name.includes(term) || sku.includes(term)) ? '' :
                            'none';
                    });
                });
            }

            sourceBranchSelect.addEventListener('change', function() {
                filterStoresByBranch(sourceBranchSelect, sourceStoreSelect);
                refreshCatalogStockDisplay();
                checkSameStore();
                updateSummary();
            });

            destinationBranchSelect.addEventListener('change', function() {
                filterStoresByBranch(destinationBranchSelect, destinationStoreSelect);
                checkSameStore();
                updateSummary();
            });

            sourceStoreSelect.addEventListener('change', function() {
                refreshCatalogStockDisplay();
                checkSameStore();
                updateSummary();
            });

            destinationStoreSelect.addEventListener('change', function() {
                checkSameStore();
                updateSummary();
            });

            document.querySelector('form').addEventListener('submit', function(event) {
                if (checkSameStore()) {
                    event.preventDefault();
                    alert('Source and destination stores must be different.');
                    return;
                }
                if (items.length === 0) {
                    event.preventDefault();
                    alert('Please add at least one product to the transfer.');
                }
            });

            // Initial setup
            filterStoresByBranch(sourceBranchSelect, sourceStoreSelect);
            filterStoresByBranch(destinationBranchSelect, destinationStoreSelect);
            refreshCatalogStockDisplay();
            checkSameStore();

            try {
                const initialData = JSON.parse(document.getElementById('initialTransferItemsData').textContent ||
                    '[]');
                initialData.forEach(function(item) {
                    items.push({
                        rowId: nextRowId++,
                        productId: item.productId,
                        name: item.name,
                        sku: item.sku,
                        quantity: item.quantity,
                    });
                });
            } catch (e) {
                // No initial items to load
            }

            render();
        });
    </script>
@endpush
