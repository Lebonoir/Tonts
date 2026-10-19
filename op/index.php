<?php
include "databasemodel/model.php";
table(); // creates the tables + default admin if they don't exist yet

if (isset($_GET['logout'])) { // the Logout link in the header
    session_destroy();
    header("Location: index.php");
    exit;
}

// already logged in? go to your own page (use the Logout link to log out)
if (isset($_SESSION['role'])) {
    header("Location: " . $pages[$_SESSION['role']]);
    exit;
}

$error = "";
$register = isset($_GET['register']); // index.php?register=1 shows the customer registration form instead of the login

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // look for a matching account
    $sql = "SELECT * FROM accounts WHERE username = '$username'";
    $result = mysqli_query($con, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);

        if (password_verify($password, $row['password'])) { // passwords are hashed
            // save login info in session so other pages know who's logged in
            session_regenerate_id(true);
            $_SESSION['id'] = $row['id'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['role'] = $row['role'];

            // redirect based on role
            header("Location: " . $pages[$row['role']]);
            exit;
        }
    }
    $error = "Invalid username or password.";
}

if (isset($_POST['register'])) {
    $fn = $_POST['fullname'];
    $un = $_POST['username'];
    $pw = $_POST['password'];
    $c  = $_POST['contact'];
    $e  = $_POST['email'];
    $r  = "customer"; // self-registration is always customer

    $error = validate_account($fn, $un, $pw, $c, $e);

    if ($error == "") {
        $pw = password_hash($pw, PASSWORD_DEFAULT);
        $directory = "img/";
        $insert = "INSERT INTO accounts
                   (fullname, username, password, role, contact, email)
                   VALUES ('$fn', '$un', '$pw', '$r', '$c', '$e')";
        $tname = "accounts"; // same name delete() uses, so deleting an account removes its picture
        save($insert, $tname, $directory);
        header("Location: index.php");
        exit();
    }
}

include "header_footer/header.php";
?>

<div class="auth">
    <aside class="auth-art" aria-hidden="true">
        <div class="mesh"><i></i><i></i><i></i></div>
        <div class="auth-copy">
            <p class="eyebrow">Appointment System</p>
            <p class="auth-title">Pick a time.<br>We confirm it.</p>
            <p class="auth-sub">Book a service, follow its status and get notified the moment staff approve it.</p>
        </div>
        <div class="mini-board">
            <div class="mini-row"><span>09:00</span><em class="ok">Available</em></div>
            <div class="mini-row"><span>10:00</span><em class="taken">Booked</em></div>
            <div class="mini-row"><span>11:00</span><em class="ok">Available</em></div>
            <div class="mini-row"><span>13:00</span><em class="wait">Pending</em></div>
        </div>
    </aside>

    <section class="auth-panel">
<?php if ($register) { ?>
    <h2>Customer Registration</h2>
    <?php if ($error != "") { echo "<div class='alert error'>$error</div>"; } ?>
    <form method="post" enctype="multipart/form-data">
        <label class="field"><span>Full name</span><input type="text" name="fullname" required></label>
        <label class="field"><span>Username</span><input type="text" name="username" required></label>
        <label class="field"><span>Password</span><input type="password" name="password" required></label>
        <div class="field-row">
            <label class="field"><span>Contact</span><input type="text" name="contact" maxlength="11" required></label>
            <label class="field"><span>Email</span><input type="email" name="email" required></label>
        </div>
        <label class="field"><span>Photo</span><input type="file" name="photo" accept="image/*"></label>
        <button type="submit" name="register">Register</button>
    </form>
    <a class="text-link" href="index.php">Back to login</a>
<?php } else { ?>
    <h2>Login</h2>
    <?php if ($error != "") { echo "<div class='alert error'>$error</div>"; } ?>
    <form method="post">
        <label class="field"><span>Username</span><input type="text" name="username" required></label>
        <label class="field"><span>Password</span><input type="password" name="password" required></label>
        <button type="submit" name="login">Login</button>
    </form>
    <a class="text-link" href="index.php?register=1">Create customer account</a>
<?php } ?>
    </section>
</div>

<?php include "header_footer/footer.php"; ?>
