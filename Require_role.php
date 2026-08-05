<?php

function require_role(array $allowed_roles) {
    $role = $_SESSION['role'] ?? null;

    if (!in_array($role, $allowed_roles, true)) {
        http_response_code(403);
        die('You do not have permission to access this page.');
    }
}

/**
 * Convenience helper for use inside a page's HTML/logic, e.g.:
 *   <?php if (can(['admin'])): ?> <button>Edit Amount</button> <?php endif; ?>
 */
function can(array $allowed_roles): bool {
    return in_array($_SESSION['role'] ?? null, $allowed_roles, true);
}