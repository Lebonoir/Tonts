<?php
include "../databasemodel/model.php";
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') { // only logged in admin can open this page
    header("Location: ../index.php");
    exit;
}

$page = "admin_acc_manage.php"; // this page's own name, used for the links and redirects
$error = "";

// the edit link adds ?id=5 to the address (no id means adding a new account)
$id = 0;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}

// what the form shows at first: empty when adding, the saved info when editing
$title = "Add Account";
$row = ["fullname" => "", "username" => "", "contact" => "", "email" => "", "role" => "staff"];
$old_username = "";
if ($id != 0) {
    $title = "Edit Account";
    $row = get_row("accounts", $id);
    $old_username = $row['username'];
}

// when the Save button is pressed (add or edit)
if (isset($_POST['save'])) {
    $fn = $_POST['fullname'];
    $un = $_POST['username'];
    $pw = $_POST['password'];
    $c  = $_POST['contact'];
    $e  = $_POST['email'];
    $r  = $_POST['role'];

    if ($id == $_SESSION['id']) { // admin can't change own role
        $r = "admin";
    }

    $error = validate_account($fn, $un, $pw, $c, $e, $old_username);

    if ($error == "") {
        if ($id == 0) { // add a new account
            $pw = password_hash($pw, PASSWORD_DEFAULT);
            $directory= "../img/";
            $insert = "INSERT INTO accounts (fullname, username, password, role, contact, email) VALUES ('$fn', '$un', '$pw', '$r', '$c', '$e')";
            $tname = "accounts"; // same name delete() uses, so deleting an account removes its picture
            save($insert,$tname,$directory);
        } else { // edit the account
            update("UPDATE accounts SET fullname = '$fn', username = '$un', contact = '$c', email = '$e', role = '$r' WHERE id = $id");
            if ($pw != "") { // a blank password means keep the old one
                $pw = password_hash($pw, PASSWORD_DEFAULT);
                update("UPDATE accounts SET password = '$pw' WHERE id = $id");
            }
        }
        header("Location: $page");
        exit;
    }
    $row = $_POST; // there was an error, so show what was typed again
}

//updated delete w/ confirmation
if (isset($_GET['deleteid'])) {
    $id = $_GET['deleteid'];
    echo "Do you really want to delete record with ID of $id? <a href='?yesdelete=$id'>Yes</a> | <a href='$page'>No</a>";
    exit();
}

if (isset($_GET['yesdelete'])) {
    if ($_GET['yesdelete'] != $_SESSION['id']) { // admin can't delete own account
        $directory= "../img/";
        delete("accounts", $_GET['yesdelete'], $directory);
    }
    header("Location: $page");
    exit();
}

include "../header_footer/header.php";
?>

<h2>Manage Accounts</h2>
<?php
echo $error;
echo "<h3>$title</h3>";
?>
<form method="post" enctype="multipart/form-data">
    Full name: <input type="text" name="fullname" value="<?php echo $row['fullname']; ?>" required><br>
    Username: <input type="text" name="username" value="<?php echo $row['username']; ?>" required><br>
    Contact: <input type="text" name="contact" value="<?php echo $row['contact']; ?>" maxlength="11" required><br>
    Email: <input type="email" name="email" value="<?php echo $row['email']; ?>" required><br>

    <?php if ($id == 0) { ?>
        Password: <input type="password" name="password" required><br>
        Photo: <input type="file" name="photo" accept="image/*"><br>
    <?php } else { ?>
        Password: <input type="password" name="password"> (leave blank to keep current)<br>
    <?php } ?>

    Role: <select name="role"><?php list_options(["staff", "admin", "customer"], $row['role']); ?></select><br>
    <button type="submit" name="save">Save</button>
    <?php
    if ($id != 0) {
        echo "<a href='$page'>Cancel</a>";
    }
    ?>
</form>
<hr>

<?php
$sql = "SELECT * FROM accounts";
$column_mappings = [
    "id"       => "ID: ",
    "fullname" => " | Name: ",
    "username" => " | Username: ",
    "role"     => " | Role: "
];
display($sql, $column_mappings, $page, $page); // reusable display (accounts)
?>

<?php include "../header_footer/footer.php"; ?>
