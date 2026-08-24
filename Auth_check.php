<?php
/**
 * auth_check.php
 * Include this at the very top of any page that should be protected, e.g.:
 *
 *   <?php require_once 'auth_check.php'; ?>
 *
 * It must run before ANY HTML or output, because it may redirect.
 */

// session_start() must run before anything is echoed to the browser.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Not logged in? Bounce to the login page and remember where they were headed.
if (empty($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

// Optional: simple inactivity timeout (30 minutes). Remove this block if you don't want it.
$timeout_seconds = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout_seconds)) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();