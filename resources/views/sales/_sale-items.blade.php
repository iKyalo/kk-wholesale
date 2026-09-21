<div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead>
            <tr class="text-muted small text-uppercase">
                <th>#</th>
                <th>Product</th>
                <th>SKU</th>
                <th class="text-end">Quantity</th>
                <th class="text-end">Unit Price</th>
                <th class="text-end">Discount</th>
                <th class="text-end">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sale->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="fw-semibold">{{ $item->product->name ?? '—' }}</td>
                    <td>{{ $item->product->sku ?? '—' }}</td>
                    <td class="text-end">{{ number_format($item->quantity) }}</td>
                    <td class="text-end">KSh {{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-end">KSh {{ number_format($item->discount, 2) }}</td>
                    <td class="text-end fw-semibold">KSh {{ number_format(($item->unit_price * $item->quantity) - $item->discount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No items recorded for this sale.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
