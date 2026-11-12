<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$baseUrl = '/luxstay_hotel_management';
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="LuxStay — welcoming spaces, thoughtful comfort and easy hotel booking.">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle).' | ' : '' ?>LuxStay Hotel</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= $baseUrl ?>/assets/css/style.css" rel="stylesheet"></head><body>
<header class="site-header sticky-top"><nav class="navbar navbar-expand-lg"><div class="container">
<a class="navbar-brand brand" href="<?= $baseUrl ?>/index.php"><span class="brand-icon">L</span>Lux<span>Stay</span></a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="mainNav"><ul class="navbar-nav mx-auto">
<li class="nav-item"><a class="nav-link <?= $currentPage==='index.php'?'active':'' ?>" href="<?= $baseUrl ?>/index.php">Home</a></li>
<li class="nav-item"><a class="nav-link <?= $currentPage==='rooms.php'?'active':'' ?>" href="<?= $baseUrl ?>/booking/rooms.php">Rooms &amp; Suites</a></li>
<li class="nav-item"><a class="nav-link <?= $currentPage==='gallery.php'?'active':'' ?>" href="<?= $baseUrl ?>/gallery.php">Gallery</a></li>
<li class="nav-item"><a class="nav-link <?= $currentPage==='contact.php'?'active':'' ?>" href="<?= $baseUrl ?>/contact.php">Contact</a></li></ul>
<div class="d-flex gap-2 py-2">
<?php if(!empty($_SESSION['user_id'])): ?><a class="btn btn-outline-brand" href="<?= $baseUrl ?>/user/dashboard.php">My Account</a><a class="btn btn-brand" href="<?= $baseUrl ?>/auth/logout.php">Logout</a>
<?php else: ?><a class="btn btn-outline-brand" href="<?= $baseUrl ?>/user/login.php">Login</a><a class="btn btn-brand" href="<?= $baseUrl ?>/user/register.php">Sign Up</a><?php endif; ?>
</div></div></div></nav></header>
