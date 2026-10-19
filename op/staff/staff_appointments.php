<?php
include "../databasemodel/model.php";
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'staff') { // only logged in staff can open this page
    header("Location: ../index.php");
    exit;
}

$page = "staff_appointments.php";
include "../appointments_manage.php"; // same appointment page for admin and staff
