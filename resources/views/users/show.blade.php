@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        {{-- Page Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
            <div>
                <h1 class="h3 mb-1">User Details</h1>
                <p class="text-muted mb-0">View account information and assignments.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Users
                </a>
                <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                    <i class="bi bi-pencil-square me-1"></i> Edit User
                </a>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <strong>There were some problems with your request:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- User Summary Card --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    {{-- Avatar --}}
                    @if (!empty($user->profile_photo_url))
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="rounded-circle"
                            style="width: 64px; height: 64px; object-fit: cover;">
                    @else
                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary text-white flex-shrink-0"
                            style="width: 64px; height: 64px; font-size: 1.25rem; font-weight: 600;">
                            {{ collect(explode(' ', $user->name))->map(fn($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('') }}
                        </div>
                    @endif

                    {{-- Name / Contact --}}
                    <div class="flex-grow-1">
                        <h4 class="mb-1">{{ $user->name }}</h4>
                        <div class="text-muted small mb-1">
                            <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                        </div>
                        @if (!empty($user->phone))
                            <div class="text-muted small">
                                <i class="bi bi-telephone me-1"></i>{{ $user->phone }}
                            </div>
                        @endif
                    </div>

                    {{-- Badges --}}
                    <div class="d-flex flex-column align-items-end gap-2">
                        @php
                            $roleBadgeMap = [
                                'Administrator' => 'bg-danger',
                                'Branch Manager' => 'bg-primary',
                                'Store Manager' => 'bg-info text-dark',
                            ];
                            $roleBadgeClass = $roleBadgeMap[$user->role->name] ?? 'bg-secondary';
                        @endphp
                        <span class="badge {{ $roleBadgeClass }}">{{ $user->role->name }}</span>

                        @if (isset($user->status))
                            <span class="badge {{ $user->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                <i
                                    class="bi bi-{{ $user->status === 'Active' ? 'check-circle' : 'dash-circle' }} me-1"></i>
                                {{ $user->status }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-6">

                {{-- Account Information --}}
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Account Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-1 row-cols-md-2 g-3">
                            <div class="col">
                                <div class="text-muted small">Full Name</div>
                                <div>{{ $user->name }}</div>
                            </div>

                            <div class="col">
                                <div class="text-muted small">Email Address</div>
                                <div>{{ $user->email }}</div>
                            </div>

                            @if (!empty($user->phone))
                                <div class="col">
                                    <div class="text-muted small">Phone Number</div>
                                    <div>{{ $user->phone }}</div>
                                </div>
                            @endif

                            @if (isset($user->role))
                                <div class="col">
                                    <div class="text-muted small">Role</div>
                                    <div><span class="badge {{ $roleBadgeClass }}">{{ $user->role->name }}</span></div>
                                </div>
                            @endif

                            @if (isset($user->status))
                                <div class="col">
                                    <div class="text-muted small">Account Status</div>
                                    <div>
                                        <span
                                            class="badge {{ $user->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $user->status }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($user->created_at))
                                <div class="col">
                                    <div class="text-muted small">Date Joined</div>
                                    <div>{{ $user->created_at->format('d M Y, H:i') }}</div>
                                </div>
                            @endif

                            @if (!empty($user->updated_at))
                                <div class="col">
                                    <div class="text-muted small">Last Updated</div>
                                    <div>{{ $user->updated_at->format('d M Y, H:i') }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Role-specific Information --}}
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Access Scope</h5>
                    </div>
                    <div class="card-body">
                        @switch($user->role)
                            @case('Administrator')
                                <p class="mb-0">
                                    <i class="bi bi-shield-lock text-danger me-1"></i>
                                    This user has <strong>system-wide access</strong> across all branches and stores.
                                </p>
                            @break

                            @case('Branch Manager')
                                <p class="mb-2">
                                    <i class="bi bi-diagram-3 text-primary me-1"></i>
                                    This user manages the following branch(es):
                                </p>
                                @if (!empty($user->branches) && $user->branches->count())
                                    <ul class="mb-0">
                                        @foreach ($user->branches as $branch)
                                            <li>{{ $branch->name }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted mb-0">No branches assigned.</p>
                                @endif
                            @break

                            @case('Store Manager')
                                <p class="mb-2">
                                    <i class="bi bi-shop text-info me-1"></i>
                                    This user manages the following store(s):
                                </p>
                                @if (!empty($user->stores) && $user->stores->count())
                                    <ul class="mb-0">
                                        @foreach ($user->stores as $store)
                                            <li>{{ $store->name }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted mb-0">No stores assigned.</p>
                                @endif
                            @break

                            @default
                                <p class="text-muted mb-0">No scope information available for this role.</p>
                        @endswitch
                    </div>
                </div>

            </div>

            <div class="col-12 col-lg-6">

                {{-- Branch Assignments --}}
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Branch Assignments</h5>
                    </div>
                    <div class="card-body p-0">
                        @if (!empty($user->branches) && $user->branches->count())
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>Branch Name</th>
                                            <th>Branch Code</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($user->branches as $branch)
                                            <tr>
                                                <td>{{ $branch->name }}</td>
                                                <td>{{ $branch->code ?? '—' }}</td>
                                                <td>{{ $branch->location ?? '—' }}</td>
                                                <td>
                                                    @php
                                                        $assignmentStatus = $branch->pivot->status ?? 'Active';
                                                    @endphp
                                                    <span
                                                        class="badge {{ $assignmentStatus === 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ $assignmentStatus }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-diagram-3 d-block mb-2" style="font-size: 1.75rem;"></i>
                                No branches assigned to this user.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Store Assignments --}}
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Store Assignments</h5>
                    </div>
                    <div class="card-body p-0">
                        @if (!empty($user->stores) && $user->stores->count())
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>Store Name</th>
                                            <th>Store Code</th>
                                            <th>Branch</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($user->stores as $store)
                                            <tr>
                                                <td>{{ $store->name }}</td>
                                                <td>{{ $store->code ?? '—' }}</td>
                                                <td>{{ $store->branch->name ?? '—' }}</td>
                                                <td>{{ $store->location ?? '—' }}</td>
                                                <td>
                                                    @php
                                                        $assignmentStatus = $store->pivot->status ?? 'Active';
                                                    @endphp
                                                    <span
                                                        class="badge {{ $assignmentStatus === 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ $assignmentStatus }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-shop d-block mb-2" style="font-size: 1.75rem;"></i>
                                No stores assigned to this user.
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- Bottom Actions --}}
        <div class="d-flex flex-wrap justify-content-between gap-2 mt-2">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Users
            </a>

            <div class="d-flex gap-2">
                @if (isset($user->status) && Route::has('users.toggle-status'))
                    <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="btn btn-outline-{{ $user->status === 'Active' ? 'warning' : 'success' }}">
                            <i class="bi bi-{{ $user->status === 'Active' ? 'slash-circle' : 'check-circle' }} me-1"></i>
                            {{ $user->status === 'Active' ? 'Deactivate' : 'Activate' }} User
                        </button>
                    </form>
                @endif

                <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                    <i class="bi bi-pencil-square me-1"></i> Edit User
                </a>
            </div>
        </div>

    </div>
@endsection
