<?php
// One shared page for Hotels, Rooms, Bookings, Guests, Payments and Feedback.
// hotels.php, rooms.php, ... all load this file. The page is picked from the file name.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// ---------- Settings for each page ----------
// fields: name => [label, input type, required?]
$pages = [
    'hotels' => [
        'title'  => 'Hotels',
        'table'  => 'hotel',
        'pk'     => 'hotel_id',
        'fields' => [
            'hotel_name'    => ['Hotel name', 'text', true],
            'hotel_address' => ['Address', 'text', true],
            'hotel_type'    => ['Hotel type', 'text', true],
        ],
        'list'   => ['hotel_id', 'hotel_name', 'hotel_address', 'hotel_type', 'created_at'],
        'search' => ['hotel_name', 'hotel_address'],
        'sql'    => 'SELECT hotel_id, hotel_name, hotel_address, hotel_type, created_at
                     FROM hotel ORDER BY hotel_id DESC',
    ],
    'rooms' => [
        'title'  => 'Rooms',
        'table'  => 'room',
        'pk'     => 'room_id',
        'fields' => [
            'hotel_id'         => ['Hotel', 'hotel', true],
            'room_number'      => ['Room number', 'text', true],
            'room_type'        => ['Room type', 'text', true],
            'room_description' => ['Description', 'textarea', false],
            'price'            => ['Price per night (৳)', 'number', true],
            'status'           => ['Status', 'room_status', true],
        ],
        'list'   => ['room_id', 'hotel_name', 'room_number', 'room_type', 'price', 'status'],
        'search' => ['room_number', 'room_type'],
        'sql'    => 'SELECT r.room_id, h.hotel_name, r.room_number, r.room_type, r.price, r.status
                     FROM room r
                     JOIN hotel h ON h.hotel_id = r.hotel_id
                     ORDER BY r.room_id DESC',
    ],
    'bookings' => [
        'title'  => 'Bookings',
        'table'  => 'booking',
        'pk'     => 'booking_id',
        'fields' => [
            'user_id'        => ['Guest', 'user', true],
            'room_id'        => ['Room', 'room', true],
            'booking_type'   => ['Booking source', 'booking_type', true],
            'check_in_date'  => ['Check-in', 'date', true],
            'check_out_date' => ['Check-out', 'date', true],
            'booking_status' => ['Status', 'booking_status', true],
        ],
        'list'   => ['booking_id', 'user_name', 'room_number', 'check_in_date', 'check_out_date',
                     'booking_type', 'booking_status', 'created_at'],
        'search' => ['user_name', 'room_number'],
        'sql'    => 'SELECT b.booking_id, u.user_name, r.room_number, b.check_in_date, b.check_out_date,
                            b.booking_type, b.booking_status, b.created_at
                     FROM booking b
                     JOIN users u ON u.user_id = b.user_id
                     JOIN room r ON r.room_id = b.room_id
                     ORDER BY b.booking_id DESC',
    ],
    'guests' => [
        'title'  => 'Guests',
        'table'  => 'users',
        'pk'     => 'user_id',
        'fields' => [
            'user_name'    => ['Full name', 'text', true],
            'user_email'   => ['Email', 'email', true],
            'user_address' => ['Address', 'text', false],
        ],
        'list'   => ['user_id', 'user_name', 'user_email', 'user_address', 'created_at'],
        'search' => ['user_name', 'user_email'],
        'sql'    => 'SELECT user_id, user_name, user_email, user_address, created_at
                     FROM users ORDER BY user_id DESC',
    ],
    'payments' => [
        'title'  => 'Payments',
        'table'  => 'payment',
        'pk'     => 'payment_id',
        'fields' => [
            'payment_status'        => ['Payment status', 'payment_status', true],
            'payment_method'        => ['Payment method', 'payment_method', true],
            'transaction_reference' => ['Transaction reference', 'text', false],
        ],
        'list'   => ['payment_id', 'booking_id', 'guest_name', 'amount', 'payment_method',
                     'payment_status', 'payment_date'],
        'search' => ['transaction_reference', 'guest_name'],
        'sql'    => 'SELECT p.payment_id, p.booking_id, u.user_name AS guest_name, p.amount,
                            p.payment_method, p.payment_status, p.payment_date, p.transaction_reference
                     FROM payment p
                     JOIN booking b ON b.booking_id = p.booking_id
                     JOIN users u ON u.user_id = b.user_id
                     ORDER BY p.payment_id DESC',
    ],
    'feedback' => [
        'title'  => 'Feedback',
        'table'  => 'feedback',
        'pk'     => 'feedback_id',
        'fields' => [],
        'list'   => ['feedback_id', 'user_name', 'rating', 'message', 'created_at'],
        'search' => ['user_name', 'message'],
        'sql'    => 'SELECT f.feedback_id, u.user_name, f.rating, f.message, f.created_at
                     FROM feedback f
                     JOIN users u ON u.user_id = f.user_id
                     ORDER BY f.feedback_id DESC',
    ],
];

