<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_user();

// Load the current profile
$stmt = $pdo->prepare('SELECT user_name, user_email, user_address FROM users WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$error = '';
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['user_name'] ?? '');
    $address = trim($_POST['user_address'] ?? '');

    if ($name === '') {
        $error = 'Name is required.';
    } else {
        $stmt = $pdo->prepare('UPDATE users SET user_name = ?, user_address = ? WHERE user_id = ?');
        $stmt->execute([$name, $address, $_SESSION['user_id']]);

        $_SESSION['user_name'] = $name;
        flash('success', 'Profile updated.');
        redirect('profile.php');
    }
}

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <h1>My Profile</h1>
        <?php show_flash(); ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="card card-body col-md-6">
            <?= csrf_field() ?>
            <label>Name</label>
            <input class="form-control mb-3" name="user_name" value="<?= e($user['user_name']) ?>" required>
            <label>Email</label>
            <input class="form-control mb-3" value="<?= e($user['user_email']) ?>" disabled>
            <label>Address</label>
            <input class="form-control mb-3" name="user_address" value="<?= e($user['user_address']) ?>">
            <button class="btn btn-brand">Save changes</button>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
