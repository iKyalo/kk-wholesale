@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2 no-print">
        <div>
            <h1 class="h3 fw-bold mb-0">Sale {{ $sale->sale_number }}</h1>
            <p class="text-muted small mb-0">{{ $sale->created_at->format('d M Y, H:i') }}</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Receipt
            </button>
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                Back to Sales
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm" id="receipt">
        <div class="card-body p-4 p-md-5">

            {{-- Business Information --}}
            <div class="text-center mb-4">
                <h2 class="fw-bold mb-1">{{ config('app.name', 'Laravel') }}</h2>
                @isset($businessInfo)
                    <p class="text-muted small mb-0">{{ $businessInfo['address'] ?? '' }}</p>
                    <p class="text-muted small mb-0">
                        {{ $businessInfo['phone'] ?? '' }}
                        @if (!empty($businessInfo['email']))
                            &middot; {{ $businessInfo['email'] }}
                        @endif
                    </p>
                @endisset
            </div>

            <hr>

            {{-- Sale Header --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <p class="text-muted small mb-1">Sale Number</p>
                    <p class="fw-semibold mb-0">{{ $sale->sale_number }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <p class="text-muted small mb-1">Date</p>
                    <p class="fw-semibold mb-0">{{ $sale->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <p class="text-muted small mb-1">Status</p>
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
                </div>
                <div class="col-md-3 col-6">
                    <p class="text-muted small mb-1">Payment Method</p>
                    <p class="fw-semibold mb-0">{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</p>
                </div>
            </div>

            {{-- Sale Information --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <p class="text-muted small mb-1">Branch</p>
                    <p class="fw-semibold mb-0">{{ $sale->store->branch->name ?? '—' }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <p class="text-muted small mb-1">Store</p>
                    <p class="fw-semibold mb-0">{{ $sale->store->name ?? '—' }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <p class="text-muted small mb-1">Cashier</p>
                    <p class="fw-semibold mb-0">{{ $sale->cashier->name ?? '—' }}</p>
                </div>
                <div class="col-md-3 col-6">
                    <p class="text-muted small mb-1">Customer</p>
                    <p class="fw-semibold mb-0">{{ $sale->customer_name ?? 'Walk-in' }}</p>
                    @if ($sale->customer_phone)
                        <p class="text-muted small mb-0">{{ $sale->customer_phone }}</p>
                    @endif
                </div>
            </div>

            <hr>

            {{-- Sale Items --}}
            @include('sales._sale-items')

            <hr>

            {{-- Payment Summary --}}
            <div class="row justify-content-end">
                <div class="col-md-5">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span>KSh {{ number_format($sale->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Discount</span>
                        <span>KSh {{ number_format($sale->discount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold">Grand Total</span>
                        <span class="fw-bold">KSh {{ number_format($sale->total, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Amount Paid</span>
                        <span>KSh {{ number_format($sale->amount_paid ?? $sale->total, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Balance</span>
                        <span>KSh {{ number_format(($sale->amount_paid ?? $sale->total) - $sale->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="text-center text-muted small mt-5">
                Thank you for your business.
            </div>

        </div>
    </div>

</div>
</div>

@push('styles')
<style>
    @media print {
        .no-print,
        nav,
        .btn {
            display: none !important;
        }
        #receipt {
            border: none !important;
            box-shadow: none !important;
        }
        .bg-light {
            background: #fff !important;
        }
    }
</style>
@endpush
@endsection
