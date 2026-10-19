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

<?php if ($register) { ?>
    <h2>Customer Registration</h2>
    <?php echo $error; ?>
    <form method="post" enctype="multipart/form-data">
        Full name: <input type="text" name="fullname" required><br>
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        Contact: <input type="text" name="contact" maxlength="11" required><br>
        Email: <input type="email" name="email" required><br>
        Photo: <input type="file" name="photo" accept="image/*"><br>
        <button type="submit" name="register">Register</button>
    </form>
    <a href="index.php">Back to login</a>
<?php } else { ?>
    <h2>Login</h2>
    <?php echo $error; ?>
    <form method="post">
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        <button type="submit" name="login">Login</button>
    </form>
    <a href="index.php?register=1">Create customer account</a>
<?php } ?>

<?php include "header_footer/footer.php"; ?>
