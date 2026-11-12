<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_user();

$error = '';
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = (int)($_POST['booking_id'] ?? 0);
    $method = $_POST['payment_method'] ?? 'cash';

    // Only allow known payment methods
    $allowed_methods = ['cash', 'bkash', 'nagad', 'bank_transfer', 'card', 'other'];
    if (!in_array($method, $allowed_methods, true)) {
        $method = 'cash';
    }

    // The booking must belong to this customer. Amount = nights x room price.
    $stmt = $pdo->prepare("SELECT b.booking_id, DATEDIFF(b.check_out_date, b.check_in_date) * r.price AS amount
                           FROM booking b
                           JOIN room r ON r.room_id = b.room_id
                           WHERE b.booking_id = ? AND b.user_id = ?
                           AND b.booking_status IN ('confirmed', 'checked_in', 'pending')");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        $error = 'Choose one of your valid bookings.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO payment (booking_id, amount, payment_method, payment_status)
                               VALUES (?, ?, ?, 'pending')
                               ON DUPLICATE KEY UPDATE amount = VALUES(amount), payment_method = VALUES(payment_method)");
        $stmt->execute([$booking_id, $booking['amount'], $method]);

        flash('success', 'Payment request saved as pending. This demo does not charge a real account.');
        redirect('../booking/my_bookings.php');
    }
}

// Bookings the customer can pay for (or just one, if booking_id is in the link)
$booking_id = (int)($_GET['booking_id'] ?? 0);

$stmt = $pdo->prepare("SELECT b.booking_id, b.check_in_date, b.check_out_date,
                              DATEDIFF(b.check_out_date, b.check_in_date) * r.price AS amount
                       FROM booking b
                       JOIN room r ON r.room_id = b.room_id
                       WHERE b.user_id = ?
                       AND b.booking_status NOT IN ('cancelled', 'checked_out')
                       AND (? = 0 OR b.booking_id = ?)
                       ORDER BY b.created_at DESC");
$stmt->execute([$_SESSION['user_id'], $booking_id, $booking_id]);
$rows = $stmt->fetchAll();

$pageTitle = 'Payment';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <h1>Payment request</h1>
        <p>Choose a booking and payment method. No real payment is collected by this academic demo.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="card card-body col-md-6">
            <?= csrf_field() ?>

            <label class="form-label">Booking</label>
            <select class="form-select mb-3" name="booking_id" required>
                <option value="">Select booking</option>
                <?php foreach ($rows as $b): ?>
                    <option value="<?= $b['booking_id'] ?>">
                        #<?= $b['booking_id'] ?> · <?= $b['check_in_date'] ?> to <?= $b['check_out_date'] ?> · <?= money($b['amount']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="form-label">Payment method</label>
            <select class="form-select mb-3" name="payment_method">
                <option value="cash">Cash at hotel</option>
                <option value="bkash">bKash</option>
                <option value="nagad">Nagad</option>
                <option value="bank_transfer">Bank transfer</option>
                <option value="card">Card (manual confirmation)</option>
            </select>

            <button class="btn btn-brand">Submit payment request</button>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
