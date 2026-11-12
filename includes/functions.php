<?php
// Shared helper functions used across the whole project.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Make text safe before printing it in HTML.
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Send the visitor to another page.
function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

// ---------- CSRF protection (stops fake form submissions) ----------

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    $sent = $_POST['_csrf'] ?? '';
    $saved = $_SESSION['_csrf'] ?? '';
    if (!hash_equals($saved, $sent)) {
        http_response_code(419);
        exit('Your form expired. Go back and try again.');
    }
}

// ---------- Flash messages (one-time notices after a redirect) ----------

function flash(string $type, string $message): void
{
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function show_flash(): void
{
    if (empty($_SESSION['_flash'])) {
        return;
    }
    $f = $_SESSION['_flash'];
    unset($_SESSION['_flash']);
    echo '<div class="notice notice-' . e($f['type']) . '">' . e($f['message']) . '</div>';
}

// ---------- Login checks ----------

function require_user(): void
{
    if (empty($_SESSION['user_id'])) {
        redirect('/luxstay_hotel_management/user/login.php');
    }
}

function require_admin(): void
{
    if (empty($_SESSION['admin_id'])) {
        redirect('/luxstay_hotel_management/admin/login.php');
    }
}

// ---------- Small helpers ----------

// Format a number as taka, e.g. ৳2,500.00
function money($amount): string
{
    return '৳' . number_format((float)$amount, 2);
}

// Check that a string is a real date in YYYY-MM-DD format.
function valid_date(string $date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// ---------- Admin page layout ----------

function admin_header(string $title, string $active = 'dashboard'): void
{
    // Sections contain menu items keyed by their active-page identifier.
    $menu = [
        'Overview' => [
            'dashboard' => ['Dashboard', 'dashboard.php', 'M3 3h8v8H3z M13 3h8v5h-8z M13 10h8v11h-8z M3 13h8v8H3z'],
        ],
        'Property' => [
            'hotels' => ['Hotels', 'hotels.php', 'M3 21h18 M5 21V7l8-4v18 M19 21V11l-6-4 M9 9v.01 M9 12v.01 M9 15v.01 M9 18v.01'],
            'rooms' => ['Rooms', 'rooms.php', 'M3 10V6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v4 M3 18v-3a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v3 M3 18v2 M21 18v2 M9 13V6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v7'],
        ],
        'Operations' => [
            'bookings' => ['Bookings', 'bookings.php', 'M8 2v4 M16 2v4 M3 10h18 M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2z M8 14h3'],
            'guests' => ['Guests', 'guests.php', 'M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2 M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8 M20 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75'],
        ],
        'Finance' => [
            'payments' => ['Payments', 'payments.php', 'M2 5h20v14H2z M2 10h20 M6 15h3'],
        ],
        'Guest experience' => [
            'feedback' => ['Feedback', 'feedback.php', 'M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z'],
            'messages' => ['Messages', 'messages.php', 'M4 4h16v16H4z M22 6l-10 7L2 6'],
        ],
        'Insights' => [
            'analytics' => ['Analytics', 'analytics.php', 'M3 3v18h18 M18 17V9 M13 17V5 M8 17v-3'],
        ],
        'Preferences' => [
            'settings' => ['Settings', 'settings.php', 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8 M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-1.7 2.94-.08-.02a1.7 1.7 0 0 0-1.72.6l-.05.07h-3.4l-.05-.07a1.7 1.7 0 0 0-1.72-.6l-.08.02-1.7-2.94.06-.06A1.7 1.7 0 0 0 9.7 15l-.09-.02v-3.4l.09-.02a1.7 1.7 0 0 0-.34-1.88l-.06-.06 1.7-2.94.08.02a1.7 1.7 0 0 0 1.72-.6l.05-.07h3.4l.05.07a1.7 1.7 0 0 0 1.72.6l.08-.02 1.7 2.94-.06.06A1.7 1.7 0 0 0 19.4 12l.09.02v3.4z'],
        ],
    ];
    $admin_name = $_SESSION['admin_name'] ?? 'Administrator';
    $first_letter = strtoupper(substr($admin_name, 0, 1));
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($title) ?> | LuxStay Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin-sidebar.css">
    <script defer src="../assets/js/admin.js"></script>
</head>
<body>
<div class="admin-shell">
    <aside class="sidebar">
        <a class="admin-brand" href="dashboard.php" aria-label="LuxStay Admin dashboard">
            <span class="brand-mark">L</span>
            <span class="brand-copy"><b>LuxStay</b><small>HOTEL ADMIN</small></span>
        </a>
        <nav class="side-nav" aria-label="Primary navigation">
            <?php foreach ($menu as $section => $items): ?>
                <div class="nav-section">
                    <h2><?= e($section) ?></h2>
                    <?php foreach ($items as $key => $item): ?>
                        <a class="<?= $active === $key ? 'active' : '' ?>"
                           href="<?= e($item[1]) ?>"
                           <?= $active === $key ? 'aria-current="page"' : '' ?>>
                            <span class="nav-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="<?= e($item[2]) ?>"></path>
                                </svg>
                            </span>
                            <span><?= e($item[0]) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-user">
            <div class="avatar"><?= e($first_letter) ?></div>
            <div class="sidebar-user-copy"><b><?= e($admin_name) ?></b><small>Administrator</small></div>
            <a href="logout.php" title="Sign out" aria-label="Sign out">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M10 17l5-5-5-5 M15 12H3 M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"></path>
                </svg>
            </a>
        </div>
    </aside>
    <main class="admin-main">
        <header class="topbar">
            <button class="mobile-menu" data-menu-toggle aria-label="Toggle menu">☰</button>
            <div class="top-title"><?= e($title) ?></div>
            <form class="top-search" action="bookings.php" method="get">
                <span>⌕</span><input name="q" placeholder="Search bookings, guests...">
            </form>
            <a class="top-icon" href="messages.php" aria-label="Messages">▤</a>
            <a class="top-icon" href="settings.php" aria-label="Settings">♧</a>
        </header>
        <div class="admin-content">
    <?php
    show_flash();
}

function admin_footer(): void
{
    echo '</div></main></div></body></html>';
}
