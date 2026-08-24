<?php
require_once 'Auth_check.php';
require_once 'Require_role.php';
require_once 'db/db_connect.php';

require_role(['admin']);

$academic_years = $pdo->query('SELECT DISTINCT academic_year FROM dues WHERE academic_year IS NOT NULL ORDER BY academic_year DESC')->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - CS Department, KsTU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar">
        <a href="home.php" class="brand">
            <img src="image/logo.jpg" alt="CS Department Logo">
            CS Department - KsTU
        </a>
        <div class="nav-links">
            <a href="Dashboard.php">Dashboard</a>
            <a href="Manage_dues.php">Manage Dues</a>
            <a href="Manage_users.php">Manage Users</a>
            <a href="Payments.php">Payments</a>
            <a href="Logout.php" class="btn btn-danger">Log Out</a>
        </div>
    </nav>

    <div class="page-wrapper">

        <div class="card">
            <h2>Export Report</h2>
            <p style="color:var(--text-muted);">Generate a PDF report of student payments. Leave filters blank to include everything.</p>

            <form method="GET" action="export_report.php" target="_blank">
                <label style="display:block; margin-bottom:0.3rem; color:var(--text-muted); font-size:0.9rem;">Academic Year</label>
                <select name="academic_year">
                    <option value="">All Academic Years</option>
                    <?php foreach ($academic_years as $ay): ?>
                        <option value="<?= htmlspecialchars($ay) ?>"><?= htmlspecialchars($ay) ?></option>
                    <?php endforeach; ?>
                </select>

                <label style="display:block; margin-bottom:0.3rem; color:var(--text-muted); font-size:0.9rem;">Level</label>
                <select name="level">
                    <option value="">All Levels</option>
                    <option value="100">Level 100</option>
                    <option value="200">Level 200</option>
                    <option value="300">Level 300</option>
                    <option value="400">Level 400</option>
                </select>

                <label style="display:block; margin-bottom:0.3rem; color:var(--text-muted); font-size:0.9rem;">Payment Status</label>
                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="success">Success</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Failed</option>
                </select>

                <button type="submit">Download PDF Report</button>
            </form>
        </div>

    </div>
</body>
</html>