@extends('layouts.app')

@section('content')
    <div class="bg-light min-vh-100 py-4">
        <div class="container-fluid px-3 px-md-4">

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
                <div>
                    <h1 class="h3 fw-bold mb-0">Stores</h1>
                    <p class="text-muted small mb-0">Manage stores across all branches.</p>
                </div>
                <a href="{{ route('stores.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create Store
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('stores.index') }}" class="row g-2 align-items-center">
                        <div class="col-md-4 col-lg-4">
                            <input type="text" name="search" class="form-control"
                                placeholder="Search by name, code, or location..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-4 col-lg-3">
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
                        <div class="col-md-4 col-lg-3">
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2 col-lg-1">
                            <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                        </div>
                        @if (request('search') || request('branch_id') || request('status'))
                            <div class="col-md-2 col-lg-1">
                                <a href="{{ route('stores.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th>#</th>
                                    <th>Store Name</th>
                                    <th>Code</th>
                                    <th>Branch</th>
                                    <th>Location</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Users</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stores as $store)
                                    <tr>
                                        <td class="fw-semibold">{{ $loop->iteration }}</td>
                                        <td class="fw-semibold">{{ $store->name }}</td>
                                        <td>{{ $store->code }}</td>
                                        <td>{{ $store->branch->name ?? '—' }}</td>
                                        <td>{{ $store->location }}</td>
                                        <td>{{ $store->phone ?? '—' }}</td>
                                        <td>{{ $store->email ?? '—' }}</td>
                                        <td>
                                            @if ($store->is_active)
                                                <span class="badge text-bg-success">Active</span>
                                            @else
                                                <span class="badge text-bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($store->users_count) }}</td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="{{ route('stores.show', $store) }}"
                                                    class="btn btn-sm btn-outline-secondary" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('stores.edit', $store) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="{{ route('stores.users.edit', $store) }}"
                                                    class="btn btn-sm btn-outline-info" title="Assign Users">
                                                    <i class="bi bi-people"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteStoreModal{{ $store->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>

                                            {{-- Delete confirmation modal --}}
                                            <div class="modal fade" id="deleteStoreModal{{ $store->id }}" tabindex="-1"
                                                aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Delete Store</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Are you sure you want to delete
                                                            <strong>{{ $store->name }}</strong>? This action cannot be
                                                            undone.
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                data-bs-dismiss="modal">Cancel</button>
                                                            <form method="POST"
                                                                action="{{ route('stores.destroy', $store) }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">Delete
                                                                    Store</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-5">
                                            <i class="bi bi-shop fs-2 d-block mb-2"></i>
                                            No stores found. Try adjusting your filters or create a new store.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (
                        $stores instanceof \Illuminate\Contracts\Pagination\Paginator ||
                            $stores instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="mt-3">
                            {{ $stores->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
