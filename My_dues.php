<?php
require_once 'Auth_check.php';
require_once 'db/db_connect.php';

$student_id = $_SESSION['user_id'];

$max_level_by_certification = [
    'degree'  => '400',
    'hnd'     => '300',
    'diploma' => '200',
];

$stmt = $pdo->prepare('SELECT certification FROM users WHERE id = :id');
$stmt->execute(['id' => $student_id]);
$certification = $stmt->fetchColumn() ?: 'degree';
$max_level = $max_level_by_certification[$certification] ?? '400';

$stmt = $pdo->prepare(
    'SELECT d.*,
        p.status AS payment_status,
        p.paid_at
     FROM dues d
     LEFT JOIN payments p
        ON p.due_id = d.id
       AND p.student_id = :sid
       AND p.status = "success"
     WHERE d.is_active = 1
       AND d.level <= :max_level
     ORDER BY d.level ASC, d.due_date IS NULL, d.due_date ASC'
);
$stmt->execute(['sid' => $student_id, 'max_level' => $max_level]);
$dues = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dues - CS Department, KsTU</title>
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
            <a href="Logout.php" class="btn btn-danger">Log Out</a>
            <a href="Payment_history.php">Payment History</a>
        </div>
    </nav>

    <div class="page-wrapper">

        <div class="card">
            <h2>My Dues</h2>
            <p style="color:var(--text-muted);">
                <?= ucfirst($certification) ?> student — showing dues for Level 100 through <?= $max_level ?>.
                Pay online and get your receipt instantly.
            </p>

            <?php if (isset($_GET['paid'])): ?>
                <p class="success">Payment successful! Your due is now marked as paid.</p>
            <?php elseif (isset($_GET['error'])): ?>
                <?php
                $messages = [
                    'already_paid'     => 'You have already paid this due.',
                    'payment_failed'   => 'Payment was not completed. Please try again.',
                    'due_not_found'    => 'That due could not be found or is no longer active.',
                    'missing_reference'=> 'Payment reference missing — please try again.',
                    'receipt_not_found'=> 'No successful payment found for that due yet.',
                ];
                $msg = $messages[$_GET['error']] ?? 'Something went wrong. Please try again.';
                ?>
                <p class="error"><?= htmlspecialchars($msg) ?></p>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Level</th>
                        <th>Amount (GHS)</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($dues)): ?>
                    <tr><td colspan="6">No active dues right now.</td></tr>
                <?php else: ?>
                    <?php foreach ($dues as $due): ?>
                        <?php $is_paid = $due['payment_status'] === 'success'; ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($due['title']) ?>
                                <?php if (!empty($due['description'])): ?>
                                    <br><span style="color:var(--text-muted); font-size:0.85rem;"><?= htmlspecialchars($due['description']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge">Level <?= htmlspecialchars($due['level']) ?></span></td>
                            <td><?= number_format($due['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($due['due_date'] ?? '—') ?></td>
                            <td>
                                <?php if ($is_paid): ?>
                                    <span class="badge" style="background:#dcfce7;color:#166534;">Paid</span>
                                <?php else: ?>
                                    <span class="badge" style="background:#fee2e2;color:#991b1b;">Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($is_paid): ?>
                                    <a href="Receipt.php?due_id=<?= $due['id'] ?>">View Receipt</a>
                                <?php else: ?>
                                    <a href="Pay.php?due_id=<?= $due['id'] ?>" class="btn" style="width:auto; padding:0.4rem 1rem;">Pay Now</a>
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