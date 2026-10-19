<?php
include "../databasemodel/model.php";
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'customer') { // only logged in customer can open this page
    header("Location: ../index.php");
    exit;
}

$page = "customer_book.php";
$me = $_SESSION['id'];

// the booking form starts empty, the "Fill in my details" link (?fill=1) fills in the details saved in the account
$name = "";
$contact = "";
$email = "";
if (isset($_GET['fill'])) {
    $account = get_row("accounts", $me);
    $name = $account['fullname'];
    $contact = $account['contact'];
    $email = $account['email'];
}
$error = "";

if (isset($_POST['book'])) {
    $n = $_POST['name'];
    $c = $_POST['contact'];
    $e = $_POST['email'];
    $d = $_POST['date'];
    $t = $_POST['time'];
    $s = $_POST['service'];

    $error = validate_appointment($n, $c, $e, $d, $t, $s);
    if ($error == "" && !isset($_POST['agree'])) { // customer has to agree to the policy first
        $error = "You must agree to the booking policy to book.";
    }
    if ($error == "" && slot_taken($d, $t)) {
        $error = "Sorry, that date and time is already booked.";
    }

    if ($error == "") {
        $directory= "../img/";
        $insert = "INSERT INTO appointments
                   (account_id, name, contact, email, date, time, service, agreed)
                   VALUES ('$me', '$n', '$c', '$e', '$d', '$t', '$s', 1)";
        $tname = "appointment_";
        save($insert,$tname,$directory);

        notify_staff("New appointment from $n for $s on $d at $t is pending confirmation.");
        notify($me, "Your appointment for $s on $d at $t was sent and is pending confirmation.");
        notify($me, "You agreed to the booking policy when you booked $s on $d at $t.");
        header("Location: $page");
        exit;
    }
}

// request to cancel (staff or admin has to approve it first)
if (isset($_GET['cancelid'])) {
    $id = (int)$_GET['cancelid'];
    echo "Do you really want to request cancellation of appointment with ID of $id? <a href='?yescancel=$id'>Yes</a> | <a href='$page'>No</a>";
    exit();
}

if (isset($_GET['yescancel'])) {
    $a = get_row("appointments", (int)$_GET['yescancel']);
    // only your own pending or approved appointments can be cancelled
    if ($a && $a['account_id'] == $me && ($a['status'] == "pending" || $a['status'] == "approved") && $a['cancel_requested'] == 0) {
        update("UPDATE appointments SET cancel_requested = 1 WHERE id = " . $a['id']);

        $details = $a['service'] . " on " . $a['date'] . " at " . $a['time'];
        notify_staff($a['name'] . " requested to cancel the appointment for $details.");
        notify($me, "Your cancellation request for $details was sent and is waiting for confirmation.");
    }
    header("Location: $page");
    exit();
}

include "../header_footer/header.php";
?>

<h2>Book an Appointment</h2>
<?php echo $error; ?>
<p>Your booking has to be confirmed by our staff first. Check the status below.</p>

<a href="customer_book.php?fill=1">Fill in my details</a> (uses the details saved in My Account)

<form method="post">
    Name: <input type="text" name="name" value="<?php echo $name; ?>" required><br>
    Contact: <input type="text" name="contact" value="<?php echo $contact; ?>" maxlength="11" required><br>
    Email: <input type="email" name="email" value="<?php echo $email; ?>"><br>
    Date: <input type="date" name="date" required><br>
    Time: <select name="time"><?php list_options($slots); ?></select><br>
    Service: <select name="service"><?php options("services", "name"); ?></select><br>

    <h4>Booking Policy</h4>
    <p>
        1. Arriving too early or too late will not be tolerated or compensated by the system.<br>
        2. Refunds are only available once a valid proof and proper communication to the owner/admin is made.
    </p>
    <input type="checkbox" name="agree" required> I have read and agree to the booking policy<br>

    <button type="submit" name="book">Book</button>
</form>
<hr>

<?php
$column_mappings = [
    "service" => "Service: ",
    "date"    => " | Date: ",
    "time"    => " | Time: ",
    "status"  => " | Status: "
];
?>

<h3>My Appointments</h3>
<?php
$sql = "SELECT * FROM appointments WHERE account_id = $me AND status IN ('pending', 'approved') AND cancel_requested = 0 ORDER BY date, time";
display($sql, $column_mappings, "", "", ["cancel" => $page]); // cancel needs approval from staff/admin
?>

<h3>Waiting for cancellation approval</h3>
<?php
$sql = "SELECT id, service, date, time, 'cancellation requested' AS status FROM appointments WHERE account_id = $me AND cancel_requested = 1 ORDER BY date, time";
display($sql, $column_mappings, "");
?>

<hr>
<h3>Already Booked</h3>
<?php
$sql = "SELECT * FROM appointments WHERE status = 'approved' AND date >= CURDATE() ORDER BY date, time";
display($sql, ["date" => "Date: ", "time" => " | Time: "], ""); // only date and time are shown
?>

<?php include "../header_footer/footer.php"; ?>
