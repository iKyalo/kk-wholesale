@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0">Receive Stock Transfer</h1>
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

    @if (!in_array($transfer->status, ['approved', 'in_transit']))

        <div class="alert alert-warning" role="alert">
            This transfer is currently <strong>{{ ucfirst(str_replace('_', ' ', $transfer->status)) }}</strong> and is not eligible for receipt.
            Only transfers that are <strong>Approved</strong> or <strong>In Transit</strong> can be received.
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
                        <p class="text-muted small mb-1">Dispatch Date</p>
                        <p class="fw-semibold mb-0">{{ optional($transfer->dispatched_at)->format('d M Y, H:i') ?? '—' }}</p>
                    </div>
                    <div class="col-md-3 col-6">
                        <p class="text-muted small mb-1">Status</p>
                        @if ($transfer->status === 'approved')
                            <span class="badge text-bg-info">Approved</span>
                        @else
                            <span class="badge text-bg-primary">In Transit</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('transfers.receive', $transfer) }}"
            onsubmit="return confirm('Confirm receipt of this stock transfer with the quantities entered?');"
        >
            @csrf

            {{-- Receiving Table --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-3">
                    <h2 class="h6 fw-bold mb-0">Receiving</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" id="receivingTable">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th class="text-end">Quantity Dispatched</th>
                                    <th style="width: 130px;">Quantity Received</th>
                                    <th class="text-end">Difference</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transfer->items as $item)
                                    @php
                                        $dispatched = $item->quantity_dispatched ?? $item->quantity;
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $item->product->name ?? '—' }}</td>
                                        <td>{{ $item->product->sku ?? '—' }}</td>
                                        <td class="text-end dispatched-qty" data-dispatched="{{ $dispatched }}">{{ number_format($dispatched) }}</td>
                                        <td>
                                            <input type="hidden" name="items[{{ $loop->index }}][stock_transfer_item_id]" value="{{ $item->id }}">
                                            <input
                                                type="number"
                                                class="form-control form-control-sm received-qty-input"
                                                name="items[{{ $loop->index }}][quantity_received]"
                                                min="0"
                                                max="{{ $dispatched }}"
                                                value="{{ old('items.' . $loop->index . '.quantity_received', $dispatched) }}"
                                                data-row="{{ $loop->index }}"
                                                required
                                            >
                                        </td>
                                        <td class="text-end difference-cell" data-row="{{ $loop->index }}">0</td>
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

            {{-- Receiving Notes --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <label for="receiving_notes" class="form-label">Receiving Notes <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea
                        class="form-control @error('receiving_notes') is-invalid @enderror"
                        id="receiving_notes"
                        name="receiving_notes"
                        rows="3"
                        placeholder="Note any damage, shortages, or discrepancies"
                    >{{ old('receiving_notes') }}</textarea>
                    @error('receiving_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">Confirm Receipt</button>
                <a href="{{ route('transfers.show', $transfer) }}" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>

    @endif

</div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function updateDifference(row) {
        const input = document.querySelector('.received-qty-input[data-row="' + row + '"]');
        const dispatchedCell = document.querySelectorAll('.dispatched-qty')[row];
        const diffCell = document.querySelector('.difference-cell[data-row="' + row + '"]');

        if (!input || !dispatchedCell || !diffCell) return;

        const dispatched = parseInt(dispatchedCell.dataset.dispatched, 10) || 0;
        let received = parseInt(input.value, 10);

        if (isNaN(received) || received < 0) {
            received = 0;
        }
        if (received > dispatched) {
            received = dispatched;
            input.value = dispatched;
        }

        const diff = received - dispatched;
        diffCell.textContent = diff === 0 ? '0' : (diff > 0 ? '+' + diff : diff);
        diffCell.className = 'text-end difference-cell' + (diff !== 0 ? ' text-danger fw-semibold' : '');
    }

    document.querySelectorAll('.received-qty-input').forEach(function (input) {
        updateDifference(input.dataset.row);
        input.addEventListener('input', function () {
            updateDifference(input.dataset.row);
        });
    });
});
</script>
@endpush
@endsection