// Field types that are shown as a drop-down list
$dropdown_types = ['hotel', 'user', 'room', 'room_status', 'booking_status',
                   'booking_type', 'payment_status', 'payment_method'];

// Which page are we on? (hotels, rooms, ...)
$page = basename($_SERVER['SCRIPT_NAME'], '.php');
if (!isset($pages[$page])) {
    http_response_code(404);
    exit('Unknown page');
}
$config = $pages[$page];
$table = $config['table'];
$pk = $config['pk'];

$action = $_GET['action'] ?? 'list';   // list, new or edit
$id = (int)($_GET['id'] ?? 0);
$error = '';
$row = [];

// ---------- Helper functions ----------

// Turn a plain list like ['cash','card'] into drop-down options.
function simple_options(array $values): array
{
    $options = [];
    foreach ($values as $value) {
        $options[] = ['id' => $value, 'label' => ucwords(str_replace('_', ' ', $value))];
    }
    return $options;
}

// Get the choices for a drop-down.
function get_options(PDO $pdo, string $type): array
{
    if ($type === 'hotel') {
        return $pdo->query('SELECT hotel_id AS id, hotel_name AS label FROM hotel ORDER BY hotel_name')->fetchAll();
    }
    if ($type === 'user') {
        return $pdo->query('SELECT user_id AS id, user_name AS label FROM users ORDER BY user_name')->fetchAll();
    }
    if ($type === 'room') {
        return $pdo->query("SELECT room_id AS id, CONCAT('Room ', room_number, ' · ', room_type) AS label
                            FROM room ORDER BY room_number")->fetchAll();
    }
    if ($type === 'room_status') {
        return simple_options(['available', 'occupied', 'maintenance', 'inactive']);
    }
    if ($type === 'booking_status') {
        return simple_options(['pending', 'confirmed', 'cancelled', 'checked_in', 'checked_out']);
    }
    if ($type === 'booking_type') {
        return simple_options(['online', 'offline']);
    }
    if ($type === 'payment_status') {
        return simple_options(['pending', 'paid', 'failed', 'refunded']);
    }
    if ($type === 'payment_method') {
        return simple_options(['cash', 'card', 'bkash', 'nagad', 'bank_transfer', 'other']);
    }
    return [];
}

// Delete a record (only hotels, rooms and feedback can be deleted here).
function delete_record(PDO $pdo, string $page, array $config, int $id): void
{
    if ($page === 'guests') {
        throw new RuntimeException('Guest accounts cannot be deleted while they may have booking records.');
    }
    if (!in_array($page, ['hotels', 'rooms', 'feedback'], true)) {
        throw new RuntimeException('This record cannot be deleted from this screen.');
    }
    $stmt = $pdo->prepare("DELETE FROM {$config['table']} WHERE {$config['pk']} = ?");
    $stmt->execute([$id]);
}

// Make sure a booking has valid dates and the room is free.
function check_booking(PDO $pdo, array $data, int $id): void
{
    $in = (string)$data['check_in_date'];
    $out = (string)$data['check_out_date'];

    if (!valid_date($in) || !valid_date($out) || $out <= $in) {
        throw new RuntimeException('Check-out must be after check-in.');
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM booking
                           WHERE room_id = ? AND booking_id <> ?
                           AND booking_status NOT IN ('cancelled', 'checked_out')
                           AND check_in_date < ? AND check_out_date > ?");
    $stmt->execute([$data['room_id'], $id, $out, $in]);
    if ($stmt->fetchColumn()) {
        throw new RuntimeException('This room is already reserved for those dates.');
    }
}

// Create or update a record from the submitted form.
function save_record(PDO $pdo, string $page, array $config, int $id): void
{
    // Read every field from the form
    $data = [];
    foreach ($config['fields'] as $name => $field) {
        $label = $field[0];
        $required = $field[2];
        $value = trim((string)($_POST[$name] ?? ''));

        if ($required && $value === '') {
            throw new RuntimeException($label . ' is required.');
        }
        $data[$name] = ($value === '') ? null : $value;
    }

    if ($page === 'bookings') {
        check_booking($pdo, $data, $id);
    }
    if ($page === 'rooms' && (float)$data['price'] < 0) {
        throw new RuntimeException('Room price cannot be negative.');
    }

    // Update an existing record
    if ($id) {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = $column . ' = ?';
        }
        $values = array_values($data);
        $values[] = $id;

        $sql = "UPDATE {$config['table']} SET " . implode(', ', $sets) . " WHERE {$config['pk']} = ?";
        $pdo->prepare($sql)->execute($values);

        // When a payment becomes "paid", remember the date
        if ($page === 'payments' && $data['payment_status'] === 'paid') {
            $pdo->prepare('UPDATE payment SET payment_date = NOW() WHERE payment_id = ? AND payment_date IS NULL')
                ->execute([$id]);
        }
        return;
    }

    // Create a new record
    if ($page === 'guests') {
        throw new RuntimeException('Create guest accounts through customer registration.');
    }
    if ($page === 'payments' || $page === 'feedback') {
        throw new RuntimeException('Create payments through the booking payment flow and feedback through the customer form.');
    }

    $columns = implode(', ', array_keys($data));
    $marks = implode(', ', array_fill(0, count($data), '?'));
    $sql = "INSERT INTO {$config['table']} ($columns) VALUES ($marks)";
    $pdo->prepare($sql)->execute(array_values($data));
}

// ---------- Handle form submissions ----------
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = $_POST['do'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    try {
        if ($do === 'delete') {
            delete_record($pdo, $page, $config, $id);
            flash('success', 'Record deleted.');
            redirect($page . '.php');
        }

        if ($do === 'save') {
            save_record($pdo, $page, $config, $id);
            flash('success', $id ? 'Record updated.' : 'Record created.');
            redirect($page . '.php');
        }
    } catch (Throwable $ex) {
        if ($ex instanceof PDOException) {
            $error = 'Could not save this record. Check required fields and linked records.';
        } else {
            $error = $ex->getMessage();
        }
        // Show the form again with what the admin typed
        $action = $id ? 'edit' : 'new';
        $row = $_POST;
    }
}

// The payments "Update" button opens the edit form
if ($page === 'payments' && $action === 'status') {
    $action = 'edit';
}

// ---------- Load the record for the edit form ----------
if ($action === 'edit' && $id && !$row) {
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE $pk = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) {
        flash('error', 'Record not found.');
        redirect($page . '.php');
    }
}

