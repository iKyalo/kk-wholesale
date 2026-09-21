@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-0">Sales</h1>
            <p class="text-muted small mb-0">Track and manage store sales.</p>
        </div>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Create Sale
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
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Sales Today</p>
                    <h4 class="fw-bold mb-0">KSh {{ number_format($salesToday ?? 0, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Sales This Month</p>
                    <h4 class="fw-bold mb-0">KSh {{ number_format($salesThisMonth ?? 0, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Total Transactions</p>
                    <h4 class="fw-bold mb-0">{{ number_format($totalTransactions ?? 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted text-uppercase small fw-semibold mb-1">Average Sale Value</p>
                    <h4 class="fw-bold mb-0">KSh {{ number_format($averageSaleValue ?? 0, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('sales.index') }}" class="row g-2 align-items-center">
                <div class="col-md-2">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Sale #"
                        value="{{ request('search') }}"
                    >
                </div>
                @isset($branches)
                    <div class="col-md-2">
                        <select name="branch_id" class="form-select">
                            <option value="">All Branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (string) request('branch_id') === (string) $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                @isset($stores)
                    <div class="col-md-2">
                        <select name="store_id" class="form-select">
                            <option value="">All Stores</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}" {{ (string) request('store_id') === (string) $store->id ? 'selected' : '' }}>
                                    {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endisset
                <div class="col-md-2">
                    <select name="payment_method" class="form-select">
                        <option value="">All Payment Methods</option>
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="mpesa" {{ request('payment_method') === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                        <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                        <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                </div>

                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                @if (request()->anyFilled(['search', 'branch_id', 'store_id', 'payment_method', 'status', 'date_from', 'date_to']))
                    <div class="col-md-3 d-flex align-items-end">
                        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary w-100">Clear Filters</a>
                    </div>
                @endif
            </form>
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
                            <th>Branch</th>
                            <th>Store</th>
                            <th>Items</th>
                            <th>Subtotal</th>
                            <th>Discount</th>
                            <th>Total</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td class="fw-semibold">{{ $sale->sale_number }}</td>
                                <td>{{ $sale->created_at->format('d M Y, H:i') }}</td>
                                <td>{{ $sale->store->branch->name ?? '—' }}</td>
                                <td>{{ $sale->store->name ?? '—' }}</td>
                                <td>{{ $sale->items_count ?? $sale->items->count() }}</td>
                                <td>KSh {{ number_format($sale->subtotal, 2) }}</td>
                                <td>KSh {{ number_format($sale->discount, 2) }}</td>
                                <td class="fw-semibold">KSh {{ number_format($sale->total, 2) }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</td>
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
                                <td colspan="11" class="text-center text-muted py-5">
                                    <i class="bi bi-receipt fs-2 d-block mb-2"></i>
                                    No sales found. Try adjusting your filters or create a new sale.
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

</div>
</div>
@endsection
