<?php
include "databasemodel/model.php";
if (!isset($_SESSION['id'])) { // any logged in user can open this page
    header("Location: index.php");
    exit;
}

$id = $_SESSION['id']; // always the logged in account, nobody else's
$row = get_row("accounts", $id);
$error = "";

// when the Save button is pressed
if (isset($_POST['update'])) {
    $fn = $_POST['fullname'];
    $un = $_POST['username'];
    $pw = $_POST['password'];
    $c  = $_POST['contact'];
    $e  = $_POST['email'];

    $error = validate_account($fn, $un, $pw, $c, $e, $row['username']);

    if ($error == "") {
        update("UPDATE accounts SET fullname = '$fn', username = '$un', contact = '$c', email = '$e' WHERE id = $id");
        if ($pw != "") { // a blank password means keep the old one
            $pw = password_hash($pw, PASSWORD_DEFAULT);
            update("UPDATE accounts SET password = '$pw' WHERE id = $id");
        }
        $_SESSION['fullname'] = $fn; // so the header shows the new name
        header("Location: my_account.php?saved=1");
        exit();
    }
}

include "header_footer/header.php";
?>

<h2>My Account</h2>
<?php
echo $error;
if (isset($_GET['saved'])) {
    echo "Account updated!<br>";
}
?>

<?php if (isset($_GET['edit'])) { // the update form only shows after pressing Update ?>
    <form method="post">
        Full name: <input type="text" name="fullname" value="<?php echo $row['fullname']; ?>" required><br>
        Username: <input type="text" name="username" value="<?php echo $row['username']; ?>" required><br>
        Contact: <input type="text" name="contact" value="<?php echo $row['contact']; ?>" maxlength="11" required><br>
        Email: <input type="email" name="email" value="<?php echo $row['email']; ?>" required><br>
        New password: <input type="password" name="password"> (leave blank to keep current)<br>
        Role: <?php echo $row['role']; ?><br>
        <button type="submit" name="update">Save</button>
        <a href="my_account.php">Cancel</a>
    </form>
<?php } else { ?>
    Full name: <?php echo $row['fullname']; ?><br>
    Username: <?php echo $row['username']; ?><br>
    Contact: <?php echo $row['contact']; ?><br>
    Email: <?php echo $row['email']; ?><br>
    Role: <?php echo $row['role']; ?><br>
    <a href="my_account.php?edit=1">Update</a>
<?php } ?>

<?php include "header_footer/footer.php"; ?>
