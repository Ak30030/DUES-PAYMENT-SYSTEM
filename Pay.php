<?php
require_once 'Auth_check.php';
require_once 'db/db_connect.php';
require_once 'Paystack_config.php';

$due_id = $_GET['due_id'] ?? null;
$student_id = $_SESSION['user_id'];

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

$stmt = $pdo->prepare(
    "SELECT id FROM payments WHERE student_id = :sid AND due_id = :did AND status = 'success'"
);
$stmt->execute(['sid' => $student_id, 'did' => $due_id]);
if ($stmt->fetch()) {
    header('Location: My_dues.php?error=already_paid');
    exit;
}

$stmt = $pdo->prepare('SELECT email, username FROM users WHERE id = :id');
$stmt->execute(['id' => $student_id]);
$student = $stmt->fetch();

$reference = 'DUES_' . $due_id . '_' . $student_id . '_' . time();
$amount_kobo = (int) round($due['amount'] * 100);

$insert = $pdo->prepare(
    'INSERT INTO payments (student_id, due_id, amount, paystack_reference, status)
     VALUES (:sid, :did, :amount, :ref, "pending")'
);
$insert->execute([
    'sid'    => $student_id,
    'did'    => $due_id,
    'amount' => $due['amount'],
    'ref'    => $reference,
]);

$callback_url = 'http://localhost/DUES_PAYMENT_SYSTEM/Verify_payment.php';

$fields = [
    'email'        => $student['email'],
    'amount'       => $amount_kobo,
    'reference'    => $reference,
    'callback_url' => $callback_url,
    'metadata'     => [
        'due_id'     => $due_id,
        'student_id' => $student_id,
    ],
];

$ch = curl_init('https://api.paystack.co/transaction/initialize');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
    'Content-Type: application/json',
]);
$response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    die('Connection to Paystack failed: ' . htmlspecialchars($curl_error));
}

$result = json_decode($response, true);

if (!empty($result['status']) && !empty($result['data']['authorization_url'])) {
    header('Location: ' . $result['data']['authorization_url']);
    exit;
} else {
    $message = $result['message'] ?? 'Unknown error starting payment.';
    die('Could not start payment: ' . htmlspecialchars($message));
}