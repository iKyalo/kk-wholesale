@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2 no-print">
        <div>
            <h1 class="h3 fw-bold mb-0">Product Performance Report</h1>
            <p class="text-muted small mb-0">See which products are driving sales.</p>
        </div>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Report
        </button>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.products') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
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
                @isset($products)
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Product</label>
                        <select name="product_id" class="form-select">
                            <option value="">All Products</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" {{ (string) request('product_id') === (string) $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
                <div class="col-12">
                    <a href="{{ route('reports.products') }}" class="btn btn-sm btn-outline-secondary">Reset Filters</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Products Sold</p>
                    <h5 class="fw-bold mb-0">{{ number_format($summary['total_products_sold'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Sales Revenue</p>
                    <h5 class="fw-bold mb-0">KSh {{ number_format($summary['total_sales_revenue'] ?? 0, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Best-Selling Product</p>
                    <h6 class="fw-bold mb-0">{{ $summary['best_selling_product'] ?? '—' }}</h6>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Products With No Sales</p>
                    <h5 class="fw-bold mb-0 text-muted">{{ number_format($summary['products_with_no_sales'] ?? 0) }}</h5>
                </div>
            </div>
        </div>
    </div>

    {{-- Top-Selling Products Chart --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-3">
            <h2 class="h6 fw-bold mb-0">Top 10 Products by Units Sold</h2>
        </div>
        <div class="card-body">
            @if (!empty($chartLabels))
                <canvas id="topProductsChart" height="120"></canvas>
            @else
                <p class="text-muted text-center py-5 mb-0">No product sales data available for this period.</p>
            @endif
        </div>
    </div>

    {{-- Product Performance Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-3">
            <h2 class="h6 fw-bold mb-0">Product Performance</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th>Product</th>
                            <th>SKU</th>
                            <th class="text-end">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'units_sold']) }}" class="text-muted text-decoration-none">
                                    Units Sold <i class="bi bi-arrow-down-up small"></i>
                                </a>
                            </th>
                            <th class="text-end">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'revenue']) }}" class="text-muted text-decoration-none">
                                    Revenue <i class="bi bi-arrow-down-up small"></i>
                                </a>
                            </th>
                            <th class="text-end">Average Selling Price</th>
                            <th class="text-end">Current Stock</th>
                            <th class="text-end no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productPerformance as $row)
                            <tr>
                                <td class="fw-semibold">{{ $row->product_name }}</td>
                                <td>{{ $row->sku }}</td>
                                <td class="text-end">{{ number_format($row->units_sold) }}</td>
                                <td class="text-end">KSh {{ number_format($row->revenue, 2) }}</td>
                                <td class="text-end">KSh {{ number_format($row->average_selling_price, 2) }}</td>
                                <td class="text-end">{{ number_format($row->current_stock) }}</td>
                                <td class="text-end no-print">
                                    @if (Route::has('products.show') && isset($row->product_id))
                                        <a href="{{ route('products.show', $row->product_id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-box-seam fs-2 d-block mb-2"></i>
                                    No product sales data found for the selected filters.
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        return;
    }

    const canvas = document.getElementById('topProductsChart');
    if (canvas) {
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: @json($chartLabels ?? []),
                datasets: [{
                    label: 'Units Sold',
                    data: @json($chartData ?? []),
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } },
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
