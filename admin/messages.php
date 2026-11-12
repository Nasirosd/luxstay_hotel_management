<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// All guest feedback, newest first
$rows = $pdo->query('SELECT f.feedback_id, u.user_name, u.user_email, f.rating, f.message, f.created_at
                     FROM feedback f
                     JOIN users u ON u.user_id = f.user_id
                     ORDER BY f.created_at DESC')->fetchAll();

admin_header('Messages', 'messages');
?>
<div class="page-head">
    <h2>Guest messages &amp; feedback</h2>
    <a class="btn secondary" href="feedback.php">Manage feedback</a>
</div>

<div class="list-panel">
    <table class="data-table">
        <thead>
            <tr><th>Guest</th><th>Email</th><th>Rating</th><th>Message</th><th>Received</th></tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['user_name']) ?></td>
                    <td><?= e($r['user_email']) ?></td>
                    <td><?= e($r['rating']) ?> / 5</td>
                    <td><?= e($r['message']) ?></td>
                    <td><?= e($r['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?>
                <tr><td colspan="5"><div class="empty">No guest messages yet.</div></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php admin_footer(); ?>
