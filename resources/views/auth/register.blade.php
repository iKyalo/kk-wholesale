@extends('layouts.app')

@section('content')
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light py-5">
        <div class="w-100" style="max-width: 480px;">

            <div class="text-center mb-4">
                <a href="{{ url('/') }}" class="text-decoration-none">
                    <span class="fs-3 fw-bold text-dark">{{ config('app.name', 'Laravel') }}</span>
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">

                    <div class="text-center mb-4">
                        <h1 class="h4 fw-bold mb-1">Create Account</h1>
                        <p class="text-muted small mb-0">Create your account to get started.</p>
                    </div>

                    @if ($errors->any() && !$errors->has('name') && !$errors->has('email') && !$errors->has('password'))
                        <div class="alert alert-danger" role="alert">
                            {{ __('Something went wrong. Please check the form and try again.') }}
                        </div>
                    @endif

                    @session('status')
                        <div class="alert alert-success" role="alert">
                            {{ $value }}
                        </div>
                    @endsession

                    {{-- <form method="POST" action="{{ route('register') }}" novalidate> --}}
                    <form method="POST" novalidate>
                        @csrf

                        {{-- Full Name --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ old('name') }}" autocomplete="name" placeholder="Jane Doe"
                                required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email Address --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                name="email" value="{{ old('email') }}" autocomplete="email"
                                placeholder="name@example.com" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Phone Number --}}
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone"
                                name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="+254 700 000000"
                                required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" autocomplete="new-password" placeholder="••••••••" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Confirm Password --}}
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                                id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                                placeholder="••••••••" required>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary py-2">
                                Register
                            </button>
                        </div>

                        <p class="text-center text-muted small mb-0">
                            Already have an account?
                            {{-- <a href="{{ route('login') }}" class="text-decoration-none">Login</a> --}}
                            <a href="" class="text-decoration-none">Login</a>
                        </p>
                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection
