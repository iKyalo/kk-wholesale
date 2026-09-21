@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-0">Assign Users</h1>
            <p class="text-muted small mb-0">Manage which users belong to {{ $store->name }}.</p>
        </div>
        <a href="{{ route('stores.show', $store) }}" class="btn btn-outline-secondary">
            Back to Store
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    {{-- Store info --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <p class="fw-semibold mb-0">{{ $store->name }}</p>
                <p class="text-muted small mb-0">{{ $store->code }} &middot; {{ $store->branch->name ?? '—' }}</p>
            </div>
            @if ($store->is_active)
                <span class="badge text-bg-success">Active</span>
            @else
                <span class="badge text-bg-secondary">Inactive</span>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="row g-2 align-items-center mb-3">
                <div class="col-md-6 col-lg-4">
                    <input
                        type="text"
                        id="userSearchInput"
                        class="form-control"
                        placeholder="Search users by name or email..."
                    >
                </div>
            </div>

            <form method="POST" action="{{ route('stores.users.sync', $store) }}">
                @csrf

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="userAssignTable">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="selectAllUsers">
                                </th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                @php
                                    $isAssigned = in_array($user->id, $assignedUsers);
                                @endphp
                                <tr class="user-row">
                                    <td>
                                        <input
                                            class="form-check-input user-checkbox"
                                            type="checkbox"
                                            name="users[]"
                                            value="{{ $user->id }}"
                                            {{ $isAssigned ? 'checked' : '' }}
                                        >
                                    </td>
                                    <td class="user-name fw-semibold">{{ $user->name }}</td>
                                    <td class="user-email">{{ $user->email }}</td>
                                    <td>{{ $user->role ?? '—' }}</td>
                                    <td>
                                        @if ($isAssigned)
                                            <span class="badge text-bg-success">Assigned</span>
                                        @else
                                            <span class="badge text-bg-secondary">Not Assigned</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No users available to assign.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4">Save Assignments</button>
                    <a href="{{ route('stores.show', $store) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                </div>
            </form>

        </div>
    </div>

</div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAllUsers');
        const checkboxes = document.querySelectorAll('.user-checkbox');
        const searchInput = document.getElementById('userSearchInput');
        const rows = document.querySelectorAll('.user-row');

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(function (checkbox) {
                    if (checkbox.closest('.user-row').style.display !== 'none') {
                        checkbox.checked = selectAll.checked;
                    }
                });
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const term = searchInput.value.trim().toLowerCase();
                rows.forEach(function (row) {
                    const name = row.querySelector('.user-name').textContent.toLowerCase();
                    const email = row.querySelector('.user-email').textContent.toLowerCase();
                    const matches = name.includes(term) || email.includes(term);
                    row.style.display = matches ? '' : 'none';
                });
            });
        }
    });
</script>
@endpush
@endsection
