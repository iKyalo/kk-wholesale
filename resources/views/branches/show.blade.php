@extends('layouts.app')

@section('content')
    <div class="bg-light min-vh-100 py-4">
        <div class="container-fluid px-3 px-md-4">

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
                <div>
                    <h1 class="h3 fw-bold mb-0">{{ $branch->name }}</h1>
                    <p class="text-muted small mb-0">Branch Code: {{ $branch->code }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('branches.edit', $branch) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i> Edit Branch
                    </a>
                    <a href="{{ route('branches.users.edit', $branch) }}" class="btn btn-outline-info">
                        <i class="bi bi-people me-1"></i> Assign Users
                    </a>
                    <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary">
                        Back to Branches
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Branch Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-3">
                    <h2 class="h6 fw-bold mb-0">Branch Information</h2>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Branch Name</p>
                            <p class="fw-semibold mb-0">{{ $branch->name }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Branch Code</p>
                            <p class="fw-semibold mb-0">{{ $branch->code }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Status</p>
                            @if ($branch->is_active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-secondary">Inactive</span>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Location</p>
                            <p class="fw-semibold mb-0">{{ $branch->location }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Phone</p>
                            <p class="fw-semibold mb-0">{{ $branch->phone ?? '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Email</p>
                            <p class="fw-semibold mb-0">{{ $branch->email ?? '—' }}</p>
                        </div>
                        <div class="col-md-8">
                            <p class="text-muted small mb-1">Address</p>
                            <p class="fw-semibold mb-0">{{ $branch->address ?? '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Created At</p>
                            <p class="fw-semibold mb-0">{{ $branch->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Last Updated</p>
                            <p class="fw-semibold mb-0">{{ $branch->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Branch Statistics --}}
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Users</p>
                            <h4 class="fw-bold mb-0">{{ number_format($usersCount ?? 0) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Stores</p>
                            <h4 class="fw-bold mb-0">{{ number_format($storesCount ?? 0) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Products</p>
                            <h4 class="fw-bold mb-0">{{ number_format($productsCount ?? 0) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Inventory Value</p>
                            <h4 class="fw-bold mb-0">KSh {{ number_format($inventoryValue ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Sales Today</p>
                            <h4 class="fw-bold mb-0">KSh {{ number_format($salesToday ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Assigned Users --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3 d-flex align-items-center justify-content-between">
                    <h2 class="h6 fw-bold mb-0">Assigned Users</h2>
                    <a href="{{ route('branches.users.edit', $branch) }}" class="btn btn-sm btn-outline-primary">Assign
                        Users</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Assigned At</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($branch->users as $user)
                                    <tr>
                                        <td class="fw-semibold">{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->role->name ?? '—' }}</td>
                                        <td>
                                            @if ($user->is_active ?? true)
                                                <span class="badge text-bg-success">Active</span>
                                            @else
                                                <span class="badge text-bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ optional($user->pivot->created_at ?? null)->format('d M Y') ?? '—' }}</td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="{{ route('users.show', $user) }}"
                                                    class="btn btn-sm btn-outline-secondary" title="View User">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    title="Remove User" data-bs-toggle="modal"
                                                    data-bs-target="#removeUserModal{{ $user->id }}">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>

                                            <div class="modal fade" id="removeUserModal{{ $user->id }}"
                                                tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Remove User</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Remove <strong>{{ $user->name }}</strong> from
                                                            {{ $branch->name }}?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                data-bs-dismiss="modal">Cancel</button>
                                                            <form method="POST"
                                                                action="{{ route('branches.users.remove', [$branch, $user]) }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn btn-danger">Remove</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No users assigned to this
                                            branch yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
