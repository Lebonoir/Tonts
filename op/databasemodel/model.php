<?php
session_start();
date_default_timezone_set("Asia/Manila"); // so the "date already passed" check uses the right time

// ===== SETTINGS =====
// prevent browser from caching pages (so pressing back after logout won't show them)
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// where each role goes after login (used by index.php and the header logo)
$pages = [
    "admin"    => "admin/admin_dashboard.php",
    "staff"    => "staff/staff_dashboard.php",
    "customer" => "customer/customer_dashboard.php"
];

// times that can be booked (opening hours, hourly), change these to fit your schedule
$slots = ["09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00"];

// ===== DATABASE CONNECTION =====
try{
    $con = mysqli_connect("localhost","root","", "sa");
}catch(mysqli_sql_exception){
    echo "cannot connect";
}

// ===== BASIC FUNCTIONS (save, update, delete, get one row) =====
function save($query,$table,$directory) {
    global $con;
    mysqli_query($con, $query);
    $pid = mysqli_insert_id($con);
    $imgnm = "$pid.jpg";
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        move_uploaded_file($_FILES['photo']['tmp_name'], "$directory$table$imgnm");
    }
}

function update($query) { // reusable update, just pass the UPDATE query
    global $con;
    mysqli_query($con, $query);
}

function delete($table, $id, $directory) {
    global $con;

    $sql = "DELETE FROM $table WHERE id = $id";
    mysqli_query($con, $sql);

    $imgnm = "$id.jpg";
    $pictodelete = "$directory$table$imgnm";

    if (file_exists($pictodelete)) {
        unlink($pictodelete);
    }
}

function get_row($table, $id) { // get one record by id (used for edit forms)
    global $con;
    $result = mysqli_query($con, "SELECT * FROM $table WHERE id = $id");
    return mysqli_fetch_array($result);
}

// ===== TABLE CREATION =====
function add_column($table, $column, $definition) { // adds a column only if the table doesn't have it yet
    global $con;
    $check = mysqli_query($con, "SHOW COLUMNS FROM $table LIKE '$column'");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($con, "ALTER TABLE $table ADD $column $definition");
    }
}

