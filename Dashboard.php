<?php
// This MUST be the first thing in the file — before any HTML/whitespace —
// because it may issue a header() redirect.
require_once 'Auth_check.php';
require_once 'Require_role.php';
require_once 'db/db_connect.php';

$is_admin = can(['admin']);
$is_exec  = can(['executive']);

// Pull a few summary numbers for admins/executives. Wrapped in try/catch
// so a missing table (e.g. before you've run the schema SQL) doesn't
// break the whole dashboard — it just shows 0 instead.
$total_users = $active_dues = $total_dues = $total_payments = 0;
try {
    $total_users    = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $total_dues     = (int) $pdo->query('SELECT COUNT(*) FROM dues')->fetchColumn();
    $active_dues    = (int) $pdo->query('SELECT COUNT(*) FROM dues WHERE is_active = 1')->fetchColumn();
    $total_payments = (int) $pdo->query('SELECT COUNT(*) FROM payments')->fetchColumn();
} catch (PDOException $e) {
    // Tables not created yet — stats just show 0, no need to break the page.
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CS Department, KsTU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar">
        <a href="home.php" class="brand">
            <img src="image/logo.jpg" alt="CS Department Logo">
            CS Department - KsTU
        </a>
        <div class="nav-links">
            <?php if ($is_admin): ?>
                <a href="Manage_dues.php">Manage Dues</a>
                <a href="manage_users.php">Manage Users</a>
            <?php endif; ?>
            <span style="color:var(--white);">Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                <span class="badge"><?= htmlspecialchars($_SESSION['role'] ?? 'user') ?></span>
            </span>
            <a href="logout.php" class="btn btn-danger">Log Out</a>
        </div>
    </nav>

    <div class="page-wrapper">

        <?php if ($is_admin): ?>

            <h3 class="section-label">Overview</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <p class="stat-label">Total Users</p>
                    <p class="stat-value"><?= $total_users ?></p>
                </div>
                <div class="stat-card accent">
                    <p class="stat-label">Active Dues</p>
                    <p class="stat-value"><?= $active_dues ?></p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Total Dues Created</p>
                    <p class="stat-value"><?= $total_dues ?></p>
                </div>
                <div class="stat-card accent">
                    <p class="stat-label">Total Payments</p>
                    <p class="stat-value"><?= $total_payments ?></p>
                </div>
            </div>

            <h3 class="section-label">Quick Actions</h3>
            <div class="action-grid">
                <a href="manage_dues.php" class="action-card">
                    <div class="action-icon">&#128179;</div>
                    <h3>Manage Dues</h3>
                    <p>Create, edit amounts, and activate/deactivate dues.</p>
                </a>
                <a href="manage_users.php" class="action-card">
                    <div class="action-icon">&#128101;</div>
                    <h3>Manage Users</h3>
                    <p>Search users and change their role — user, executive, or admin.</p>
                </a>
                <a href="#" class="action-card" style="opacity:0.6; cursor:not-allowed;" onclick="return false;">
                    <div class="action-icon">&#128203;</div>
                    <h3>Payments (coming soon)</h3>
                    <p>View and filter all student payment records.</p>
                </a>
            </div>

        <?php elseif ($is_exec): ?>

            <h3 class="section-label">Quick Actions</h3>
            <div class="action-grid">
                <a href="#" class="action-card" style="opacity:0.6; cursor:not-allowed;" onclick="return false;">
                    <div class="action-icon">&#128179;</div>
                    <h3>Dues &amp; Payments (coming soon)</h3>
                    <p>View dues and manage payments. Amounts are admin-only.</p>
                </a>
            </div>

        <?php else: ?>

            <div class="card">
                <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>
                <p>Your dues and payment history will show up here once that page is ready.</p>
            </div>

        <?php endif; ?>

    </div>
</body>
</html>