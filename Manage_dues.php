<?php
require_once 'Auth_check.php';
require_once 'Require_role.php';
require_once 'db/db_connect.php';

require_role(['admin']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id            = $_POST['id'] ?? '';
    $title         = trim($_POST['title'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $amount        = $_POST['amount'] ?? '';
    $level         = $_POST['level'] ?? '';
    $academic_year = trim($_POST['academic_year'] ?? '');
    $due_date      = $_POST['due_date'] ?? '';

    $valid_levels = ['100', '200', '300', '400'];

    if ($title === '' || $amount === '' || !is_numeric($amount) || $amount <= 0) {
        $error = 'Title and a valid amount are required.';
    } elseif (!in_array($level, $valid_levels, true)) {
        $error = 'Please select a valid level.';
    } else {
        $academic_year = $academic_year !== '' ? $academic_year : null;
        $due_date      = $due_date !== '' ? $due_date : null;

        if ($id === '') {
            $stmt = $pdo->prepare(
                'INSERT INTO dues (title, description, amount, level, academic_year, due_date)
                 VALUES (:title, :description, :amount, :level, :academic_year, :due_date)'
            );
            $stmt->execute([
                'title' => $title, 'description' => $description, 'amount' => $amount,
                'level' => $level, 'academic_year' => $academic_year, 'due_date' => $due_date,
            ]);
            $success = 'Due created.';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE dues SET title = :title, description = :description, amount = :amount,
                 level = :level, academic_year = :academic_year, due_date = :due_date
                 WHERE id = :id'
            );
            $stmt->execute([
                'title' => $title, 'description' => $description, 'amount' => $amount,
                'level' => $level, 'academic_year' => $academic_year, 'due_date' => $due_date,
                'id' => $id,
            ]);
            $success = 'Due updated.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    $stmt = $pdo->prepare('UPDATE dues SET is_active = NOT is_active WHERE id = :id');
    $stmt->execute(['id' => $_POST['id']]);
}

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM dues WHERE id = :id');
    $stmt->execute(['id' => $_GET['edit']]);
    $editing = $stmt->fetch();
}

$dues = $pdo->query('SELECT * FROM dues ORDER BY level ASC, created_at DESC')->fetchAll();
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
            <a href="Dashboard.php">Dashboard</a>
            <a href="logout.php" class="btn btn-danger">Log Out</a>
        </div>
    </nav>

    <div class="page-wrapper">

        <div class="card" style="margin-bottom:1.5rem;">
            <h2><?= $editing ? 'Edit Due' : 'Create a New Due' ?></h2>
            <p style="color:var(--text-muted); font-size:0.9rem;">
                Each due is tied to a level. Degree students see Level 100-400, HND sees 100-300, Diploma sees 100-200 — based on what they picked at registration.
            </p>

            <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
            <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

            <form method="POST" action="Manage_dues.php" id="dueForm">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= htmlspecialchars($editing['id'] ?? '') ?>">

                <input type="text" name="title" placeholder="Title (e.g. First Semester Dues 2026/2027)"
                       value="<?= htmlspecialchars($editing['title'] ?? '') ?>" required>

                <textarea name="description" placeholder="Description (optional)" rows="2"><?= htmlspecialchars($editing['description'] ?? '') ?></textarea>

                <input type="number" step="0.01" min="0.01" name="amount" placeholder="Amount (GHS)"
                       value="<?= htmlspecialchars($editing['amount'] ?? '') ?>" required>

                <select name="level" required>
                    <option value="" disabled <?= empty($editing['level']) ? 'selected' : '' ?>>Select Level</option>
                    <option value="100" <?= ($editing['level'] ?? '') === '100' ? 'selected' : '' ?>>Level 100</option>
                    <option value="200" <?= ($editing['level'] ?? '') === '200' ? 'selected' : '' ?>>Level 200</option>
                    <option value="300" <?= ($editing['level'] ?? '') === '300' ? 'selected' : '' ?>>Level 300</option>
                    <option value="400" <?= ($editing['level'] ?? '') === '400' ? 'selected' : '' ?>>Level 400</option>
                </select>

                <input type="text" name="academic_year" placeholder="Academic Year (e.g. 2026/2027)"
                       value="<?= htmlspecialchars($editing['academic_year'] ?? '') ?>">

                <label style="display:block; margin-bottom:0.3rem; color:var(--text-muted); font-size:0.9rem;">Due date</label>
                <input type="date" name="due_date" value="<?= htmlspecialchars($editing['due_date'] ?? '') ?>">

                <button type="submit" id="dueSubmitBtn"><?= $editing ? 'Save Changes' : 'Create Due' ?></button>
            </form>
            <?php if ($editing): ?>
                <p class="note"><a href="Manage_dues.php">Cancel editing / create a new due instead</a></p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>All Dues</h2>

            <input type="text" id="dueSearch" class="table-search" placeholder="Search dues by title or level...">

            <table id="duesTable">
                <thead>
                    <tr>
                        <th class="sortable" data-type="text">Title</th>
                        <th class="sortable" data-type="number">Amount (GHS)</th>
                        <th class="sortable" data-type="text">Level</th>
                        <th class="sortable" data-type="text">Due Date</th>
                        <th class="sortable" data-type="text">Status</th>
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
                            <td><span class="badge">Level <?= htmlspecialchars($due['level']) ?></span></td>
                            <td><?= htmlspecialchars($due['due_date'] ?? '—') ?></td>
                            <td>
                                <span class="badge" style="<?= $due['is_active'] ? '' : 'background:#fee2e2;color:#991b1b;' ?>">
                                    <?= $due['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <a href="Manage_dues.php?edit=<?= $due['id'] ?>">Edit</a>
                                &nbsp;|&nbsp;
                                <form method="POST" action="Manage_dues.php" style="display:inline;"
                                      class="toggle-form"
                                      data-confirm-msg="<?= $due['is_active']
                                          ? 'Deactivate "' . htmlspecialchars($due['title'], ENT_QUOTES) . '"? Students will no longer be able to pay this due.'
                                          : 'Activate "' . htmlspecialchars($due['title'], ENT_QUOTES) . '"? Students will be able to pay this due again.' ?>">
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
            <p class="note" id="noResultsMsg" style="display:none;">No dues match your search.</p>
        </div>

    </div>

<script src="javascript/manage-dues.js"></script>
</body>
</html>