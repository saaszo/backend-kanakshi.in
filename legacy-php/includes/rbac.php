<?php
/**
 * Role-Based Access Control (RBAC) System
 * Flattened for Saaszo Pro — All admins have full access.
 */

/**
 * Check if the current user has specific department permission.
 * Now returns true for all authorized administrators.
 */
function canAccess(string $department): bool
{
    // If the user's role is anything other than 'customer', they are an admin with full permissions.
    // This removes the 'super_admin' vs 'staff' distinction as requested.
    return isAdmin();
}

/**
 * Guard function for admin pages.
 * Ensures the user has permission to view the current department.
 */
function guardPermission(string $department): void
{
    if (!canAccess($department)) {
        setFlash('error', 'You do not have permission to access the ' . ucfirst($department) . ' department.');
        redirect(url('admin/index.php'));
    }
}

/**
 * Get available roles for user management.
 * Simplified as requested: All administrators now hold equal, full-access permissions.
 */
function getAdminRoles(): array
{
    return [
        'admin' => 'Administrator (Full Access)'
    ];
}
