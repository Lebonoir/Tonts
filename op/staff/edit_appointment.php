<?php
include "../databasemodel/model.php";
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'staff') { // only logged in staff can open this page
    header("Location: ../index.php");
    exit;
}

$error = "";

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $old = get_row("appointments", $id); // to see if the status changed
    $n  = $_POST['name'];
    $c  = $_POST['contact'];
    $e  = $_POST['email'];
    $d  = $_POST['date'];
    $t  = $_POST['time'];
    $sv = $_POST['service'];
    $s  = $_POST['status'];

    $error = validate_appointment($n, $c, $e, $d, $t, $sv, false); // false = old dates are fine when editing
    if ($error == "" && $s == "approved" && slot_taken($d, $t, $id)) {
        $error = "That date and time is already booked.";
    }

    if ($error == "") {
        $sql = "UPDATE appointments
                SET name = '$n', contact = '$c', email = '$e', date = '$d',
                    time = '$t', service = '$sv', status = '$s'
                WHERE id = $id";
        update($sql);
        if ($old['status'] != $s) {
            update("UPDATE appointments SET cancel_requested = 0 WHERE id = $id"); // status changed by hand, no more cancel request
            notify_appointment($id);
        }
        header("Location: staff_appointments.php");
        exit();
    }
}

$row = get_row("appointments", $_GET['id']);
include "../header_footer/header.php";
?>

<h2>Edit Appointment</h2>
<?php if ($error != "") { echo "<div class='alert error'>$error</div>"; } ?>
<section class="panel narrow">
<form method="post">
    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
    <label class="field"><span>Name</span><input type="text" name="name" value="<?php echo $row['name']; ?>" required></label>
    <div class="field-row">
        <label class="field"><span>Contact</span><input type="text" name="contact" value="<?php echo $row['contact']; ?>" maxlength="11" required></label>
        <label class="field"><span>Email</span><input type="email" name="email" value="<?php echo $row['email']; ?>"></label>
    </div>
    <div class="field-row">
        <label class="field"><span>Date</span><input type="date" name="date" value="<?php echo $row['date']; ?>" required></label>
        <label class="field"><span>Time</span><input type="time" name="time" value="<?php echo $row['time']; ?>" required></label>
    </div>
    <label class="field"><span>Service</span><select name="service"><?php options("services", "name", $row['service']); ?></select></label>
    <label class="field"><span>Status</span>
    <select name="status">
        <option value="pending"   <?php if ($row['status'] == "pending")   echo "selected"; ?>>pending</option>
        <option value="approved"  <?php if ($row['status'] == "approved")  echo "selected"; ?>>approved</option>
        <option value="completed" <?php if ($row['status'] == "completed") echo "selected"; ?>>completed</option>
        <option value="cancelled" <?php if ($row['status'] == "cancelled") echo "selected"; ?>>cancelled</option>
    </select></label>
    <div class="form-actions">
    <button type="submit" name="update">Update</button>
    <a href="staff_appointments.php">Back</a>
    </div>
</form>
</section>

<?php include "../header_footer/footer.php"; ?>
