@extends('layouts.app')

@section('page-title', 'Edit User')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0">Edit User</h1>
            <p class="text-muted small mb-0">Update details and access for {{ $user->name }}.</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
            Back to Users
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Please fix the following errors:</strong>

            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('users.update', $user) }}" novalidate>
                @csrf
                @method('PUT')

                @include('users._form')

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4">Update User</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
