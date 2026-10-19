<?php
include "../databasemodel/model.php";
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') { // only logged in admin can open this page
    header("Location: ../index.php");
    exit;
}

$page = "admin_appointments.php";
include "../appointments_manage.php"; // same appointment page for admin and staff
