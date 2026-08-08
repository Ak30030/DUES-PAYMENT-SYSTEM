<?php
session_start();
require_once 'db/db_connect.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: Dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username      = trim($_POST['username'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $password      = $_POST['password'] ?? '';
    $confirm       = $_POST['confirm_password'] ?? '';
    $certification = $_POST['certification'] ?? '';

    $valid_certifications = ['degree', 'hnd', 'diploma'];

    if ($username === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($certification, $valid_certifications, true)) {
        $error = 'Please select your certification type.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :u OR email = :e');
        $stmt->execute(['u' => $username, 'e' => $email]);

        if ($stmt->fetch()) {
            $error = 'That username or email is already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $insert = $pdo->prepare(
                'INSERT INTO users (username, email, password, certification) VALUES (:u, :e, :p, :c)'
            );
            $insert->execute(['u' => $username, 'e' => $email, 'p' => $hashed, 'c' => $certification]);

            $success = 'Account created! You can now log in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - CS Department, KsTU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-wrapper">
    <div class="auth-box">
        <div style="text-align:center; margin-bottom:1rem;">
            <img src="image/logo.jpg" alt="CS Department Logo" style="height:70px; width:70px; border-radius:50%; object-fit:cover;">
        </div>
        <h2>Register</h2>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

            <select name="certification" required>
                <option value="" disabled <?= empty($_POST['certification']) ? 'selected' : '' ?>>Select Certification</option>
                <option value="degree"  <?= ($_POST['certification'] ?? '') === 'degree'  ? 'selected' : '' ?>>Degree (4 years)</option>
                <option value="hnd"     <?= ($_POST['certification'] ?? '') === 'hnd'     ? 'selected' : '' ?>>HND (3 years)</option>
                <option value="diploma" <?= ($_POST['certification'] ?? '') === 'diploma' ? 'selected' : '' ?>>Diploma (2 years)</option>
            </select>

            <input type="password" name="password" placeholder="Password (min 8 characters)" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>

        <p class="note">Already have an account? <a href="login.php">Log in</a></p>
    </div>
    </div>
</body>
</html>