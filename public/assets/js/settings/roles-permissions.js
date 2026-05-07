/**
 * Roles & Permissions Management
 */

(function () {
    'use strict';

    const notyf = typeof window.Notiflix !== 'undefined' && window.Notiflix.Notify
        ? window.Notiflix.Notify
        : {
            success(message) { alert(message); },
            failure(message) { alert(message); },
        };

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            return;
        }

        const routes = window.rolesPermissionsRoutes || {};
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // ─── Roles Tab ───────────────────────────────────────────────

        $('#addRoleBtn').on('click', function () {
            $('#editRoleId').val('');
            $('#newRoleName').val('');
            $('#addRoleForm').slideDown(200);
            $('#newRoleName').focus();
        });

        $('#cancelRoleBtn').on('click', function () {
            $('#addRoleForm').slideUp(200);
            $('#editRoleId').val('');
            $('#newRoleName').val('');
        });

        $(document).on('click', '.edit-role-btn', function () {
            const roleId = $(this).data('role-id');
            const roleName = $(this).data('role-name');
            $('#editRoleId').val(roleId);
            $('#newRoleName').val(roleName);
            $('#addRoleForm').slideDown(200);
            $('#newRoleName').focus();
        });

        $('#saveRoleBtn').on('click', function () {
            const name = $('#newRoleName').val().trim();
            const id = $('#editRoleId').val();
            const $btn = $(this);

            if (!name) {
                notyf.failure('Role name is required.');
                return;
            }

            if (!/^[a-z_]+$/.test(name)) {
                notyf.failure('Role name must be lowercase letters and underscores only.');
                return;
            }

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

            $.ajax({
                url: routes.saveRole,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                data: { name: name, id: id || null },
                success(response) {
                    notyf.success(response.message || 'Role saved.');
                    location.reload();
                },
                error(xhr) {
                    $btn.prop('disabled', false).html('Save');
                    notyf.failure(xhr.responseJSON?.message || 'Failed to save role.');
                },
            });
        });

        $(document).on('click', '.delete-role-btn', function () {
            const roleId = $(this).data('role-id');
            const roleName = $(this).data('role-name');

            Swal.fire({
                title: 'Delete Role?',
                text: 'Are you sure you want to delete the role "' + roleName + '"? This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it',
            }).then(function (result) {
                if (!result.isConfirmed) return;

                const url = routes.deleteRole.replace(':id', roleId);

                $.ajax({
                    url: url,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success(response) {
                        notyf.success(response.message || 'Role deleted.');
                        location.reload();
                    },
                    error(xhr) {
                        notyf.failure(xhr.responseJSON?.message || 'Failed to delete role.');
                    },
                });
            });
        });

        // Click "Edit Permissions" button on roles tab → switch to permissions tab with role selected
        $(document).on('click', '.edit-permissions-btn', function () {
            const roleId = $(this).data('role-id');
            $('#permissionRoleSelect').val(roleId).trigger('change');
            $('#permissions-tab').tab('show');
        });

        // ─── Permissions Tab ─────────────────────────────────────────

        $('#permissionRoleSelect').on('change', function () {
            const roleId = $(this).val();

            if (!roleId) {
                $('#permissionsGrid').hide();
                $('#permissionsPlaceholder').show();
                return;
            }

            // Reset all checkboxes
            $('.permission-checkbox').prop('checked', false);
            $('.group-check-all').prop('checked', false);

            // Load current permissions for this role
            $.ajax({
                url: routes.rolePermissions,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                data: { role_id: roleId },
                success(response) {
                    const perms = response.permissions || [];

                    perms.forEach(function (perm) {
                        $('.permission-checkbox[value="' + perm + '"]').prop('checked', true);
                    });

                    // Update group "select all" state
                    updateGroupCheckAllStates();

                    $('#permissionsPlaceholder').hide();
                    $('#permissionsGrid').show();
                },
                error(xhr) {
                    notyf.failure(xhr.responseJSON?.message || 'Failed to load permissions.');
                },
            });
        });

        // Group "Select All" toggle
        $(document).on('change', '.group-check-all', function () {
            const group = $(this).data('group');
            const isChecked = $(this).is(':checked');
            $('.permission-checkbox[data-group="' + group + '"]').prop('checked', isChecked);
        });

        // Individual checkbox → update group "select all"
        $(document).on('change', '.permission-checkbox', function () {
            updateGroupCheckAllStates();
        });

        function updateGroupCheckAllStates() {
            $('.group-check-all').each(function () {
                const group = $(this).data('group');
                const $groupBoxes = $('.permission-checkbox[data-group="' + group + '"]');
                const allChecked = $groupBoxes.length > 0 && $groupBoxes.filter(':checked').length === $groupBoxes.length;
                $(this).prop('checked', allChecked);
            });
        }

        $('#syncPermissionsBtn').on('click', function () {
            const roleId = $('#permissionRoleSelect').val();
            const $btn = $(this);

            if (!roleId) {
                notyf.failure('Please select a role first.');
                return;
            }

            const permissions = [];
            $('.permission-checkbox:checked').each(function () {
                permissions.push($(this).val());
            });

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

            $.ajax({
                url: routes.syncPermissions,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                data: { role_id: roleId, permissions: permissions },
                success(response) {
                    $btn.prop('disabled', false).html('<i class="ti tabler-check me-1"></i> Save Permissions');
                    notyf.success(response.message || 'Permissions synced.');
                },
                error(xhr) {
                    $btn.prop('disabled', false).html('<i class="ti tabler-check me-1"></i> Save Permissions');
                    notyf.failure(xhr.responseJSON?.message || 'Failed to sync permissions.');
                },
            });
        });

        // ─── Staff Tab ───────────────────────────────────────────────

        $(document).on('click', '.impersonate-btn', function (e) {
            e.preventDefault();
            const name = $(this).data('name');
            const href = $(this).attr('href');

            Swal.fire({
                title: 'Impersonate User?',
                text: 'You will be logged in as "' + name + '". You can stop impersonation from the sidebar.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, impersonate',
            }).then(function (result) {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });
})();
