<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!empty($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$error = '';
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    // Find the customer by email
    $stmt = $pdo->prepare('SELECT user_id, user_name, password FROM users WHERE user_email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['user_name'];
        redirect('dashboard.php');
    }

    $error = 'Email or password is incorrect.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Customer Login | LuxStay</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="login-page">
    <form class="login-card" method="post">
        <?php echo csrf_field(); ?>
        <a href="../index.php" class="admin-brand"><span class="brand-mark">L</span>LuxStay</a>
        <h1>Welcome back</h1>
        <p>Sign in to manage your stays and bookings.</p>

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
        <p>New to LuxStay? <a href="register.php">Create an account</a></p>
    </form>
</body>
</html>
