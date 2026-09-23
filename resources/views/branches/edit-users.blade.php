@extends('layouts.app')

@section('content')
    @php
        // Assigned user IDs: prefer old input (after a validation failure) so
        // selections survive, otherwise fall back to the branch's current users.
$assignedIds = old(
    'users',
    optional($branch->users ?? collect())
        ->pluck('id')
        ->toArray() ?? [],
);
$assignedIds = collect($assignedIds)->map(fn($id) => (int) $id)->all();

$roleBadgeMap = [
    'Administrator' => 'bg-danger',
    'Branch Manager' => 'bg-primary',
    'Store Manager' => 'bg-info text-dark',
        ];
    @endphp

    <div class="container-fluid py-4">

        {{-- Page Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
            <div>
                <h1 class="h3 mb-1">Edit Branch Users</h1>
                <p class="text-muted mb-0">Manage users assigned to {{ $branch->name }}</p>
            </div>
            <a href="{{ route('branches.show', $branch) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
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

        {{-- Branch Information Card --}}
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Branch Information</h5>
            </div>
            <div class="card-body">
                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
                    <div class="col">
                        <div class="text-muted small">Branch Name</div>
                        <div>{{ $branch->name }}</div>
                    </div>
                    <div class="col">
                        <div class="text-muted small">Branch Code</div>
                        <div>{{ $branch->code ?? '—' }}</div>
                    </div>
                    <div class="col">
                        <div class="text-muted small">Location / Address</div>
                        <div>{{ $branch->location ?? ($branch->address ?? '—') }}</div>
                    </div>
                    <div class="col">
                        <div class="text-muted small">Assigned Users</div>
                        <div><span class="badge bg-secondary" id="assignedCountBadge">{{ count($assignedIds) }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- User Assignment Card --}}
        <form action="{{ route('branches.users.update', $branch) }}" method="POST" id="branchUsersForm">
            @csrf
            @method('PUT')

            <div class="card mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h5 class="mb-0">Assign Users</h5>
                        <div class="input-group" style="max-width: 320px;">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="userSearchInput" placeholder="Search users..."
                                aria-label="Search users">
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if ($users->count())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle" id="usersTable">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="selectAllUsers"
                                                    aria-label="Select all users">
                                            </div>
                                        </th>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Assignment Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        @php
                                            $isAssigned = in_array((int) $user->id, $assignedIds, true);
                                            $roleBadgeClass = $roleBadgeMap[$user->role->name] ?? 'bg-secondary';
                                        @endphp
                                        <tr class="user-row {{ $isAssigned ? 'table-active' : '' }}"
                                            data-name="{{ strtolower($user->name) }}"
                                            data-email="{{ strtolower($user->email) }}"
                                            data-phone="{{ strtolower($user->phone ?? '') }}">
                                            <td>
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input user-checkbox" type="checkbox"
                                                        name="users[]" value="{{ $user->id }}"
                                                        id="user_{{ $user->id }}" {{ $isAssigned ? 'checked' : '' }}
                                                        aria-label="Assign {{ $user->name }} to this branch">
                                                </div>
                                            </td>
                                            <td>
                                                <label for="user_{{ $user->id }}"
                                                    class="mb-0">{{ $user->name }}</label>
                                            </td>
                                            <td class="text-muted">{{ $user->email }}</td>
                                            <td class="text-muted">{{ $user->phone ?? '—' }}</td>
                                            <td>
                                                <span class="badge {{ $roleBadgeClass }}">{{ $user->role->name }}</span>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge assignment-badge {{ $isAssigned ? 'bg-success' : 'bg-light text-muted border' }}">
                                                    <i
                                                        class="bi bi-{{ $isAssigned ? 'check-circle' : 'dash-circle' }} me-1"></i>
                                                    <span
                                                        class="assignment-label">{{ $isAssigned ? 'Assigned' : 'Not Assigned' }}</span>
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- No search results empty state --}}
                        <div class="text-center text-muted py-5 d-none" id="noSearchResults">
                            <i class="bi bi-search d-block mb-2" style="font-size: 1.75rem;"></i>
                            No users match your search.
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-people d-block mb-2" style="font-size: 1.75rem;"></i>
                            No users are available to assign.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex flex-wrap justify-content-end gap-2">
                <a href="{{ route('branches.show', $branch) }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="saveChangesBtn"
                    {{ $users->count() ? '' : 'disabled' }}>
                    <span class="spinner-border spinner-border-sm d-none me-1" id="saveChangesSpinner"
                        aria-hidden="true"></span>
                    <i class="bi bi-check-lg me-1" id="saveChangesIcon"></i>
                    Save Changes
                </button>
            </div>
        </form>

    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            var $rows = $('.user-row');
            var $selectAll = $('#selectAllUsers');
            var $searchInput = $('#userSearchInput');
            var $noResults = $('#noSearchResults');

            function syncSelectAllState() {
                var total = $rows.filter(':visible').length;
                var checked = $rows.filter(':visible').find('.user-checkbox:checked').length;

                if (total === 0) {
                    $selectAll.prop('checked', false).prop('indeterminate', false);
                } else if (checked === 0) {
                    $selectAll.prop('checked', false).prop('indeterminate', false);
                } else if (checked === total) {
                    $selectAll.prop('checked', true).prop('indeterminate', false);
                } else {
                    $selectAll.prop('checked', false).prop('indeterminate', true);
                }
            }

            // Select all / unselect all (applies to currently visible/filtered rows)
            $selectAll.on('change', function() {
                var isChecked = $(this).is(':checked');
                $rows.filter(':visible').find('.user-checkbox').prop('checked', isChecked);
                syncSelectAllState();
            });

            $(document).on('change', '.user-checkbox', function() {
                syncSelectAllState();
            });

            // Search / filter by name, email, or phone
            $searchInput.on('keyup input', function() {
                var query = $(this).val().toLowerCase().trim();
                var visibleCount = 0;

                $rows.each(function() {
                    var $row = $(this);
                    var matches = query === '' ||
                        $row.data('name').indexOf(query) !== -1 ||
                        $row.data('email').indexOf(query) !== -1 ||
                        $row.data('phone').indexOf(query) !== -1;

                    $row.toggle(matches);
                    if (matches) {
                        visibleCount++;
                    }
                });

                $noResults.toggleClass('d-none', visibleCount !== 0);
                syncSelectAllState();
            });

            // Initialize select-all state on load (in case of pre-checked rows)
            syncSelectAllState();

            // Loading state on submit
            $('#branchUsersForm').on('submit', function() {
                var $btn = $('#saveChangesBtn');
                $btn.prop('disabled', true);
                $('#saveChangesSpinner').removeClass('d-none');
                $('#saveChangesIcon').addClass('d-none');
            });
        });
    </script>
@endpush
