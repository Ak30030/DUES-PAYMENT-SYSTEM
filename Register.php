<?php
session_start();
require_once 'db/db_connect.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check for existing username/email before inserting.
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :u OR email = :e');
        $stmt->execute(['u' => $username, 'e' => $email]);

        if ($stmt->fetch()) {
            $error = 'That username or email is already registered.';
        } else {
            // password_hash uses bcrypt by default — never store plain text passwords.
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $insert = $pdo->prepare(
                'INSERT INTO users (username, email, password) VALUES (:u, :e, :p)'
            );
            $insert->execute(['u' => $username, 'e' => $email, 'p' => $hashed]);

            $success = 'Account created! You can now log in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Student Management System</title>
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
            <input type="password" name="password" placeholder="Password (min 8 characters)" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>

        <p class="note">Already have an account? <a href="login.php">Log in</a></p>
    </div>
    </div>
</body>
</html>