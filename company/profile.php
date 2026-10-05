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
    "SELECT
        u.name,
        u.email,
        u.phone,

        c.company_name,
        c.industry,
        c.description,
        c.website,
        c.address,
        c.approval_status

     FROM users u

     INNER JOIN companies c
        ON u.user_id = c.user_id

     WHERE u.user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$company = $result->fetch_assoc();

$stmt->close();

if (!$company) {
    die("Company profile not found.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Company Profile - JIMS</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            min-height: 100vh;

            color: #1f2937;

            background:
                linear-gradient(
                    135deg,
                    #eef2ff 0%,
                    #f8fafc 45%,
                    #e0f2fe 100%
                );

            overflow: hidden;
        }

        body::before {

            content: "";

            position: fixed;

            width: 350px;
            height: 350px;

            background: rgba(37, 99, 235, 0.12);

            border-radius: 50%;

            top: -120px;
            left: -100px;

            filter: blur(5px);

            z-index: -1;
        }

        body::after {

            content: "";

            position: fixed;

            width: 400px;
            height: 400px;

            background: rgba(96, 165, 250, 0.12);

            border-radius: 50%;

            bottom: -160px;
            right: -120px;

            filter: blur(5px);

            z-index: -1;
        }

        .container {

            width: 90%;

            max-width: 1050px;

            height: 94vh;

            margin: 3vh auto;

            display: flex;

            flex-direction: column;
        }

        .top-bar {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 18px;

            flex-shrink: 0;
        }

        .back-btn {

            text-decoration: none;

            color: #475569;

            font-weight: 600;

            padding: 9px 15px;

            border-radius: 9px;

            background:
                rgba(255, 255, 255, 0.35);

            border:
                1px solid rgba(255, 255, 255, 0.6);

            backdrop-filter: blur(10px);

            transition: 0.25s;
        }

        .back-btn:hover {

            color: #2563eb;

            background:
                rgba(255, 255, 255, 0.65);

            transform: translateX(-2px);
        }

        .logout {

            text-decoration: none;

            color: #dc2626;

            font-weight: 600;

            padding: 9px 16px;

            border-radius: 9px;

            background:
                rgba(255, 255, 255, 0.35);

            border:
                1px solid rgba(254, 202, 202, 0.6);

            backdrop-filter: blur(10px);

            transition: 0.25s;
        }

        .logout:hover {

            background: #dc2626;

            color: white;

            transform: translateY(-2px);
        }

        .profile-card {

            flex: 1;

            min-height: 0;

            overflow: hidden;

            background:
                rgba(255, 255, 255, 0.42);

            backdrop-filter: blur(18px);

            -webkit-backdrop-filter: blur(18px);

            border:
                1px solid rgba(255, 255, 255, 0.65);

            border-radius: 22px;

            padding: 30px;

            box-shadow:
                0 20px 45px
                rgba(15, 23, 42, 0.10);

            display: flex;

            flex-direction: column;
        }

        .profile-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 22px;

            flex-shrink: 0;
        }

        .profile-header h1 {

            font-size: 29px;

            color: #0f172a;

            margin-bottom: 6px;
        }

        .profile-header p {

            color: #64748b;

            font-size: 14px;
        }

        .update-btn {

            text-decoration: none;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #1d4ed8
                );

            color: white;

            padding: 11px 18px;

            border-radius: 10px;

            font-size: 14px;

            font-weight: 600;

            box-shadow:
                0 6px 15px
                rgba(37, 99, 235, 0.20);

            transition: 0.25s;
        }

        .update-btn:hover {

            transform: translateY(-2px);

            box-shadow:
                0 10px 20px
                rgba(37, 99, 235, 0.28);
        }

        .profile-content {

            flex: 1;

            min-height: 0;

            overflow-y: auto;

            padding-right: 6px;
        }

        .profile-content::-webkit-scrollbar {

            width: 6px;
        }

        .profile-content::-webkit-scrollbar-track {

            background:
                rgba(255, 255, 255, 0.25);

            border-radius: 10px;
        }

        .profile-content::-webkit-scrollbar-thumb {

            background:
                rgba(100, 116, 139, 0.35);

            border-radius: 10px;
        }

        .section {

            background:
                rgba(255, 255, 255, 0.38);

            border:
                1px solid rgba(255, 255, 255, 0.65);

            border-radius: 15px;

            padding: 20px;

            margin-bottom: 16px;

            box-shadow:
                0 5px 15px
                rgba(15, 23, 42, 0.04);
        }

        .section h2 {

            font-size: 18px;

            margin-bottom: 16px;

            color: #0f172a;
        }

        .info-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 13px;
        }

        .info-box {

            background:
                rgba(255, 255, 255, 0.55);

            padding: 14px 16px;

            border-radius: 11px;

            border:
                1px solid
                rgba(226, 232, 240, 0.65);

            transition: 0.2s;
        }

        .info-box:hover {

            background:
                rgba(255, 255, 255, 0.72);

            transform: translateY(-1px);
        }

        .info-box label {

            display: block;

            font-size: 12px;

            color: #64748b;

            margin-bottom: 5px;

            font-weight: 600;
        }

        .info-box p {

            font-size: 14px;

            font-weight: 600;

            color: #1e293b;

            word-break: break-word;

            line-height: 1.5;
        }

        .status {

            display: inline-block;

            padding: 8px 15px;

            border-radius: 30px;

            font-size: 13px;

            font-weight: 700;
        }

        .approved {

            background:
                rgba(220, 252, 231, 0.85);

            color: #15803d;
        }

        .pending {

            background:
                rgba(254, 243, 199, 0.85);

            color: #b45309;
        }

        .rejected {

            background:
                rgba(254, 226, 226, 0.85);

            color: #dc2626;
        }

        .unknown {

            background:
                rgba(226, 232, 240, 0.85);

            color: #475569;
        }

        .description-box {

            margin-top: 13px;
        }

        .description {

            line-height: 1.6;

            color: #475569;

            white-space: pre-line;

            font-weight: 500 !important;
        }

        .website {

            color: #2563eb;

            text-decoration: none;

            font-weight: 600;
        }

        .website:hover {

            text-decoration: underline;
        }

        .navigation {

            display: flex;

            justify-content: center;

            align-items: center;

            gap: 25px;

            margin-top: 15px;

            padding-top: 5px;

            flex-wrap: wrap;

            flex-shrink: 0;
        }

        .navigation a {

            text-decoration: none;

            color: #475569;

            font-size: 13px;

            font-weight: 600;

            transition: 0.2s;
        }

        .navigation a:hover {

            color: #2563eb;

            transform: translateY(-1px);
        }

        @media (max-width: 750px) {

            body {
                overflow: auto;
            }

            .container {

                width: 94%;

                height: auto;

                min-height: 96vh;

                margin: 2vh auto;
            }

            .profile-card {

                overflow: visible;

                padding: 22px;
            }

            .profile-header {

                flex-direction: column;

                align-items: flex-start;

                gap: 15px;
            }

            .update-btn {

                width: 100%;

                text-align: center;
            }

            .profile-content {

                overflow: visible;
            }

            .info-grid {

                grid-template-columns: 1fr;
            }

            .navigation {

                gap: 15px;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <div class="top-bar">

        <a
            href="dashboard.php"
            class="back-btn"
        >
            ← Back to Dashboard
        </a>

        <a
            href="../auth/logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

    <div class="profile-card">

        <div class="profile-header">

            <div>

                <h1>
                    Company Profile
                </h1>

                <p>
                    Manage and view your company information
                </p>

            </div>

            <a
                href="update_profile.php"
                class="update-btn"
            >
                ✏️ Update Information
            </a>

        </div>

        <div class="profile-content">

            <div class="section">

                <h2>
                    👤 Account Information
                </h2>

                <div class="info-grid">

                    <div class="info-box">

                        <label>
                            Name
                        </label>

                        <p>

                            <?php

                            echo htmlspecialchars(
                                $company["name"]
                            );

                            ?>

                        </p>

                    </div>

                    <div class="info-box">

                        <label>
                            Email
                        </label>

                        <p>

                            <?php

                            echo htmlspecialchars(
                                $company["email"]
                            );

                            ?>

                        </p>

                    </div>

                    <div class="info-box">

                        <label>
                            Phone
                        </label>

                        <p>

                            <?php

                            echo htmlspecialchars(
                                $company["phone"]
                                ?? "Not provided"
                            );

                            ?>

                        </p>

                    </div>

                </div>

            </div>

            <div class="section">

                <h2>
                    🛡️ Company Approval Status
                </h2>

                <?php

                if (
                    $company["approval_status"]
                    == "approved"
                ):

                ?>

                    <span class="status approved">
                        ✓ Approved
                    </span>

                <?php

                elseif (
                    $company["approval_status"]
                    == "pending"
                ):

                ?>

                    <span class="status pending">
                        ⏳ Pending
                    </span>

                <?php

                elseif (
                    $company["approval_status"]
                    == "rejected"
                ):

                ?>

                    <span class="status rejected">
                        ✕ Rejected
                    </span>

                <?php else: ?>

                    <span class="status unknown">

                        <?php

                        echo htmlspecialchars(
                            $company["approval_status"]
                        );

                        ?>

                    </span>

                <?php endif; ?>

            </div>

            <div class="section">

                <h2>
                    🏢 Company Details
                </h2>

                <div class="info-grid">

                    <div class="info-box">

                        <label>
                            Company Name
                        </label>

                        <p>

                            <?php

                            echo htmlspecialchars(
                                $company["company_name"]
                            );

                            ?>

                        </p>

                    </div>

                    <div class="info-box">

                        <label>
                            Industry
                        </label>

                        <p>

                            <?php

                            echo htmlspecialchars(
                                $company["industry"]
                                ?: "Not provided"
                            );

                            ?>

                        </p>

                    </div>

                    <div class="info-box">

                        <label>
                            Website
                        </label>

                        <p>

                            <?php

                            if (
                                !empty(
                                    $company["website"]
                                )
                            ):

                            ?>

                                <a
                                    href="<?php
                                    echo htmlspecialchars(
                                        $company["website"]
                                    );
                                    ?>"
                                    target="_blank"
                                    class="website"
                                >
                                    Visit Website ↗
                                </a>

                            <?php else: ?>

                                Not provided

                            <?php endif; ?>

                        </p>

                    </div>

                    <div class="info-box">

                        <label>
                            Address
                        </label>

                        <p>

                            <?php

                            echo htmlspecialchars(
                                $company["address"]
                                ?: "Not provided"
                            );

                            ?>

                        </p>

                    </div>

                </div>

                <div class="info-box description-box">

                    <label>
                        Description
                    </label>

                    <p class="description">

                        <?php

                        echo htmlspecialchars(
                            $company["description"]
                            ?: "No company description added yet."
                        );

                        ?>

                    </p>

                </div>

            </div>

        </div>

        <div class="navigation">

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="jobs.php">
                My Jobs
            </a>

            <a href="post_job.php">
                Post Job / Internship
            </a>

            <a href="change_password.php">
                Change Password
            </a>

        </div>

    </div>

</div>

</body>

</html>