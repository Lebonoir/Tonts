<?php
include "../databasemodel/model.php";
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'customer') { // only logged in customer can open this page
    header("Location: ../index.php");
    exit;
}
include "../header_footer/header.php";
?>

<h2>Customer Dashboard</h2>

<h3>My Booking History</h3>
<?php
// every booking this customer ever made, newest first (if a cancellation was requested, that is shown instead of the status)
$sql = "SELECT *, IF(cancel_requested = 1, 'cancellation requested', status) AS shown_status
        FROM appointments WHERE account_id = " . $_SESSION['id'] . " ORDER BY date DESC, time DESC";
$column_mappings = [
    "service"      => "Service: ",
    "date"         => " | Date: ",
    "time"         => " | Time: ",
    "shown_status" => " | Status: "
];
display($sql, $column_mappings, "");
?>

<?php include "../header_footer/footer.php"; ?>
