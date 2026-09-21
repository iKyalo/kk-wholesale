<div class="row g-3">

    {{-- Branch Name --}}
    <div class="col-md-6">
        <label for="name" class="form-label">Branch Name</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
            value="{{ old('name', $branch->name ?? '') }}" placeholder="e.g. Westlands Branch" required autofocus>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Branch Code --}}
    <div class="col-md-6">
        <label for="code" class="form-label">Branch Code</label>
        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code"
            value="{{ old('code', $branch->code ?? '') }}" placeholder="e.g. WLB-001" required>
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Location --}}
    <div class="col-md-6">
        <label for="location" class="form-label">Location</label>
        <input type="text" class="form-control @error('location') is-invalid @enderror" id="location"
            name="location" value="{{ old('location', $branch->location ?? '') }}" placeholder="e.g. Nairobi, Kenya"
            required>
        @error('location')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Status --}}
    <div class="col-md-6">
        <label for="is_active" class="form-label">Status</label>
        <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
            <option value="1" {{ old('is_active', $branch->is_active ?? 1) == 1 ? 'selected' : '' }}>Active
            </option>
            <option value="0" {{ old('is_active', $branch->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive
            </option>
        </select>
        @error('is_active')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Phone --}}
    <div class="col-md-6">
        <label for="phone" class="form-label">Phone</label>
        <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone"
            value="{{ old('phone', $branch->phone ?? '') }}" placeholder="+254 700 000000">
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Email --}}
    <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
            value="{{ old('email', $branch->email ?? '') }}" placeholder="branch@example.com">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Address --}}
    <div class="col-12">
        <label for="address" class="form-label">Address</label>
        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3"
            placeholder="Full postal or physical address">{{ old('address', $branch->address ?? '') }}</textarea>
        @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

</div>
