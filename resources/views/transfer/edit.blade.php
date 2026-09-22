@extends('layouts.app')

@section('content')
<div class="bg-light min-vh-100 py-4">
<div class="container-fluid px-3 px-md-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0">Edit Stock Transfer</h1>
            <p class="text-muted small mb-0">{{ $transfer->transfer_number }}</p>
        </div>
        <a href="{{ route('transfers.index') }}" class="btn btn-outline-secondary">
            Back to Transfers
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            Please fix the errors below and try again.
        </div>
    @endif

    @if ($transfer->status !== 'pending')

        <div class="alert alert-warning" role="alert">
            This transfer is currently <strong>{{ ucfirst(str_replace('_', ' ', $transfer->status)) }}</strong> and can no longer be edited.
            Only transfers with a <strong>Pending</strong> status can be modified.
        </div>

        <a href="{{ route('transfers.show', $transfer) }}" class="btn btn-outline-primary">
            View Transfer Details
        </a>

    @else

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('transfers.update', $transfer) }}" novalidate>
                    @csrf
                    @method('PUT')

                    @include('transfers._form')

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">Update Transfer</button>
                        <a href="{{ route('transfers.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

    @endif

</div>
</div>
@endsection
