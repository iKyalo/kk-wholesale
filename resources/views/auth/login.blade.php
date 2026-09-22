@extends('layouts.auth')

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
                        <h1 class="h4 fw-bold mb-1">Welcome Back</h1>
                        <p class="text-muted small mb-0">Log in to your account to continue.</p>
                    </div>

                    @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
                        <div class="alert alert-danger" role="alert">
                            {{ __('These credentials do not match our records.') }}
                        </div>
                    @endif

                    @session('status')
                        <div class="alert alert-success" role="alert">
                            {{ $value }}
                        </div>
                    @endsession

                    <form method="POST" action="{{ route('login.store') }}" novalidate>
                        @csrf

                        {{-- Email Address --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                name="email" value="{{ old('email') }}" autocomplete="email"
                                placeholder="name@example.com" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" autocomplete="current-password" placeholder="••••••••"
                                required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Remember Me --}}
                        <div class="mb-4 d-flex align-items-center justify-content-between">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember"
                                    {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label small" for="remember">
                                    Remember me
                                </label>
                            </div>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="small text-decoration-none">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary py-2">
                                Login
                            </button>
                        </div>

                        @if (Route::has('register'))
                            <p class="text-center text-muted small mb-0">
                                Don't have an account?
                                <a href="{{ route('register') }}" class="text-decoration-none">Register</a>
                            </p>
                        @endif
                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection
