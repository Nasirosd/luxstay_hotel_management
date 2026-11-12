<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_user();

// Count this customer's bookings
$stmt = $pdo->prepare("SELECT COUNT(*) AS total,
                              SUM(booking_status IN ('pending', 'confirmed', 'checked_in')) AS active
                       FROM booking WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$counts = $stmt->fetch();

$pageTitle = 'My Account';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <h1>Hello, <?= e($_SESSION['user_name'] ?? 'Guest') ?>!</h1>
        <p>Manage your LuxStay account and reservations.</p>

        <div class="row g-3 my-3">
            <div class="col-md-4">
                <div class="card card-body">
                    <strong>Total bookings</strong>
                    <h2><?= $counts['total'] ?? 0 ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-body">
                    <strong>Active bookings</strong>
                    <h2><?= $counts['active'] ?? 0 ?></h2>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-brand" href="../booking/rooms.php">Book a room</a>
            <a class="btn btn-outline-brand" href="../booking/my_bookings.php">My bookings</a>
            <a class="btn btn-outline-brand" href="profile.php">Edit profile</a>
            <a class="btn btn-outline-brand" href="../feedback/create.php">Leave feedback</a>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
