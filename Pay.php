<?php
require_once 'Auth_check.php';
require_once 'db/db_connect.php';

$due_id = $_GET['due_id'] ?? null;

if (!$due_id) {
    header('Location: My_dues.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM dues WHERE id = :id AND is_active = 1');
$stmt->execute(['id' => $due_id]);
$due = $stmt->fetch();

if (!$due) {
    header('Location: My_dues.php?error=due_not_found');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Pay Dues</title>
<link rel="stylesheet" href="css/style.css">

</head>
<body>

<div class="pay-card">
    <h2><?= htmlspecialchars($due['Level'] ?? 'Dues Payment') ?></h2>
    <p>You're about to pay for:</p>
    <div class="pay-amount">GHS <?= number_format($due['amount'], 2) ?></div>
    <button class="pay-btn" id="payBtn" data-due-id="<?= (int) $due_id ?>">Pay Now</button>
    <div class="pay-error" id="payError"></div>
</div>
<script src="javascript/pay.js"></script>
</body>
</html>