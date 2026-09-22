{{--
    Expects: $roles, $branches, $stores
    Optional: $user (when editing)
    Role values assumed: administrator, branch_manager, store_manager
--}}

<div class="row g-3">

    {{-- Full Name --}}
    <div class="col-md-6">
        <label for="name" class="form-label">Full Name</label>
        <input
            type="text"
            class="form-control @error('name') is-invalid @enderror"
            id="name"
            name="name"
            value="{{ old('name', $user->name ?? '') }}"
            placeholder="e.g. Jane Wanjiru"
            required
            autofocus
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Email --}}
    <div class="col-md-6">
        <label for="email" class="form-label">Email Address</label>
        <input
            type="email"
            class="form-control @error('email') is-invalid @enderror"
            id="email"
            name="email"
            value="{{ old('email', $user->email ?? '') }}"
            placeholder="name@example.com"
            required
        >
        @error('email')
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
            value="{{ old('phone', $user->phone ?? '') }}"
            placeholder="+254 700 000000"
        >
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Status --}}
    <div class="col-md-6">
        <label for="is_active" class="form-label">Status</label>
        <select
            class="form-select @error('is_active') is-invalid @enderror"
            id="is_active"
            name="is_active"
        >
            <option value="1" {{ old('is_active', $user->is_active ?? 1) == 1 ? 'selected' : '' }}>Active</option>
            <option value="0" {{ old('is_active', $user->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('is_active')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Password --}}
    <div class="col-md-6">
        <label for="password" class="form-label">
            Password
            @isset($user)
                <span class="text-muted fw-normal">(leave blank to keep current password)</span>
            @endisset
        </label>
        <input
            type="password"
            class="form-control @error('password') is-invalid @enderror"
            id="password"
            name="password"
            autocomplete="new-password"
            placeholder="••••••••"
            {{ isset($user) ? '' : 'required' }}
        >
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Confirm Password --}}
    <div class="col-md-6">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input
            type="password"
            class="form-control @error('password_confirmation') is-invalid @enderror"
            id="password_confirmation"
            name="password_confirmation"
            autocomplete="new-password"
            placeholder="••••••••"
            {{ isset($user) ? '' : 'required' }}
        >
        @error('password_confirmation')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12"><hr></div>

    {{-- Role --}}
    <div class="col-md-6">
        <label for="role" class="form-label">Role</label>
        <select
            class="form-select @error('role') is-invalid @enderror"
            id="role"
            name="role"
            required
        >
            <option value="" disabled {{ old('role', $user->role ?? '') ? '' : 'selected' }}>Select a role</option>
            @foreach ($roles as $roleValue => $roleLabel)
                <option value="{{ $roleValue }}" {{ (string) old('role', $user->role ?? '') === (string) $roleValue ? 'selected' : '' }}>
                    {{ $roleLabel }}
                </option>
            @endforeach
        </select>
        @error('role')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text" id="roleHelpText">
            Administrators have system-wide access and are not tied to a specific branch or store.
        </div>
    </div>

    {{-- Branch(es) — only relevant for Branch Manager --}}
    <div class="col-md-6" id="branchField">
        <label for="branch_ids" class="form-label">Authorized Branch(es)</label>
        <select
            class="form-select @error('branch_ids') is-invalid @enderror @error('branch_ids.*') is-invalid @enderror"
            id="branch_ids"
            name="branch_ids[]"
            multiple
            size="4"
        >
            @foreach ($branches as $branch)
                <option
                    value="{{ $branch->id }}"
                    {{ collect(old('branch_ids', $user->branches->pluck('id')->all() ?? []))->contains($branch->id) ? 'selected' : '' }}
                >
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        @error('branch_ids')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple branches.</div>
    </div>

    {{-- Store(s) — only relevant for Store Manager --}}
    <div class="col-md-6" id="storeField">
        <label for="store_ids" class="form-label">Authorized Store(s)</label>
        <select
            class="form-select @error('store_ids') is-invalid @enderror @error('store_ids.*') is-invalid @enderror"
            id="store_ids"
            name="store_ids[]"
            multiple
            size="4"
        >
            @foreach ($stores as $store)
                <option
                    value="{{ $store->id }}"
                    {{ collect(old('store_ids', $user->stores->pluck('id')->all() ?? []))->contains($store->id) ? 'selected' : '' }}
                >
                    {{ $store->name }} @if ($store->branch ?? false)({{ $store->branch->name }})@endif
                </option>
            @endforeach
        </select>
        @error('store_ids')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple stores.</div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('role');
    const branchField = document.getElementById('branchField');
    const storeField = document.getElementById('storeField');

    function syncFieldsToRole() {
        const role = roleSelect.value;

        branchField.classList.toggle('d-none', role !== 'branch_manager');
        storeField.classList.toggle('d-none', role !== 'store_manager');
    }

    roleSelect.addEventListener('change', syncFieldsToRole);
    syncFieldsToRole();
});
</script>
@endpush
