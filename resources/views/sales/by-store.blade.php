@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    <div class="mb-4">
        <h1 class="h3 fw-bold mb-0">Sales by Store</h1>
        <p class="text-muted small mb-0">View sales activity for a specific store.</p>
    </div>

    {{-- Store Selection --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('sales.by-store') }}" class="row g-2 align-items-end">
                @isset($branches)
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Branch</label>
                        <select name="branch_id" class="form-select">
                            <option value="">Select a Branch</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) request('branch_id') === (string) $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                @isset($stores)
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Store</label>
                        <select name="store_id" class="form-select">
                            <option value="">Select a Store</option>
                            @foreach ($stores as $storeOption)
                                <option value="{{ $storeOption->id }}" {{ (isset($store) && $store->id === $storeOption->id) ? 'selected' : '' }}>
                                    {{ $storeOption->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-outline-primary">View Store Sales</button>
                </div>
            </form>
        </div>
    </div>

    @isset($store)

        {{-- Store Sales Summary --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <h2 class="h5 fw-bold mb-0">{{ $store->name }}</h2>
                    <p class="text-muted small mb-0">{{ $store->branch->name ?? '—' }}</p>
                </div>
                <div class="row g-3 text-center">
                    <div class="col-md-4">
                        <p class="text-muted small mb-1">Total Sales</p>
                        <p class="fw-bold fs-5 mb-0">KSh {{ number_format($storeTotalSales ?? 0, 2) }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted small mb-1">Number of Transactions</p>
                        <p class="fw-bold fs-5 mb-0">{{ number_format($storeTransactionCount ?? 0) }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted small mb-1">Average Sale Value</p>
                        <p class="fw-bold fs-5 mb-0">KSh {{ number_format($storeAverageSaleValue ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sales Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>Sale #</th>
                                <th>Date</th>
                                <th>Cashier</th>
                                <th>Items</th>
                                <th>Payment Method</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sales as $sale)
                                <tr>
                                    <td class="fw-semibold">{{ $sale->sale_number }}</td>
                                    <td>{{ $sale->created_at->format('d M Y, H:i') }}</td>
                                    <td>{{ $sale->cashier->name ?? '—' }}</td>
                                    <td>{{ $sale->items_count ?? $sale->items->count() }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</td>
                                    <td class="fw-semibold">KSh {{ number_format($sale->total, 2) }}</td>
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
                                    <td>
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-secondary" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('sales.show', $sale) }}?print=1" class="btn btn-sm btn-outline-primary" title="Print Receipt">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="bi bi-receipt fs-2 d-block mb-2"></i>
                                        No sales found for this store in the selected period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($sales instanceof \Illuminate\Contracts\Pagination\Paginator || $sales instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="mt-3">
                        {{ $sales->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>

    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-shop fs-2 d-block mb-2"></i>
                Select a branch and store above to view its sales.
            </div>
        </div>
    @endisset

</div>
</div>
@endsection
