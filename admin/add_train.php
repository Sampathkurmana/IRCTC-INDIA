<?php
$pageTitle = 'Add Train — Admin';
require_once 'admin_header.php';

$errors = [];
$data   = ['train_number'=>'','train_name'=>'','source'=>'','destination'=>'',
           'departure_time'=>'','arrival_time'=>'','total_seats'=>'100',
           'price'=>'','train_type'=>'Express','days_of_operation'=>'Mon,Tue,Wed,Thu,Fri,Sat,Sun'];

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

    if (!preg_match('/^[0-9]{4,6}$/', $data['train_number'])) $errors[] = 'Train number must be 4-6 digits.';
    if (strlen($data['train_name']) < 3)    $errors[] = 'Train name too short.';
    if (strlen($data['source']) < 2)        $errors[] = 'Source station required.';
    if (strlen($data['destination']) < 2)   $errors[] = 'Destination station required.';
    if (strtolower($data['source']) === strtolower($data['destination'])) $errors[] = 'Source and destination cannot be same.';
    if (!$data['departure_time'])           $errors[] = 'Departure time required.';
    if (!$data['arrival_time'])             $errors[] = 'Arrival time required.';
    if ($data['total_seats'] < 10)          $errors[] = 'Minimum 10 seats.';
    if ($data['price'] <= 0)               $errors[] = 'Valid price required.';
    if (empty($days))                       $errors[] = 'Select at least one operating day.';

    if (empty($errors)) {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id FROM trains WHERE train_number = ?");
        $stmt->bind_param('s', $data['train_number']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Train number already exists.';
        } else {
            $stmt->close();
            $available = $data['total_seats'];
            $stmt = $db->prepare("
                INSERT INTO trains (train_number, train_name, source, destination, departure_time, arrival_time,
                                    total_seats, available_seats, price, train_type, days_of_operation)
                VALUES (?,?,?,?,?,?,?,?,?,?,?)
            ");
            // Types: s s s s s s i i d s s  (11 params)
            $stmt->bind_param('ssssssiidss',
                $data['train_number'], $data['train_name'], $data['source'], $data['destination'],
                $data['departure_time'], $data['arrival_time'],
                $data['total_seats'], $available, $data['price'],
                $data['train_type'], $data['days_of_operation']
            );
            if ($stmt->execute()) {
                setFlash('success', 'Train "' . $data['train_name'] . '" added successfully!');
                redirect('manage_trains.php');
            } else {
                $errors[] = 'Database error. Please try again.';
            }
            $stmt->close();
        }
    }
}

$allDays = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
$selectedDays = explode(',', $data['days_of_operation']);
?>

<div style="max-width:760px;">
    <div class="flex-between mb-3">
        <h1 style="font-family:var(--font-head);font-size:1.3rem;font-weight:700;">Add New Train</h1>
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
                        <input type="text" name="train_number" placeholder="e.g. 12301" required
                               pattern="[0-9]{4,6}" maxlength="6"
                               value="<?= htmlspecialchars($data['train_number']) ?>">
                        <span class="form-hint">4-6 digit unique number</span>
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
                    <input type="text" name="train_name" placeholder="e.g. Howrah Rajdhani Express" required
                           maxlength="150" value="<?= htmlspecialchars($data['train_name']) ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Source Station *</label>
                        <input type="text" name="source" placeholder="e.g. New Delhi" required
                               value="<?= htmlspecialchars($data['source']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Destination Station *</label>
                        <input type="text" name="destination" placeholder="e.g. Howrah" required
                               value="<?= htmlspecialchars($data['destination']) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Departure Time *</label>
                        <input type="time" name="departure_time" required value="<?= htmlspecialchars($data['departure_time']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Arrival Time *</label>
                        <input type="time" name="arrival_time" required value="<?= htmlspecialchars($data['arrival_time']) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Total Seats *</label>
                        <input type="number" name="total_seats" placeholder="100" required
                               min="10" max="1000" value="<?= htmlspecialchars($data['total_seats']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Ticket Price (₹) *</label>
                        <input type="number" name="price" placeholder="850.00" required
                               min="1" step="0.01" value="<?= htmlspecialchars($data['price']) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Days of Operation *</label>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:4px;">
                        <?php foreach ($allDays as $day): ?>
                        <label style="display:flex;align-items:center;gap:5px;padding:6px 12px;border:1.5px solid var(--border);border-radius:6px;cursor:pointer;font-size:0.85rem;font-weight:500;
                            <?= in_array($day, $selectedDays) ? 'background:var(--navy);color:white;border-color:var(--navy);' : '' ?>">
                            <input type="checkbox" name="days[]" value="<?= $day ?>"
                                   <?= in_array($day, $selectedDays) ? 'checked' : '' ?>
                                   style="display:none;"
                                   onchange="this.closest('label').style.background=this.checked?'var(--navy)':'';this.closest('label').style.color=this.checked?'white':'';this.closest('label').style.borderColor=this.checked?'var(--navy)':'var(--border)';">
                            <?= $day ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div style="display:flex;gap:1rem;margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary btn-lg">➕ Add Train</button>
                    <a href="manage_trains.php" class="btn btn-outline btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>