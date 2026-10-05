<?php

session_start();

require_once "../config/database.php";

// ONLY ADMIN CAN ACCESS

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../auth/login.php");
    exit();
}

// GET DATA

$user_id = $_GET["id"] ?? 0;
$action = $_GET["action"] ?? "";

// VALIDATE USER ID


if (!is_numeric($user_id) || $user_id <= 0) {
    die("Invalid student ID.");
}

// VALIDATE ACTION


if ($action != "activate" && $action != "deactivate") {
    die("Invalid action.");
}

// MAKE SURE USER IS A STUDENT

$stmt = $conn->prepare(
    "SELECT user_id
     FROM users
     WHERE user_id = ?
     AND role = 'student'"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$student = $result->fetch_assoc();

$stmt->close();


if (!$student) {
    die("Student not found.");
}


// SET ACCOUNT STATUS

if ($action == "activate") {

    $status = "active";

} else {

    $status = "inactive";

}

// UPDATE ACCOUNT STATUS

$stmt = $conn->prepare(
    "UPDATE users
     SET account_status = ?
     WHERE user_id = ?
     AND role = 'student'"
);

$stmt->bind_param(
    "si",
    $status,
    $user_id
);


if (!$stmt->execute()) {

    $stmt->close();

    die("Failed to update student account.");

}


$stmt->close();

// REDIRECT


header("Location: students.php");

exit();

?>