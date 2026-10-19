<?php
// the appointment page for both admin and staff, opened through admin/admin_appointments.php or staff/staff_appointments.php
if (!isset($page)) { // opened directly instead of through one of them
    header("Location: index.php");
    exit;
}

$error = "";

// the edit link adds ?id=5 to the address (no id means adding a new appointment)
$id = 0;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}

// what the form shows at first: empty when adding, the saved info when editing
$title = "Add Appointment";
$row = ["name" => "", "contact" => "", "email" => "", "date" => "", "time" => "", "service" => "", "status" => "approved"];
if ($id != 0) {
    $title = "Edit Appointment";
    $row = get_row("appointments", $id);
}

// when the Save button is pressed (add walk-ins / phone calls with account_id 0, or edit)
if (isset($_POST['save'])) {
    $n  = $_POST['name'];
    $c  = $_POST['contact'];
    $e  = $_POST['email'];
    $d  = $_POST['date'];
    $t  = $_POST['time'];
    $sv = $_POST['service'];
    $s  = $_POST['status'];

    $error = validate_appointment($n, $c, $e, $d, $t, $sv, $id == 0); // old dates are fine when editing
    if ($error == "" && $s == "approved" && slot_taken($d, $t, $id)) {
        $error = "That date and time is already booked.";
    }

    if ($error == "") {
        if ($id == 0) { // add a new appointment
            $directory= "../img/";
            $insert = "INSERT INTO appointments
                       (account_id, name, contact, email, date, time, service, status)
                       VALUES (0, '$n', '$c', '$e', '$d', '$t', '$sv', '$s')";
            $tname = "appointment_";
            save($insert,$tname,$directory);
        } else { // edit the appointment
            update("UPDATE appointments SET name = '$n', contact = '$c', email = '$e', date = '$d', time = '$t', service = '$sv', status = '$s' WHERE id = $id");
            if ($row['status'] != $s) { // status changed by hand: no more cancel request, tell the customer
                update("UPDATE appointments SET cancel_requested = 0 WHERE id = $id");
                notify_appointment($id);
            }
        }
        header("Location: $page");
        exit;
    }
    $row = $_POST; // there was an error, so show what was typed again
}

// confirm a pending appointment ((int) makes sure the id is a number)
if (isset($_GET['confirmid'])) {
    $a = get_row("appointments", (int)$_GET['confirmid']);
    if (slot_taken($a['date'], $a['time'], $a['id'])) {
        $error = "Can't confirm, that date and time is already booked by another appointment.";
    } else {
        update("UPDATE appointments SET status = 'approved' WHERE id = " . $a['id']);
        notify_appointment($a['id']);
        header("Location: $page");
        exit();
    }
}

// accept a cancellation request
if (isset($_GET['acceptid'])) {
    $a = get_row("appointments", (int)$_GET['acceptid']);
    if ($a && $a['cancel_requested'] == 1) {
        update("UPDATE appointments SET status = 'cancelled', cancel_requested = 0 WHERE id = " . $a['id']);
        notify_appointment($a['id']);
    }
    header("Location: $page");
    exit();
}

// decline a cancellation request
if (isset($_GET['declineid'])) {
    $a = get_row("appointments", (int)$_GET['declineid']);
    if ($a && $a['cancel_requested'] == 1) {
        update("UPDATE appointments SET cancel_requested = 0 WHERE id = " . $a['id']);
        $details = $a['service'] . " on " . $a['date'] . " at " . $a['time'];
        notify($a['account_id'], "Your cancellation request for $details was declined. The appointment is still " . $a['status'] . ".");
    }
    header("Location: $page");
    exit();
}

//updated delete w/ confirmation
if (isset($_GET['deleteid'])) {
    $id = $_GET['deleteid'];
    $title = "Delete this appointment?";
    $question = "Do you really want to delete record with ID of $id?";
    $yes = "?yesdelete=$id";
    $no = $page;
    $danger = true;
    include "../header_footer/confirm.php";
    exit();
}

if (isset($_GET['yesdelete'])) {
    $a = get_row("appointments", (int)$_GET['yesdelete']); // get it first, it's gone after deleting
    $directory= "../img/";
    delete("appointments", $_GET['yesdelete'], $directory);

    if ($a && $a['account_id'] > 0) { // 0 = walk-in, no account to notify
        $details = $a['service'] . " on " . $a['date'] . " at " . $a['time'];
        $what = "was deleted.";
        if ($a['status'] == "pending") {
            $what = "was not approved and has been removed.";
        }
        notify($a['account_id'], "Your appointment for $details $what");
    }
    header("Location: $page");
    exit();
}

include "../header_footer/header.php";
?>

<h2>Manage Appointments</h2>
<?php if ($error != "") { echo "<div class='alert error'>$error</div>"; } ?>

<div class="split">
<section class="panel">
<?php echo "<h3>$title</h3>"; ?>
<form method="post">
    <label class="field"><span>Name</span><input type="text" name="name" value="<?php echo $row['name']; ?>" required></label>
    <div class="field-row">
        <label class="field"><span>Contact</span><input type="text" name="contact" value="<?php echo $row['contact']; ?>" maxlength="11" required></label>
        <label class="field"><span>Email</span><input type="email" name="email" value="<?php echo $row['email']; ?>"></label>
    </div>
    <div class="field-row">
        <label class="field"><span>Date</span><input type="date" name="date" value="<?php echo $row['date']; ?>" required></label>
        <label class="field"><span>Time</span><select name="time"><?php list_options($slots, substr($row['time'], 0, 5)); ?></select></label>
    </div>
    <label class="field"><span>Service</span><select name="service"><?php options("services", "name", $row['service']); ?></select></label>
    <label class="field"><span>Status</span><select name="status"><?php list_options(["pending", "approved", "completed", "cancelled"], $row['status']); ?></select></label>
    <div class="form-actions">
    <button type="submit" name="save">Save</button>
    <?php
    if ($id != 0) {
        echo "<a href='$page'>Cancel</a>";
    }
    ?>
    </div>
</form>
</section>

<section class="list">

<?php
$column_mappings = [
    "id"      => "ID: ",
    "name"    => " | Name: ",
    "contact" => " | Contact: ",
    "service" => " | Service: ",
    "date"    => " | Date: ",
    "time"    => " | Time: ",
    "status"  => " | Status: "
];
?>

<h3>Pending - waiting for confirmation</h3>
<?php
// the last part adds an extra link, ["confirm" => $page] makes a "confirm" link to this page
$sql = "SELECT * FROM appointments WHERE status = 'pending' AND cancel_requested = 0 ORDER BY date, time";
display($sql, $column_mappings, $page, $page, ["confirm" => $page]);
?>

<h3>Cancellation requests - waiting for approval</h3>
<?php
$sql = "SELECT * FROM appointments WHERE cancel_requested = 1 ORDER BY date, time";
display($sql, $column_mappings, "", "", ["accept" => $page, "decline" => $page]);
?>

<h3>Other appointments</h3>
<?php
$sql = "SELECT * FROM appointments WHERE status != 'pending' AND cancel_requested = 0 ORDER BY date, time";
display($sql, $column_mappings, $page, $page);
?>
</section>
</div>

<?php include "../header_footer/footer.php"; ?>
