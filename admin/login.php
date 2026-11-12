<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Already logged in? Go straight to the dashboard.
if (!empty($_SESSION['admin_id'])) {
    redirect('dashboard.php');
}

$error = '';
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    // Find the admin by email address
    $stmt = $pdo->prepare('SELECT admin_id, admin_name, password FROM admin WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    // Check the password against the saved hash
    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['admin_name'];
        redirect('dashboard.php');
    }

    $error = 'Invalid administrator email or password.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Login | LuxStay</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="login-page">
    <form class="login-card" method="post">
        <?php echo csrf_field(); ?>
        <a class="admin-brand" href="../index.php"><span class="brand-mark">L</span>LuxStay</a>
        <h1>Administrator Login</h1>
        <p>Sign in to manage your hotel operations.</p>

        <?php if ($error): ?>
            <div class="notice notice-error"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="form-group">
            <label>Email address</label>
            <input type="email" name="email" required autocomplete="username">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required autocomplete="current-password">
        </div>
        <button class="btn">Sign in</button>
    </form>
</body>
</html>
