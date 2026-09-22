@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-0">Stock Transfers</h1>
            <p class="text-muted small mb-0">Manage stock movement between stores.</p>
        </div>
        <a href="{{ route('transfers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Create Transfer
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Transfers</p>
                    <h4 class="fw-bold mb-0">{{ number_format($totalTransfers ?? 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Pending</p>
                    <h4 class="fw-bold mb-0 text-warning">{{ number_format($pendingTransfers ?? 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">In Transit</p>
                    <h4 class="fw-bold mb-0 text-primary">{{ number_format($inTransitTransfers ?? 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Completed</p>
                    <h4 class="fw-bold mb-0 text-success">{{ number_format($completedTransfers ?? 0) }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('transfers.index') }}" class="row g-2 align-items-center">
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control" placeholder="Transfer #" value="{{ request('search') }}">
                </div>
                @isset($branches)
                    <div class="col-md-2">
                        <select name="from_branch_id" class="form-select">
                            <option value="">From Branch</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) request('from_branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                @isset($stores)
                    <div class="col-md-2">
                        <select name="from_store_id" class="form-select">
                            <option value="">From Store</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}" {{ (string) request('from_store_id') === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                @isset($branches)
                    <div class="col-md-2">
                        <select name="to_branch_id" class="form-select">
                            <option value="">To Branch</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) request('to_branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                @isset($stores)
                    <div class="col-md-2">
                        <select name="to_store_id" class="form-select">
                            <option value="">To Store</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}" {{ (string) request('to_store_id') === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
                @if (request()->anyFilled(['search', 'from_branch_id', 'from_store_id', 'to_branch_id', 'to_store_id', 'status', 'date_from', 'date_to']))
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('transfers.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Transfers Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th>Transfer #</th>
                            <th>Date</th>
                            <th>From Branch</th>
                            <th>From Store</th>
                            <th>To Branch</th>
                            <th>To Store</th>
                            <th>Items</th>
                            <th>Requested By</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transfers as $transfer)
                            <tr>
                                <td class="fw-semibold">{{ $transfer->transfer_number }}</td>
                                <td>{{ $transfer->transfer_date->format('d M Y') }}</td>
                                <td>{{ $transfer->sourceStore->branch->name ?? '—' }}</td>
                                <td>{{ $transfer->sourceStore->name ?? '—' }}</td>
                                <td>{{ $transfer->destinationStore->branch->name ?? '—' }}</td>
                                <td>{{ $transfer->destinationStore->name ?? '—' }}</td>
                                <td>{{ $transfer->items_count ?? $transfer->items->count() }}</td>
                                <td>{{ $transfer->requestedBy->name ?? '—' }}</td>
                                <td>
                                    @switch($transfer->status)
                                        @case('pending')
                                            <span class="badge text-bg-warning">Pending</span>
                                            @break
                                        @case('approved')
                                            <span class="badge text-bg-info">Approved</span>
                                            @break
                                        @case('in_transit')
                                            <span class="badge text-bg-primary">In Transit</span>
                                            @break
                                        @case('completed')
                                            <span class="badge text-bg-success">Completed</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge text-bg-danger">Rejected</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge text-bg-secondary">Cancelled</span>
                                            @break
                                        @default
                                            <span class="badge text-bg-secondary">{{ ucfirst($transfer->status) }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-1 flex-wrap">
                                        <a href="{{ route('transfers.show', $transfer) }}" class="btn btn-sm btn-outline-secondary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if ($transfer->status === 'pending')
                                            <a href="{{ route('transfers.edit', $transfer) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('transfers.approve', $transfer) }}" class="btn btn-sm btn-outline-success" title="Approve / Reject">
                                                <i class="bi bi-check2-square"></i>
                                            </a>
                                        @endif

                                        @if (in_array($transfer->status, ['approved', 'in_transit']))
                                            <a href="{{ route('transfers.receive', $transfer) }}" class="btn btn-sm btn-outline-info" title="Receive">
                                                <i class="bi bi-box-arrow-in-down"></i>
                                            </a>
                                        @endif

                                        @if (in_array($transfer->status, ['pending', 'approved']))
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Cancel"
                                                data-bs-toggle="modal"
                                                data-bs-target="#cancelTransferModal{{ $transfer->id }}"
                                            >
                                                <i class="bi bi-x-lg"></i>
                                            </button>

                                            <div class="modal fade" id="cancelTransferModal{{ $transfer->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Cancel Transfer</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Are you sure you want to cancel transfer <strong>{{ $transfer->transfer_number }}</strong>?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                                            <form method="POST" action="{{ route('transfers.destroy', $transfer) }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">Cancel Transfer</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">
                                    <i class="bi bi-arrow-left-right fs-2 d-block mb-2"></i>
                                    No stock transfers found. Try adjusting your filters or create a new transfer.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($transfers instanceof \Illuminate\Contracts\Pagination\Paginator || $transfers instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="mt-3">
                    {{ $transfers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
</div>
@endsection
