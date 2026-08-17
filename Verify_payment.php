<?php
require_once 'Auth_check.php';
require_once 'db/db_connect.php';
require_once 'paystack_config.php';
require_once 'Send_receipt_email.php';

$reference = $_GET['reference'] ?? '';

if (!$reference) {
    header('Location: My_dues.php?error=missing_reference');
    exit;
}

$ch = curl_init('https://api.paystack.co/transaction/verify/' . rawurlencode($reference));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
]);
$response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    die('Connection to Paystack failed while verifying: ' . htmlspecialchars($curl_error));
}

$result = json_decode($response, true);
$data = $result['data'] ?? null;

$stmt = $pdo->prepare('SELECT * FROM payments WHERE paystack_reference = :ref');
$stmt->execute(['ref' => $reference]);
$payment = $stmt->fetch();

if (!$payment) {
    die('Payment record not found for this reference.');
}

if ($data && $data['status'] === 'success') {
    $update = $pdo->prepare(
        'UPDATE payments
         SET status = "success", paystack_transaction_id = :txn, channel = :channel, paid_at = NOW()
         WHERE id = :id'
    );
    $update->execute([
        'txn'     => $data['id'],
        'channel' => $data['channel'] ?? null,
        'id'      => $payment['id'],
    ]);

    $info_stmt = $pdo->prepare(
        'SELECT u.username, u.email, d.title, d.level
         FROM users u, dues d
         WHERE u.id = :sid AND d.id = :did'
    );
    $info_stmt->execute(['sid' => $payment['student_id'], 'did' => $payment['due_id']]);
    $info = $info_stmt->fetch();

    if ($info) {
        send_receipt_email(
            $info['email'],
            $info['username'],
            $info['title'],
            $info['level'],
            $payment['amount'],
            $reference,
            date('Y-m-d H:i:s')
        );
    }

    header('Location: My_dues.php?paid=1');
    exit;
} else {
    $update = $pdo->prepare('UPDATE payments SET status = "failed" WHERE id = :id');
    $update->execute(['id' => $payment['id']]);

    header('Location: My_dues.php?error=payment_failed');
    exit;
}