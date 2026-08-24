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
    $index_number  = trim($_POST['index_number'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $password      = $_POST['password'] ?? '';
    $confirm       = $_POST['confirm_password'] ?? '';
    $certification = $_POST['certification'] ?? '';

    $valid_certifications = ['degree', 'hnd', 'diploma'];

    if ($username === '' || $index_number === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (!ctype_digit($index_number) || strlen($index_number) !== 12) {
        $error = 'Index number must be exactly 12 digits.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($certification, $valid_certifications, true)) {
        $error = 'Please select your certification type.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :u OR email = :e OR index_number = :n');
        $stmt->execute(['u' => $username, 'e' => $email, 'n' => $index_number]);

        if ($stmt->fetch()) {
            $error = 'That username, email, or index number is already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $insert = $pdo->prepare(
                'INSERT INTO users (username, index_number, email, password, certification) VALUES (:u, :n, :e, :p, :c)'
            );
            $insert->execute(['u' => $username, 'n' => $index_number, 'e' => $email, 'p' => $hashed, 'c' => $certification]);

            header('Location: login.php?registered=1');
            exit;
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

        <form method="POST" action="register.php" id="registerForm">
            <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>

            <input type="text" name="index_number" id="index_number" placeholder="Index Number (12 digits)"
                   value="<?= htmlspecialchars($_POST['index_number'] ?? '') ?>"
                   maxlength="12" inputmode="numeric" pattern="\d{12}" required>
            <p class="field-hint" id="indexHint"></p>

            <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

            <select name="certification" required>
                <option value="" disabled <?= empty($_POST['certification']) ? 'selected' : '' ?>>Select Certification</option>
                <option value="degree"  <?= ($_POST['certification'] ?? '') === 'degree'  ? 'selected' : '' ?>>Degree (4 years)</option>
                <option value="hnd"     <?= ($_POST['certification'] ?? '') === 'hnd'     ? 'selected' : '' ?>>HND (3 years)</option>
                <option value="diploma" <?= ($_POST['certification'] ?? '') === 'diploma' ? 'selected' : '' ?>>Diploma (2 years)</option>
            </select>

            <div class="password-wrapper">
                <input type="password" name="password" id="password" placeholder="Password (min 8 characters)" required>
                <span class="toggle-password" onclick="togglePassword('password', this)">&#128065;</span>
            </div>
            <div class="strength-meter"><div class="strength-fill" id="strengthFill"></div></div>

            <div class="password-wrapper">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                <span class="toggle-password" onclick="togglePassword('confirm_password', this)">&#128065;</span>
            </div>
            <p class="field-hint" id="matchHint"></p>

            <button type="submit" id="registerBtn">Register</button>
        </form>

        <p class="note">Already have an account? <a href="login.php">Log in</a></p>
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

    const indexNumber = document.getElementById('index_number');
    const indexHint = document.getElementById('indexHint');
    const password = document.getElementById('password');
    const confirm = document.getElementById('confirm_password');
    const strengthFill = document.getElementById('strengthFill');
    const matchHint = document.getElementById('matchHint');
    const form = document.getElementById('registerForm');
    const submitBtn = document.getElementById('registerBtn');

    indexNumber.addEventListener('input', () => {
        indexNumber.value = indexNumber.value.replace(/\D/g, '');

        const len = indexNumber.value.length;
        if (len === 0) {
            indexHint.textContent = '';
            indexHint.className = 'field-hint';
        } else if (len < 12) {
            indexHint.textContent = `${len}/12 digits`;
            indexHint.className = 'field-hint';
        } else {
            indexHint.textContent = 'Valid index number';
            indexHint.className = 'field-hint match';
        }
    });

    function checkStrength(value) {
        let score = 0;
        if (value.length >= 8) score++;
        if (/[A-Z]/.test(value)) score++;
        if (/[0-9]/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;

        const colors = ['#b71c1c', '#e65100', '#f9a825', '#2e7d32'];
        const widths = ['25%', '50%', '75%', '100%'];

        if (value.length === 0) {
            strengthFill.style.width = '0%';
            return;
        }
        const idx = Math.max(score - 1, 0);
        strengthFill.style.width = widths[idx];
        strengthFill.style.background = colors[idx];
    }

    function checkMatch() {
        if (confirm.value === '') {
            matchHint.textContent = '';
            matchHint.className = 'field-hint';
            return;
        }
        if (password.value === confirm.value) {
            matchHint.textContent = 'Passwords match';
            matchHint.className = 'field-hint match';
        } else {
            matchHint.textContent = 'Passwords do not match';
            matchHint.className = 'field-hint mismatch';
        }
    }

    password.addEventListener('input', () => {
        checkStrength(password.value);
        checkMatch();
    });
    confirm.addEventListener('input', checkMatch);

    form.addEventListener('submit', (e) => {
        if (indexNumber.value.length !== 12) {
            e.preventDefault();
            indexHint.textContent = 'Index number must be exactly 12 digits';
            indexHint.className = 'field-hint mismatch';
            return;
        }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span>Creating account...';
    });
    </script>
</body>
</html>