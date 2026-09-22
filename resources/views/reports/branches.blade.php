@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2 no-print">
        <div>
            <h1 class="h3 fw-bold mb-0">Branch Performance Report</h1>
            <p class="text-muted small mb-0">Compare business activity across branches.</p>
        </div>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Report
        </button>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.branches') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                @isset($branches)
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Branch</label>
                        <select name="branch_id" class="form-select">
                            <option value="">All Branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) request('branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
                <div class="col-12">
                    <a href="{{ route('reports.branches') }}" class="btn btn-sm btn-outline-secondary">Reset Filters</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Branches</p>
                    <h5 class="fw-bold mb-0">{{ number_format($summary['total_branches'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Revenue</p>
                    <h5 class="fw-bold mb-0">KSh {{ number_format($summary['total_revenue'] ?? 0, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Transactions</p>
                    <h5 class="fw-bold mb-0">{{ number_format($summary['total_transactions'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Units Sold</p>
                    <h5 class="fw-bold mb-0">{{ number_format($summary['total_units_sold'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
    </div>

    {{-- Branch Performance Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th>Branch</th>
                            <th class="text-end">Number of Stores</th>
                            <th class="text-end">Transactions</th>
                            <th class="text-end">Units Sold</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-end">Average Sale Value</th>
                            <th class="text-end no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($branchPerformance as $row)
                            <tr>
                                <td class="fw-semibold">{{ $row->branch_name }}</td>
                                <td class="text-end">{{ number_format($row->store_count) }}</td>
                                <td class="text-end">{{ number_format($row->transactions) }}</td>
                                <td class="text-end">{{ number_format($row->units_sold) }}</td>
                                <td class="text-end">KSh {{ number_format($row->revenue, 2) }}</td>
                                <td class="text-end">KSh {{ number_format($row->average_sale_value, 2) }}</td>
                                <td class="text-end no-print">
                                    @if (Route::has('branches.show') && isset($row->branch_id))
                                        <a href="{{ route('branches.show', $row->branch_id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-diagram-3 fs-2 d-block mb-2"></i>
                                    No branch performance data found for the selected filters.
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
