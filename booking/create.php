<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_user();

// Find the room the customer wants to book
$room_id = (int)($_GET['room_id'] ?? $_POST['room_id'] ?? 0);

$stmt = $pdo->prepare("SELECT r.*, h.hotel_name
                       FROM room r
                       JOIN hotel h ON h.hotel_id = r.hotel_id
                       WHERE r.room_id = ? AND r.status = 'available'");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    flash('error', 'That room is not available.');
    redirect('rooms.php');
}

$error = '';
$check_in = $_POST['check_in_date'] ?? ($_GET['check_in'] ?? '');
$check_out = $_POST['check_out_date'] ?? ($_GET['check_out'] ?? '');
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!valid_date($check_in) || !valid_date($check_out) || $check_in < date('Y-m-d') || $check_out <= $check_in) {
        $error = 'Choose valid dates. Check-in cannot be in the past and check-out must be later.';
    } else {
        try {
            $pdo->beginTransaction();

            // Lock the room row so two people cannot book it at the same moment
            $stmt = $pdo->prepare('SELECT room_id, status FROM room WHERE room_id = ? FOR UPDATE');
            $stmt->execute([$room_id]);
            $locked_room = $stmt->fetch();

            // Count bookings that overlap with the chosen dates
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM booking
                                   WHERE room_id = ?
                                   AND booking_status NOT IN ('cancelled', 'checked_out')
                                   AND check_in_date < ? AND check_out_date > ?");
            $stmt->execute([$room_id, $check_out, $check_in]);
            $overlaps = $stmt->fetchColumn();

            if (!$locked_room || $locked_room['status'] !== 'available' || $overlaps > 0) {
                $pdo->rollBack();
                $error = 'This room has just become unavailable for those dates. Please choose another date.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO booking (user_id, room_id, booking_type, check_in_date, check_out_date, booking_status)
                                       VALUES (?, ?, 'online', ?, ?, 'pending')");
                $stmt->execute([$_SESSION['user_id'], $room_id, $check_in, $check_out]);
                $booking_id = $pdo->lastInsertId();
                $pdo->commit();

                flash('success', 'Booking request #' . $booking_id . ' submitted. You can track it in My Bookings.');
                redirect('my_bookings.php');
            }
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Booking could not be saved. Please try again.';
            error_log($ex->getMessage());
        }
    }
}

$pageTitle = 'Book ' . $room['room_type'];
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <h1>Book <?= e($room['room_type']) ?></h1>
        <p><?= e($room['hotel_name']) ?> · Room <?= e($room['room_number']) ?> · <?= money($room['price']) ?> per night</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="card card-body col-md-7">
            <?= csrf_field() ?>
            <input type="hidden" name="room_id" value="<?= $room_id ?>">

            <label class="form-label">Check-in</label>
            <input class="form-control mb-3" type="date" name="check_in_date" min="<?= date('Y-m-d') ?>" value="<?= e($check_in) ?>" required>

            <label class="form-label">Check-out</label>
            <input class="form-control mb-3" type="date" name="check_out_date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= e($check_out) ?>" required>

            <button class="btn btn-brand">Submit booking request</button>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
