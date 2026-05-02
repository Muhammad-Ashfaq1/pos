@extends('tenant.settings.layout.settings-master')

@php
    $pageTitle = 'Roles & Permissions';
@endphp

@section('content-body')
    <ul class="nav nav-tabs mb-4" id="rolesPermissionsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles-pane" type="button" role="tab" aria-controls="roles-pane" aria-selected="true">
                <i class="ti tabler-shield me-1"></i> Roles
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="permissions-tab" data-bs-toggle="tab" data-bs-target="#permissions-pane" type="button" role="tab" aria-controls="permissions-pane" aria-selected="false">
                <i class="ti tabler-lock me-1"></i> Permissions
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff-pane" type="button" role="tab" aria-controls="staff-pane" aria-selected="false">
                <i class="ti tabler-users me-1"></i> Staff
            </button>
        </li>
    </ul>

    <div class="tab-content" id="rolesPermissionsTabContent">
        {{-- Roles Tab --}}
        <div class="tab-pane fade show active" id="roles-pane" role="tabpanel" aria-labelledby="roles-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Manage Roles</h6>
                <button type="button" class="btn btn-sm btn-primary" id="addRoleBtn">
                    <i class="ti tabler-plus me-1"></i> Add Role
                </button>
            </div>

            <div id="addRoleForm" class="mb-3" style="display:none;">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label for="newRoleName" class="form-label">Role Name <small class="text-muted">(lowercase, underscores only)</small></label>
                        <input type="text" class="form-control" id="newRoleName" name="name" placeholder="e.g. warehouse_staff" pattern="[a-z_]+" maxlength="50">
                        <input type="hidden" id="editRoleId" value="">
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" id="saveRoleBtn">Save</button>
                        <button type="button" class="btn btn-outline-secondary" id="cancelRoleBtn">Cancel</button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="rolesTable">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Permissions</th>
                            <th>Users</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr data-role-id="{{ $role->id }}">
                                <td>
                                    <span class="badge bg-label-primary">{{ str($role->name)->replace('_', ' ')->title() }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $role->permissions->count() }} permission(s)</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ \App\Models\User::where('tenant_id', $tenant->getKey())->where('role', $role->name)->count() }}</span>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-icon btn-text-primary edit-permissions-btn" data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}" title="Edit Permissions">
                                        <i class="ti tabler-lock"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-text-warning edit-role-btn" data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}" title="Edit Role">
                                        <i class="ti tabler-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon btn-text-danger delete-role-btn" data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}" title="Delete Role">
                                        <i class="ti tabler-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No custom roles yet. Click "Add Role" to create one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Permissions Tab --}}
        <div class="tab-pane fade" id="permissions-pane" role="tabpanel" aria-labelledby="permissions-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Assign Permissions to Role</h6>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="permissionRoleSelect" class="form-label">Select Role</label>
                    <select class="form-select" id="permissionRoleSelect">
                        <option value="">-- Choose a role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ str($role->name)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="permissionsGrid" style="display:none;">
                @foreach($permissionGroups as $group => $permissions)
                    <div class="card mb-3 shadow-none border">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ $group }}</h6>
                            <div class="form-check">
                                <input class="form-check-input group-check-all" type="checkbox" data-group="{{ $group }}" id="checkAll{{ Str::camel($group) }}">
                                <label class="form-check-label small" for="checkAll{{ Str::camel($group) }}">Select All</label>
                            </div>
                        </div>
                        <div class="card-body py-2">
                            <div class="row">
                                @foreach($permissions as $perm)
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $perm['name'] }}" id="perm_{{ Str::slug($perm['name'], '_') }}" data-group="{{ $group }}">
                                            <label class="form-check-label" for="perm_{{ Str::slug($perm['name'], '_') }}">
                                                {{ $perm['label'] }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="text-end">
                    <button type="button" class="btn btn-primary" id="syncPermissionsBtn">
                        <i class="ti tabler-check me-1"></i> Save Permissions
                    </button>
                </div>
            </div>

            <div id="permissionsPlaceholder" class="text-center text-muted py-5">
                <i class="ti tabler-shield-lock" style="font-size: 3rem;"></i>
                <p class="mt-2">Select a role to manage its permissions.</p>
            </div>
        </div>

        {{-- Staff Tab --}}
        <div class="tab-pane fade" id="staff-pane" role="tabpanel" aria-labelledby="staff-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Staff Members</h6>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="staffTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $member)
                            <tr>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->email }}</td>
                                <td>
                                    <span class="badge bg-label-info">{{ str($member->role)->replace('_', ' ')->title() }}</span>
                                </td>
                                <td>
                                    @if($member->is_active)
                                        <span class="badge bg-label-success">Active</span>
                                    @else
                                        <span class="badge bg-label-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $member->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('tenant.settings.roles-permissions.staff.impersonate', $member) }}"
                                       class="btn btn-sm btn-icon btn-text-warning impersonate-btn"
                                       title="Impersonate {{ $member->name }}"
                                       data-name="{{ $member->name }}">
                                        <i class="ti tabler-user-check"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No staff members found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('page-script-content')
    <script>
        window.rolesPermissionsRoutes = {
            saveRole: '{{ route("tenant.settings.roles-permissions.roles.save") }}',
            deleteRole: '{{ route("tenant.settings.roles-permissions.roles.destroy", ":id") }}',
            rolePermissions: '{{ route("tenant.settings.roles-permissions.role-permissions") }}',
            syncPermissions: '{{ route("tenant.settings.roles-permissions.permissions.sync") }}',
        };
    </script>
    <script src="{{ asset('assets/js/settings/roles-permissions.js') }}"></script>
@endsection
