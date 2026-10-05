<?php

session_start();

require_once "../config/database.php";

// ========================================
// ONLY ADMIN CAN ACCESS
// ========================================

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../auth/login.php");
    exit();
}


// ========================================
// GET JOB ID AND ACTION
// ========================================

$job_id = $_GET["id"] ?? 0;
$action = $_GET["action"] ?? "";


// ========================================
// VALIDATE JOB ID
// ========================================

if (!is_numeric($job_id) || $job_id <= 0) {
    die("Invalid job ID.");
}


// ========================================
// DETERMINE NEW STATUS
// ========================================

if ($action == "approve") {

    $new_status = "approved";

} elseif ($action == "reject") {

    $new_status = "rejected";

} else {

    die("Invalid action.");

}


// ========================================
// UPDATE JOB STATUS
// ========================================

$stmt = $conn->prepare(
    "UPDATE jobs
     SET status = ?
     WHERE job_id = ?"
);

$stmt->bind_param(
    "si",
    $new_status,
    $job_id
);


if ($stmt->execute()) {

    header("Location: jobs.php");
    exit();

} else {

    echo "Failed to update job status.";

}


$stmt->close();

?>