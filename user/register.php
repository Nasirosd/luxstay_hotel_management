<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Enter a name, valid email and password of at least 8 characters.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (user_name, user_email, user_address, password) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, $address, password_hash($password, PASSWORD_DEFAULT)]);

            flash('success', 'Account created. Please sign in.');
            redirect('login.php');
        } catch (PDOException $ex) {
            $error = 'This email may already be registered.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Create Account | LuxStay</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="login-page">
    <form class="login-card" method="post">
        <?php echo csrf_field(); ?>
        <a href="../index.php" class="admin-brand"><span class="brand-mark">L</span>LuxStay</a>
        <h1>Create your account</h1>

        <?php if ($error): ?>
            <div class="notice notice-error"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="form-group">
            <label>Full name</label>
            <input name="name" required maxlength="100">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Address</label>
            <input name="address" maxlength="255">
        </div>
        <div class="form-group">
            <label>Password (8+ characters)</label>
            <input type="password" name="password" minlength="8" required>
        </div>
        <button class="btn">Register</button>
        <p>Already registered? <a href="login.php">Sign in</a></p>
    </form>
</body>
</html>
