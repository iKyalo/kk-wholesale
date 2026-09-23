@extends('layouts.app')

@section('page-title', 'Add User')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0">Add User</h1>
            <p class="text-muted small mb-0">Create a new system user and assign their access.</p>
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
            <form method="POST" action="{{ route('users.store') }}" novalidate>
                @csrf

                @include('users._form')

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4">Save User</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
