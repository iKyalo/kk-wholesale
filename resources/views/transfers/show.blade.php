@extends('layouts.app')

@section('content')
    <div class="bg-light min-vh-100 py-4">
        <div class="container-fluid px-3 px-md-4">

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
                <div>
                    <h1 class="h3 fw-bold mb-0">{{ $transfer->transfer_number }}</h1>
                    <p class="text-muted small mb-0">
                        {{ $transfer->created_at->format('d M Y') }} &middot; Requested by
                        {{ $transfer->user->name ?? '—' }}
                    </p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @switch($transfer->status)
                        @case('pending')
                            <span class="badge text-bg-warning align-self-center">Pending</span>
                        @break

                        @case('approved')
                            <span class="badge text-bg-info align-self-center">Approved</span>
                        @break

                        @case('in_transit')
                            <span class="badge text-bg-primary align-self-center">In Transit</span>
                        @break

                        @case('completed')
                            <span class="badge text-bg-success align-self-center">Completed</span>
                        @break

                        @case('rejected')
                            <span class="badge text-bg-danger align-self-center">Rejected</span>
                        @break

                        @case('cancelled')
                            <span class="badge text-bg-secondary align-self-center">Cancelled</span>
                        @break
                    @endswitch

                    @if ($transfer->status === 'pending')
                        <a href="{{ route('transfers.edit', $transfer) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                        <a href="{{ route('transfers.approve', $transfer) }}" class="btn btn-outline-success">
                            <i class="bi bi-check2-square me-1"></i> Approve / Reject
                        </a>
                    @endif

                    @if (in_array($transfer->status, ['approved', 'in_transit']))
                        <a href="{{ route('transfers.receive', $transfer) }}" class="btn btn-outline-info">
                            <i class="bi bi-box-arrow-in-down me-1"></i> Receive
                        </a>
                    @endif

                    <a href="{{ route('transfers.index') }}" class="btn btn-outline-secondary">
                        Back to Transfers
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Transfer Route --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-5">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Source</p>
                            <p class="fw-bold mb-0">{{ $transfer->sourceStore->name ?? '—' }}</p>
                            <p class="text-muted small mb-0">{{ $transfer->sourceStore->branch->name ?? '—' }}</p>
                        </div>
                        <div class="col-md-2 text-center">
                            <i class="bi bi-arrow-right fs-2 text-muted"></i>
                        </div>
                        <div class="col-md-5">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Destination</p>
                            <p class="fw-bold mb-0">{{ $transfer->destinationStore->name ?? '—' }}</p>
                            <p class="text-muted small mb-0">{{ $transfer->destinationStore->branch->name ?? '—' }}</p>
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
                                    <th class="text-end">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transfer->items as $item)
                                    <tr>
                                        <td class="fw-semibold">{{ $item->product->name ?? '—' }}</td>
                                        <td>{{ $item->product->sku ?? '—' }}</td>
                                        <td class="text-end">{{ number_format($item->quantity) }}</td>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No items on this transfer.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row g-4">

                {{-- Transfer Timeline --}}
                {{-- <div class="col-lg-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 pt-3">
                            <h2 class="h6 fw-bold mb-0">Transfer Timeline</h2>
                        </div>
                        <div class="card-body">
                            @forelse ($transfer->events ?? [] as $event)
                                <div class="d-flex mb-3">
                                    <div class="me-3">
                                        <span
                                            class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px;">
                                            <i class="bi bi-circle-fill" style="font-size: 8px;"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="fw-semibold mb-0">{{ ucfirst(str_replace('_', ' ', $event->type)) }}</p>
                                        <p class="text-muted small mb-0">
                                            {{ $event->user->name ?? '—' }} &middot;
                                            {{ $event->created_at->format('d M Y, H:i') }}
                                        </p>
                                        @if ($event->notes)
                                            <p class="small mb-0">{{ $event->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small mb-0">No timeline events recorded yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div> --}}

                {{-- Additional Information --}}
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 pt-3">
                            <h2 class="h6 fw-bold mb-0">Additional Information</h2>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-1">Notes</p>
                            <p class="mb-3">{{ $transfer->notes ?? '—' }}</p>

                            @if ($transfer->status === 'rejected' && $transfer->rejection_reason)
                                <p class="text-muted small mb-1">Rejection Reason</p>
                                <p class="text-danger mb-3">{{ $transfer->rejection_reason }}</p>
                            @endif

                            <p class="text-muted small mb-1">Created At</p>
                            <p class="mb-3">{{ $transfer->created_at->format('d M Y, H:i') }}</p>

                            <p class="text-muted small mb-1">Last Updated</p>
                            <p class="mb-0">{{ $transfer->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
