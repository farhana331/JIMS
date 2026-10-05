<?php

session_start();

require_once "../config/database.php";


// ONLY COMPANY CAN ACCESS

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] != "company"
) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// GET COMPANY ID


$stmt = $conn->prepare(
    "SELECT company_id
     FROM companies
     WHERE user_id = ?"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$company = $result->fetch_assoc();

$stmt->close();


if (!$company) {
    die("Company profile not found.");
}

$company_id = $company["company_id"];



// GET JOB ID
// ==========================
// Supports both:
// close_job.php?id=5
// and
// POST job_id=5


$job_id = $_GET["id"] ?? $_POST["job_id"] ?? 0;


if (
    !is_numeric($job_id) ||
    $job_id <= 0
) {
    die("Invalid job ID.");
}

$job_id = (int)$job_id;

// VERIFY JOB BELONGS TO COMPANY

$stmt = $conn->prepare(
    "SELECT
        job_id,
        title,
        status
     
     FROM jobs
     
     WHERE job_id = ?
     AND company_id = ?"
);

$stmt->bind_param(
    "ii",
    $job_id,
    $company_id
);

$stmt->execute();

$result = $stmt->get_result();

$job = $result->fetch_assoc();

$stmt->close();


if (!$job) {

    die(
        "Job not found or unauthorized access."
    );

}

// CHECK STATUS

if ($job["status"] != "approved") {

    die(
        "Only approved vacancies can be closed."
    );

}

// CLOSE VACANCY

$stmt = $conn->prepare(
    "UPDATE jobs
     
     SET status = 'closed'
     
     WHERE job_id = ?
     AND company_id = ?
     AND status = 'approved'"
);

$stmt->bind_param(
    "ii",
    $job_id,
    $company_id
);


if ($stmt->execute()) {

    header(
        "Location: jobs.php?closed=1"
    );

    exit();

} else {

    die(
        "Failed to close vacancy."
    );

}

$stmt->close();

?>