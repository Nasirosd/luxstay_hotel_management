<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Rooms & Suites';

// All rooms that can be booked, cheapest first
$rooms = $pdo->query("SELECT r.room_id, r.room_number, r.room_type, r.room_description, r.price, h.hotel_name
                      FROM room r
                      JOIN hotel h ON h.hotel_id = r.hotel_id
                      WHERE r.status = 'available'
                      ORDER BY r.price")->fetchAll();

// Dates picked on the home page (optional)
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';

// Room photos (repeated in turn)
$images = [
    'photo-1611892440504-42a792e24d32',
    'photo-1590490360182-c33d57733427',
    'photo-1616486338812-3dadae4b4ace',
];

require_once __DIR__ . '/../includes/header.php';
?>
<section class="page-banner">
    <div class="container">
        <span class="eyebrow light">FIND YOUR SPACE</span>
        <h1>Rooms &amp; Suites</h1>
        <p>Comfortable spaces for every kind of visit.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (!$rooms): ?>
            <div class="alert alert-info">No available rooms at the moment. Please check back soon.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($rooms as $i => $r): ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="room-card h-100">
                            <img src="https://images.unsplash.com/<?= $images[$i % 3] ?>?auto=format&fit=crop&w=900&q=85" alt="<?= e($r['room_type']) ?>">
                            <div>
                                <small><?= e($r['hotel_name'] . ' · ' . $r['room_number']) ?></small>
                                <h3><?= e($r['room_type']) ?></h3>
                                <p><?= e($r['room_description'] ?: 'Comfortable accommodation for your stay.') ?></p>
                                <strong class="price"><?= money($r['price']) ?><small> / night</small></strong>

                                <form method="get" action="create.php">
                                    <input type="hidden" name="room_id" value="<?= $r['room_id'] ?>">
                                    <input type="hidden" name="check_in" value="<?= e($check_in) ?>">
                                    <input type="hidden" name="check_out" value="<?= e($check_out) ?>">
                                    <button class="btn btn-brand w-100 mt-3">Book this room</button>
                                </form>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
