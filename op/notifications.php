<?php
include "databasemodel/model.php";
if (!isset($_SESSION['id'])) { // any logged in user can open this page
    header("Location: index.php");
    exit;
}

$id = $_SESSION['id']; // always the logged in account's own notifications

if (isset($_GET['markread'])) {
    update("UPDATE notifications SET seen = 1 WHERE account_id = $id");
    header("Location: notifications.php");
    exit();
}

if (isset($_GET['clear'])) {
    update("DELETE FROM notifications WHERE account_id = $id");
    header("Location: notifications.php");
    exit();
}

include "header_footer/header.php";
?>

<h2>Notifications</h2>
<a href="notifications.php?markread=1">Mark all as read</a> |
<a href="notifications.php?clear=1">Clear all</a>
<hr>

<?php
$sql = "SELECT *, IF(seen = 0, '[NEW] ', '') AS tag FROM notifications WHERE account_id = $id ORDER BY id DESC";
$column_mappings = [
    "tag"     => "",
    "message" => "",
    "created" => " | "
];
display($sql, $column_mappings, ""); // no links, just the list
?>

<?php include "header_footer/footer.php"; ?>
