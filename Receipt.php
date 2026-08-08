<?php
require_once 'Auth_check.php';
require_once 'db/db_connect.php';

$due_id = $_GET['due_id'] ?? null;
$student_id = $_SESSION['user_id'];

$stmt = $pdo->prepare(
    'SELECT p.*, d.title, d.description, d.level, d.academic_year, u.username, u.email
     FROM payments p
     JOIN dues d ON d.id = p.due_id
     JOIN users u ON u.id = p.student_id
     WHERE p.due_id = :did AND p.student_id = :sid AND p.status = "success"
     ORDER BY p.paid_at DESC
     LIMIT 1'
);
$stmt->execute(['did' => $due_id, 'sid' => $student_id]);
$payment = $stmt->fetch();

if (!$payment) {
    header('Location: my_dues.php?error=receipt_not_found');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - CS Department, KsTU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar no-print">
        <a href="home.php" class="brand">
            <img src="image/logo.jpg" alt="CS Department Logo">
            CS Department - KsTU
        </a>
        <div class="nav-links">
            <a href="My_dues.php">My Dues</a>
            <a href="Logout.php" class="btn btn-danger">Log Out</a>
        </div>
    </nav>

    <div class="receipt-box">
        <img src="image/logo.jpg" alt="" class="receipt-watermark">

        <div class="receipt-header">
            <img src="image/logo.jpg" alt="CS Department Logo">
            <h2>Payment Receipt</h2>
            <p style="color:var(--text-muted); margin:0;">Computer Science Department, KsTU</p>
        </div>

        <div class="receipt-status">
            <span class="badge" style="background:#dcfce7;color:#166534;">Payment Successful</span>
        </div>

        <div class="receipt-amount">GHS <?= number_format($payment['amount'], 2) ?></div>

        <div class="receipt-row">
            <span>Due</span>
            <span><?= htmlspecialchars($payment['title']) ?></span>
        </div>
        <div class="receipt-row">
            <span>Level</span>
            <span>Level <?= htmlspecialchars($payment['level']) ?></span>
        </div>
        <?php if (!empty($payment['academic_year'])): ?>
        <div class="receipt-row">
            <span>Academic Year</span>
            <span><?= htmlspecialchars($payment['academic_year']) ?></span>
        </div>
        <?php endif; ?>
        <div class="receipt-row">
            <span>Paid By</span>
            <span><?= htmlspecialchars($payment['username']) ?></span>
        </div>
        <div class="receipt-row">
            <span>Email</span>
            <span><?= htmlspecialchars($payment['email']) ?></span>
        </div>
        <div class="receipt-row">
            <span>Reference</span>
            <span><?= htmlspecialchars($payment['paystack_reference']) ?></span>
        </div>
        <?php if (!empty($payment['channel'])): ?>
        <div class="receipt-row">
            <span>Payment Method</span>
            <span><?= htmlspecialchars(ucfirst($payment['channel'])) ?></span>
        </div>
        <?php endif; ?>
        <div class="receipt-row">
            <span>Date Paid</span>
            <span><?= htmlspecialchars(date('d M Y, g:i A', strtotime($payment['paid_at']))) ?></span>
        </div>

        <div class="receipt-signature">
            <img src="image/signature.png" alt="Authorized Signature">
            <p>Authorized Signature — CS Department</p>
        </div>

        <div class="no-print" style="text-align:center; margin-top:1.5rem;">
            <button onclick="window.print()">Print / Save as PDF</button>
        </div>
    </div>

</body>
</html>