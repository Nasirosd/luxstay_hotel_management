<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Paid revenue for each month (last 12 months)
$monthly = $pdo->query("SELECT DATE_FORMAT(COALESCE(payment_date, created_at), '%Y-%m') AS month_key,
                               SUM(amount) AS revenue,
                               COUNT(*) AS payments
                        FROM payment
                        WHERE payment_status = 'paid'
                        GROUP BY month_key
                        ORDER BY month_key DESC
                        LIMIT 12")->fetchAll();

// How many payments are pending, paid, failed...
$status = $pdo->query('SELECT payment_status, COUNT(*) AS total FROM payment GROUP BY payment_status')->fetchAll();

// Hotels with the most bookings
$top = $pdo->query("SELECT h.hotel_name, COUNT(b.booking_id) AS bookings
                    FROM hotel h
                    LEFT JOIN room r ON r.hotel_id = h.hotel_id
                    LEFT JOIN booking b ON b.room_id = r.room_id
                    GROUP BY h.hotel_id
                    ORDER BY bookings DESC
                    LIMIT 5")->fetchAll();

admin_header('Analytics', 'analytics');
?>
<div class="welcome">
    <h2>Business analytics</h2>
    <p>Live summaries calculated from your booking and payment records.</p>
</div>

<div class="sub-panels">
    <div class="panel">
        <div class="panel-head"><h3>Monthly paid revenue</h3></div>
        <table class="data-table">
            <thead>
                <tr><th>Month</th><th>Paid revenue</th><th>Payments</th></tr>
            </thead>
            <tbody>
                <?php foreach ($monthly as $m): ?>
                    <tr>
                        <td><?= e($m['month_key']) ?></td>
                        <td><?= money($m['revenue']) ?></td>
                        <td><?= e($m['payments']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$monthly): ?>
                    <tr><td colspan="3">No paid payments yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="panel">
        <div class="panel-head"><h3>Payment status</h3></div>
        <?php foreach ($status as $s): ?>
            <div class="activity">
                <b><?= e(ucfirst($s['payment_status'])) ?></b>
                <span style="margin-left:auto"><?= e($s['total']) ?></span>
            </div>
        <?php endforeach; ?>
        <?php if (!$status): ?>
            <div class="empty">No payment records.</div>
        <?php endif; ?>
    </div>
</div>

<div class="panel" style="margin-top:16px">
    <div class="panel-head"><h3>Bookings by hotel</h3></div>
    <table class="data-table">
        <thead>
            <tr><th>Hotel</th><th>Bookings</th></tr>
        </thead>
        <tbody>
            <?php foreach ($top as $t): ?>
                <tr>
                    <td><?= e($t['hotel_name']) ?></td>
                    <td><?= e($t['bookings']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php admin_footer(); ?>
