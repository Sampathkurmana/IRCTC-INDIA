<?php
$pageTitle = 'Book Ticket — IRCTC';
$rootPath  = '';
$cssPath   = '';
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config/database.php';
if (!isset($_SESSION['user_id'])) { setFlash('error','Please login to book.'); redirect('login.php'); }

$db       = getDB();
$train_id = (int)($_GET['train_id'] ?? $_POST['train_id'] ?? 0);
$journey_date = sanitize($_GET['date'] ?? $_POST['journey_date'] ?? date('Y-m-d'));

if ($journey_date < date('Y-m-d')) { setFlash('error','Invalid date.'); redirect('search.php'); }

$stmt = $db->prepare("SELECT * FROM trains WHERE id = ?");
$stmt->bind_param('i', $train_id);
$stmt->execute();
$train = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$train) { setFlash('error','Train not found.'); redirect('search.php'); }
if ($train['available_seats'] <= 0) { setFlash('error','No seats available.'); redirect('search.php'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seat_count = (int)($_POST['seat_count'] ?? 1);
    $p_names    = $_POST['passenger_name'] ?? [];
    $p_ages     = $_POST['passenger_age'] ?? [];
    $p_genders  = $_POST['passenger_gender'] ?? [];

    if ($seat_count < 1 || $seat_count > 6)         $errors[] = 'You can book 1 to 6 seats at once.';
    if ($seat_count > $train['available_seats'])     $errors[] = 'Only ' . $train['available_seats'] . ' seats available.';
    if (count($p_names) !== $seat_count)             $errors[] = 'Passenger details mismatch.';

    foreach ($p_names as $i => $pname) {
        if (strlen(trim($pname)) < 2)          $errors[] = 'Passenger ' . ($i+1) . ': Name too short.';
        $age = (int)($p_ages[$i] ?? 0);
        if ($age < 1 || $age > 120)            $errors[] = 'Passenger ' . ($i+1) . ': Invalid age.';
        if (empty($p_genders[$i]))             $errors[] = 'Passenger ' . ($i+1) . ': Select gender.';
    }

    if (empty($errors)) {
        $db->begin_transaction();
        try {
            $pnr          = generatePNR();
            $total_amount = $seat_count * $train['price'];
            $user_id      = $_SESSION['user_id'];

            // Insert booking
            $stmt = $db->prepare("INSERT INTO bookings (user_id,train_id,journey_date,seat_count,total_amount,pnr_number) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('iiisds', $user_id, $train_id, $journey_date, $seat_count, $total_amount, $pnr);
            $stmt->execute();
            $booking_id = $db->insert_id;
            $stmt->close();

            // Insert passengers
            $seats = range(1, $train['total_seats']);
            shuffle($seats);
            $assigned_seats = array_slice($seats, 0, $seat_count);

            $pstmt = $db->prepare("INSERT INTO passengers (booking_id,name,age,gender,seat_number) VALUES (?,?,?,?,?)");
            foreach ($p_names as $i => $pname) {
                $age    = (int)$p_ages[$i];
                $gender = sanitize($p_genders[$i]);
                $seat   = 'S' . $assigned_seats[$i];
                $pstmt->bind_param('isiss', $booking_id, $pname, $age, $gender, $seat);
                $pstmt->execute();
            }
            $pstmt->close();

            // Decrement seats
            $stmt = $db->prepare("UPDATE trains SET available_seats = available_seats - ? WHERE id = ? AND available_seats >= ?");
            $stmt->bind_param('iii', $seat_count, $train_id, $seat_count);
            $stmt->execute();
            if ($db->affected_rows === 0) throw new Exception('Seat update failed.');
            $stmt->close();

            $db->commit();
            setFlash('success', 'Booking confirmed! PNR: ' . $pnr);
            redirect('my_bookings.php?highlight=' . $pnr);
        } catch (Exception $e) {
            $db->rollback();
            $errors[] = 'Booking failed: ' . $e->getMessage();
        }
    }
}
?>
<?php require_once 'includes/header.php'; ?>

<div class="container page-wrap">
    <div class="booking-steps">
        <div class="step active"><div class="step-num">1</div> Passenger Details</div>
        <div class="step"><div class="step-num">2</div> Review &amp; Pay</div>
        <div class="step"><div class="step-num">3</div> Confirmation</div>
    </div>

    <?php if ($errors): ?>
    <div class="flash flash-error">
        <span class="flash-icon">✕</span>
        <ul style="margin:0;padding-left:1.2rem;">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="booking-layout">
        <div>
            <!-- Train Info -->
            <div class="card mb-3">
                <div class="train-card-header" style="border-radius:var(--radius) var(--radius) 0 0;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span class="train-number-badge"><?= htmlspecialchars($train['train_number']) ?></span>
                        <span class="train-name-text"><?= htmlspecialchars($train['train_name']) ?></span>
                        <span class="train-type-tag"><?= htmlspecialchars($train['train_type']) ?></span>
                    </div>
                    <span style="color:rgba(255,255,255,0.7);font-size:0.85rem;">
                        📅 <?= date('D, d M Y', strtotime($journey_date)) ?>
                    </span>
                </div>
                <div class="train-card-body" style="border-radius:0 0 var(--radius) var(--radius);">
                    <div class="time-block">
                        <div class="time"><?= substr($train['departure_time'],0,5) ?></div>
                        <div class="station"><?= htmlspecialchars($train['source']) ?></div>
                    </div>
                    <div class="journey-line">
                        <div class="dot"></div>
                        <div class="line"></div>
                        <div class="dot" style="background:var(--navy3);"></div>
                    </div>
                    <div class="time-block">
                        <div class="time"><?= substr($train['arrival_time'],0,5) ?></div>
                        <div class="station"><?= htmlspecialchars($train['destination']) ?></div>
                    </div>
                    <div class="seats-price">
                        <div class="price-amount">₹<?= number_format($train['price'],2) ?></div>
                        <div style="font-size:0.8rem;color:var(--muted);">per passenger</div>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <form method="POST" action="">
                <input type="hidden" name="train_id" value="<?= $train_id ?>">
                <input type="hidden" name="journey_date" value="<?= htmlspecialchars($journey_date) ?>">

                <div class="card mb-3">
                    <div class="card-header">
                        <h3>Number of Passengers</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group" style="max-width:200px;">
                            <label>Seats (max 6, available: <?= $train['available_seats'] ?>)</label>
                            <select name="seat_count" id="seat_count">
                                <?php for ($i=1; $i<=min(6,$train['available_seats']); $i++): ?>
                                <option value="<?= $i ?>" <?= (isset($_POST['seat_count']) && $_POST['seat_count']==$i)?'selected':'' ?>><?= $i ?> Seat<?= $i>1?'s':'' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <input type="hidden" id="price_per_seat" value="<?= $train['price'] ?>">
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><h3>Passenger Details</h3></div>
                    <div class="card-body">
                        <div id="passenger-rows">
                            <!-- JS renders this -->
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    🎫 Confirm Booking
                </button>
            </form>
        </div>

        <!-- Summary -->
        <div class="booking-summary">
            <div class="card">
                <div class="card-header"><h3>Booking Summary</h3></div>
                <div class="card-body">
                    <div class="summary-item">
                        <span class="label">Train</span>
                        <span class="value"><?= htmlspecialchars($train['train_number']) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Journey Date</span>
                        <span class="value"><?= date('d M Y', strtotime($journey_date)) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Route</span>
                        <span class="value" style="font-size:0.82rem;"><?= htmlspecialchars($train['source']) ?> → <?= htmlspecialchars($train['destination']) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Seats</span>
                        <span class="value" id="seat-count-summary">1</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Price/seat</span>
                        <span class="value">₹<?= number_format($train['price'],2) ?></span>
                    </div>
                    <div class="summary-total">
                        <span>Total Amount</span>
                        <span id="total-price-display">₹<?= number_format($train['price'],2) ?></span>
                    </div>
                </div>
            </div>
            <div class="card" style="margin-top:1rem;">
                <div class="card-body" style="font-size:0.82rem;color:var(--muted);">
                    <strong style="color:var(--navy);display:block;margin-bottom:6px;">ℹ Booking Info</strong>
                    • Seats assigned randomly<br>
                    • PNR generated on confirmation<br>
                    • Cancel anytime from My Bookings<br>
                    • Carry valid photo ID during journey
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
