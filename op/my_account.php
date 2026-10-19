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
if ($error != "") {
    echo "<div class='alert error'>$error</div>";
}
if (isset($_GET['saved'])) {
    echo "<div class='alert success'>Account updated.</div>";
}
?>

<?php if (isset($_GET['edit'])) { // the update form only shows after pressing Update ?>
<section class="panel narrow">
    <form method="post">
        <label class="field"><span>Full name</span><input type="text" name="fullname" value="<?php echo $row['fullname']; ?>" required></label>
        <label class="field"><span>Username</span><input type="text" name="username" value="<?php echo $row['username']; ?>" required></label>
        <div class="field-row">
            <label class="field"><span>Contact</span><input type="text" name="contact" value="<?php echo $row['contact']; ?>" maxlength="11" required></label>
            <label class="field"><span>Email</span><input type="email" name="email" value="<?php echo $row['email']; ?>" required></label>
        </div>
        <label class="field"><span>New password</span><input type="password" name="password"><small>Leave blank to keep current</small></label>
        <p class="role-line">Role <span class="role-chip"><?php echo $row['role']; ?></span></p>
        <div class="form-actions">
        <button type="submit" name="update">Save</button>
        <a href="my_account.php">Cancel</a>
        </div>
    </form>
</section>
<?php } else { ?>
<section class="profile">
    <div class="profile-head">
        <span class="avatar big" aria-hidden="true"><?php echo htmlspecialchars(strtoupper(substr($row['fullname'], 0, 1))); ?></span>
        <div>
            <p class="profile-name"><?php echo $row['fullname']; ?></p>
            <span class="role-chip"><?php echo $row['role']; ?></span>
        </div>
    </div>
    <dl class="profile-list">
        <div><dt>Full name</dt><dd><?php echo $row['fullname']; ?></dd></div>
        <div><dt>Username</dt><dd><?php echo $row['username']; ?></dd></div>
        <div><dt>Contact</dt><dd class="mono"><?php echo $row['contact']; ?></dd></div>
        <div><dt>Email</dt><dd><?php echo $row['email']; ?></dd></div>
        <div><dt>Role</dt><dd><?php echo $row['role']; ?></dd></div>
    </dl>
    <a href="my_account.php?edit=1">Update</a>
</section>
<?php } ?>

<?php include "header_footer/footer.php"; ?>
