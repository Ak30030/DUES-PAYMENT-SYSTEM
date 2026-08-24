<?php
require_once 'Auth_check.php';
require_once 'db/db_connect.php';

$student_id = $_SESSION['user_id'];

$stmt = $pdo->prepare(
    'SELECT p.*, d.title AS due_title, d.level, d.academic_year
     FROM payments p
     JOIN dues d ON d.id = p.due_id
     WHERE p.student_id = :sid
     ORDER BY p.created_at DESC'
);
$stmt->execute(['sid' => $student_id]);
$payments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - CS Department, KsTU</title>
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
            <a href="My_dues.php">My Dues</a>
            <a href="Logout.php" class="btn btn-danger">Log Out</a>
        </div>
    </nav>

    <div class="page-wrapper">

        <div class="card">
            <h2>Payment History</h2>
            <p style="color:var(--text-muted);">Every payment you've made, across every semester and level.</p>
            <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Due</th>
                        <th>Level</th>
                        <th>Academic Year</th>
                        <th>Amount (GHS)</th>
                        <th>Reference</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    <div>
                <?php if (empty($payments)): ?>
                    <tr><td colspan="9">You haven't made any payments yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['due_title']) ?></td>
                            <td><span class="badge">Level <?= htmlspecialchars($p['level']) ?></span></td>
                            <td><?= htmlspecialchars($p['academic_year'] ?? '—') ?></td>
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
                            <td>
                                <?php if ($p['status'] === 'success'): ?>
                                    <a href="Receipt.php?due_id=<?= $p['due_id'] ?>">View</a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>