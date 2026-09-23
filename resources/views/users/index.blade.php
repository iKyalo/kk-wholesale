@extends('layouts.app')

@section('page-title', 'Users')

@section('content')

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-0">Users</h1>
            <p class="text-muted small mb-0">Manage system users and their access.</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Add User
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name, email, or phone..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Role</label>
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        @foreach ($roles as $roleValue => $roleLabel)
                            <option value="{{ $roleValue }}"
                                {{ (string) request('role') === (string) $roleValue ? 'selected' : '' }}>
                                {{ $roleLabel->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}"
                                {{ (string) request('branch_id') === (string) $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Store</label>
                    <select name="store_id" class="form-select">
                        <option value="">All Stores</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}"
                                {{ (string) request('store_id') === (string) $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary" title="Apply Filters">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>

                @if (request()->anyFilled(['search', 'role', 'status', 'branch_id', 'store_id']))
                    <div class="col-12">
                        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear Filters
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">

            @forelse ($users as $user)
                @if ($loop->first)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Assigned Branch</th>
                                    <th>Assigned Store</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                @endif

                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="fw-semibold">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone ?? '—' }}</td>
                    <td>
                        @switch($user->role?->name)
                            @case('administrator')
                                <span class="badge text-bg-primary">Administrator</span>
                            @break

                            @case('branch_manager')
                                <span class="badge text-bg-info">Branch Manager</span>
                            @break

                            @case('store_manager')
                                <span class="badge text-bg-secondary">Store Manager</span>
                            @break

                            @default
                                <span class="badge text-bg-secondary">
                                    {{ $user->role?->name ?? 'No Role' }}
                                </span>
                        @endswitch
                    </td>
                    <td>
                        @if (strtolower($user->role->name) === 'administrator')
                            <span class="text-muted small">All Branches</span>
                        @elseif (($user->branches ?? collect())->isNotEmpty())
                            {{ $user->branches->pluck('name')->implode(', ') }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if (strtolower($user->role->name) === 'administrator')
                            <span class="text-muted small">All Stores</span>
                        @elseif (($user->stores ?? collect())->isNotEmpty())
                            {{ $user->stores->pluck('name')->implode(', ') }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if ($user->status === 'active')
                            <span class="badge text-bg-success">Active</span>
                        @else
                            <span class="badge text-bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="dropdown text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                Actions <i class="bi bi-chevron-down small"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item" href="{{ route('users.show', $user) }}">
                                        <i class="bi bi-eye me-2"></i> View
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('users.edit', $user) }}">
                                        <i class="bi bi-pencil me-2"></i> Edit
                                    </a>
                                </li>
                                {{-- <li>
                                    <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                        data-bs-target="#toggleStatusModal{{ $user->id }}">
                                        @if ($user->is_active)
                                            <i class="bi bi-slash-circle me-2"></i> Deactivate
                                        @else
                                            <i class="bi bi-check-circle me-2"></i> Activate
                                        @endif
                                    </button>
                                </li> --}}
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal"
                                        data-bs-target="#deleteUserModal{{ $user->id }}">
                                        <i class="bi bi-trash me-2"></i> Delete
                                    </button>
                                </li>
                            </ul>
                        </div>

                        {{-- Activate / Deactivate confirmation modal --}}
                        <div class="modal fade" id="toggleStatusModal{{ $user->id }}" tabindex="-1"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            {{ $user->is_active ? 'Deactivate' : 'Activate' }} User
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        @if ($user->is_active)
                                            Are you sure you want to deactivate <strong>{{ $user->name }}</strong>? They
                                            will no longer be able to sign in.
                                        @else
                                            Are you sure you want to activate <strong>{{ $user->name }}</strong>? They
                                            will regain access to the system.
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                        {{-- <form method="POST" action="{{ route('users.toggle-status', $user) }}"> --}}
                                        <form method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                class="btn {{ $user->is_active ? 'btn-warning' : 'btn-success' }}">
                                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Delete confirmation modal --}}
                        <div class="modal fade" id="deleteUserModal{{ $user->id }}" tabindex="-1"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Delete User</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to delete <strong>{{ $user->name }}</strong>? This action
                                        cannot be undone.
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <form method="POST" action="{{ route('users.destroy', $user) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete User</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>

                @if ($loop->last)
                    </tbody>
                    </table>
        </div>

        @if (
            $users instanceof \Illuminate\Contracts\Pagination\Paginator ||
                $users instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-3">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
        @endif
        @empty
            {{-- Empty State --}}
            <div class="text-center py-5">
                <i class="bi bi-people fs-1 text-muted d-block mb-3"></i>
                <h2 class="h5 fw-bold mb-1">No users found.</h2>
                <p class="text-muted small mb-4">
                    Try adjusting your filters, or add a new user to get started.
                </p>
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i> Add User
                </a>
            </div>
            @endforelse

        </div>
        </div>

    @endsection
