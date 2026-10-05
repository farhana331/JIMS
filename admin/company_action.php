<?php

session_start();

require_once "../config/database.php";

// Only admin can access
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: companies.php");
    exit();
}

$company_id = intval($_POST["company_id"]);
$action = $_POST["action"];

if ($company_id <= 0) {
    header("Location: companies.php");
    exit();
}

if ($action == "approve") {

    $newStatus = "approved";

} elseif ($action == "reject") {

    $newStatus = "rejected";

} else {

    header("Location: companies.php");
    exit();
}

$stmt = $conn->prepare(
    "UPDATE companies
     SET approval_status = ?
     WHERE company_id = ?"
);

$stmt->bind_param(
    "si",
    $newStatus,
    $company_id
);

if ($stmt->execute()) {
    $message = "Company status updated successfully.";
} else {
    $message = "Failed to update company status.";
}

$stmt->close();

header("Location: companies.php");
exit();

?>