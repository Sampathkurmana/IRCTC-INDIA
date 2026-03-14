<?php
$pageTitle = 'Edit Train — Admin';
require_once 'admin_header.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM trains WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$train = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$train) { setFlash('error','Train not found.'); redirect('manage_trains.php'); }

$errors = [];
$data   = $train;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['train_number']       = sanitize($_POST['train_number'] ?? '');
    $data['train_name']         = sanitize($_POST['train_name'] ?? '');
    $data['source']             = sanitize($_POST['source'] ?? '');
    $data['destination']        = sanitize($_POST['destination'] ?? '');
    $data['departure_time']     = sanitize($_POST['departure_time'] ?? '');
    $data['arrival_time']       = sanitize($_POST['arrival_time'] ?? '');
    $data['total_seats']        = (int)($_POST['total_seats'] ?? 100);
    $data['price']              = (float)($_POST['price'] ?? 0);
    $data['train_type']         = sanitize($_POST['train_type'] ?? 'Express');
    $days                       = $_POST['days'] ?? [];
    $data['days_of_operation']  = implode(',', array_map('sanitize', $days));

    if (!preg_match('/^[0-9]{4,6}$/', $data['train_number']))  $errors[] = 'Train number must be 4-6 digits.';
    if (strlen($data['train_name']) < 3)    $errors[] = 'Train name too short.';
    if (strlen($data['source']) < 2)        $errors[] = 'Source station required.';
    if (strlen($data['destination']) < 2)   $errors[] = 'Destination required.';
    if (!$data['departure_time'])           $errors[] = 'Departure time required.';
    if (!$data['arrival_time'])             $errors[] = 'Arrival time required.';
    if ($data['total_seats'] < 10)          $errors[] = 'Minimum 10 seats.';
    if ($data['price'] <= 0)               $errors[] = 'Valid price required.';
    if (empty($days))                       $errors[] = 'Select at least one operating day.';

    if (empty($errors)) {
        // Adjust available seats proportionally if total seats changed
        $diff = $data['total_seats'] - $train['total_seats'];
        $newAvail = max(0, $train['available_seats'] + $diff);

        $stmt = $db->prepare("
            UPDATE trains SET train_number=?, train_name=?, source=?, destination=?,
            departure_time=?, arrival_time=?, total_seats=?, available_seats=?, price=?,
            train_type=?, days_of_operation=? WHERE id=?
        ");
        $stmt->bind_param('ssssssiidss' . 'i',
            $data['train_number'], $data['train_name'], $data['source'], $data['destination'],
            $data['departure_time'], $data['arrival_time'],
            $data['total_seats'], $newAvail, $data['price'],
            $data['train_type'], $data['days_of_operation'], $id
        );
        if ($stmt->execute()) {
            setFlash('success', 'Train updated successfully.');
            redirect('manage_trains.php');
        } else {
            $errors[] = 'Update failed. Please try again.';
        }
        $stmt->close();
    }
}

$allDays = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
$selectedDays = explode(',', $data['days_of_operation']);
?>

<div style="max-width:760px;">
    <div class="flex-between mb-3">
        <h1 style="font-family:var(--font-head);font-size:1.3rem;font-weight:700;">
            Edit Train — <?= htmlspecialchars($train['train_number']) ?>
        </h1>
        <a href="manage_trains.php" class="btn btn-outline btn-sm">← All Trains</a>
    </div>

    <?php if ($errors): ?>
    <div class="flash flash-error">
        <span class="flash-icon">✕</span>
        <ul style="margin:0;padding-left:1.2rem;">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label>Train Number *</label>
                        <input type="text" name="train_number" required pattern="[0-9]{4,6}" maxlength="6"
                               value="<?= htmlspecialchars($data['train_number']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Train Type *</label>
                        <select name="train_type">
                            <?php foreach (['Express','Superfast','Rajdhani','Shatabdi','Local','Duronto'] as $t): ?>
                            <option value="<?= $t ?>" <?= $data['train_type']===$t?'selected':'' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Train Name *</label>
                    <input type="text" name="train_name" required maxlength="150"
                           value="<?= htmlspecialchars($data['train_name']) ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Source Station *</label>
                        <input type="text" name="source" required value="<?= htmlspecialchars($data['source']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Destination *</label>
                        <input type="text" name="destination" required value="<?= htmlspecialchars($data['destination']) ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Departure Time *</label>
                        <input type="time" name="departure_time" required value="<?= htmlspecialchars(substr($data['departure_time'],0,5)) ?>">
                    </div>
                    <div class="form-group">
                        <label>Arrival Time *</label>
                        <input type="time" name="arrival_time" required value="<?= htmlspecialchars(substr($data['arrival_time'],0,5)) ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Total Seats *</label>
                        <input type="number" name="total_seats" min="10" max="1000"
                               value="<?= htmlspecialchars($data['total_seats']) ?>">
                        <span class="form-hint">Currently <?= $train['available_seats'] ?> available</span>
                    </div>
                    <div class="form-group">
                        <label>Ticket Price (₹) *</label>
                        <input type="number" name="price" min="1" step="0.01"
                               value="<?= htmlspecialchars($data['price']) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Days of Operation *</label>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:4px;">
                        <?php foreach ($allDays as $day): ?>
                        <?php $checked = in_array($day, $selectedDays); ?>
                        <label style="display:flex;align-items:center;gap:5px;padding:6px 12px;border:1.5px solid var(--border);border-radius:6px;cursor:pointer;font-size:0.85rem;font-weight:500;
                            <?= $checked ? 'background:var(--navy);color:white;border-color:var(--navy);' : '' ?>">
                            <input type="checkbox" name="days[]" value="<?= $day ?>" <?= $checked?'checked':'' ?>
                                   style="display:none;"
                                   onchange="this.closest('label').style.background=this.checked?'var(--navy)':'';this.closest('label').style.color=this.checked?'white':'';this.closest('label').style.borderColor=this.checked?'var(--navy)':'var(--border)';">
                            <?= $day ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div style="display:flex;gap:1rem;margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary btn-lg">💾 Save Changes</button>
                    <a href="manage_trains.php" class="btn btn-outline btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>
