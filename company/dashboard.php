<?php

session_start();

require_once "../config/database.php";

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] != "company"
) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT company_id, company_name, approval_status
     FROM companies
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$company = $result->fetch_assoc();

$stmt->close();

if (!$company) {
    die("Company profile not found.");
}

$company_id = $company["company_id"];

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM jobs
     WHERE company_id = ?"
);

$stmt->bind_param("i", $company_id);
$stmt->execute();

$result = $stmt->get_result();

$total_jobs = $result->fetch_assoc()["total"];

$stmt->close();

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM jobs
     WHERE company_id = ?
     AND status = 'approved'"
);

$stmt->bind_param("i", $company_id);
$stmt->execute();

$result = $stmt->get_result();

$approved_jobs = $result->fetch_assoc()["total"];

$stmt->close();

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM jobs
     WHERE company_id = ?
     AND status = 'pending'"
);

$stmt->bind_param("i", $company_id);
$stmt->execute();

$result = $stmt->get_result();

$pending_jobs = $result->fetch_assoc()["total"];

$stmt->close();

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM applications a

     INNER JOIN jobs j
        ON a.job_id = j.job_id

     WHERE j.company_id = ?"
);

$stmt->bind_param("i", $company_id);
$stmt->execute();

$result = $stmt->get_result();

$total_applications = $result->fetch_assoc()["total"];

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Company Dashboard - JIMS</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            width: 100%;
            height: 100%;
        }

        body {

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            color: #1f2937;

            height: 100vh;

            overflow: hidden;

            background:

                radial-gradient(
                    circle at 90% 10%,
                    rgba(99, 130, 255, 0.35),
                    transparent 28%
                ),

                radial-gradient(
                    circle at 5% 85%,
                    rgba(120, 140, 255, 0.25),
                    transparent 25%
                ),

                linear-gradient(
                    135deg,
                    #edf5ff 0%,
                    #dce8ff 50%,
                    #e8edff 100%
                );

        }

        .header {

            height: 125px;

            background:
                rgba(30, 58, 138, 0.88);

            backdrop-filter:
                blur(16px);

            -webkit-backdrop-filter:
                blur(16px);

            color: white;

            padding:
                22px 6.5%;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.12);

            border-bottom:
                1px solid
                rgba(255, 255, 255, 0.2);

        }

        .header h1 {

            font-size: 30px;

            margin-bottom: 7px;

        }

        .header p {

            font-size: 15px;

            color: #dbeafe;

            margin-bottom: 4px;

        }

        .header .welcome {

            font-size: 16px;

            color: white;

        }

        .container {

            width: 79%;

            max-width: 1200px;

            height:
                calc(100vh - 125px);

            margin:
                0 auto;

            padding-top: 18px;

            display: flex;

            flex-direction: column;

        }

        .status-box {

            background:
                rgba(255, 255, 255, 0.55);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);

            border-radius: 14px;

            padding:
                15px 25px;

            margin-bottom: 15px;

            border:
                1px solid
                rgba(255, 255, 255, 0.75);

            border-left:
                5px solid #2563eb;

            box-shadow:
                0 7px 20px
                rgba(30, 64, 175, 0.08);

            min-height: 82px;

        }

        .status-box h2 {

            font-size: 20px;

            margin-bottom: 8px;

            color: #1f2937;

        }

        .status-box p {

            font-size: 15px;

        }

        .approved {

            color: #15803d;

            font-weight: bold;

        }

        .pending {

            color: #d97706;

            font-weight: bold;

        }

        .rejected {

            color: #dc2626;

            font-weight: bold;

        }

        .section-title {

            font-size: 23px;

            color: #172554;

            margin-bottom: 12px;

        }

        .overview-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 17px;

            margin-bottom: 16px;

        }

        .overview-card {

            text-decoration: none;

            color: inherit;

            background:
                rgba(255, 255, 255, 0.58);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);

            border:
                1px solid
                rgba(255, 255, 255, 0.8);

            border-radius: 15px;

            padding:
                18px 21px;

            height: 125px;

            display: flex;

            flex-direction: column;

            justify-content: center;

            box-shadow:
                0 7px 20px
                rgba(30, 64, 175, 0.08);

            transition:
                0.25s ease;

        }

        .overview-card:hover {

            transform:
                translateY(-5px);

            background:
                rgba(255, 255, 255, 0.78);

            box-shadow:
                0 12px 28px
                rgba(37, 99, 235, 0.15);

            border-color:
                rgba(37, 99, 235, 0.35);

        }

        .card-label {

            font-size: 14px;

            color: #64748b;

            font-weight: 600;

            margin-bottom: 6px;

        }

        .card-number {

            font-size: 35px;

            font-weight: bold;

            color: #1e3a8a;

        }

        .card-link {

            margin-top: 6px;

            font-size: 13px;

            color: #2563eb;

            font-weight: 600;

        }

        .menu-section {

            background:
                rgba(255, 255, 255, 0.55);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);

            border:
                1px solid
                rgba(255, 255, 255, 0.75);

            border-radius: 15px;

            padding:
                16px 25px;

            margin-bottom: 15px;

            box-shadow:
                0 7px 20px
                rgba(30, 64, 175, 0.08);

        }

        .menu-section h2 {

            color: #172554;

            font-size: 21px;

            margin-bottom: 12px;

        }

        .menu-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 13px;

        }

        .menu-button {

            display: block;

            text-decoration: none;

            text-align: center;

            padding:
                11px 15px;

            border-radius: 9px;

            background:
                rgba(239, 246, 255, 0.65);

            color: #1d4ed8;

            font-size: 14px;

            font-weight: 600;

            border:
                1px solid #bfdbfe;

            transition:
                0.25s ease;

        }

        .menu-button:hover {

            background:
                #2563eb;

            color: white;

            transform:
                translateY(-2px);

            box-shadow:
                0 7px 15px
                rgba(37, 99, 235, 0.18);

        }

        .disabled-message {

            color: #64748b;

            background:
                rgba(249, 250, 251, 0.6);

            padding: 10px;

            border-radius: 8px;

            font-size: 14px;

        }

        .logout-area {

            text-align: center;

            margin-top: auto;

            padding-bottom: 8px;

        }

        .logout {

            display: inline-block;

            text-decoration: none;

            color: #dc2626;

            font-size: 14px;

            font-weight: 600;

            padding:
                7px 18px;

            border:
                1px solid #fecaca;

            border-radius: 8px;

            background:
                rgba(255, 245, 245, 0.65);

            transition:
                0.25s ease;

        }

        .logout:hover {

            background:
                #dc2626;

            color: white;

        }

        @media (max-width: 1100px) {

            body {

                overflow: hidden;

            }

            .container {

                width: 90%;

            }

            .overview-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }

        @media (max-width: 700px) {

            body {

                overflow-y: auto;

            }

            .header {

                height: auto;

                padding:
                    20px 6%;

            }

            .container {

                width: 92%;

                height: auto;

                padding-bottom: 20px;

            }

            .overview-grid {

                grid-template-columns:
                    1fr;

            }

            .menu-grid {

                grid-template-columns:
                    1fr;

            }

            .overview-card {

                height: 110px;

            }

        }

    </style>

