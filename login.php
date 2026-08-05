<?php
session_start();
require_once 'db/db_connect.php';

// If already logged in, don't show the login form again.
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? ''); // username or email
    $password   = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $error = 'Please enter both your username/email and password.';
    } else {
        // Look up by username OR email
        $stmt = $pdo->prepare(
            'SELECT id, username, password, role FROM users WHERE username = :id1 OR email = :id2 LIMIT 1'
        );
        $stmt->execute(['id1' => $identifier, 'id2' => $identifier]);
        $user = $stmt->fetch();

        // password_verify checks the plain text password against the bcrypt hash.
        // Always give the same generic error whether the user or password was wrong —
        // don't tell an attacker which one was incorrect.
        if ($user && password_verify($password, $user['password'])) {

            // Regenerate the session ID on login to prevent session fixation attacks.
            session_regenerate_id(true);

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['last_activity'] = time();

            // Send them back to whatever page they were trying to reach, if any.
            $redirect = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
            unset($_SESSION['redirect_after_login']);

            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid username/email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - dues payment system</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-wrapper">
    <div class="auth-box">
        <div style="text-align:center; margin-bottom:1rem;">
            <img src="image/logo.jpg" alt="CS Department Logo" style="height:70px; width:70px; border-radius:50%; object-fit:cover;">
        </div>
        <h2>Login</h2>

        <?php if (!empty($_GET['timeout'])): ?>
            <p class="error">Your session expired. Please log in again.</p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="text" name="identifier" placeholder="Username or Email" required autofocus>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Log In</button>
        </form>

        <p class="note">Don't have an account? <a href="register.php">Register</a></p>
    </div>
    </div>
</body>
</html>