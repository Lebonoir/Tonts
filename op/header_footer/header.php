<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$loggedIn = isset($_SESSION['id']);
$role = $loggedIn ? $_SESSION['role'] : null;

// pages inside the admin/staff/customer folders need "../" to reach the main folder
$base = "";
$folder = basename(dirname($_SERVER['SCRIPT_NAME']));
if ($folder == "admin" || $folder == "staff" || $folder == "customer") {
    $base = "../";
}

// where the logo takes each role ($pages comes from the model)
$home = "index.php";
if ($loggedIn) {
    $home = $pages[$role];
}

// the Notifications link shows how many unseen notifications this account has
$notif_text = "Notifications";
if ($loggedIn) {
    $new = count_rows("notifications", "account_id = " . $_SESSION['id'] . " AND seen = 0");
    if ($new > 0) {
        $notif_text = "Notifications ($new)";
    }
}
?>
<link rel="stylesheet" href="<?php echo $base; ?>design/style.css">

<header class="site-header">
    <nav>
        <a href="<?php echo $base . $home; ?>" class="logo">Appointment System</a>

        <ul class="nav-links">
            <?php if ($loggedIn) { ?>

                <?php if ($role === "admin") { ?>
                    <li><a href="<?php echo $base; ?>admin/admin_dashboard.php">Dashboard</a></li>
                    <li><a href="<?php echo $base; ?>admin/admin_appointments.php">Appointments</a></li>
                    <li><a href="<?php echo $base; ?>admin/admin_acc_manage.php">Manage Accounts</a></li>
                    <li><a href="<?php echo $base; ?>admin/admin_services.php">Manage Services</a></li>
                <?php } ?>

                <?php if ($role === "staff") { ?>
                    <li><a href="<?php echo $base; ?>staff/staff_dashboard.php">Dashboard</a></li>
                    <li><a href="<?php echo $base; ?>staff/staff_appointments.php">Appointments</a></li>
                <?php } ?>

                <?php if ($role === "customer") { ?>
                    <li><a href="<?php echo $base; ?>customer/customer_dashboard.php">Dashboard</a></li>
                    <li><a href="<?php echo $base; ?>customer/customer_book.php">Book Appointment</a></li>
                <?php } ?>

                <li><a href="<?php echo $base; ?>availability.php">Available Times</a></li>
                <li><a href="<?php echo $base; ?>notifications.php"><?php echo $notif_text; ?></a></li>
                <li><a href="<?php echo $base; ?>my_account.php">My Account</a></li>
                <li class="user-info"><?php echo htmlspecialchars($_SESSION['fullname']); ?> (<?php echo $role; ?>)</li>
                <li><a href="<?php echo $base; ?>index.php?logout=1">Logout</a></li>
            <?php } else { ?>
                <li><a href="<?php echo $base; ?>index.php">Login</a></li>
            <?php } ?>
        </ul>
    </nav>
</header>

<div class="content">
