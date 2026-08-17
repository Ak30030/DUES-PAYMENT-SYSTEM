<?php
require_once 'Auth_check.php';
require_once 'Require_role.php';
require_once 'db/db_connect.php';

require_role(['admin']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_role') {
    $target_id  = $_POST['user_id'] ?? '';
    $new_role   = $_POST['role'] ?? '';
    $valid_roles = ['admin', 'executive', 'student'];

    if (!in_array($new_role, $valid_roles, true)) {
        $error = 'Invalid role selected.';
    } elseif ((int) $target_id === (int) $_SESSION['user_id'] && $new_role !== 'admin') {
        $error = "You can't remove your own admin role while logged in as yourself.";
    } else {
        $stmt = $pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
        $stmt->execute(['role' => $new_role, 'id' => $target_id]);
        $success = 'Role updated.';
    }
}

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare(
        'SELECT id, username, email, role, certification, created_at FROM users
         WHERE username LIKE :s1 OR email LIKE :s2 ORDER BY created_at DESC'
    );
    $stmt->execute(['s1' => '%' . $search . '%', 's2' => '%' . $search . '%']);
} else {
    $stmt = $pdo->query('SELECT id, username, email, role, certification, created_at FROM users ORDER BY created_at DESC');
}
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - CS Department, KsTU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar">
        <a href="home.php" class="brand">
            <img src="image/logo.jpg" alt="CS Department Logo">
            CS Department - KsTU
        </a>
        <div class="nav-links">
            <a href="Dashboard.php">Dashboard</a>
            <a href="Manage_dues.php">Manage Dues</a>
            <a href="Logout.php" class="btn btn-danger">Log Out</a>
        </div>
    </nav>

    <div class="page-wrapper">

        <div class="card">
            <h2>Manage Users</h2>

            <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
            <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

            <form method="GET" action="Manage_users.php" style="max-width:320px;">
                <input type="text" name="search" placeholder="Search by username or email"
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
            </form>

            <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Certification</th>
                        <th>Current Role</th>
                        <th>Joined</th>
                        <th>Change Role</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="6">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['username']) ?>
                                <?php if ((int) $u['id'] === (int) $_SESSION['user_id']): ?>
                                    <span class="badge">You</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($u['certification'] ?? '—')) ?></td>
                            <td><span class="badge"><?= htmlspecialchars($u['role']) ?></span></td>
                            <td><?= htmlspecialchars($u['created_at']) ?></td>
                            <td>
                                <form method="POST" action="Manage_users.php" style="display:flex; gap:0.4rem; margin:0;">
                                    <input type="hidden" name="action" value="change_role">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <select name="role" style="margin:0; width:auto;">
                                        <option value="student"   <?= $u['role'] === 'student'   ? 'selected' : '' ?>>Student</option>
                                        <option value="executive" <?= $u['role'] === 'executive' ? 'selected' : '' ?>>Executive</option>
                                        <option value="admin"     <?= $u['role'] === 'admin'     ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                    <button type="submit" style="width:auto; padding:0.4rem 0.8rem;">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>

    </div>
</body>
</html>