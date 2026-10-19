<?php
include "../databasemodel/model.php";
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'staff') { // only logged in staff can open this page
    header("Location: ../index.php");
    exit;
}
include "../header_footer/header.php";
?>

<h2>Staff Dashboard</h2>

<h3>Appointments</h3>
<?php
// each line is: what to show => the condition to count (works like $column_mappings in display())
$counts = [
    "Today"                 => "date = CURDATE()",
    "Pending"               => "status = 'pending'",
    "Approved"              => "status = 'approved'",
    "Completed"             => "status = 'completed'",
    "Cancelled"             => "status = 'cancelled'",
    "Cancellation requests" => "cancel_requested = 1",
    "Total"                 => "1" // 1 means no condition, so it counts everything
];

// go through the list one line at a time and print it
echo "<div class='stats'>";
foreach ($counts as $label => $condition) {
    echo "<div class='stat'><b>" . count_rows("appointments", $condition) . "</b>$label</div>";
}
echo "</div>";
?>

<?php include "../header_footer/footer.php"; ?>
