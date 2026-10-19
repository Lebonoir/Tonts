<?php
include "databasemodel/model.php";
if (!isset($_SESSION['id'])) { // any logged in user can open this page
    header("Location: index.php");
    exit;
}

$date = date("Y-m-d"); // date to check, today by default
if (isset($_GET['date'])) {
    $date = $_GET['date'];
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date < date("Y-m-d")) {
    $date = date("Y-m-d");
}

include "header_footer/header.php";
?>

<h2>Available Dates and Times</h2>

<form method="get" class="inline-form">
    <label class="field"><span>Date</span><input type="date" name="date" value="<?php echo $date; ?>" min="<?php echo date("Y-m-d"); ?>" required></label>
    <button type="submit">Check</button>
</form>

<?php
echo "<h3>$date</h3>";
echo "<div class='slots'>";
$shown = 0;
foreach ($slots as $time) {
    if ("$date $time" < date("Y-m-d H:i")) { // time already passed today
        continue;
    }

    if (slot_taken($date, $time)) {
        echo "<span class='slot booked'>$time - Booked</span>";
    } else {
        echo "<span class='slot available'>$time - Available</span>";
    }
    $shown++;
}
echo "</div>";

if ($shown == 0) {
    echo "<p class='empty'>No times left on this date.</p>";
}
?>

<?php include "header_footer/footer.php"; ?>
