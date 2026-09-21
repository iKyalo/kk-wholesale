@extends('layouts.app')

@section('content')
    <div class="bg-light min-vh-100 py-4">
        <div class="container-fluid px-3 px-md-4">

            <div class="mb-4">
                <h1 class="h3 fw-bold mb-0">Dashboard</h1>
                <p class="text-muted small mb-0">Overview of sales, inventory, and stock movement.</p>
            </div>

            {{-- Summary Cards --}}
            <div class="row g-3 mb-4">

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="text-muted text-uppercase small fw-semibold mb-1">Sales Today</p>
                                    <h4 class="fw-bold mb-1">KSh {{ number_format($salesToday, 2) }}</h4>
                                    <p class="text-muted small mb-0">{{ number_format($salesTodayTransactions) }}
                                        transactions</p>
                                </div>
                                <span
                                    class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center"
                                    style="width:44px;height:44px;">
                                    <i class="bi bi-cash-coin fs-5"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="text-muted text-uppercase small fw-semibold mb-1">Sales This Month</p>
                                    <h4 class="fw-bold mb-1">KSh {{ number_format($salesThisMonth, 2) }}</h4>
                                    <p class="text-muted small mb-0">{{ number_format($salesThisMonthTransactions) }}
                                        transactions</p>
                                </div>
                                <span
                                    class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center"
                                    style="width:44px;height:44px;">
                                    <i class="bi bi-graph-up-arrow fs-5"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="text-muted text-uppercase small fw-semibold mb-1">Low Stock Products</p>
                                    <h4 class="fw-bold mb-1">{{ number_format($lowStockCount) }}</h4>
                                    @if ($lowStockCount > 0)
                                        <span class="badge text-bg-warning">Needs attention</span>
                                    @else
                                        <span class="badge text-bg-success">All good</span>
                                    @endif
                                </div>
                                <span
                                    class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 d-flex align-items-center justify-content-center"
                                    style="width:44px;height:44px;">
                                    <i class="bi bi-exclamation-triangle fs-5"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="text-muted text-uppercase small fw-semibold mb-1">Pending Stock Transfers</p>
                                    <h4 class="fw-bold mb-1">{{ number_format($pendingTransfersCount) }}</h4>
                                    @if ($pendingTransfersCount > 0)
                                        <span class="badge text-bg-info">Awaiting action</span>
                                    @else
                                        <span class="badge text-bg-secondary">None pending</span>
                                    @endif
                                </div>
                                <span
                                    class="bg-info bg-opacity-10 text-info rounded-circle p-2 d-flex align-items-center justify-content-center"
                                    style="width:44px;height:44px;">
                                    <i class="bi bi-arrow-left-right fs-5"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Inventory Summary --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <h2 class="h6 fw-bold mb-0">Inventory Summary</h2>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6 col-md-4 col-lg-2">
                            <p class="text-muted small mb-1">Total Products</p>
                            <p class="fw-bold fs-5 mb-0">{{ number_format($totalProducts) }}</p>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <p class="text-muted small mb-1">Units in Stock</p>
                            <p class="fw-bold fs-5 mb-0">{{ number_format($totalUnits) }}</p>
                        </div>
                        <div class="col-6 col-md-4 col-lg-3">
                            <p class="text-muted small mb-1">Inventory Value</p>
                            <p class="fw-bold fs-5 mb-0">KSh {{ number_format($totalInventoryValue, 2) }}</p>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <p class="text-muted small mb-1">Branches</p>
                            <p class="fw-bold fs-5 mb-0">{{ number_format($totalBranches) }}</p>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <p class="text-muted small mb-1">Stores</p>
                            <p class="fw-bold fs-5 mb-0">{{ number_format($totalStores) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Sales --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-3 d-flex align-items-center justify-content-between">
                    <h2 class="h6 fw-bold mb-0">Recent Sales</h2>
                    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-primary">View All Sales</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th>Sale #</th>
                                    <th>Date</th>
                                    <th>Store</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentSales as $sale)
                                    <tr>
                                        <td class="fw-semibold">{{ $sale->sale_number }}</td>
                                        <td>{{ $sale->created_at->format('d M Y, H:i') }}</td>
                                        <td>{{ $sale->store->name }}</td>
                                        <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                                        <td>{{ $sale->items_count }}</td>
                                        <td>KSh {{ number_format($sale->total, 2) }}</td>
                                        <td>{{ $sale->payment_method }}</td>
                                        <td>
                                            @if ($sale->status === 'completed')
                                                <span class="badge text-bg-success">Completed</span>
                                            @elseif ($sale->status === 'pending')
                                                <span class="badge text-bg-warning">Pending</span>
                                            @elseif ($sale->status === 'cancelled')
                                                <span class="badge text-bg-danger">Cancelled</span>
                                            @else
                                                <span class="badge text-bg-secondary">{{ ucfirst($sale->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No recent sales to display.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Low-Stock Products --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-3 d-flex align-items-center justify-content-between">
                    <h2 class="h6 fw-bold mb-0">Low-Stock Products</h2>
                    <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-primary">View Inventory</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Store</th>
                                    <th>Current Stock</th>
                                    <th>Minimum Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($lowStockProducts as $product)
                                    <tr>
                                        <td class="fw-semibold">{{ $product->name }}</td>
                                        <td>{{ $product->sku }}</td>
                                        <td>{{ $product->store->name }}</td>
                                        <td>{{ number_format($product->current_stock) }}</td>
                                        <td>{{ number_format($product->minimum_stock) }}</td>
                                        <td>
                                            @if ($product->current_stock <= 0)
                                                <span class="badge text-bg-danger">Critical</span>
                                            @else
                                                <span class="badge text-bg-warning">Low</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No low-stock products right
                                            now.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Pending Stock Transfers --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-3 d-flex align-items-center justify-content-between">
                    <h2 class="h6 fw-bold mb-0">Pending Stock Transfers</h2>
                    <a href="{{ route('transfers.index') }}" class="btn btn-sm btn-outline-primary">View All
                        Transfers</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th>Transfer #</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Items</th>
                                    <th>Requested By</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pendingTransfers as $transfer)
                                    <tr>
                                        <td class="fw-semibold">{{ $transfer->transfer_number }}</td>
                                        <td>{{ $transfer->fromStore->name }}</td>
                                        <td>{{ $transfer->toStore->name }}</td>
                                        <td>{{ $transfer->items_count }}</td>
                                        <td>{{ $transfer->requestedBy->name }}</td>
                                        <td>{{ $transfer->created_at->format('d M Y') }}</td>
                                        <td>
                                            @if ($transfer->status === 'pending')
                                                <span class="badge text-bg-warning">Pending</span>
                                            @elseif ($transfer->status === 'approved')
                                                <span class="badge text-bg-info">Approved</span>
                                            @elseif ($transfer->status === 'in_transit')
                                                <span class="badge text-bg-primary">In Transit</span>
                                            @else
                                                <span
                                                    class="badge text-bg-secondary">{{ ucfirst($transfer->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('transfers.show', $transfer->id) }}"
                                                class="btn btn-sm btn-outline-secondary">Review</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No pending stock transfers.
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
@endsection
