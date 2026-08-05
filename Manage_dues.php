<?php
require_once 'Auth_check.php';
require_once 'Require_role.php';
require_once 'db/db_connect.php';

require_role(['admin']); // executives are blocked from this whole page

$error = '';
$success = '';

// ── Handle create / update ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id            = $_POST['id'] ?? '';
    $title         = trim($_POST['title'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $amount        = $_POST['amount'] ?? '';
    $program       = trim($_POST['program'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '');
    $due_date      = $_POST['due_date'] ?? '';

    if ($title === '' || $amount === '' || !is_numeric($amount) || $amount <= 0) {
        $error = 'Title and a valid amount are required.';
    } else {
        $program       = $program !== '' ? $program : null;
        $academic_year = $academic_year !== '' ? $academic_year : null;
        $due_date      = $due_date !== '' ? $due_date : null;

        if ($id === '') {
            // Create new due
            $stmt = $pdo->prepare(
                'INSERT INTO dues (title, description, amount, program, academic_year, due_date)
                 VALUES (:title, :description, :amount, :program, :academic_year, :due_date)'
            );
            $stmt->execute([
                'title' => $title, 'description' => $description, 'amount' => $amount,
                'program' => $program, 'academic_year' => $academic_year, 'due_date' => $due_date,
            ]);
            $success = 'Due created.';
        } else {
            // Update existing due — only reachable here because require_role already
            // confirmed this user is admin, so amount edits are safe to allow.
            $stmt = $pdo->prepare(
                'UPDATE dues SET title = :title, description = :description, amount = :amount,
                 program = :program, academic_year = :academic_year, due_date = :due_date
                 WHERE id = :id'
            );
            $stmt->execute([
                'title' => $title, 'description' => $description, 'amount' => $amount,
                'program' => $program, 'academic_year' => $academic_year, 'due_date' => $due_date,
                'id' => $id,
            ]);
            $success = 'Due updated.';
        }
    }
}

// ── Handle activate / deactivate toggle ─────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    $stmt = $pdo->prepare('UPDATE dues SET is_active = NOT is_active WHERE id = :id');
    $stmt->execute(['id' => $_POST['id']]);
}

// ── If editing, load that due's current values ──────────────
$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM dues WHERE id = :id');
    $stmt->execute(['id' => $_GET['edit']]);
    $editing = $stmt->fetch();
}

// ── List all dues ────────────────────────────────────────────
$dues = $pdo->query('SELECT * FROM dues ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Dues - CS Department, KsTU</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar">
        <a href="home.php" class="brand">
            <img src="image/logo.jpg" alt="CS Department Logo">
            CS Department - KsTU
        </a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php" class="btn btn-danger">Log Out</a>
        </div>
    </nav>

    <div class="page-wrapper">

        <div class="card" style="margin-bottom:1.5rem;">
            <h2><?= $editing ? 'Edit Due' : 'Create a New Due' ?></h2>

            <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
            <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

            <form method="POST" action="manage_dues.php">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= htmlspecialchars($editing['id'] ?? '') ?>">

                <input type="text" name="title" placeholder="Title (e.g. First Semester Dues 2026/2027)"
                       value="<?= htmlspecialchars($editing['title'] ?? '') ?>" required>

                <textarea name="description" placeholder="Description (optional)" rows="2"><?= htmlspecialchars($editing['description'] ?? '') ?></textarea>

                <input type="number" step="0.01" min="0.01" name="amount" placeholder="Amount (GHS)"
                       value="<?= htmlspecialchars($editing['amount'] ?? '') ?>" required>

                <input type="text" name="program" placeholder="Program (optional, leave blank for all)"
                       value="<?= htmlspecialchars($editing['program'] ?? '') ?>">

                <input type="text" name="academic_year" placeholder="Academic Year (e.g. 2026/2027)"
                       value="<?= htmlspecialchars($editing['academic_year'] ?? '') ?>">

                <label style="display:block; margin-bottom:0.3rem; color:var(--text-muted); font-size:0.9rem;">Due date</label>
                <input type="date" name="due_date" value="<?= htmlspecialchars($editing['due_date'] ?? '') ?>">

                <button type="submit"><?= $editing ? 'Save Changes' : 'Create Due' ?></button>
            </form>
            <?php if ($editing): ?>
                <p class="note"><a href="manage_dues.php">Cancel editing / create a new due instead</a></p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>All Dues</h2>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Amount (GHS)</th>
                        <th>Program</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($dues)): ?>
                    <tr><td colspan="6">No dues created yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($dues as $due): ?>
                        <tr>
                            <td><?= htmlspecialchars($due['title']) ?></td>
                            <td><?= number_format($due['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($due['program'] ?? 'All') ?></td>
                            <td><?= htmlspecialchars($due['due_date'] ?? '—') ?></td>
                            <td>
                                <span class="badge" style="<?= $due['is_active'] ? '' : 'background:#fee2e2;color:#991b1b;' ?>">
                                    <?= $due['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <a href="manage_dues.php?edit=<?= $due['id'] ?>">Edit</a>
                                &nbsp;|&nbsp;
                                <form method="POST" action="manage_dues.php" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?= $due['id'] ?>">
                                    <button type="submit" style="width:auto; padding:0.1rem 0.5rem; background:none; color:var(--primary); text-decoration:underline; box-shadow:none;">
                                        <?= $due['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>