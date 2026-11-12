<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$error = '';
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['admin_name'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    // Get the saved password hash
    $stmt = $pdo->prepare('SELECT password FROM admin WHERE admin_id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    $saved_hash = $stmt->fetchColumn();

    if ($name === '') {
        $error = 'Administrator name is required.';
    } elseif ($new_password !== '' && (!password_verify($current_password, $saved_hash) || strlen($new_password) < 10)) {
        $error = 'To change password, enter your current password and a new password of at least 10 characters.';
    } else {
        if ($new_password !== '') {
            // Change name and password
            $stmt = $pdo->prepare('UPDATE admin SET admin_name = ?, password = ? WHERE admin_id = ?');
            $stmt->execute([$name, password_hash($new_password, PASSWORD_DEFAULT), $_SESSION['admin_id']]);
        } else {
            // Change name only
            $stmt = $pdo->prepare('UPDATE admin SET admin_name = ? WHERE admin_id = ?');
            $stmt->execute([$name, $_SESSION['admin_id']]);
        }

        $_SESSION['admin_name'] = $name;
        flash('success', 'Settings updated.');
        redirect('settings.php');
    }
}

admin_header('Settings', 'settings');
?>
<div class="page-head"><h2>Account settings</h2></div>

<?php if ($error): ?>
    <div class="notice notice-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="panel" style="max-width:650px">
    <form method="post">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="form-group full">
                <label>Administrator name</label>
                <input name="admin_name" value="<?= e($_SESSION['admin_name']) ?>" required>
            </div>
            <div class="form-group full">
                <label>Current password (required only to change password)</label>
                <input type="password" name="current_password">
            </div>
            <div class="form-group full">
                <label>New password (10+ characters)</label>
                <input type="password" name="new_password" minlength="10">
            </div>
        </div>
        <div class="form-actions">
            <button class="btn">Save settings</button>
        </div>
    </form>
</div>

<?php admin_footer(); ?>
