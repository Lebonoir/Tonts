<?php
include "../databasemodel/model.php";
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') { // only logged in admin can open this page
    header("Location: ../index.php");
    exit;
}
include "../header_footer/header.php";
?>

<h2>Admin Dashboard</h2>

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

<h3>Accounts</h3>
<?php
$counts = [
    "Admin"    => "role = 'admin'",
    "Staff"    => "role = 'staff'",
    "Customer" => "role = 'customer'"
];

echo "<div class='stats'>";
foreach ($counts as $label => $condition) {
    echo "<div class='stat'><b>" . count_rows("accounts", $condition) . "</b>$label</div>";
}
echo "</div>";
?>

<h3>Services</h3>
<?php echo "<div class='stats'><div class='stat'><b>" . count_rows("services") . "</b>Total services</div></div>"; ?>

<?php include "../header_footer/footer.php"; ?>
