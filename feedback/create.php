<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_user();

$error = '';
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);

    if ($message === '' || $rating < 1 || $rating > 5) {
        $error = 'Write feedback and choose a rating from 1 to 5.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO feedback (user_id, message, rating) VALUES (?, ?, ?)');
        $stmt->execute([$_SESSION['user_id'], $message, $rating]);

        flash('success', 'Thank you for your feedback.');
        redirect('../user/dashboard.php');
    }
}

$pageTitle = 'Feedback';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <h1>Share your feedback</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="card card-body col-md-7">
            <?= csrf_field() ?>

            <label class="form-label">Your message</label>
            <textarea name="message" class="form-control mb-3" rows="5" required></textarea>

            <label class="form-label">Rating</label>
            <select name="rating" class="form-select mb-3" required>
                <option value="">Choose rating</option>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?= $i ?>"><?= $i ?> / 5</option>
                <?php endfor; ?>
            </select>

            <button class="btn btn-brand">Submit feedback</button>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
