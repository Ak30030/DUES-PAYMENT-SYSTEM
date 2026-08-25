<?php
require_once 'Auth_check.php';
require_once 'Require_role.php';
require_once 'db/db_connect.php';

$is_admin = can(['admin']);
$is_exec  = can(['executive']);


$total_users = $active_dues = $total_dues = $total_payments = 0;
try {
    $total_users    = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $total_dues     = (int) $pdo->query('SELECT COUNT(*) FROM dues')->fetchColumn();
    $active_dues    = (int) $pdo->query('SELECT COUNT(*) FROM dues WHERE is_active = 1')->fetchColumn();
    $total_payments = (int) $pdo->query('SELECT COUNT(*) FROM payments')->fetchColumn();
} catch (PDOException $e) {

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
                <a href="Manage_users.php">Manage Users</a>
                <a href="Payments.php">Payments</a>
                <a href="Reports.php">Reports</a>
            <?php elseif ($is_exec): ?>
                <a href="Payments.php">Payments</a>
            <?php else: ?>
                <a href="My_dues.php">My Dues</a>
                <a href="Payment_history.php">Payment History</a>
            <?php endif; ?>
            <span style="color:var(--white);">Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                <span class="badge"><?= htmlspecialchars($_SESSION['role'] ?? 'student') ?></span>
            </span>
            <a href="Logout.php" class="btn btn-danger">Log Out</a>
        </div>
    </nav>

    <div class="page-wrapper">

        <?php if ($is_admin): ?>

            <h3 class="section-label">Overview</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <p class="stat-label">Total Users</p>
                    <p class="stat-value" data-count="<?= $total_users ?>">0</p>
                </div>
                <div class="stat-card accent">
                    <p class="stat-label">Active Dues</p>
                    <p class="stat-value" data-count="<?= $active_dues ?>">0</p>
                </div>
                <div class="stat-card">
                    <p class="stat-label">Total Dues Created</p>
                    <p class="stat-value" data-count="<?= $total_dues ?>">0</p>
                </div>
                <div class="stat-card accent">
                    <p class="stat-label">Total Payments</p>
                    <p class="stat-value" data-count="<?= $total_payments ?>">0</p>
                </div>
            </div>

            <h3 class="section-label">Quick Actions</h3>
            <div class="action-grid">
                <a href="Manage_dues.php" class="action-card">
                    <div class="action-icon">&#128179;</div>
                    <h3>Manage Dues</h3>
                    <p>Create, edit amounts, and activate/deactivate dues.</p>
                </a>
                <a href="Manage_users.php" class="action-card">
                    <div class="action-icon">&#128101;</div>
                    <h3>Manage Users</h3>
                    <p>Search users and change their role — student, executive, or admin.</p>
                </a>
                <a href="Payments.php" class="action-card">
                    <div class="action-icon">&#128203;</div>
                    <h3>Payments</h3>
                    <p>View and filter all student payment records.</p>
                </a>
                <a href="Reports.php" class="action-card">
                    <div class="action-icon">&#128196;</div>
                    <h3>Reports</h3>
                    <p>Export a filtered PDF report of student payment data.</p>
                </a>
            </div>

        <?php elseif ($is_exec): ?>

            <h3 class="section-label">Quick Actions</h3>
            <div class="action-grid">
                <a href="Payments.php" class="action-card">
                    <div class="action-icon">&#128179;</div>
                    <h3>Payments</h3>
                    <p>View and filter all student payment records. Amounts are admin-only.</p>
                </a>
            </div>

        <?php else: ?>

            <div class="action-grid">
                <a href="My_dues.php" class="action-card">
                    <div class="action-icon">&#128179;</div>
                    <h3>My Dues</h3>
                    <p>See active dues and pay online.</p>
                </a>
                <a href="Payment_history.php" class="action-card">
                    <div class="action-icon">&#128203;</div>
                    <h3>Payment History</h3>
                    <p>View every payment you've made across all semesters.</p>
                </a>
            </div>

        <?php endif; ?>

    </div>

    <script src="javascript/dashboard.js"></script>
</body>
</html>