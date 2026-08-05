<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dues Payment System - CS Department, KsTU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar">
        <a href="home.php" class="brand">
            <img src="image/logo.jpg" alt="CS Department Logo">
            Computer Science Department - KsTU
        </a>
        <div class="nav-links">
            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php" class="btn btn-danger">Log Out</a>
            <?php else: ?>
                <a href="login.php" class="btn">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="ribbon">EVOLUTION IN TECHNOLOGY</div>

    <section class="hero">
        <img src="image/logo.jpg" alt="CS Department Logo" class="logo">
        <h1>Dues Payment System</h1>
        <p>Computer Science Department, Kumasi Technical University &mdash;
           pay your dues, track your status, and get your receipt instantly.</p>

        <?php if (empty($_SESSION['user_id'])): ?>
            <a href="login.php" class="btn">Login</a>
            <a href="register.php" class="btn btn-outline">Register</a>
        <?php else: ?>
            <a href="dashboard.php" class="btn">Go to Dashboard</a>
        <?php endif; ?>
    </section>

    <section class="highlights">
        <div class="highlight-card">
            <div class="highlight-icon">&#128179;</div>
            <h3>Pay Your Dues</h3>
            <p>Secure online payment via Paystack &mdash; card or mobile money.</p>
        </div>
        <div class="highlight-card">
            <div class="highlight-icon">&#129517;</div>
            <h3>Instant Receipts</h3>
            <p>Get an auto-generated receipt the moment your payment clears.</p>
        </div>
        <div class="highlight-card">
            <div class="highlight-icon">&#128274;</div>
            <h3>Role-Based Access</h3>
            <p>Admins, executives, and students each see exactly what they need.</p>
        </div>
    </section>

</body>
</html>