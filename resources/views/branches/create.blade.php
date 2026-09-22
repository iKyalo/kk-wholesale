@extends('layouts.app')

@section('content')
    <div class="bg-light min-vh-100 py-4">
        <div class="container-fluid px-3 px-md-4">

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-0">Create Branch</h1>
                    <p class="text-muted small mb-0">Add a new branch to your organization.</p>
                </div>
                <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary">
                    Back to Branches
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    Please fix the errors below and try again.
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('branches.store') }}" novalidate>
                        @csrf

                        @include('branches.form')

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary px-4">Save Branch</button>
                            <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
