@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0">Review Transfer</h1>
            <p class="text-muted small mb-0">{{ $transfer->transfer_number }}</p>
        </div>
        <a href="{{ route('transfers.show', $transfer) }}" class="btn btn-outline-secondary">
            Back to Transfer
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            Please fix the errors below and try again.
        </div>
    @endif

    @if ($transfer->status !== 'pending')

        <div class="alert alert-warning" role="alert">
            This transfer is currently <strong>{{ ucfirst(str_replace('_', ' ', $transfer->status)) }}</strong>.
            Only transfers with a <strong>Pending</strong> status can be approved or rejected.
        </div>

    @else

        {{-- Transfer Summary --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <p class="text-muted small mb-1">Source Store</p>
                        <p class="fw-semibold mb-0">{{ $transfer->sourceStore->name ?? '—' }}</p>
                    </div>
                    <div class="col-md-3 col-6">
                        <p class="text-muted small mb-1">Destination Store</p>
                        <p class="fw-semibold mb-0">{{ $transfer->destinationStore->name ?? '—' }}</p>
                    </div>
                    <div class="col-md-3 col-6">
                        <p class="text-muted small mb-1">Requested By</p>
                        <p class="fw-semibold mb-0">{{ $transfer->requestedBy->name ?? '—' }}</p>
                    </div>
                    <div class="col-md-3 col-6">
                        <p class="text-muted small mb-1">Transfer Date</p>
                        <p class="fw-semibold mb-0">{{ $transfer->transfer_date->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transfer Items --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-3">
                <h2 class="h6 fw-bold mb-0">Transfer Items</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>Product</th>
                                <th>SKU</th>
                                <th class="text-end">Available Source Stock</th>
                                <th class="text-end">Requested Quantity</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transfer->items as $item)
                                @php
                                    $available = $item->available_source_stock ?? $item->product->stock_by_store[$transfer->source_store_id] ?? null;
                                    $insufficient = $available !== null && $available < $item->quantity;
                                @endphp
                                <tr class="{{ $insufficient ? 'table-danger' : '' }}">
                                    <td class="fw-semibold">{{ $item->product->name ?? '—' }}</td>
                                    <td>{{ $item->product->sku ?? '—' }}</td>
                                    <td class="text-end">{{ $available !== null ? number_format($available) : '—' }}</td>
                                    <td class="text-end">{{ number_format($item->quantity) }}</td>
                                    <td>
                                        @if ($insufficient)
                                            <span class="badge text-bg-danger">Insufficient Stock</span>
                                        @else
                                            <span class="badge text-bg-success">Available</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No items on this transfer.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row g-4">

            {{-- Approve Transfer --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-bold mb-2">Approve Transfer</h2>
                        <p class="text-muted small mb-3">
                            Approving will allow the source store to dispatch the requested items.
                        </p>
                        <form
                            method="POST"
                            action="{{ route('transfers.approve', $transfer) }}"
                            onsubmit="return confirm('Approve this stock transfer?');"
                        >
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check2-square me-1"></i> Approve Transfer
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Reject Transfer --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h6 fw-bold mb-2">Reject Transfer</h2>
                        <form
                            method="POST"
                            action="{{ route('transfers.reject', $transfer) }}"
                            onsubmit="return confirm('Are you sure you want to reject this stock transfer?');"
                        >
                            @csrf
                            <label for="rejection_reason" class="form-label">Rejection Reason</label>
                            <textarea
                                class="form-control @error('rejection_reason') is-invalid @enderror mb-3"
                                id="rejection_reason"
                                name="rejection_reason"
                                rows="2"
                                placeholder="Explain why this transfer is being rejected"
                                required
                            >{{ old('rejection_reason') }}</textarea>
                            @error('rejection_reason')
                                <div class="invalid-feedback d-block mb-3">{{ $message }}</div>
                            @enderror
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-x-lg me-1"></i> Reject Transfer
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    @endif

</div>
</div>
@endsection
