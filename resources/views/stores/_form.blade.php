<div class="row g-3">

    {{-- Store Name --}}
    <div class="col-md-6">
        <label for="name" class="form-label">Store Name</label>
        <input
            type="text"
            class="form-control @error('name') is-invalid @enderror"
            id="name"
            name="name"
            value="{{ old('name', $store->name ?? '') }}"
            placeholder="e.g. Westlands Mall Store"
            required
            autofocus
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Store Code --}}
    <div class="col-md-6">
        <label for="code" class="form-label">Store Code</label>
        <input
            type="text"
            class="form-control @error('code') is-invalid @enderror"
            id="code"
            name="code"
            value="{{ old('code', $store->code ?? '') }}"
            placeholder="e.g. WLM-S01"
            required
        >
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Branch --}}
    <div class="col-md-6">
        <label for="branch_id" class="form-label">Branch</label>
        <select
            class="form-select @error('branch_id') is-invalid @enderror"
            id="branch_id"
            name="branch_id"
            required
        >
            <option value="" disabled {{ old('branch_id', $store->branch_id ?? '') ? '' : 'selected' }}>
                Select a branch
            </option>
            @foreach ($branches as $branch)
                <option
                    value="{{ $branch->id }}"
                    {{ (string) old('branch_id', $store->branch_id ?? '') === (string) $branch->id ? 'selected' : '' }}
                >
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        @error('branch_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">A store must always belong to a branch.</div>
    </div>

    {{-- Status --}}
    <div class="col-md-6">
        <label for="is_active" class="form-label">Status</label>
        <select
            class="form-select @error('is_active') is-invalid @enderror"
            id="is_active"
            name="is_active"
        >
            <option value="1" {{ old('is_active', $store->is_active ?? 1) == 1 ? 'selected' : '' }}>Active</option>
            <option value="0" {{ old('is_active', $store->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('is_active')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Location --}}
    <div class="col-md-6">
        <label for="location" class="form-label">Location</label>
        <input
            type="text"
            class="form-control @error('location') is-invalid @enderror"
            id="location"
            name="location"
            value="{{ old('location', $store->location ?? '') }}"
            placeholder="e.g. Nairobi, Kenya"
            required
        >
        @error('location')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Phone --}}
    <div class="col-md-6">
        <label for="phone" class="form-label">Phone</label>
        <input
            type="tel"
            class="form-control @error('phone') is-invalid @enderror"
            id="phone"
            name="phone"
            value="{{ old('phone', $store->phone ?? '') }}"
            placeholder="+254 700 000000"
        >
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Email --}}
    <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input
            type="email"
            class="form-control @error('email') is-invalid @enderror"
            id="email"
            name="email"
            value="{{ old('email', $store->email ?? '') }}"
            placeholder="store@example.com"
        >
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Address --}}
    <div class="col-12">
        <label for="address" class="form-label">Address</label>
        <textarea
            class="form-control @error('address') is-invalid @enderror"
            id="address"
            name="address"
            rows="3"
            placeholder="Full postal or physical address"
        >{{ old('address', $store->address ?? '') }}</textarea>
        @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

</div>