</head>

<body>

<header class="header">

    <h1>
        Company Dashboard
    </h1>

    <p class="welcome">

        Welcome,

        <strong>
            <?php
            echo htmlspecialchars(
                $company["company_name"]
            );
            ?>
        </strong>

        !

    </p>

    <p>
        You are logged in as a Company.
    </p>

</header>

<div class="container">

    <div class="status-box">

        <h2>
            Account Status
        </h2>

        <?php if (
            $company["approval_status"]
            == "approved"
        ): ?>

            <p class="approved">
                Your company account is
                Approved ✅
            </p>

        <?php elseif (
            $company["approval_status"]
            == "pending"
        ): ?>

            <p class="pending">
                Your company account is
                waiting for admin approval ⏳
            </p>

        <?php elseif (
            $company["approval_status"]
            == "rejected"
        ): ?>

            <p class="rejected">
                Your company account has been
                rejected ❌
            </p>

        <?php else: ?>

            <p>

                Status:

                <?php

                echo htmlspecialchars(
                    $company["approval_status"]
                );

                ?>

            </p>

        <?php endif; ?>

    </div>

    <h2 class="section-title">
        Company Overview
    </h2>

    <div class="overview-grid">

        <a
            href="jobs.php"
            class="overview-card"
        >

            <span class="card-label">
                Total Jobs
            </span>

            <span class="card-number">
                <?php
                echo $total_jobs;
                ?>
            </span>

            <span class="card-link">
                View all jobs →
            </span>

        </a>

        <a
            href="jobs.php?status=approved"
            class="overview-card"
        >

            <span class="card-label">
                Approved Jobs
            </span>

            <span class="card-number">
                <?php
                echo $approved_jobs;
                ?>
            </span>

            <span class="card-link">
                View approved jobs →
            </span>

        </a>

        <a
            href="jobs.php?status=pending"
            class="overview-card"
        >

            <span class="card-label">
                Pending Jobs
            </span>

            <span class="card-number">
                <?php
                echo $pending_jobs;
                ?>
            </span>

            <span class="card-link">
                View pending jobs →
            </span>

        </a>

        <a
            href="applications.php"
            class="overview-card"
        >

            <span class="card-label">
                Total Applications
            </span>

            <span class="card-number">
                <?php
                echo $total_applications;
                ?>
            </span>

            <span class="card-link">
                View applications →
            </span>

        </a>

    </div>

    <div class="menu-section">

        <h2>
            Company Menu
        </h2>

        <?php if (
            $company["approval_status"]
            == "approved"
        ): ?>

            <div class="menu-grid">

                <a
                    href="post_job.php"
                    class="menu-button"
                >
                    + Post Job / Internship
                </a>

                <a
                    href="jobs.php"
                    class="menu-button"
                >
                    My Posted Jobs
                </a>

                <a
                    href="profile.php"
                    class="menu-button"
                >
                    Company Profile
                </a>

            </div>

        <?php else: ?>

            <p class="disabled-message">

                Job posting will be available
                after admin approval.

            </p>

            <br>

            <div class="menu-grid">

                <a
                    href="profile.php"
                    class="menu-button"
                >
                    Company Profile
                </a>

            </div>

        <?php endif; ?>

    </div>

    <div class="menu-section">

        <h2>
            Account Settings
        </h2>

        <div class="menu-grid">

            <a
                href="change_password.php"
                class="menu-button"
            >
                🔒 Change Password
            </a>

        </div>

    </div>

    <div class="logout-area">

        <a
            href="../auth/logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</div>

</body>

</html>