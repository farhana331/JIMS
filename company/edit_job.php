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
    "SELECT company_id
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

$job_id = $_GET["id"] ?? $_POST["job_id"] ?? 0;

if (
    !is_numeric($job_id) ||
    $job_id <= 0
) {
    die("Invalid job ID.");
}

$job_id = (int)$job_id;

$stmt = $conn->prepare(
    "SELECT
        job_id,
        title,
        job_type,
        description,
        requirements,
        salary,
        location,
        deadline,
        vacancy_count,
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
    die("Job not found or unauthorized access.");
}

if ($job["status"] == "closed") {
    die("This vacancy is already closed and cannot be edited.");
}

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS selected_count
     
     FROM applications

     WHERE job_id = ?
     AND status = 'selected'"
);

$stmt->bind_param(
    "i",
    $job_id
);

$stmt->execute();

$result = $stmt->get_result();
$selected_data = $result->fetch_assoc();

$stmt->close();

$selected_count =
    (int)$selected_data["selected_count"];

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title =
        trim($_POST["title"] ?? "");

    $job_type =
        trim($_POST["job_type"] ?? "");

    $description =
        trim($_POST["description"] ?? "");

    $requirements =
        trim($_POST["requirements"] ?? "");

    $salary =
        trim($_POST["salary"] ?? "");

    $location =
        trim($_POST["location"] ?? "");

    $deadline =
        $_POST["deadline"] ?? "";

    $vacancy_count =
        $_POST["vacancy_count"] ?? 0;

    if (
        empty($title) ||
        empty($job_type) ||
        empty($description) ||
        empty($deadline)
    ) {

        $message =
            "Please fill in all required fields.";

    } elseif (
        !is_numeric($vacancy_count) ||
        $vacancy_count < 1
    ) {

        $message =
            "Vacancy count must be at least 1.";

    } elseif (
        $vacancy_count < $selected_count
    ) {

        $message =
            "Vacancy count cannot be less than the number of already selected candidates.";

    } else {

        $stmt = $conn->prepare(
            "UPDATE jobs

             SET title = ?,
                 job_type = ?,
                 description = ?,
                 requirements = ?,
                 salary = ?,
                 location = ?,
                 deadline = ?,
                 vacancy_count = ?

             WHERE job_id = ?
             AND company_id = ?"
        );

        $vacancy_count =
            (int)$vacancy_count;

        $stmt->bind_param(
            "sssssssiii",
            $title,
            $job_type,
            $description,
            $requirements,
            $salary,
            $location,
            $deadline,
            $vacancy_count,
            $job_id,
            $company_id
        );

        if ($stmt->execute()) {

            header(
                "Location: jobs.php?updated=1"
            );

            exit();

        } else {

            $message =
                "Failed to update job.";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Job - JIMS</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            min-height: 100vh;

            overflow: hidden;

            background:
                linear-gradient(
                    135deg,
                    #dbeafe,
                    #eef2ff,
                    #e0e7ff
                );

            color: #1f2937;

            position: relative;
        }

        body::before {

            content: "";

            position: fixed;

            width: 350px;
            height: 350px;

            background: #93c5fd;

            border-radius: 50%;

            top: -120px;
            left: -100px;

            opacity: 0.35;

            filter: blur(20px);

            z-index: 0;
        }

        body::after {

            content: "";

            position: fixed;

            width: 350px;
            height: 350px;

            background: #c4b5fd;

            border-radius: 50%;

            right: -100px;
            bottom: -120px;

            opacity: 0.35;

            filter: blur(20px);

            z-index: 0;
        }

        .page-wrapper {

            width: 100%;
            height: 100vh;

            display: flex;

            align-items: center;
            justify-content: center;

            padding: 20px;

            position: relative;

            z-index: 1;
        }

        .main-card {

            width: 100%;

            max-width: 1050px;

            height: 94vh;

            background:
                rgba(255, 255, 255, 0.38);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);

            border:
                1px solid rgba(255, 255, 255, 0.65);

            border-radius: 24px;

            box-shadow:
                0 20px 50px
                rgba(31, 41, 55, 0.15);

            display: flex;

            flex-direction: column;

            overflow: hidden;
        }

        .page-header {

            padding: 22px 30px;

            border-bottom:
                1px solid
                rgba(255,255,255,0.55);

            display: flex;

            align-items: center;

            justify-content: space-between;

            flex-shrink: 0;
        }

        .page-header h1 {

            font-size: 26px;

            color: #1e3a8a;

            margin-bottom: 5px;
        }

        .page-header p {

            color: #64748b;

            font-size: 13px;
        }

        .back-btn {

            text-decoration: none;

            color: #1d4ed8;

            background:
                rgba(255,255,255,0.55);

            border:
                1px solid
                rgba(255,255,255,0.8);

            padding: 10px 16px;

            border-radius: 10px;

            font-size: 14px;

            font-weight: 600;

            transition: 0.25s;
        }

        .back-btn:hover {

            background: #2563eb;

            color: white;

            transform: translateY(-2px);
        }

        .content {

            flex: 1;

            display: grid;

            grid-template-columns:
                0.85fr 1.5fr;

            gap: 22px;

            padding: 22px 30px;

            min-height: 0;
        }

        .info-panel {

            background:
                rgba(255,255,255,0.42);

            backdrop-filter:
                blur(14px);

            -webkit-backdrop-filter:
                blur(14px);

            border:
                1px solid
                rgba(255,255,255,0.65);

            border-radius: 18px;

            padding: 22px;

            height: 100%;
        }

        .info-panel h2 {

            font-size: 20px;

            color: #1e3a8a;

            margin-bottom: 18px;
        }

        .info-box {

            background:
                rgba(255,255,255,0.48);

            border-radius: 12px;

            padding: 16px;

            margin-bottom: 14px;

            border:
                1px solid
                rgba(255,255,255,0.65);
        }

        .info-label {

            display: block;

            font-size: 12px;

            color: #64748b;

            margin-bottom: 7px;

            font-weight: 600;

            text-transform: uppercase;
        }

        .info-value {

            font-size: 16px;

            font-weight: 700;

            color: #1e293b;
        }

        .approved {

            color: #15803d;
        }

        .pending {

            color: #d97706;
        }

        .rejected {

            color: #dc2626;
        }

        .selected-number {

            color: #2563eb;

            font-size: 24px;

            font-weight: bold;
        }

        .notice {

            margin-top: 18px;

            padding: 14px;

            border-radius: 12px;

            background:
                rgba(219,234,254,0.65);

            color: #1e40af;

            font-size: 13px;

            line-height: 1.5;
        }

        .form-panel {

            background:
                rgba(255,255,255,0.46);

            backdrop-filter:
                blur(14px);

            -webkit-backdrop-filter:
                blur(14px);

            border:
                1px solid
                rgba(255,255,255,0.7);

            border-radius: 18px;

            padding: 22px;

            height: 100%;

            display: flex;

            flex-direction: column;

            min-height: 0;
        }

        .form-panel h2 {

            font-size: 20px;

            color: #1e3a8a;

            margin-bottom: 16px;

            flex-shrink: 0;
        }

        .form-grid {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 13px 16px;

            overflow: hidden;

            flex: 1;
        }

        .form-group {

            display: flex;

            flex-direction: column;
        }

        .form-group.full {

            grid-column:
                1 / -1;
        }

        label {

            font-size: 13px;

            font-weight: 600;

            color: #334155;

            margin-bottom: 6px;
        }

        input,
        select,
        textarea {

            width: 100%;

            border:
                1px solid
                rgba(148,163,184,0.45);

            background:
                rgba(255,255,255,0.55);

            border-radius: 9px;

            padding: 10px 12px;

            font-family: inherit;

            font-size: 13px;

            color: #1f2937;

            outline: none;

            transition: 0.2s;
        }

        input:focus,
        select:focus,
        textarea:focus {

            border-color: #60a5fa;

            background:
                rgba(255,255,255,0.78);

            box-shadow:
                0 0 0 3px
                rgba(96,165,250,0.15);
        }

        textarea {

            resize: none;

            line-height: 1.4;
        }

        .description-box {

            height: 82px;
        }

        .requirements-box {

            height: 72px;
        }

        .form-hint {

            font-size: 11px;

            color: #64748b;

            margin-top: 4px;
        }

        .message {

            padding: 10px 14px;

            border-radius: 9px;

            background:
                rgba(254,226,226,0.75);

            color: #b91c1c;

            font-size: 13px;

            margin-bottom: 12px;
        }

        .form-actions {

            grid-column:
                1 / -1;

            display: flex;

            align-items: center;

            justify-content: flex-end;

            gap: 10px;

            margin-top: 3px;
        }

        .cancel-btn {

            text-decoration: none;

            padding: 10px 18px;

            border-radius: 9px;

            background:
                rgba(255,255,255,0.6);

            color: #475569;

            border:
                1px solid
                rgba(148,163,184,0.4);

            font-size: 13px;

            font-weight: 600;

            transition: 0.2s;
        }

        .cancel-btn:hover {

            background: #f1f5f9;

            transform: translateY(-1px);
        }

        .update-btn {

            border: none;

            padding: 10px 20px;

            border-radius: 9px;

            background:
                linear-gradient(
                    135deg,
                    #1e3a8a,
                    #2563eb
                );

            color: white;

            font-size: 13px;

            font-weight: 600;

            cursor: pointer;

            box-shadow:
                0 5px 14px
                rgba(37,99,235,0.22);

            transition: 0.25s;
        }

        .update-btn:hover {

            transform: translateY(-2px);

            box-shadow:
                0 8px 18px
                rgba(37,99,235,0.30);
        }

        .bottom-bar {

            padding: 12px 30px;

            border-top:
                1px solid
                rgba(255,255,255,0.55);

            display: flex;

            justify-content: center;

            flex-shrink: 0;
        }

        .logout {

            text-decoration: none;

            color: #dc2626;

            font-size: 13px;

            font-weight: 600;

            padding: 7px 15px;

            border:
                1px solid
                rgba(248,113,113,0.35);

            border-radius: 8px;

            background:
                rgba(254,226,226,0.45);

            transition: 0.2s;
        }

        .logout:hover {

            background: #dc2626;

            color: white;
        }

        @media (max-width: 850px) {

            body {
                overflow: auto;
            }

            .page-wrapper {
                height: auto;
                min-height: 100vh;
            }

            .main-card {
                height: auto;
                min-height: 95vh;
            }

            .content {
                grid-template-columns: 1fr;
            }

            .info-panel,
            .form-panel {
                height: auto;
            }

            .form-grid {
                overflow: visible;
            }
        }

        @media (max-width: 600px) {

            .page-wrapper {
                padding: 10px;
            }

            .main-card {
                border-radius: 18px;
            }

            .page-header {
                padding: 18px;
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .content {
                padding: 15px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
            }

            .form-actions {
                grid-column: auto;
            }
        }

    </style>

</head>

<body>

<div class="page-wrapper">

    <div class="main-card">

        <div class="page-header">

            <div>

                <h1>
                    Edit Job / Internship
                </h1>

                <p>
                    Update your job posting information
                </p>

            </div>

            <a
                href="jobs.php"
                class="back-btn"
            >
                ← Back to My Jobs
            </a>

        </div>

        <div class="content">

            <div class="info-panel">

                <h2>
                    Current Information
                </h2>

                <div class="info-box">

                    <span class="info-label">
                        Job Status
                    </span>

                    <span class="info-value">

                        <?php

                        if ($job["status"] == "approved") {

                            echo '<span class="approved">Approved ✅</span>';

                        } elseif ($job["status"] == "pending") {

                            echo '<span class="pending">Pending ⏳</span>';

                        } elseif ($job["status"] == "rejected") {

                            echo '<span class="rejected">Rejected ❌</span>';

                        } else {

                            echo htmlspecialchars(
                                $job["status"]
                            );
                        }

                        ?>

                    </span>

                </div>

                <div class="info-box">

                    <span class="info-label">
                        Selected Candidates
                    </span>

                    <span class="selected-number">
                        <?php echo $selected_count; ?>
                    </span>

                </div>

                <div class="info-box">

                    <span class="info-label">
                        Current Vacancy
                    </span>

                    <span class="info-value">
                        <?php
                        echo (int)$job["vacancy_count"];
                        ?>
                        Positions
                    </span>

                </div>

                <div class="info-box">

                    <span class="info-label">
                        Current Deadline
                    </span>

                    <span class="info-value">
                        <?php
                        echo htmlspecialchars(
                            $job["deadline"]
                        );
                        ?>
                    </span>

                </div>

                <div class="notice">

                    💡 <strong>Note:</strong>

                    Vacancy count cannot be reduced below
                    the number of candidates who have already
                    been selected.

                </div>

            </div>

            <div class="form-panel">

                <h2>
                    Update Job Details
                </h2>

                <?php if (!empty($message)): ?>

                    <div class="message">

                        <?php
                        echo htmlspecialchars($message);
                        ?>

                    </div>

                <?php endif; ?>

                <form
                    method="POST"
                    class="form-grid"
                >

                    <input
                        type="hidden"
                        name="job_id"
                        value="<?php echo $job_id; ?>"
                    >

                    <div class="form-group full">

                        <label>
                            Job / Internship Title *
                        </label>

                        <input
                            type="text"
                            name="title"
                            value="<?php
                                echo htmlspecialchars(
                                    $job["title"]
                                );
                            ?>"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label>
                            Type *
                        </label>

                        <select
                            name="job_type"
                            required
                        >

                            <option
                                value="Job"
                                <?php
                                echo (
                                    $job["job_type"] == "Job"
                                )
                                ? "selected"
                                : "";
                                ?>
                            >
                                Job
                            </option>

                            <option
                                value="Internship"
                                <?php
                                echo (
                                    $job["job_type"] == "Internship"
                                )
                                ? "selected"
                                : "";
                                ?>
                            >
                                Internship
                            </option>

                        </select>

                    </div>

                    <div class="form-group">

                        <label>
                            Number of Positions *
                        </label>

                        <input
                            type="number"
                            name="vacancy_count"
                            min="<?php
                                echo max(
                                    1,
                                    $selected_count
                                );
                            ?>"
                            value="<?php
                                echo (int)
                                    $job["vacancy_count"];
                            ?>"
                            required
                        >

                        <span class="form-hint">
                            Already selected:
                            <?php echo $selected_count; ?>
                        </span>

                    </div>

                    <div class="form-group">

                        <label>
                            Salary
                        </label>

                        <input
                            type="text"
                            name="salary"
                            value="<?php
                                echo htmlspecialchars(
                                    $job["salary"] ?? ""
                                );
                            ?>"
                            placeholder="e.g. 20,000 BDT"
                        >

                    </div>

                    <div class="form-group">

                        <label>
                            Location
                        </label>

                        <input
                            type="text"
                            name="location"
                            value="<?php
                                echo htmlspecialchars(
                                    $job["location"] ?? ""
                                );
                            ?>"
                            placeholder="e.g. Dhaka / Remote"
                        >

                    </div>

                    <div class="form-group">

                        <label>
                            Application Deadline *
                        </label>

                        <input
                            type="date"
                            name="deadline"
                            value="<?php
                                echo htmlspecialchars(
                                    $job["deadline"]
                                );
                            ?>"
                            required
                        >

                    </div>

                    <div class="form-group full">

                        <label>
                            Description *
                        </label>

                        <textarea
                            name="description"
                            class="description-box"
                            required
                        ><?php
                            echo htmlspecialchars(
                                $job["description"] ?? ""
                            );
                        ?></textarea>

                    </div>

                    <div class="form-group full">

                        <label>
                            Requirements
                        </label>

                        <textarea
                            name="requirements"
                            class="requirements-box"
                            placeholder="Example: PHP, MySQL, HTML, CSS..."
                        ><?php
                            echo htmlspecialchars(
                                $job["requirements"] ?? ""
                            );
                        ?></textarea>

                    </div>

                    <div class="form-actions">

                        <a
                            href="jobs.php"
                            class="cancel-btn"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="update-btn"
                        >
                            Update Job
                        </button>

                    </div>

                </form>

            </div>

        </div>

        <div class="bottom-bar">

            <a
                href="../auth/logout.php"
                class="logout"
            >
                Logout
            </a>

        </div>

    </div>

</div>

</body>

</html>