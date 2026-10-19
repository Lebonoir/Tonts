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
$new = 0;
if ($loggedIn) {
    $new = count_rows("notifications", "account_id = " . $_SESSION['id'] . " AND seen = 0");
    if ($new > 0) {
        $notif_text = "Notifications ($new)";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Book, confirm and manage appointments.">
    <title>Appointment System</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='9' fill='%230f766e'/%3E%3Cpath d='M9 13h14M12 8v4M20 8v4M10 11h12a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H10a1 1 0 0 1-1-1V12a1 1 0 0 1 1-1Z' fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?php echo $base; ?>design/style.css">
    <script>document.documentElement.classList.add("js");</script>
    <script src="<?php echo $base; ?>design/app.js" defer></script>
</head>
<body class="<?php echo $loggedIn ? "role-" . $role : "guest"; ?>">
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header">
    <nav>
        <a href="<?php echo $base . $home; ?>" class="logo">
            <span class="logo-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M5 9.5h14M8.5 3.5v3M15.5 3.5v3M6 6h12a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z"/><path class="tick" d="m9.5 14.5 2 2 3.5-4"/></svg>
            </span>
            <span class="logo-text">Appointment System</span>
        </a>

        <ul class="nav-links">
            <?php if ($loggedIn) { ?>

                <?php if ($role === "admin") { ?>
                    <li><a href="<?php echo $base; ?>admin/admin_dashboard.php"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="4" rx="1.5"/><rect x="13" y="10" width="7" height="10" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/></svg><span>Dashboard</span></a></li>
                    <li><a href="<?php echo $base; ?>admin/admin_appointments.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 9.5h14M8.5 3.5v3M15.5 3.5v3M6 6h12a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z"/></svg><span>Appointments</span></a></li>
                    <li><a href="<?php echo $base; ?>admin/admin_acc_manage.php"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8.5" r="3.5"/><path d="M3 19.5c.8-3.2 3.2-5 6-5s5.2 1.8 6 5M15.5 5.2a3.5 3.5 0 0 1 0 6.6M17.5 14.8c1.7.6 3 2.2 3.5 4.7"/></svg><span>Manage Accounts</span></a></li>
                    <li><a href="<?php echo $base; ?>admin/admin_services.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3.8 12.6V4.8a1 1 0 0 1 1-1h7.8l7.6 7.6a1 1 0 0 1 0 1.4l-7.8 7.8a1 1 0 0 1-1.4 0Z"/><circle cx="8.5" cy="8.5" r="1.4"/></svg><span>Manage Services</span></a></li>
                <?php } ?>

                <?php if ($role === "staff") { ?>
                    <li><a href="<?php echo $base; ?>staff/staff_dashboard.php"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="4" rx="1.5"/><rect x="13" y="10" width="7" height="10" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/></svg><span>Dashboard</span></a></li>
                    <li><a href="<?php echo $base; ?>staff/staff_appointments.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 9.5h14M8.5 3.5v3M15.5 3.5v3M6 6h12a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z"/></svg><span>Appointments</span></a></li>
                <?php } ?>

                <?php if ($role === "customer") { ?>
                    <li><a href="<?php echo $base; ?>customer/customer_dashboard.php"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="4" rx="1.5"/><rect x="13" y="10" width="7" height="10" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/></svg><span>Dashboard</span></a></li>
                    <li><a href="<?php echo $base; ?>customer/customer_book.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 9.5h14M8.5 3.5v3M15.5 3.5v3M6 6h12a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1ZM12 12.5v5M9.5 15h5"/></svg><span>Book Appointment</span></a></li>
                <?php } ?>

                <li><a href="<?php echo $base; ?>availability.php"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg><span>Available Times</span></a></li>
                <li><a href="<?php echo $base; ?>notifications.php" aria-label="<?php echo $notif_text; ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 16.5V11a6 6 0 0 1 12 0v5.5l1.5 2h-15ZM10 20.5a2.2 2.2 0 0 0 4 0"/></svg><span>Notifications</span><?php if ($new > 0) { ?><b class="nav-count"><?php echo $new; ?></b><?php } ?></a></li>
                <li><a href="<?php echo $base; ?>my_account.php"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="9" r="3.8"/><path d="M5 20c1-3.6 3.8-5.6 7-5.6s6 2 7 5.6"/></svg><span>My Account</span></a></li>
                <li class="user-info"><span class="avatar" aria-hidden="true"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['fullname'], 0, 1))); ?></span><span class="user-text"><span class="user-name"><?php echo htmlspecialchars($_SESSION['fullname']); ?></span> <span class="user-role">(<?php echo $role; ?>)</span></span></li>
                <li class="logout"><a href="<?php echo $base; ?>index.php?logout=1" title="Logout"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 4.5H6a1 1 0 0 0-1 1v13a1 1 0 0 0 1 1h8M16 8l4 4-4 4M20 12H10"/></svg><span>Logout</span></a></li>
            <?php } else { ?>
                <li><a href="<?php echo $base; ?>index.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 4.5h8a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-8M4 12h10M11 8l4 4-4 4"/></svg><span>Login</span></a></li>
            <?php } ?>
        </ul>
    </nav>
</header>

<main class="content" id="main">
