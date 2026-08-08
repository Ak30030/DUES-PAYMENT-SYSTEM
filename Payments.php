<?php
require_once 'Auth_check.php';
require_once 'Require_role.php';
require_once 'db/db_connect.php';

require_role(['admin', 'executive']);

$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$where = [];
$params = [];

if (in_array($status_filter, ['pending', 'success', 'failed'], true)) {
    $where[] = 'p.status = :status';
    $params['status'] = $status_filter;
}

if ($search !== '') {
    $where[] = '(u.username LIKE :search1 OR p.paystack_reference LIKE :search2 OR d.level LIKE :search3)';
    $params['search1'] = '%' . $search . '%';
    $params['search2'] = '%' . $search . '%';
    $params['search3'] = '%' . $search . '%';
}

$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $pdo->prepare(
    "SELECT p.*, d.title AS due_title, d.level, u.username
     FROM payments p
     JOIN dues d ON d.id = p.due_id
     JOIN users u ON u.id = p.student_id
     $where_sql
     ORDER BY p.created_at DESC"
);
$stmt->execute($params);
$payments = $stmt->fetchAll();

$total_success = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'success'")->fetchColumn();
$count_success = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'success'")->fetchColumn();
$count_pending = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'")->fetchColumn();
$count_failed  = $pdo->query("SELECT COUNT(*) FROM payments WHERE status = 'failed'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - CS Department, KsTU</title>
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
            <?php if (can(['admin'])): ?>
                <a href="Manage_dues.php">Manage Dues</a>
                <a href="Manage_users.php">Manage Users</a>
            <?php endif; ?>
            <a href="Logout.php" class="btn btn-danger">Log Out</a>
        </div>
    </nav>

    <div class="page-wrapper">

        <div class="stats-grid">
            <div class="stat-card">
                <p class="stat-label">Total Collected (GHS)</p>
                <p class="stat-value"><?= number_format($total_success, 2) ?></p>
            </div>
            <div class="stat-card accent">
                <p class="stat-label">Successful Payments</p>
                <p class="stat-value"><?= $count_success ?></p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Pending</p>
                <p class="stat-value"><?= $count_pending ?></p>
            </div>
            <div class="stat-card accent">
                <p class="stat-label">Failed</p>
                <p class="stat-value"><?= $count_failed ?></p>
            </div>
        </div>

        <div class="card">
            <h2>All Payments</h2>

            <form method="GET" action="Payments.php" style="display:flex; gap:0.6rem; flex-wrap:wrap; align-items:flex-end; margin-bottom:1rem;">
                <div style="flex:1; min-width:200px;">
                    <input type="text" name="search" placeholder="Search by name, reference, or level"
                           value="<?= htmlspecialchars($search) ?>" style="margin-bottom:0;">
                </div>
                <div>
                    <select name="status" style="margin-bottom:0; width:auto;">
                        <option value="">All Statuses</option>
                        <option value="success" <?= $status_filter === 'success' ? 'selected' : '' ?>>Success</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="failed"  <?= $status_filter === 'failed'  ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>
                <button type="submit" style="width:auto; padding:0.6rem 1.2rem;">Filter</button>
                <?php if ($search !== '' || $status_filter !== ''): ?>
                    <a href="Payments.php" class="btn" style="width:auto; padding:0.6rem 1.2rem; background:var(--text-muted);">Reset</a>
                <?php endif; ?>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Due</th>
                        <th>Level</th>
                        <th>Amount (GHS)</th>
                        <th>Reference</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($payments)): ?>
                    <tr><td colspan="8">No payments found.</td></tr>
                <?php else: ?>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['username']) ?></td>
                            <td><?= htmlspecialchars($p['due_title']) ?></td>
                            <td><span class="badge">Level <?= htmlspecialchars($p['level']) ?></span></td>
                            <td><?= number_format($p['amount'], 2) ?></td>
                            <td style="font-size:0.85rem;"><?= htmlspecialchars($p['paystack_reference']) ?></td>
                            <td><?= htmlspecialchars($p['channel'] ? ucfirst($p['channel']) : '—') ?></td>
                            <td>
                                <?php
                                $badge_style = [
                                    'success' => 'background:#dcfce7;color:#166534;',
                                    'pending' => 'background:#fef9c3;color:#854d0e;',
                                    'failed'  => 'background:#fee2e2;color:#991b1b;',
                                ][$p['status']];
                                ?>
                                <span class="badge" style="<?= $badge_style ?>"><?= ucfirst($p['status']) ?></span>
                            </td>
                            <td><?= htmlspecialchars(date('d M Y, g:i A', strtotime($p['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>