// Default values for new records
if ($action === 'new' && !$row) {
    if ($page === 'rooms') {
        $row['status'] = 'available';
    }
    if ($page === 'bookings') {
        $row['booking_type'] = 'online';
        $row['booking_status'] = 'pending';
    }
}

admin_header($config['title'], $page);
?>

<div class="page-head">
    <h2><?= e($config['title']) ?></h2>
    <?php if ($action === 'list' && in_array($page, ['hotels', 'rooms', 'bookings'], true)): ?>
        <a class="btn" href="?action=new">+ Add <?= e(rtrim($config['title'], 's')) ?></a>
    <?php else: ?>
        <a class="btn secondary" href="<?= $page ?>.php">← Back to list</a>
    <?php endif; ?>
</div>

<?php if ($error): ?>
    <div class="notice notice-error"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($action === 'new' || $action === 'edit'): ?>

    <!-- Add / edit form -->
    <div class="panel">
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="do" value="save">
            <input type="hidden" name="id" value="<?= $id ?>">
            <div class="form-grid">
                <?php foreach ($config['fields'] as $name => $field): ?>
                    <?php
                    $label = $field[0];
                    $type = $field[1];
                    $required = $field[2] ? 'required' : '';
                    $value = $row[$name] ?? '';
                    ?>
                    <div class="form-group">
                        <label><?= e($label) ?></label>

                        <?php if (in_array($type, $dropdown_types, true)): ?>
                            <select name="<?= e($name) ?>" <?= $required ?>>
                                <option value="">Select…</option>
                                <?php foreach (get_options($pdo, $type) as $option): ?>
                                    <option value="<?= e($option['id']) ?>"
                                        <?= ((string)$value === (string)$option['id']) ? 'selected' : '' ?>>
                                        <?= e($option['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ($type === 'textarea'): ?>
                            <textarea name="<?= e($name) ?>" rows="4"><?= e($value) ?></textarea>

                        <?php else: ?>
                            <input type="<?= e($type) ?>" name="<?= e($name) ?>" value="<?= e($value) ?>"
                                <?= $required ?> <?= ($type === 'number') ? 'min="0" step="0.01"' : '' ?>>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="form-actions">
                <button class="btn">Save changes</button>
                <a class="btn secondary" href="<?= $page ?>.php">Cancel</a>
            </div>
        </form>
    </div>

<?php else: ?>
    <?php
    // List page: load the rows, then filter them by the search box
    $search = trim($_GET['q'] ?? '');
    $rows = $pdo->query($config['sql'])->fetchAll();

    if ($search !== '') {
        $matches = [];
        foreach ($rows as $r) {
            foreach ($config['search'] as $column) {
                if (stripos((string)($r[$column] ?? ''), $search) !== false) {
                    $matches[] = $r;
                    break;
                }
            }
        }
        $rows = $matches;
    }
    ?>

    <form class="filters" method="get">
        <input name="q" value="<?= e($search) ?>" placeholder="Search <?= e(strtolower($config['title'])) ?>">
        <button class="btn secondary">Search</button>
        <?php if ($search !== ''): ?>
            <a class="btn secondary" href="<?= $page ?>.php">Clear</a>
        <?php endif; ?>
    </form>

    <div class="list-panel">
        <table class="data-table">
            <thead>
                <tr>
                    <?php foreach ($config['list'] as $column): ?>
                        <th><?= e(ucwords(str_replace('_', ' ', $column))) ?></th>
                    <?php endforeach; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <?php foreach ($config['list'] as $column): ?>
                            <td>
                                <?php if (in_array($column, ['booking_status', 'payment_status', 'status'], true)): ?>
                                    <span class="pill <?= e($r[$column]) ?>"><?= e(str_replace('_', ' ', $r[$column])) ?></span>
                                <?php elseif ($column === 'price' || $column === 'amount'): ?>
                                    <?= money($r[$column]) ?>
                                <?php elseif ($column === 'message'): ?>
                                    <?= e(mb_strimwidth((string)$r[$column], 0, 75, '…')) ?>
                                <?php else: ?>
                                    <?= e($r[$column] ?? '—') ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>

                        <td>
                            <div style="display:flex;gap:5px;align-items:center">
                                <?php if ($page === 'payments'): ?>
                                    <a class="btn secondary small" href="?action=edit&id=<?= $r[$pk] ?>">Update</a>
                                <?php elseif ($page !== 'feedback'): ?>
                                    <a class="btn secondary small" href="?action=edit&id=<?= $r[$pk] ?>">Edit</a>
                                <?php endif; ?>

                                <?php if (in_array($page, ['hotels', 'rooms', 'feedback'], true)): ?>
                                    <form method="post" onsubmit="return confirm('Delete this record?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="do" value="delete">
                                        <input type="hidden" name="id" value="<?= $r[$pk] ?>">
                                        <button class="btn danger small">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="<?= count($config['list']) + 1 ?>"><div class="empty">No records found.</div></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<?php admin_footer(); ?>
