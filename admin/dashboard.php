<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// ---------- Numbers for the top cards ----------
$new_bookings = $pdo->query("SELECT COUNT(*) FROM booking WHERE created_at >= CURDATE()")->fetchColumn();
$available_rooms = $pdo->query("SELECT COUNT(*) FROM room WHERE status = 'available'")->fetchColumn();
$checked_in = $pdo->query("SELECT COUNT(*) FROM booking WHERE booking_status = 'checked_in'")->fetchColumn();
$checked_out = $pdo->query("SELECT COUNT(*) FROM booking WHERE booking_status = 'checked_out'")->fetchColumn();

// ---------- Revenue and booking totals ----------
$revenue = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payment WHERE payment_status = 'paid'")->fetchColumn();
$total = $pdo->query('SELECT COUNT(*) FROM booking')->fetchColumn();
$online = $pdo->query("SELECT COUNT(*) FROM booking WHERE booking_type = 'online'")->fetchColumn();
$offline = max(0, $total - $online);

// ---------- Latest 5 bookings ----------
$activities = $pdo->query("SELECT b.booking_id, b.created_at, b.booking_status,
                                  u.user_name, r.room_number, r.room_type
                           FROM booking b
                           JOIN users u ON u.user_id = b.user_id
                           JOIN room r ON r.room_id = b.room_id
                           ORDER BY b.created_at DESC
                           LIMIT 5")->fetchAll();

// ---------- Paid revenue for the last 7 days (for the bar chart) ----------
$week = $pdo->query("SELECT DATE_FORMAT(COALESCE(p.payment_date, p.created_at), '%a') AS day_label,
                            SUM(p.amount) AS total
                     FROM payment p
                     WHERE p.payment_status = 'paid'
                       AND COALESCE(p.payment_date, p.created_at) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                     GROUP BY DATE(COALESCE(p.payment_date, p.created_at)),
                              DATE_FORMAT(COALESCE(p.payment_date, p.created_at), '%a')
                     ORDER BY DATE(COALESCE(p.payment_date, p.created_at))")->fetchAll();

// Highest day, used to scale the bars
$max = 1;
foreach ($week as $day) {
    if ((float)$day['total'] > $max) {
        $max = (float)$day['total'];
    }
}

// Percentage of online bookings for the progress bar
$online_percent = $total ? round($online / $total * 100) : 0;

// Cards: [label, value, icon, colour class]
$cards = [
    ['New Booking', $new_bookings, '▣', ''],
    ['Available Room', $available_rooms, '▤', 'yellow'],
    ['Check In', $checked_in, '↳', 'green'],
    ['Check Out', $checked_out, '↗', 'coral'],
];

$total_guests = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$total_hotels = $pdo->query('SELECT COUNT(*) FROM hotel')->fetchColumn();

admin_header('Dashboard', 'dashboard');
?>
<div class="welcome">
    <h2>Hi, <?= e($_SESSION['admin_name'] ?? 'Admin') ?>!</h2>
    <p>Here is what is happening with your hotel business today.</p>
</div>

<div class="dashboard-grid">
    <section>
        <!-- Top cards -->
        <div class="stats-grid">
            <?php foreach ($cards as $card): ?>
                <article class="stat-card">
                    <div class="stat-top">
                        <span class="stat-icon <?= $card[3] ?>"><?= $card[2] ?></span>
                        <svg class="spark" viewBox="0 0 70 27">
                            <polyline points="0,20 12,17 23,21 34,10 45,14 56,5 70,2" fill="none" stroke="#22c982" stroke-width="1.5"/>
                        </svg>
                    </div>
                    <div class="stat-value"><?= number_format((int)$card[1]) ?></div>
                    <div class="stat-label"><?= e($card[0]) ?></div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Recent activities -->
        <div class="panel activities">
            <div class="panel-head">
                <h3>Recent activities</h3>
                <a class="btn secondary small" href="bookings.php">View all</a>
            </div>
            <?php if (!$activities): ?>
                <div class="empty">No recent bookings yet.</div>
            <?php else: ?>
                <?php foreach ($activities as $a): ?>
                    <div class="activity">
                        <div class="avatar"><?= e(strtoupper(substr($a['user_name'], 0, 1))) ?></div>
                        <div>
                            <b><?= e($a['user_name']) ?></b>
                            <small>Room <?= e($a['room_number']) ?>, <?= e($a['room_type']) ?> · <?= e($a['booking_status']) ?></small>
                        </div>
                        <time><?= e(date('M j, H:i', strtotime($a['created_at']))) ?></time>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section>
        <!-- Revenue chart -->
        <div class="panel">
            <div class="panel-head">
                <h3>Revenue Stat</h3>
                <select class="select-mini" disabled><option>Last 7 days</option></select>
            </div>
            <div class="revenue-value"><?= money($revenue) ?><span class="growth">● Paid revenue</span></div>
            <div class="chart">
                <div class="chart-y">
                    <span>৳<?= number_format($max) ?></span>
                    <span>৳<?= number_format($max * .66) ?></span>
                    <span>৳<?= number_format($max * .33) ?></span>
                    <span>৳0</span>
                </div>
                <div class="bars">
                    <?php if ($week): ?>
                        <?php foreach ($week as $day): ?>
                            <div class="bar-col">
                                <div class="bar" style="height:<?= max(3, (float)$day['total'] / $max * 100) ?>%"></div>
                                <span><?= e($day['day_label']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach (['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'] as $name): ?>
                            <div class="bar-col">
                                <div class="bar" style="height:3%"></div>
                                <span><?= e($name) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Online / offline bookings -->
        <div class="panel" style="margin-top:16px">
            <div class="panel-head">
                <h3>Bookings</h3>
                <a class="btn secondary small" href="bookings.php">Manage</a>
            </div>
            <div class="revenue-value"><?= number_format((int)$total) ?> <span class="stat-label">Total bookings</span></div>
            <div class="booking-progress"><span style="width:<?= $online_percent ?>%"></span></div>
            <div class="booking-breakdown">
                <div><span class="dot"></span>Online Booking<b><?= number_format((int)$online) ?></b></div>
                <div><span class="dot coral"></span>Offline Booking<b><?= number_format((int)$offline) ?></b></div>
            </div>
        </div>
    </section>
</div>

<div class="sub-panels">
    <div class="panel">
        <div class="panel-head"><h3>Quick actions</h3></div>
        <div class="form-actions">
            <a class="btn" href="rooms.php?action=new">Add room</a>
            <a class="btn secondary" href="hotels.php?action=new">Add hotel</a>
            <a class="btn secondary" href="bookings.php">View bookings</a>
        </div>
    </div>
    <div class="panel">
        <div class="panel-head"><h3>System overview</h3></div>
        <div class="booking-breakdown">
            <div>Registered guests<b><?= $total_guests ?></b></div>
            <div>Hotels<b><?= $total_hotels ?></b></div>
        </div>
    </div>
</div>

<?php admin_footer(); ?>
