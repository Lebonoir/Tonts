<?php
include "../databasemodel/model.php";
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') { // only logged in admin can open this page
    header("Location: ../index.php");
    exit;
}

$page = "admin_services.php";
$error = "";

if (isset($_POST['save'])) {
    $n = $_POST['name'];
    $p = $_POST['price'];

    if (empty($n) || $p <= 0) {
        $error = "Please enter a service name and a price above 0.";
    } else if (check_duplicate("services", "name", $n)) {
        $error = "Service already exists.";
    } else {
        $directory= "../img/";
        $insert = "INSERT INTO services (name, price) VALUES ('$n', '$p')";
        $tname = "service_";
        save($insert,$tname,$directory);
        header("Location: $page");
        exit;
    }
}

//updated delete w/ confirmation
if (isset($_GET['deleteid'])) {
    $id = $_GET['deleteid'];
    $title = "Delete this service?";
    $question = "Do you really want to delete record with ID of $id?";
    $yes = "?yesdelete=$id";
    $no = $page;
    $danger = true;
    include "../header_footer/confirm.php";
    exit();
}

if (isset($_GET['yesdelete'])) {
    $directory= "../img/";
    delete("services", $_GET['yesdelete'], $directory);
    header("Location: $page");
    exit();
}

include "../header_footer/header.php";
?>

<h2>Manage Services</h2>
<?php if ($error != "") { echo "<div class='alert error'>$error</div>"; } ?>

<div class="split">
<section class="panel">
<h3>New service</h3>
<form method="post">
    <label class="field"><span>Service name</span><input type="text" name="name" required></label>
    <label class="field"><span>Price</span><input type="number" name="price" step="0.01" required></label>
    <div class="form-actions">
    <button type="submit" name="save">Add service</button>
    </div>
</form>
</section>

<section class="list">
<h3>All services</h3>
<?php
$sql = "SELECT * FROM services";
$column_mappings = [
    "id"    => "ID: ",
    "name"  => " | Service: ",
    "price" => " | Price: "
];
display($sql, $column_mappings, $page);
?>
</section>
</div>

<?php include "../header_footer/footer.php"; ?>
