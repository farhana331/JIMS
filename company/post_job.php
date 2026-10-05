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

$message = "";

$stmt = $conn->prepare(
    "SELECT company_id, approval_status
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if ($approval_status != "approved") {

        $message =
            "Your company must be approved by an admin before posting a job.";

    } else {

        $title =
            trim($_POST["title"] ?? "");

        $job_type =
            $_POST["job_type"] ?? "";

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
            $job_type != "Job" &&
            $job_type != "Internship"
        ) {

            $message =
                "Invalid job type.";

        } elseif (
            !is_numeric($vacancy_count) ||
            $vacancy_count < 1
        ) {

            $message =
                "Number of positions must be at least 1.";

        } elseif ($deadline < date("Y-m-d")) {

            $message =
                "Deadline cannot be in the past.";

        } else {

            $stmt = $conn->prepare(
                "INSERT INTO jobs
                (
                    company_id,
                    title,
                    job_type,
                    description,
                    requirements,
                    salary,
                    location,
                    deadline,
                    vacancy_count,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
            );

            $vacancy_count =
                (int)$vacancy_count;

            $stmt->bind_param(
                "isssssssi",
                $company_id,
                $title,
                $job_type,
                $description,
                $requirements,
                $salary,
                $location,
                $deadline,
                $vacancy_count
            );

            if ($stmt->execute()) {

                header("Location: jobs.php");
                exit();

            } else {

                $message =
                    "Failed to post job/internship.";
            }

            $stmt->close();
        }
    }
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

    <title>Post Job - JIMS</title>

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
                    #dbeafe 0%,
                    #eff6ff 45%,
                    #dbeafe 100%
                );

            overflow: hidden;

            position: relative;
        }

        body::before {

            content: "";

            position: fixed;

            width: 420px;
            height: 420px;

            background:
                rgba(37, 99, 235, 0.16);

            border-radius: 50%;

            top: -180px;
            left: -120px;

            filter: blur(10px);

            z-index: -1;
        }

        body::after {

            content: "";

            position: fixed;

            width: 400px;
            height: 400px;

            background:
                rgba(96, 165, 250, 0.18);

            border-radius: 50%;

            bottom: -180px;
            right: -120px;

            filter: blur(10px);

            z-index: -1;
        }

        .page-wrapper {

            width: 100%;

            height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 25px;
        }

        .glass-card {

            width: 100%;

            max-width: 900px;

            max-height: 94vh;

            overflow-y: auto;

            background:
                rgba(255, 255, 255, 0.42);

            border:
                1px solid rgba(255, 255, 255, 0.65);

            border-radius: 22px;

            padding: 30px 35px;

            box-shadow:
                0 20px 45px
                rgba(30, 58, 138, 0.13);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);
        }

        .glass-card::-webkit-scrollbar {

            width: 0;
            display: none;
        }

        .glass-card {

            scrollbar-width: none;
        }

        .page-header {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 25px;

            padding-bottom: 20px;

            border-bottom:
                1px solid
                rgba(37, 99, 235, 0.12);
        }

        .page-header h1 {

            font-size: 28px;

            color: #1e3a8a;

            margin-bottom: 6px;
        }

        .page-header p {

            color: #64748b;

            font-size: 14px;
        }

        .back-button {

            text-decoration: none;

            display: inline-block;

            padding: 10px 17px;

            border-radius: 10px;

            color: #1d4ed8;

            background:
                rgba(255, 255, 255, 0.55);

            border:
                1px solid
                rgba(37, 99, 235, 0.18);

            font-size: 14px;

            font-weight: 600;

            transition: 0.25s;
        }

        .back-button:hover {

            background: #2563eb;

            color: white;

            transform: translateY(-2px);
        }

        .message {

            padding: 14px 17px;

            border-radius: 11px;

            margin-bottom: 22px;

            background:
                rgba(254, 226, 226, 0.72);

            border:
                1px solid
                rgba(220, 38, 38, 0.15);

            color: #b91c1c;

            font-size: 14px;
        }

        .approval-box {

            padding: 22px;

            border-radius: 15px;

            background:
                rgba(255, 255, 255, 0.48);

            border:
                1px solid
                rgba(37, 99, 235, 0.14);

            margin-bottom: 20px;
        }

        .approval-box h3 {

            color: #1e3a8a;

            margin-bottom: 10px;
        }

        .approval-box p {

            color: #64748b;

            line-height: 1.6;

            font-size: 14px;
        }

        .status {

            display: inline-block;

            margin-top: 10px;

            padding: 7px 13px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: 600;

            background:
                rgba(245, 158, 11, 0.12);

            color: #b45309;
        }

        .form-grid {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 20px;
        }

        .form-group {

            display: flex;

            flex-direction: column;
        }

        .form-group.full {

            grid-column: 1 / -1;
        }

        label {

            font-size: 14px;

            font-weight: 600;

            color: #334155;

            margin-bottom: 8px;
        }

        .required {

            color: #dc2626;
        }

        input,
        select,
        textarea {

            width: 100%;

            border: 1px solid
                rgba(37, 99, 235, 0.16);

            outline: none;

            border-radius: 11px;

            padding: 13px 14px;

            font-family: inherit;

            font-size: 14px;

            color: #1f2937;

            background:
                rgba(255, 255, 255, 0.55);

            backdrop-filter:
                blur(8px);

            transition: 0.25s;
        }

        input,
        select {

            height: 46px;
        }

        textarea {

            min-height: 125px;

            resize: vertical;
        }

        input:focus,
        select:focus,
        textarea:focus {

            border-color: #2563eb;

            background:
                rgba(255, 255, 255, 0.78);

            box-shadow:
                0 0 0 3px
                rgba(37, 99, 235, 0.10);
        }

        input::placeholder,
        textarea::placeholder {

            color: #94a3b8;
        }

        .form-actions {

            margin-top: 25px;

            padding-top: 22px;

            border-top:
                1px solid
                rgba(37, 99, 235, 0.12);

            display: flex;

            justify-content: flex-end;

            gap: 12px;
        }

        .cancel-button {

            text-decoration: none;

            padding: 12px 22px;

            border-radius: 10px;

            color: #475569;

            background:
                rgba(255, 255, 255, 0.45);

            border:
                1px solid
                rgba(100, 116, 139, 0.16);

            font-weight: 600;

            font-size: 14px;

            transition: 0.25s;
        }

        .cancel-button:hover {

            background:
                rgba(255, 255, 255, 0.8);

            transform: translateY(-2px);
        }

        .submit-button {

            border: none;

            padding: 12px 24px;

            border-radius: 10px;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    #1e3a8a,
                    #2563eb
                );

            font-size: 14px;

            font-weight: 600;

            cursor: pointer;

            box-shadow:
                0 5px 15px
                rgba(37, 99, 235, 0.22);

            transition: 0.25s;
        }

        .submit-button:hover {

            transform: translateY(-2px);

            box-shadow:
                0 8px 20px
                rgba(37, 99, 235, 0.28);
        }

        .logout-area {

            text-align: center;

            margin-top: 20px;
        }

        .logout {

            text-decoration: none;

            display: inline-block;

            color: #dc2626;

            font-weight: 600;

            padding: 9px 18px;

            border:
                1px solid
                rgba(220, 38, 38, 0.18);

            border-radius: 9px;

            background:
                rgba(255, 245, 245, 0.45);

            font-size: 13px;

            transition: 0.25s;
        }

        .logout:hover {

            background: #dc2626;

            color: white;

            transform: translateY(-2px);
        }

        @media (max-width: 700px) {

            body {

                overflow: auto;
            }

            .page-wrapper {

                min-height: 100vh;

                height: auto;

                padding: 15px;
            }

            .glass-card {

                max-height: none;

                padding: 25px 20px;
            }

            .page-header {

                flex-direction: column;

                align-items: flex-start;
            }

            .form-grid {

                grid-template-columns: 1fr;
            }

            .form-group.full {

                grid-column: auto;
            }

            .form-actions {

                flex-direction: column;
            }

            .submit-button,
            .cancel-button {

                width: 100%;

                text-align: center;
            }
        }

    </style>

