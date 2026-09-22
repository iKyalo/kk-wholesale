@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2 no-print">
        <div>
            <h1 class="h3 fw-bold mb-0">Stock Transfer Report</h1>
            <p class="text-muted small mb-0">Analyze stock transfers between stores.</p>
        </div>
        <div class="d-flex gap-2">
            @if (Route::has('reports.transfers.export'))
                <a href="{{ route('reports.transfers.export', request()->query()) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-download me-1"></i> Export CSV
                </a>
            @endif
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Report
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.transfers') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                @isset($branches)
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Source Branch</label>
                        <select name="source_branch_id" class="form-select">
                            <option value="">Any</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) request('source_branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                @isset($stores)
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Source Store</label>
                        <select name="source_store_id" class="form-select">
                            <option value="">Any</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}" {{ (string) request('source_store_id') === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                @isset($branches)
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Destination Branch</label>
                        <select name="destination_branch_id" class="form-select">
                            <option value="">Any</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) request('destination_branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                @isset($stores)
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Destination Store</label>
                        <select name="destination_store_id" class="form-select">
                            <option value="">Any</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}" {{ (string) request('destination_store_id') === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
                <div class="col-12">
                    <a href="{{ route('reports.transfers') }}" class="btn btn-sm btn-outline-secondary">Reset Filters</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Transfers</p>
                    <h5 class="fw-bold mb-0">{{ number_format($summary['total_transfers'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Pending</p>
                    <h5 class="fw-bold mb-0 text-warning">{{ number_format($summary['pending'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">In Transit</p>
                    <h5 class="fw-bold mb-0 text-primary">{{ number_format($summary['in_transit'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Completed</p>
                    <h5 class="fw-bold mb-0 text-success">{{ number_format($summary['completed'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Rejected</p>
                    <h5 class="fw-bold mb-0 text-danger">{{ number_format($summary['rejected'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
    </div>

    {{-- Transfer Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th>Transfer #</th>
                            <th>Date</th>
                            <th>Source Store</th>
                            <th>Destination Store</th>
                            <th class="text-end">Total Items</th>
                            <th class="text-end">Total Units</th>
                            <th>Requested By</th>
                            <th>Status</th>
                            <th class="text-end no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transfers as $transfer)
                            <tr>
                                <td class="fw-semibold">{{ $transfer->transfer_number }}</td>
                                <td>{{ $transfer->transfer_date->format('d M Y') }}</td>
                                <td>{{ $transfer->source_store_name }}</td>
                                <td>{{ $transfer->destination_store_name }}</td>
                                <td class="text-end">{{ number_format($transfer->total_items) }}</td>
                                <td class="text-end">{{ number_format($transfer->total_units) }}</td>
                                <td>{{ $transfer->requested_by_name }}</td>
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
                                        @default
                                            <span class="badge text-bg-secondary">{{ ucfirst($transfer->status) }}</span>
                                    @endswitch
                                </td>
                                <td class="text-end no-print">
                                    @if (Route::has('transfers.show') && isset($transfer->id))
                                        <a href="{{ route('transfers.show', $transfer->id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-arrow-left-right fs-2 d-block mb-2"></i>
                                    No stock transfers found for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>

@push('styles')
<style>
    @media print {
        .no-print, nav, .btn { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .bg-light { background: #fff !important; }
    }
</style>
@endpush
@endsection
