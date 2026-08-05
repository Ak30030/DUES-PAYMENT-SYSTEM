<?php

function require_role(array $allowed_roles) {
    $role = $_SESSION['role'] ?? null;

    if (!in_array($role, $allowed_roles, true)) {
        http_response_code(403);
        die('You do not have permission to access this page.');
    }
}

function can(array $allowed_roles): bool {
    return in_array($_SESSION['role'] ?? null, $allowed_roles, true);
}