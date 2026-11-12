<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_user();
verify_csrf();

// Cancel a booking (only pending bookings of this customer)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = (int)($_POST['booking_id'] ?? 0);

    $stmt = $pdo->prepare("UPDATE booking SET booking_status = 'cancelled'
                           WHERE booking_id = ? AND user_id = ? AND booking_status = 'pending'");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);

    if ($stmt->rowCount()) {
        flash('success', 'Booking cancelled.');
    } else {
        flash('error', 'Only pending bookings can be cancelled.');
    }
    redirect('my_bookings.php');
}

// This customer's bookings, with the total price and payment status
$stmt = $pdo->prepare('SELECT b.*, r.room_number, r.room_type, r.price, h.hotel_name, p.payment_status,
                              DATEDIFF(b.check_out_date, b.check_in_date) * r.price AS total_amount
                       FROM booking b
                       JOIN room r ON r.room_id = b.room_id
                       JOIN hotel h ON h.hotel_id = r.hotel_id
                       LEFT JOIN payment p ON p.booking_id = b.booking_id
                       WHERE b.user_id = ?
                       ORDER BY b.created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$rows = $stmt->fetchAll();

$pageTitle = 'My Bookings';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>My Bookings</h1>
            <a class="btn btn-brand" href="rooms.php">Book a room</a>
        </div>
        <?php show_flash(); ?>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Booking</th><th>Hotel / Room</th><th>Dates</th><th>Total</th>
                        <th>Status</th><th>Payment</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $b): ?>
                        <tr>
                            <td>#<?= $b['booking_id'] ?></td>
                            <td><?= e($b['hotel_name'] . ' · ' . $b['room_number'] . ' ' . $b['room_type']) ?></td>
                            <td><?= e($b['check_in_date']) ?><br><?= e($b['check_out_date']) ?></td>
                            <td><?= money($b['total_amount']) ?></td>
                            <td><?= e(ucwords(str_replace('_', ' ', $b['booking_status']))) ?></td>
                            <td><?= e($b['payment_status'] ?? 'Not requested') ?></td>
                            <td>
                                <?php if ($b['booking_status'] === 'pending'): ?>
                                    <form method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
                                        <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Cancel this booking?')">Cancel</button>
                                    </form>
                                <?php elseif ($b['booking_status'] === 'confirmed' && empty($b['payment_status'])): ?>
                                    <a class="btn btn-sm btn-brand" href="../payment/payment.php?booking_id=<?= $b['booking_id'] ?>">Pay</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$rows): ?>
                        <tr><td colspan="7" class="text-center py-4">You have no bookings yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
