@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2 no-print">
        <div>
            <h1 class="h3 fw-bold mb-0">Sales Report</h1>
            <p class="text-muted small mb-0">Revenue, transactions, and sales trends.</p>
        </div>
        <div class="d-flex gap-2">
            @if (Route::has('reports.sales.export'))
                <a href="{{ route('reports.sales.export', request()->query()) }}" class="btn btn-outline-secondary">
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
            <form method="GET" action="{{ route('reports.sales') }}" class="row g-2 align-items-end">
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
                        <label class="form-label small text-muted mb-1">Branch</label>
                        <select name="branch_id" class="form-select">
                            <option value="">All Branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) request('branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                @isset($stores)
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Store</label>
                        <select name="store_id" class="form-select">
                            <option value="">All Stores</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}" {{ (string) request('store_id') === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="">All Methods</option>
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="mpesa" {{ request('payment_method') === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                        <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                        <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Sale Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
                <div class="col-md-2 d-grid">
                    <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Revenue</p>
                    <h5 class="fw-bold mb-0">KSh {{ number_format($summary['total_revenue'] ?? 0, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Transactions</p>
                    <h5 class="fw-bold mb-0">{{ number_format($summary['total_transactions'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Items Sold</p>
                    <h5 class="fw-bold mb-0">{{ number_format($summary['total_items_sold'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Average Sale Value</p>
                    <h5 class="fw-bold mb-0">KSh {{ number_format($summary['average_sale_value'] ?? 0, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Discounts</p>
                    <h5 class="fw-bold mb-0">KSh {{ number_format($summary['total_discounts'] ?? 0, 2) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">

        {{-- Sales Over Time --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3">
                    <h2 class="h6 fw-bold mb-0">Sales Over Time</h2>
                </div>
                <div class="card-body">
                    @if (!empty($chartLabels))
                        <canvas id="salesOverTimeChart" height="110"></canvas>
                    @else
                        <p class="text-muted text-center py-5 mb-0">No sales data available for this period.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sales by Payment Method --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3">
                    <h2 class="h6 fw-bold mb-0">Sales by Payment Method</h2>
                </div>
                <div class="card-body">
                    @if (!empty($paymentMethodLabels))
                        <canvas id="paymentMethodChart" height="220"></canvas>
                    @else
                        <p class="text-muted text-center py-5 mb-0">No payment data available.</p>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- Sales by Store --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-3">
            <h2 class="h6 fw-bold mb-0">Sales by Store</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th>Store</th>
                            <th>Branch</th>
                            <th class="text-end">Transactions</th>
                            <th class="text-end">Items Sold</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-end">Average Sale Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($storePerformance as $row)
                            <tr>
                                <td class="fw-semibold">{{ $row->store_name }}</td>
                                <td>{{ $row->branch_name }}</td>
                                <td class="text-end">{{ number_format($row->transactions) }}</td>
                                <td class="text-end">{{ number_format($row->items_sold) }}</td>
                                <td class="text-end">KSh {{ number_format($row->revenue, 2) }}</td>
                                <td class="text-end">KSh {{ number_format($row->average_sale_value, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No store sales data for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent Sales --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-3">
            <h2 class="h6 fw-bold mb-0">Recent Sales</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th>Sale #</th>
                            <th>Date</th>
                            <th>Store</th>
                            <th>Cashier</th>
                            <th>Payment Method</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th class="text-end no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td class="fw-semibold">{{ $sale->sale_number }}</td>
                                <td>{{ $sale->created_at->format('d M Y, H:i') }}</td>
                                <td>{{ $sale->store->name ?? '—' }}</td>
                                <td>{{ $sale->cashier->name ?? '—' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</td>
                                <td class="text-end">KSh {{ number_format($sale->total, 2) }}</td>
                                <td>
                                    @if ($sale->status === 'completed')
                                        <span class="badge text-bg-success">Completed</span>
                                    @elseif ($sale->status === 'pending')
                                        <span class="badge text-bg-warning">Pending</span>
                                    @elseif ($sale->status === 'cancelled')
                                        <span class="badge text-bg-danger">Cancelled</span>
                                    @elseif ($sale->status === 'refunded')
                                        <span class="badge text-bg-info">Refunded</span>
                                    @else
                                        <span class="badge text-bg-secondary">{{ ucfirst($sale->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-end no-print">
                                    @if (Route::has('sales.show'))
                                        <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No sales found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        return;
    }

    const salesOverTimeCanvas = document.getElementById('salesOverTimeChart');
    if (salesOverTimeCanvas) {
        new Chart(salesOverTimeCanvas, {
            type: 'line',
            data: {
                labels: @json($chartLabels ?? []),
                datasets: [{
                    label: 'Revenue (KSh)',
                    data: @json($chartData ?? []),
                    fill: true,
                    tension: 0.3,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } },
            },
        });
    }

    const paymentMethodCanvas = document.getElementById('paymentMethodChart');
    if (paymentMethodCanvas) {
        new Chart(paymentMethodCanvas, {
            type: 'doughnut',
            data: {
                labels: @json($paymentMethodLabels ?? []),
                datasets: [{
                    data: @json($paymentMethodData ?? []),
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
            },
        });
    }
});
</script>
@endpush

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
