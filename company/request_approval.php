<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "company") {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

$stmt = $conn->prepare(
    "SELECT company_id, approval_status, approval_requested
     FROM companies
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$company = $result->fetch_assoc();
$stmt->close();

if (!$company) {

    $message = "Company profile not found.";

} elseif ($company["approval_status"] == "approved") {

    $message = "Your company is already approved.";

} elseif ($company["approval_status"] == "rejected") {

    $message = "Your company approval was rejected.";

} elseif ($company["approval_requested"] == 1) {

    $message = "Your approval request is already sent. Please wait for admin approval.";

} else {

    $company_id = $company["company_id"];

    $stmt = $conn->prepare(
        "UPDATE companies
         SET approval_requested = 1
         WHERE company_id = ?"
    );

    $stmt->bind_param("i", $company_id);

    if ($stmt->execute()) {
        $message = "Approval request sent to admin successfully.";
    } else {
        $message = "Failed to send approval request.";
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Request Approval - JIMS</title>
</head>

<body>

<h1>Company Approval Request</h1>

<p>
    <?php echo htmlspecialchars($message); ?>
</p>

<a href="dashboard.php">Back to Dashboard</a>

</body>

</html>