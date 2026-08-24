<?php
session_start();
require_once 'db/db_connect.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $error = 'Please enter your login details and password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password, role FROM users WHERE index_number = :n LIMIT 1');
        $stmt->execute(['n' => $identifier]);
        $user = $stmt->fetch();

        if (!$user) {
            $stmt = $pdo->prepare('SELECT id, username, password, role FROM users WHERE username = :u LIMIT 1');
            $stmt->execute(['u' => $identifier]);
            $matched_by_name = $stmt->fetch();

            if ($matched_by_name && $matched_by_name['role'] === 'student') {
                $error = 'Students must log in with their index number, not their name.';
            } elseif ($matched_by_name) {
                $user = $matched_by_name;
            }
        }

        if ($user && empty($error) && password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['last_activity'] = time();

            $redirect = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
            unset($_SESSION['redirect_after_login']);

            header('Location: ' . $redirect);
            exit;
        } elseif (empty($error)) {
            $error = 'Invalid login details or password.';
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

        <?php if (!empty($_GET['registered'])): ?>
            <p class="success">Account created successfully! Please log in.</p>
        <?php endif; ?>

        <?php if (!empty($_GET['timeout'])): ?>
            <p class="error">Your session expired. Please log in again.</p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="text" name="identifier" placeholder="Index Number (students) or Name (admin/executive)" required autofocus>

            <div class="password-wrapper">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <span class="toggle-password" onclick="togglePassword('password', this)">&#128065;</span>
            </div>

            <button type="submit">Log In</button>
        </form>

        <p class="note">Don't have an account? <a href="register.php">Register</a></p>
    </div>
    </div>

    <script>
    function togglePassword(id, el) {
        const field = document.getElementById(id);
        if (field.type === 'password') {
            field.type = 'text';
            el.style.opacity = '0.5';
        } else {
            field.type = 'password';
            el.style.opacity = '1';
        }
    }
    </script>
</body>
</html>