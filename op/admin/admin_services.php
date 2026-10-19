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
    echo "Do you really want to delete record with ID of $id? <a href='?yesdelete=$id'>Yes</a> | <a href='$page'>No</a>";
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
<?php echo $error; ?>

<form method="post">
    Service name: <input type="text" name="name" required><br>
    Price: <input type="number" name="price" step="0.01" required><br>
    <button type="submit" name="save">Add service</button>
</form>
<hr>

<?php
$sql = "SELECT * FROM services";
$column_mappings = [
    "id"    => "ID: ",
    "name"  => " | Service: ",
    "price" => " | Price: "
];
display($sql, $column_mappings, $page);
?>

<?php include "../header_footer/footer.php"; ?>