</head>

<body>

<div class="page-wrapper">

    <div class="glass-card">

        <div class="page-header">

            <div>

                <h1>
                    Post Job / Internship
                </h1>

                <p>
                    Create a new opportunity for students and job seekers.
                </p>

            </div>

            <a
                href="dashboard.php"
                class="back-button"
            >
                ← Dashboard
            </a>

        </div>

        <?php if (!empty($message)): ?>

            <div class="message">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>

        <?php if ($approval_status != "approved"): ?>

            <div class="approval-box">

                <h3>
                    Company Approval Required
                </h3>

                <p>
                    Your company account is not approved yet.
                    You need admin approval before you can post
                    jobs or internships.
                </p>

                <span class="status">

                    Current Status:
                    <?php
                    echo htmlspecialchars(
                        $approval_status
                    );
                    ?>

                </span>

            </div>

        <?php else: ?>

            <form method="POST">

                <div class="form-grid">

                    <div class="form-group full">

                        <label>
                            Job / Internship Title
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="title"
                            placeholder="Example: PHP Developer Intern"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label>
                            Type
                            <span class="required">*</span>
                        </label>

                        <select
                            name="job_type"
                            required
                        >

                            <option value="">
                                Select Type
                            </option>

                            <option value="Job">
                                Job
                            </option>

                            <option value="Internship">
                                Internship
                            </option>

                        </select>

                    </div>

                    <div class="form-group">

                        <label>
                            Number of Positions
                            <span class="required">*</span>
                        </label>

                        <input
                            type="number"
                            name="vacancy_count"
                            min="1"
                            value="1"
                            required
                        >

                    </div>

                    <div class="form-group full">

                        <label>
                            Description
                            <span class="required">*</span>
                        </label>

                        <textarea
                            name="description"
                            placeholder="Describe the job or internship, responsibilities, work environment, etc."
                            required
                        ></textarea>

                    </div>

                    <div class="form-group full">

                        <label>
                            Requirements
                        </label>

                        <textarea
                            name="requirements"
                            placeholder="Example: PHP, MySQL, HTML, CSS, JavaScript..."
                        ></textarea>

                    </div>

                    <div class="form-group">

                        <label>
                            Salary
                        </label>

                        <input
                            type="text"
                            name="salary"
                            placeholder="Example: 15,000 - 25,000 BDT"
                        >

                    </div>

                    <div class="form-group">

                        <label>
                            Location
                        </label>

                        <input
                            type="text"
                            name="location"
                            placeholder="Example: Dhaka / Remote"
                        >

                    </div>

                    <div class="form-group">

                        <label>
                            Application Deadline
                            <span class="required">*</span>
                        </label>

                        <input
                            type="date"
                            name="deadline"
                            min="<?php echo date('Y-m-d'); ?>"
                            required
                        >

                    </div>

                </div>

                <div class="form-actions">

                    <a
                        href="dashboard.php"
                        class="cancel-button"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="submit-button"
                    >
                        Post Job / Internship
                    </button>

                </div>

            </form>

        <?php endif; ?>

        <div class="logout-area">

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