function table(){ // table creation function (only works if invoked and if tables are not existing yet)
    global $con;
    mysqli_query($con, "CREATE TABLE IF NOT EXISTS accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fullname VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20),
        contact VARCHAR(11) NOT NULL DEFAULT '',
        email VARCHAR(120) NOT NULL DEFAULT ''
    )");

    // for databases made before these columns existed
    add_column("accounts", "contact", "VARCHAR(11) NOT NULL DEFAULT ''");
    add_column("accounts", "email", "VARCHAR(120) NOT NULL DEFAULT ''");

    mysqli_query($con, "CREATE TABLE IF NOT EXISTS appointments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        account_id INT NOT NULL DEFAULT 0,
        name VARCHAR(100) NOT NULL,
        contact VARCHAR(11) NOT NULL,
        email VARCHAR(120),
        date DATE NOT NULL,
        time TIME NOT NULL,
        service VARCHAR(100) NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        cancel_requested TINYINT NOT NULL DEFAULT 0,
        agreed TINYINT NOT NULL DEFAULT 0 -- 1 = customer agreed to the booking policy
    )");

    // older databases were created before the agreed column existed, so add it if it's missing
    $check = mysqli_query($con, "SHOW COLUMNS FROM appointments LIKE 'agreed'");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($con, "ALTER TABLE appointments ADD agreed TINYINT NOT NULL DEFAULT 0");
    }

    mysqli_query($con, "CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL
    )");

    mysqli_query($con, "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        account_id INT NOT NULL,
        message VARCHAR(255) NOT NULL,
        seen TINYINT DEFAULT 0,
        created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // default admin so you can log in the first time (admin / admin123)
    if (!check_duplicate("accounts", "username", "admin")) {
        $pw = password_hash("admin123", PASSWORD_DEFAULT);
        mysqli_query($con, "INSERT INTO accounts (fullname, username, password, role)
                            VALUES ('Administrator', 'admin', '$pw', 'admin')");
    }
}

// ===== DISPLAYING AND COUNTING =====
// reusable function for displaying records
// $url   = page for the delete link (leave "" for no delete link)
// $edit  = page for the edit link   (leave "" for no edit link)
// $extra = any other links, example: ["confirm" => "page.php"] makes a confirm link to page.php?confirmid=ID
function display($sql, $column_mappings, $url, $edit = "", $extra = []) {
    global $con;

    $result = mysqli_query($con, $sql);
    if (mysqli_num_rows($result) == 0) {
        echo "<p class='empty'>No records found.</p>";
        return;
    }

    while ($row = mysqli_fetch_array($result)) {
        echo "<div class='record'>"; // one card per record, see style.css
        foreach ($column_mappings as $column_name => $label) {
            $value = $row[$column_name];
            if ($column_name == "time") { // 10:00:00 -> 10:00
                $value = substr($value, 0, 5);
            }
            if ($column_name == "status" || $column_name == "shown_status") { // status shows as a colored badge
                $value = "<span class='badge " . str_replace(" ", "-", $value) . "'>$value</span>";
            }
            echo $label . $value;
        }

        $id = $row['id'];
        foreach ($extra as $label => $page) {
            echo " <a href='$page?{$label}id=$id'>$label</a> ";
        }
        if ($edit != "") {
            echo " <a href='$edit?id=$id'>edit</a> ";
        }
        if ($url != "") {
            echo " <a href='$url?deleteid=$id'>delete</a> ";
        }
        echo "</div>";
    }
}

function check_duplicate($table, $column, $value) { // for duplicate checking/validations
    global $con;

    $result = mysqli_query($con, "SELECT * FROM $table WHERE $column = '$value'");
    return mysqli_num_rows($result) > 0;
}

function count_rows($table, $where = "1") { // count records, example: count_rows("accounts", "role = 'staff'")
    global $con;
    $result = mysqli_query($con, "SELECT COUNT(*) AS total FROM $table WHERE $where");
    $row = mysqli_fetch_array($result);
    return $row['total'];
}

// ===== DROPDOWNS =====
function list_options($values, $selected = "") { // dropdown options from a plain list, example: list_options(["staff", "admin"], $role)
    foreach ($values as $value) {
        $mark = "";
        if ($value == $selected) {
            $mark = "selected";
        }
        echo "<option value='$value' $mark>$value</option>";
    }
}

function options($table, $column, $selected = "") { // dropdown options from any table column
    global $con;
    $values = [];
    $result = mysqli_query($con, "SELECT $column FROM $table ORDER BY $column");
    while ($row = mysqli_fetch_array($result)) {
        $values[] = $row[$column];
    }
    list_options($values, $selected);
}

// ===== NOTIFICATIONS =====
function notify($account_id, $message) { // send a notification to one account
    global $con;
    $message = mysqli_real_escape_string($con, $message);
    mysqli_query($con, "INSERT INTO notifications (account_id, message) VALUES ('$account_id', '$message')");
}

function notify_staff($message) { // send a notification to every admin and staff account
    global $con;
    $message = mysqli_real_escape_string($con, $message);
    mysqli_query($con, "INSERT INTO notifications (account_id, message) SELECT id, '$message' FROM accounts WHERE role IN ('admin', 'staff')");
}

function notify_appointment($id) { // tell the customer the current status of their appointment
    $row = get_row("appointments", $id);
    if ($row['account_id'] > 0) { // 0 = walk-in, no account to notify
        notify($row['account_id'], "Your appointment for {$row['service']} on {$row['date']} at {$row['time']} is now {$row['status']}.");
    }
}

// ===== VALIDATORS (they return an error message, or "" if everything is fine) =====
function validate_contact($contact, $email) { // used by accounts and appointments
    if (!preg_match('/^09[0-9]{9}$/', $contact)) {
        return "Contact number must be 11 digits and start with 09.";
    }
    if ($email != "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email address.";
    }
    return "";
}

function validate_account($fn, $un, $pw, $contact, $email, $old_username = "") { // $old_username is only given when editing an existing account
    if (empty($fn) || empty($un) || empty($contact) || empty($email) || ($old_username == "" && empty($pw))) {
        return "Please fill out all fields.";
    }
    $error = validate_contact($contact, $email);
    if ($error != "") {
        return $error;
    }
    if (!preg_match('/^[A-Za-z0-9_]{4,20}$/', $un)) {
        return "Username must be 4-20 letters, numbers or underscore.";
    }
    if ($pw != "" && strlen($pw) < 6) {
        return "Password must be at least 6 characters.";
    }
    if ($un != $old_username && check_duplicate("accounts", "username", $un)) {
        return "Username already taken.";
    }
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0 && !getimagesize($_FILES['photo']['tmp_name'])) {
        return "The photo must be an image file.";
    }
    return "";
}

function validate_appointment($name, $contact, $email, $date, $time, $service, $check_past = true) {
    global $slots; // the times that can be booked
    if (empty($name) || empty($service)) {
        return "Please fill out all fields.";
    }
    if (!check_duplicate("services", "name", $service)) { // the service has to be one the admin made
        return "Please choose one of the available services.";
    }
    $error = validate_contact($contact, $email);
    if ($error != "") {
        return $error;
    }
    if (!in_array($time, $slots)) {
        return "Please choose one of the available times.";
    }
    if ($check_past && "$date $time" < date("Y-m-d H:i")) {
        return "That date and time has already passed.";
    }
    return "";
}

function slot_taken($date, $time, $id = 0) { // true if another approved appointment already has that date and time
    global $con;
    $result = mysqli_query($con, "SELECT * FROM appointments WHERE date = '$date' AND time = '$time' AND status = 'approved' AND id != $id");
    return mysqli_num_rows($result) > 0;
}

?>
