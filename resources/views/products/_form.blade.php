<div class="row g-4">

    {{-- Basic Information --}}
    <div class="col-12">
        <h2 class="h6 fw-bold mb-3">Basic Information</h2>
        <div class="row g-3">

            <div class="col-md-6">
                <label for="name" class="form-label">Product Name</label>
                <input
                    type="text"
                    class="form-control @error('name') is-invalid @enderror"
                    id="name"
                    name="name"
                    value="{{ old('name', $product->name ?? '') }}"
                    placeholder="e.g. 1kg Sugar"
                    required
                    autofocus
                >
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="sku" class="form-label">SKU</label>
                <input
                    type="text"
                    class="form-control @error('sku') is-invalid @enderror"
                    id="sku"
                    name="sku"
                    value="{{ old('sku', $product->sku ?? '') }}"
                    placeholder="e.g. SUG-1KG"
                    required
                >
                @error('sku')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="barcode" class="form-label">Barcode <span class="text-muted fw-normal">(optional)</span></label>
                <input
                    type="text"
                    class="form-control @error('barcode') is-invalid @enderror"
                    id="barcode"
                    name="barcode"
                    value="{{ old('barcode', $product->barcode ?? '') }}"
                    placeholder="e.g. 6009123456789"
                >
                @error('barcode')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @isset($categories)
                <div class="col-md-6">
                    <label for="category_id" class="form-label">Category</label>
                    <select
                        class="form-select @error('category_id') is-invalid @enderror"
                        id="category_id"
                        name="category_id"
                    >
                        <option value="">Uncategorized</option>
                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                {{ (string) old('category_id', $product->category_id ?? '') === (string) $category->id ? 'selected' : '' }}
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endisset

            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea
                    class="form-control @error('description') is-invalid @enderror"
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="Short description of the product"
                >{{ old('description', $product->description ?? '') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

        </div>
    </div>

    <div class="col-12"><hr></div>

    {{-- Pricing --}}
    <div class="col-12">
        <h2 class="h6 fw-bold mb-3">Pricing</h2>
        <div class="row g-3">

            <div class="col-md-6">
                <label for="cost_price" class="form-label">Cost Price (KSh)</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    class="form-control @error('cost_price') is-invalid @enderror"
                    id="cost_price"
                    name="cost_price"
                    value="{{ old('cost_price', $product->cost_price ?? '') }}"
                    placeholder="0.00"
                    required
                >
                @error('cost_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="selling_price" class="form-label">Selling Price (KSh)</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    class="form-control @error('selling_price') is-invalid @enderror"
                    id="selling_price"
                    name="selling_price"
                    value="{{ old('selling_price', $product->selling_price ?? '') }}"
                    placeholder="0.00"
                    required
                >
                @error('selling_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

        </div>
    </div>

    <div class="col-12"><hr></div>

    {{-- Inventory Settings --}}
    <div class="col-12">
        <h2 class="h6 fw-bold mb-3">Inventory Settings</h2>
        <div class="row g-3">

            <div class="col-md-6">
                <label for="minimum_stock" class="form-label">Minimum Stock Level</label>
                <input
                    type="number"
                    min="0"
                    class="form-control @error('minimum_stock') is-invalid @enderror"
                    id="minimum_stock"
                    name="minimum_stock"
                    value="{{ old('minimum_stock', $product->minimum_stock ?? '') }}"
                    placeholder="e.g. 10"
                    required
                >
                @error('minimum_stock')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Used to flag this product as low stock across stores.</div>
            </div>

            <div class="col-md-6">
                <label for="is_active" class="form-label">Status</label>
                <select
                    class="form-select @error('is_active') is-invalid @enderror"
                    id="is_active"
                    name="is_active"
                >
                    <option value="1" {{ old('is_active', $product->is_active ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $product->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('is_active')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

        </div>
    </div>

    <div class="col-12"><hr></div>

    {{-- Product Image --}}
    <div class="col-12">
        <h2 class="h6 fw-bold mb-3">Product Image <span class="text-muted fw-normal">(optional)</span></h2>

        @isset($product)
            @if ($product->image_url ?? false)
                <div class="mb-3">
                    <img
                        src="{{ $product->image_url }}"
                        alt="{{ $product->name }}"
                        class="img-thumbnail"
                        style="max-width: 160px;"
                    >
                    <div class="form-text">Current image. Uploading a new file below will replace it.</div>
                </div>
            @endif
        @endisset

        <input
            type="file"
            class="form-control @error('image') is-invalid @enderror"
            id="image"
            name="image"
            accept="image/*"
        >
        @error('image')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

</div>
