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
<style>
.pay-card {
    max-width: 420px;
    margin: 60px auto;
    padding: 32px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    text-align: center;
}
.pay-card h2 {
    color: #001f54;
    margin-bottom: 8px;
}
.pay-amount {
    font-size: 2rem;
    font-weight: 700;
    color: #b71c1c;
    margin: 16px 0;
}
.pay-btn {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 8px;
    background: #001f54;
    color: #fff;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.1s ease;
}
.pay-btn:hover:not(:disabled) {
    background: #b71c1c;
}
.pay-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #fff;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    vertical-align: middle;
    margin-right: 8px;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
.pay-error {
    margin-top: 16px;
    padding: 10px;
    border-radius: 6px;
    background: #fdecea;
    color: #b71c1c;
    font-size: 0.9rem;
    display: none;
}
</style>
</head>
<body>

<div class="pay-card">
    <h2><?= htmlspecialchars($due['Level'] ?? 'Dues Payment') ?></h2>
    <p>You're about to pay for:</p>
    <div class="pay-amount">GHS <?= number_format($due['amount'], 2) ?></div>
    <button class="pay-btn" id="payBtn" data-due-id="<?= (int) $due_id ?>">Pay Now</button>
    <div class="pay-error" id="payError"></div>
</div>

<script>
const payBtn = document.getElementById('payBtn');
const payError = document.getElementById('payError');

payBtn.addEventListener('click', async () => {
    payError.style.display = 'none';
    payBtn.disabled = true;
    payBtn.innerHTML = '<span class="spinner"></span>Processing...';

    try {
        const res = await fetch(`initialize_payment.php?due_id=${payBtn.dataset.dueId}`);
        const data = await res.json();

        if (data.status && data.authorization_url) {
            window.location.href = data.authorization_url;
        } else {
            throw new Error(data.message || 'Something went wrong.');
        }
    } catch (err) {
        payError.textContent = err.message;
        payError.style.display = 'block';
        payBtn.disabled = false;
        payBtn.innerHTML = 'Pay Now';
    }
});
</script>

</body>
</html>