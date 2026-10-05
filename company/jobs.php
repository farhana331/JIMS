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
        company_id,
        approval_status
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
$approval_status = $company["approval_status"];

$stmt = $conn->prepare(
    "SELECT
        job_id,
        title,
        job_type,
        location,
        deadline,
        vacancy_count,
        status
     FROM jobs
     WHERE company_id = ?
     ORDER BY job_id DESC"
);

$stmt->bind_param("i", $company_id);
$stmt->execute();

$jobs = $stmt->get_result();

$message = "";

if (isset($_GET["updated"])) {
    $message = "Job updated successfully.";
}

if (isset($_GET["closed"])) {
    $message = "Vacancy closed successfully.";
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Jobs - JIMS</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            height: 100%;
        }

        body {

            font-family: Arial, Helvetica, sans-serif;

            color: #1f2937;

            min-height: 100vh;

            overflow: hidden;

            background:
                radial-gradient(
                    circle at 10% 15%,
                    rgba(147, 197, 253, 0.45),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 90% 85%,
                    rgba(196, 181, 253, 0.40),
                    transparent 32%
                ),
                linear-gradient(
                    135deg,
                    #dbeafe,
                    #eff6ff,
                    #ede9fe
                );

            position: relative;
        }

        body::before {

            content: "";

            position: fixed;

            width: 280px;

            height: 280px;

            border-radius: 50%;

            background:
                rgba(59, 130, 246, 0.18);

            filter: blur(65px);

            top: -100px;

            left: -80px;

            pointer-events: none;
        }

        body::after {

            content: "";

            position: fixed;

            width: 300px;

            height: 300px;

            border-radius: 50%;

            background:
                rgba(139, 92, 246, 0.16);

            filter: blur(65px);

            right: -100px;

            bottom: -100px;

            pointer-events: none;
        }

        .page {

            width: 100%;

            height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 25px;

            position: relative;

            z-index: 1;
        }

        .main-container {

            width: 92%;

            max-width: 1200px;

            height: 92vh;

            padding: 30px;

            border-radius: 22px;

            background:
                rgba(255, 255, 255, 0.48);

            border:
                1px solid rgba(255, 255, 255, 0.70);

            backdrop-filter: blur(16px);

            -webkit-backdrop-filter: blur(16px);

            box-shadow:
                0 15px 40px rgba(30, 64, 175, 0.12);

            display: flex;

            flex-direction: column;

            overflow: hidden;
        }

        .top-section {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 25px;

            flex-shrink: 0;
        }

        .page-title {

            font-size: 30px;

            color: #1e3a8a;

            margin-bottom: 7px;
        }

        .page-subtitle {

            font-size: 14px;

            color: #64748b;
        }

        .back-button {

            display: inline-block;

            text-decoration: none;

            padding: 11px 18px;

            border-radius: 10px;

            background:
                rgba(255, 255, 255, 0.65);

            color: #1d4ed8;

            border:
                1px solid #dbeafe;

            font-size: 14px;

            font-weight: 600;

            transition: 0.25s;
        }

        .back-button:hover {

            background: #2563eb;

            color: white;

            transform: translateY(-2px);

            box-shadow:
                0 7px 18px rgba(37, 99, 235, 0.20);
        }

        .content {

            flex: 1;

            min-height: 0;

            overflow-y: auto;

            padding-right: 8px;

            scrollbar-width: thin;

            scrollbar-color:
                #bfdbfe
                transparent;
        }

        .content::-webkit-scrollbar {
            width: 6px;
        }

        .content::-webkit-scrollbar-track {
            background: transparent;
        }

        .content::-webkit-scrollbar-thumb {

            background: #bfdbfe;

            border-radius: 10px;
        }

        .success-message {

            background:
                rgba(220, 252, 231, 0.75);

            color: #15803d;

            border:
                1px solid #bbf7d0;

            padding: 14px 18px;

            border-radius: 12px;

            margin-bottom: 20px;

            font-size: 14px;

            font-weight: 600;
        }

        .warning-box {

            background:
                rgba(254, 249, 195, 0.70);

            color: #92400e;

            border:
                1px solid #fde68a;

            padding: 18px;

            border-radius: 14px;

            font-size: 14px;

            line-height: 1.6;
        }

        .jobs-grid {

            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 20px;
        }

        .job-card {

            background:
                rgba(255, 255, 255, 0.62);

            border:
                1px solid rgba(255, 255, 255, 0.80);

            border-radius: 16px;

            padding: 23px;

            box-shadow:
                0 6px 20px rgba(30, 64, 175, 0.08);

            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease,
                background 0.25s ease;
        }

        .job-card:hover {

            transform: translateY(-5px);

            background:
                rgba(255, 255, 255, 0.78);

            box-shadow:
                0 12px 28px rgba(37, 99, 235, 0.14);
        }

        .job-title {

            font-size: 21px;

            color: #1e3a8a;

            margin-bottom: 12px;

            line-height: 1.35;
        }

        .status-badge {

            display: inline-block;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 700;

            margin-bottom: 18px;
        }

        .status-pending {

            background: #fef3c7;

            color: #b45309;

            border: 1px solid #fde68a;
        }

        .status-approved {

            background: #dcfce7;

            color: #15803d;

            border: 1px solid #bbf7d0;
        }

        .status-rejected {

            background: #fee2e2;

            color: #dc2626;

            border: 1px solid #fecaca;
        }

        .status-closed {

            background: #f1f5f9;

            color: #475569;

            border: 1px solid #e2e8f0;
        }

        .job-info {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 10px;

            margin-bottom: 18px;
        }

        .info-item {

            background:
                rgba(239, 246, 255, 0.75);

            border:
                1px solid #dbeafe;

            padding: 11px 12px;

            border-radius: 10px;

            font-size: 13px;

            color: #475569;

            line-height: 1.4;
        }

        .info-item strong {

            display: block;

            color: #1e40af;

            font-size: 12px;

            margin-bottom: 4px;
        }

        .vacancy-box {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 8px;

            margin-bottom: 18px;
        }

        .vacancy-item {

            text-align: center;

            padding: 12px 6px;

            border-radius: 10px;

            background:
                rgba(239, 246, 255, 0.65);

            border:
                1px solid #dbeafe;
        }

        .vacancy-number {

            display: block;

            font-size: 21px;

            font-weight: 700;

            color: #1e3a8a;

            margin-bottom: 4px;
        }

        .vacancy-label {

            font-size: 10px;

            color: #64748b;

            line-height: 1.3;
        }

        .actions {

            display: flex;

            flex-wrap: wrap;

            gap: 9px;

            align-items: center;

            margin-top: 8px;
        }

        .action-button {

            display: inline-block;

            text-decoration: none;

            border: none;

            padding: 10px 13px;

            border-radius: 9px;

            font-size: 12px;

            font-weight: 600;

            cursor: pointer;

            transition: 0.25s;

            font-family: inherit;
        }

        .view-button {

            background: #eff6ff;

            color: #1d4ed8;

            border: 1px solid #dbeafe;
        }

        .view-button:hover {

            background: #2563eb;

            color: white;

            transform: translateY(-2px);
        }

        .edit-button {

            background: #f5f3ff;

            color: #6d28d9;

            border: 1px solid #ddd6fe;
        }

        .edit-button:hover {

            background: #7c3aed;

            color: white;

            transform: translateY(-2px);
        }

        .close-button {

            background: #fff1f2;

            color: #dc2626;

            border: 1px solid #fecdd3;
        }

        .close-button:hover {

            background: #dc2626;

            color: white;

            transform: translateY(-2px);
        }

        .status-message {

            font-size: 13px;

            color: #64748b;

            margin-top: 12px;

            line-height: 1.5;
        }

        .status-message.warning {
            color: #b45309;
        }

        .status-message.danger {
            color: #dc2626;
        }

        .status-message.muted {
            color: #64748b;
        }

        .empty-state {

            text-align: center;

            padding: 50px 20px;

            background:
                rgba(255, 255, 255, 0.58);

            border:
                1px solid rgba(255, 255, 255, 0.75);

            border-radius: 18px;

            box-shadow:
                0 6px 20px rgba(30, 64, 175, 0.07);
        }

        .empty-state h2 {

            font-size: 22px;

            color: #1e3a8a;

            margin-bottom: 10px;
        }

        .empty-state p {

            color: #64748b;

            font-size: 14px;

            margin-bottom: 20px;
        }

        .post-button {

            display: inline-block;

            text-decoration: none;

            background: #2563eb;

            color: white;

            padding: 11px 18px;

            border-radius: 10px;

            font-weight: 600;

            font-size: 13px;

            transition: 0.25s;
        }

        .post-button:hover {

            background: #1d4ed8;

            transform: translateY(-2px);

            box-shadow:
                0 7px 18px
                rgba(37, 99, 235, 0.20);
        }

        .bottom-links {

            display: flex;

            justify-content: center;

            align-items: center;

            gap: 8px;

            margin-top: 20px;

            padding-top: 17px;

            border-top:
                1px solid
                rgba(148, 163, 184, 0.25);

            flex-shrink: 0;
        }

        .bottom-link {

            text-decoration: none;

            color: #475569;

            font-size: 13px;

            padding: 8px 13px;

            border-radius: 8px;

            transition: 0.25s;
        }

        .bottom-link:hover {

            background: #eff6ff;

            color: #1d4ed8;
        }

        .bottom-link.logout {

            color: #dc2626;
        }

        .bottom-link.logout:hover {

            background: #fff1f2;

            color: #b91c1c;
        }

        @media (max-width: 900px) {

            body {
                overflow: auto;
            }

            .page {

                height: auto;

                min-height: 100vh;

                padding: 15px;
            }

            .main-container {

                width: 100%;

                height: auto;

                min-height: 95vh;
            }

            .jobs-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {

            .top-section {

                flex-direction: column;

                align-items: flex-start;
            }

            .page-title {
                font-size: 25px;
            }

            .job-info {
                grid-template-columns: 1fr;
            }

            .vacancy-box {

                grid-template-columns:
                    repeat(3, 1fr);
            }

            .bottom-links {
                flex-wrap: wrap;
            }
        }

    </style>

</head>

<body>

<div class="page">

    <div class="main-container">

        <div class="top-section">

            <div>

                <h1 class="page-title">
                    My Jobs & Internships
                </h1>

                <p class="page-subtitle">
                    Manage your posted jobs and internship opportunities
                </p>

            </div>

            <a
                href="dashboard.php"
                class="back-button"
            >
                ← Back to Dashboard
            </a>

        </div>

        <div class="content">

            <?php if (!empty($message)): ?>

                <div class="success-message">

                    ✓

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>

            <?php if ($approval_status != "approved"): ?>

                <div class="warning-box">

                    ⚠️ Your company must be approved by
                    the administrator before you can
                    manage jobs.

                </div>

            <?php else: ?>

                <?php if ($jobs->num_rows > 0): ?>

                    <div class="jobs-grid">

                        <?php while ($job = $jobs->fetch_assoc()): ?>

                            <?php

                            $selected_stmt =
                                $conn->prepare(
                                    "SELECT COUNT(*) AS selected_count
                                     FROM applications
                                     WHERE job_id = ?
                                     AND status = 'selected'"
                                );

                            $selected_stmt->bind_param(
                                "i",
                                $job["job_id"]
                            );

                            $selected_stmt->execute();

                            $selected_result =
                                $selected_stmt->get_result();

                            $selected_data =
                                $selected_result->fetch_assoc();

                            $selected_stmt->close();

                            $selected_count =
                                (int)$selected_data["selected_count"];

                            $vacancy_count =
                                (int)$job["vacancy_count"];

                            $remaining =
                                max(
                                    0,
                                    $vacancy_count -
                                    $selected_count
                                );

                            ?>

                            <div class="job-card">

                                <h2 class="job-title">

                                    <?php
                                    echo htmlspecialchars(
                                        $job["title"]
                                    );
                                    ?>

                                </h2>

                                <?php if (
                                    $job["status"] == "pending"
                                ): ?>

                                    <span
                                        class="status-badge
                                        status-pending"
                                    >
                                        Pending ⏳
                                    </span>

                                <?php elseif (
                                    $job["status"] == "approved"
                                ): ?>

                                    <span
                                        class="status-badge
                                        status-approved"
                                    >
                                        Approved ✓
                                    </span>

                                <?php elseif (
                                    $job["status"] == "rejected"
                                ): ?>

                                    <span
                                        class="status-badge
                                        status-rejected"
                                    >
                                        Rejected ✕
                                    </span>

                                <?php elseif (
                                    $job["status"] == "closed"
                                ): ?>

                                    <span
                                        class="status-badge
                                        status-closed"
                                    >
                                        Closed 🔒
                                    </span>

                                <?php else: ?>

                                    <span
                                        class="status-badge
                                        status-closed"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $job["status"]
                                        );
                                        ?>

                                    </span>

                                <?php endif; ?>

                                <div class="job-info">

                                    <div class="info-item">

                                        <strong>
                                            Type
                                        </strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $job["job_type"]
                                        );
                                        ?>

                                    </div>

                                    <div class="info-item">

                                        <strong>
                                            Location
                                        </strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $job["location"]
                                            ?? "Not specified"
                                        );
                                        ?>

                                    </div>

                                    <div class="info-item">

                                        <strong>
                                            Deadline
                                        </strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $job["deadline"]
                                        );
                                        ?>

                                    </div>

                                </div>

                                <div class="vacancy-box">

                                    <div class="vacancy-item">

                                        <span class="vacancy-number">

                                            <?php
                                            echo $vacancy_count;
                                            ?>

                                        </span>

                                        <span class="vacancy-label">
                                            Required Positions
                                        </span>

                                    </div>

                                    <div class="vacancy-item">

                                        <span class="vacancy-number">

                                            <?php
                                            echo $selected_count;
                                            ?>

                                        </span>

                                        <span class="vacancy-label">
                                            Selected
                                        </span>

                                    </div>

                                    <div class="vacancy-item">

                                        <span class="vacancy-number">

                                            <?php
                                            echo $remaining;
                                            ?>

                                        </span>

                                        <span class="vacancy-label">
                                            Remaining
                                        </span>

                                    </div>

                                </div>

                                <?php if (
                                    $job["status"] == "approved"
                                ): ?>

                                    <div class="actions">

                                        <a
                                            href="applicants.php?job_id=<?php echo $job["job_id"]; ?>"
                                            class="
                                                action-button
                                                view-button
                                            "
                                        >
                                            👥 View Applicants
                                        </a>

                                        <a
                                            href="edit_job.php?id=<?php echo $job["job_id"]; ?>"
                                            class="
                                                action-button
                                                edit-button
                                            "
                                        >
                                            ✏️ Edit Job
                                        </a>

                                        <?php if ($remaining > 0): ?>

                                            <form
                                                method="POST"
                                                action="close_job.php"
                                                style="display:inline;"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="job_id"
                                                    value="<?php
                                                    echo $job["job_id"];
                                                    ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="
                                                        action-button
                                                        close-button
                                                    "
                                                    onclick="
                                                        return confirm(
                                                            'Are you sure you want to close this vacancy?'
                                                        );
                                                    "
                                                >
                                                    🔒 Close Vacancy
                                                </button>

                                            </form>

                                        <?php else: ?>

                                            <p
                                                class="
                                                    status-message
                                                    warning
                                                "
                                            >
                                                All required positions
                                                have been filled.
                                                Vacancy is being closed
                                                automatically.
                                            </p>

                                        <?php endif; ?>

                                    </div>

                                <?php elseif (
                                    $job["status"] == "pending"
                                ): ?>

                                    <p
                                        class="
                                            status-message
                                            warning
                                        "
                                    >
                                        ⏳ Waiting for admin approval.
                                    </p>

                                    <div class="actions">

                                        <a
                                            href="edit_job.php?id=<?php echo $job["job_id"]; ?>"
                                            class="
                                                action-button
                                                edit-button
                                            "
                                        >
                                            ✏️ Edit Job
                                        </a>

                                    </div>

                                <?php elseif (
                                    $job["status"] == "rejected"
                                ): ?>

                                    <p
                                        class="
                                            status-message
                                            danger
                                        "
                                    >
                                        ❌ This job was rejected by
                                        the administrator.
                                    </p>

                                <?php elseif (
                                    $job["status"] == "closed"
                                ): ?>

                                    <p
                                        class="
                                            status-message
                                            muted
                                        "
                                    >
                                        🔒 This vacancy is closed.
                                        No more applications can
                                        be received.
                                    </p>

                                <?php endif; ?>

                            </div>

                        <?php endwhile; ?>

                    </div>

                <?php else: ?>

                    <div class="empty-state">

                        <h2>
                            No Jobs Posted Yet
                        </h2>

                        <p>
                            You have not posted any jobs
                            or internships yet.
                        </p>

                        <a
                            href="post_job.php"
                            class="post-button"
                        >
                            + Post Job / Internship
                        </a>

                    </div>

                <?php endif; ?>

            <?php endif; ?>

        </div>

        <div class="bottom-links">

            <a
                href="dashboard.php"
                class="bottom-link"
            >
                Dashboard
            </a>

            <a
                href="post_job.php"
                class="bottom-link"
            >
                + Post Job / Internship
            </a>

            <a
                href="../auth/logout.php"
                class="
                    bottom-link
                    logout
                "
            >
                Logout
            </a>

        </div>

    </div>

</div>

</body>

</html>