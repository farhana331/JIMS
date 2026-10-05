<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "company") {
    header("Location: ../auth/login.php");
    exit();
}

$application_id = $_GET["application_id"] ?? 0;
$job_id = $_GET["job_id"] ?? 0;

if (!is_numeric($application_id) || $application_id <= 0) {
    die("Invalid application ID.");
}

if (!is_numeric($job_id) || $job_id <= 0) {
    die("Invalid job ID.");
}

$application_id = intval($application_id);
$job_id = intval($job_id);

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

$stmt = $conn->prepare(
    "SELECT
        a.application_id,
        a.status,
        u.name AS student_name,
        j.title AS job_title

     FROM applications a

     INNER JOIN students s
        ON a.student_id = s.student_id

     INNER JOIN users u
        ON s.user_id = u.user_id

     INNER JOIN jobs j
        ON a.job_id = j.job_id

     WHERE a.application_id = ?
     AND a.job_id = ?
     AND j.company_id = ?"
);

$stmt->bind_param(
    "iii",
    $application_id,
    $job_id,
    $company_id
);

$stmt->execute();

$result = $stmt->get_result();
$application = $result->fetch_assoc();

$stmt->close();

if (!$application) {
    die("Application not found.");
}

if ($application["status"] != "shortlisted") {
    die(
        "Only shortlisted applicants can be scheduled for an interview."
    );
}

$stmt = $conn->prepare(
    "SELECT
        interview_id,
        interview_date,
        interview_time,
        interview_type,
        meeting_link,
        notes,
        status

     FROM interviews

     WHERE application_id = ?

     ORDER BY interview_id DESC

     LIMIT 1"
);

$stmt->bind_param(
    "i",
    $application_id
);

$stmt->execute();

$result = $stmt->get_result();
$existing_interview = $result->fetch_assoc();

$stmt->close();

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $interview_date =
        $_POST["interview_date"] ?? "";

    $interview_time =
        $_POST["interview_time"] ?? "";

    $interview_type =
        $_POST["interview_type"] ?? "";

    $meeting_link =
        trim($_POST["meeting_link"] ?? "");

    $notes =
        trim($_POST["notes"] ?? "");

    if (
        empty($interview_date) ||
        empty($interview_time) ||
        empty($interview_type)
    ) {

        $message =
            "Please fill in all required fields.";

        $message_type = "error";

    } elseif (
        $interview_type === "Online" &&
        empty($meeting_link)
    ) {

        $message =
            "Please provide a meeting link for an online interview.";

        $message_type = "error";

    } else {

        $check = $conn->prepare(
            "SELECT interview_id
             FROM interviews
             WHERE application_id = ?
             AND status = 'scheduled'"
        );

        $check->bind_param(
            "i",
            $application_id
        );

        $check->execute();

        $existing = $check->get_result();

        $check->close();

        if ($existing->num_rows > 0) {

            $message =
                "An interview is already scheduled for this application.";

            $message_type = "warning";

        } else {

            $stmt = $conn->prepare(
                "INSERT INTO interviews
                (
                    application_id,
                    interview_date,
                    interview_time,
                    interview_type,
                    meeting_link,
                    notes,
                    status
                )

                VALUES (?, ?, ?, ?, ?, ?, 'scheduled')"
            );

            $stmt->bind_param(
                "isssss",
                $application_id,
                $interview_date,
                $interview_time,
                $interview_type,
                $meeting_link,
                $notes
            );

            if ($stmt->execute()) {

                $stmt->close();

                header(
                    "Location: applicants.php?job_id=" . $job_id
                );

                exit();

            } else {

                $message =
                    "Failed to schedule interview.";

                $message_type = "error";

                $stmt->close();
            }
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

    <title>Schedule Interview - JIMS</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {

            font-family:
                Inter,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;

            color: #172033;

            min-height: 100vh;

            background:
                radial-gradient(
                    circle at 10% 10%,
                    rgba(37, 99, 235, 0.13),
                    transparent 28%
                ),

                radial-gradient(
                    circle at 90% 15%,
                    rgba(79, 70, 229, 0.12),
                    transparent 30%
                ),

                linear-gradient(
                    135deg,
                    #eef4ff 0%,
                    #f8fafc 48%,
                    #eef2ff 100%
                );

            padding-bottom: 50px;
        }

        .navbar {

            height: 72px;

            position: sticky;
            top: 0;
            z-index: 100;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 6%;

            background:
                rgba(255, 255, 255, 0.68);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            border-bottom:
                1px solid rgba(255, 255, 255, 0.75);

            box-shadow:
                0 8px 30px rgba(15, 23, 42, 0.05);
        }

        .brand {

            text-decoration: none;

            font-size: 25px;
            font-weight: 800;

            letter-spacing: -0.8px;

            color: #2563eb;
        }

        .brand span {
            color: #172033;
        }

        .nav_actions {

            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav_btn {

            text-decoration: none;

            padding: 10px 17px;

            border-radius: 12px;

            font-size: 14px;
            font-weight: 600;

            transition: 0.2s ease;
        }

        .dashboard_btn {

            color: #1d4ed8;

            background:
                rgba(219, 234, 254, 0.75);

            border:
                1px solid rgba(147, 197, 253, 0.35);
        }

        .dashboard_btn:hover {

            background:
                rgba(191, 219, 254, 0.9);

            transform: translateY(-1px);
        }

        .logout_btn {

            color: #dc2626;

            background:
                rgba(254, 226, 226, 0.72);

            border:
                1px solid rgba(252, 165, 165, 0.3);
        }

        .logout_btn:hover {

            background:
                rgba(254, 202, 202, 0.9);

            transform: translateY(-1px);
        }

        .container {

            width: 88%;

            max-width: 900px;

            margin: 0 auto;

            padding-top: 42px;
        }

        .back_link {

            display: inline-flex;
            align-items: center;

            gap: 7px;

            text-decoration: none;

            color: #2563eb;

            font-size: 14px;
            font-weight: 600;

            margin-bottom: 22px;

            transition: 0.2s ease;
        }

        .back_link:hover {
            transform: translateX(-3px);
        }

        .page_header {

            margin-bottom: 26px;
        }

        .page_header h1 {

            font-size: 32px;

            letter-spacing: -0.7px;

            color: #111827;

            margin-bottom: 7px;
        }

        .page_header p {

            color: #667085;

            font-size: 15px;
        }

        .glass_card {

            background:
                rgba(255, 255, 255, 0.68);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            border:
                1px solid rgba(255, 255, 255, 0.82);

            box-shadow:
                0 15px 45px rgba(15, 23, 42, 0.07);

            border-radius: 20px;

            padding: 26px;
        }

        .applicant_card {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 18px;

            margin-bottom: 22px;
        }

        .section_title {

            display: flex;
            align-items: center;

            gap: 10px;

            font-size: 18px;

            color: #111827;

            margin-bottom: 20px;
        }

        .section_icon {

            width: 38px;
            height: 38px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 12px;

            background:
                linear-gradient(
                    135deg,
                    #eff6ff,
                    #eef2ff
                );

            color: #2563eb;

            font-size: 18px;
        }

        .info_box {

            padding: 15px 17px;

            border-radius: 14px;

            background:
                rgba(248, 250, 252, 0.75);

            border:
                1px solid rgba(226, 232, 240, 0.8);
        }

        .info_label {

            display: block;

            color: #98a2b3;

            font-size: 12px;

            font-weight: 600;

            text-transform: uppercase;

            letter-spacing: 0.4px;

            margin-bottom: 5px;
        }

        .info_value {

            color: #172033;

            font-size: 15px;

            font-weight: 650;
        }

        .status_badge {

            display: inline-flex;
            align-items: center;

            gap: 5px;

            padding: 7px 12px;

            border-radius: 999px;

            font-size: 12px;

            font-weight: 700;

            color: #6d28d9;

            background:
                #f5f3ff;

            border:
                1px solid #ede9fe;
        }

        .alert {

            padding: 14px 17px;

            border-radius: 13px;

            margin-bottom: 22px;

            font-size: 14px;

            font-weight: 600;

            border: 1px solid transparent;
        }

        .alert_error {

            color: #b91c1c;

            background:
                rgba(254, 226, 226, 0.82);

            border-color:
                rgba(252, 165, 165, 0.45);
        }

        .alert_warning {

            color: #92400e;

            background:
                rgba(254, 243, 199, 0.82);

            border-color:
                rgba(253, 230, 138, 0.55);
        }

        .alert_success {

            color: #166534;

            background:
                rgba(220, 252, 231, 0.82);

            border-color:
                rgba(134, 239, 172, 0.45);
        }

        .existing_card {

            margin-top: 4px;
        }

        .scheduled_header {

            display: flex;
            align-items: center;
            gap: 13px;

            margin-bottom: 24px;
        }

        .success_icon {

            width: 46px;
            height: 46px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 14px;

            background:
                #ecfdf3;

            color: #16a34a;

            font-size: 22px;
        }

        .scheduled_header h2 {

            color: #111827;

            font-size: 20px;

            margin-bottom: 3px;
        }

        .scheduled_header p {

            color: #667085;

            font-size: 13px;
        }

        .interview_grid {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 15px;
        }

        .meeting_box {

            margin-top: 18px;

            padding: 15px 17px;

            border-radius: 14px;

            background:
                rgba(239, 246, 255, 0.75);

            border:
                1px solid rgba(191, 219, 254, 0.7);
        }

        .meeting_box a {

            display: inline-block;

            margin-top: 7px;

            color: #2563eb;

            text-decoration: none;

            font-size: 14px;

            font-weight: 700;

            word-break: break-all;
        }

        .meeting_box a:hover {
            text-decoration: underline;
        }

        .notes_box {

            margin-top: 18px;

            padding: 16px 17px;

            border-radius: 14px;

            background:
                rgba(248, 250, 252, 0.78);

            border:
                1px solid rgba(226, 232, 240, 0.8);

            color: #475467;

            line-height: 1.65;

            font-size: 14px;
        }

        .already_text {

            margin-top: 18px;

            padding-top: 17px;

            border-top:
                1px solid rgba(226, 232, 240, 0.75);

            color: #166534;

            font-size: 14px;

            font-weight: 700;
        }

        .form_card {

            margin-top: 22px;
        }

        .form_header {

            display: flex;
            align-items: center;

            gap: 13px;

            margin-bottom: 24px;
        }

        .form_icon {

            width: 46px;
            height: 46px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 14px;

            background:
                linear-gradient(
                    135deg,
                    #eff6ff,
                    #eef2ff
                );

            color: #2563eb;

            font-size: 21px;
        }

        .form_header h2 {

            font-size: 20px;

            color: #111827;

            margin-bottom: 3px;
        }

        .form_header p {

            font-size: 13px;

            color: #667085;
        }

        .form_group {

            margin-bottom: 20px;
        }

        .form_group label {

            display: block;

            color: #344054;

            font-size: 13px;

            font-weight: 650;

            margin-bottom: 8px;
        }

        .required {
            color: #dc2626;
        }

        input,
        select,
        textarea {

            width: 100%;

            border: 1px solid #dbe3ef;

            background:
                rgba(255, 255, 255, 0.78);

            border-radius: 12px;

            padding: 12px 14px;

            font-family: inherit;

            font-size: 14px;

            color: #172033;

            outline: none;

            transition: 0.2s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {

            border-color: #93c5fd;

            background: #ffffff;

            box-shadow:
                0 0 0 4px rgba(37, 99, 235, 0.08);
        }

        textarea {

            resize: vertical;

            min-height: 120px;

            line-height: 1.6;
        }

        input::placeholder,
        textarea::placeholder {
            color: #98a2b3;
        }

        .form_row {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 18px;
        }

        .helper_text {

            display: block;

            color: #98a2b3;

            font-size: 12px;

            margin-top: 6px;
        }

        .submit_area {

            display: flex;

            justify-content: flex-end;

            margin-top: 25px;

            padding-top: 20px;

            border-top:
                1px solid rgba(226, 232, 240, 0.75);
        }

        .submit_btn {

            border: none;

            cursor: pointer;

            padding: 12px 22px;

            border-radius: 12px;

            color: white;

            font-size: 14px;

            font-weight: 700;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #4f46e5
                );

            box-shadow:
                0 8px 18px rgba(37, 99, 235, 0.18);

            transition: 0.2s ease;
        }

        .submit_btn:hover {

            transform: translateY(-2px);

            box-shadow:
                0 12px 25px rgba(37, 99, 235, 0.25);
        }

        .footer {

            text-align: center;

            color: #98a2b3;

            font-size: 12px;

            margin-top: 30px;
        }

        @media (max-width: 700px) {

            .navbar {
                padding: 0 5%;
            }

            .dashboard_btn {
                display: none;
            }

            .container {

                width: 92%;

                padding-top: 30px;
            }

            .page_header h1 {
                font-size: 27px;
            }

            .applicant_card,
            .interview_grid,
            .form_row {

                grid-template-columns: 1fr;
            }

            .glass_card {
                padding: 20px;
            }

            .submit_area {
                justify-content: stretch;
            }

            .submit_btn {
                width: 100%;
            }
        }

        @media (max-width: 450px) {

            .brand {
                font-size: 22px;
            }

            .nav_btn {
                padding: 9px 12px;
            }

            .page_header h1 {
                font-size: 24px;
            }
        }

    </style>

</head>

<body>

<nav class="navbar">

    <a
        href="dashboard.php"
        class="brand"
    >
        JIMS<span>.</span>
    </a>

    <div class="nav_actions">

        <a
            href="dashboard.php"
            class="nav_btn dashboard_btn"
        >
            Dashboard
        </a>

        <a
            href="../auth/logout.php"
            class="nav_btn logout_btn"
        >
            Logout
        </a>

    </div>

</nav>

<main class="container">

    <a
        href="applicants.php?job_id=<?php echo $job_id; ?>"
        class="back_link"
    >
        ← Back to Applicants
    </a>

    <div class="page_header">

        <h1>
            Schedule Interview
        </h1>

        <p>
            Arrange an interview for the shortlisted applicant.
        </p>

    </div>

    <div class="glass_card applicant_card">

        <div>

            <h2 class="section_title">

                <span class="section_icon">
                    👤
                </span>

                Applicant Information

            </h2>

            <div class="info_box">

                <span class="info_label">
                    Student
                </span>

                <span class="info_value">

                    <?php
                    echo htmlspecialchars(
                        $application["student_name"]
                    );
                    ?>

                </span>

            </div>

        </div>

        <div>

            <h2 class="section_title">

                <span class="section_icon">
                    💼
                </span>

                Opportunity

            </h2>

            <div class="info_box">

                <span class="info_label">
                    Job / Internship
                </span>

                <span class="info_value">

                    <?php
                    echo htmlspecialchars(
                        $application["job_title"]
                    );
                    ?>

                </span>

            </div>

        </div>

    </div>

    <div class="glass_card" style="margin-bottom:22px;">

        <div class="info_box">

            <span class="info_label">
                Application Status
            </span>

            <span class="status_badge">
                ⭐ Shortlisted
            </span>

        </div>

    </div>

    <?php if (!empty($message)): ?>

        <div
            class="alert
            <?php
            echo $message_type === "warning"
                ? "alert_warning"
                : "alert_error";
            ?>"
        >

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>

    <?php if ($existing_interview): ?>

        <div class="glass_card existing_card">

            <div class="scheduled_header">

                <div class="success_icon">
                    ✓
                </div>

                <div>

                    <h2>
                        Interview Already Scheduled
                    </h2>

                    <p>
                        The interview details are shown below.
                    </p>

                </div>

            </div>

            <div class="interview_grid">

                <div class="info_box">

                    <span class="info_label">
                        Date
                    </span>

                    <span class="info_value">

                        <?php
                        echo htmlspecialchars(
                            $existing_interview["interview_date"]
                        );
                        ?>

                    </span>

                </div>

                <div class="info_box">

                    <span class="info_label">
                        Time
                    </span>

                    <span class="info_value">

                        <?php
                        echo htmlspecialchars(
                            $existing_interview["interview_time"]
                        );
                        ?>

                    </span>

                </div>

                <div class="info_box">

                    <span class="info_label">
                        Interview Type
                    </span>

                    <span class="info_value">

                        <?php
                        echo htmlspecialchars(
                            $existing_interview["interview_type"]
                        );
                        ?>

                    </span>

                </div>

                <div class="info_box">

                    <span class="info_label">
                        Status
                    </span>

                    <span class="status_badge">
                        ✓ Scheduled
                    </span>

                </div>

            </div>

            <?php if (!empty($existing_interview["meeting_link"])): ?>

                <div class="meeting_box">

                    <span class="info_label">
                        Meeting Link
                    </span>

                    <a
                        href="<?php
                        echo htmlspecialchars(
                            $existing_interview["meeting_link"]
                        );
                        ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Open Meeting →
                    </a>

                </div>

            <?php endif; ?>

            <?php if (!empty($existing_interview["notes"])): ?>

                <div class="notes_box">

                    <strong>
                        Notes / Instructions
                    </strong>

                    <br><br>

                    <?php
                    echo nl2br(
                        htmlspecialchars(
                            $existing_interview["notes"]
                        )
                    );
                    ?>

                </div>

            <?php endif; ?>

            <div class="already_text">

                ✓ Interview has already been scheduled for this application.

            </div>

        </div>

    <?php else: ?>

        <div class="glass_card form_card">

            <div class="form_header">

                <div class="form_icon">
                    📅
                </div>

                <div>

                    <h2>
                        Interview Details
                    </h2>

                    <p>
                        Set the date, time and interview method.
                    </p>

                </div>

            </div>

            <form method="POST">

                <div class="form_row">

                    <div class="form_group">

                        <label for="interview_date">

                            Interview Date
                            <span class="required">*</span>

                        </label>

                        <input
                            type="date"
                            name="interview_date"
                            id="interview_date"
                            required
                        >

                    </div>

                    <div class="form_group">

                        <label for="interview_time">

                            Interview Time
                            <span class="required">*</span>

                        </label>

                        <input
                            type="time"
                            name="interview_time"
                            id="interview_time"
                            required
                        >

                    </div>

                </div>

                <div class="form_group">

                    <label for="interview_type">

                        Interview Type
                        <span class="required">*</span>

                    </label>

                    <select
                        name="interview_type"
                        id="interview_type"
                        required
                    >

                        <option value="">
                            Select Interview Type
                        </option>

                        <option value="Online">
                            Online
                        </option>

                        <option value="Physical">
                            Physical
                        </option>

                        <option value="Phone">
                            Phone
                        </option>

                    </select>

                    <span class="helper_text">
                        Select how the interview will be conducted.
                    </span>

                </div>

                <div class="form_group">

                    <label for="meeting_link">

                        Meeting Link

                    </label>

                    <input
                        type="url"
                        name="meeting_link"
                        id="meeting_link"
                        placeholder="https://meet.google.com/..."
                    >

                    <span
                        class="helper_text"
                        id="meeting_help"
                    >
                        Required only for online interviews.
                    </span>

                </div>

                <div class="form_group">

                    <label for="notes">

                        Notes / Instructions

                    </label>

                    <textarea
                        name="notes"
                        id="notes"
                        rows="5"
                        placeholder="Interview instructions, documents to bring, preparation guidelines..."
                    ></textarea>

                    <span class="helper_text">
                        Add any useful instructions for the applicant.
                    </span>

                </div>

                <div class="submit_area">

                    <button
                        type="submit"
                        class="submit_btn"
                    >
                        📅 Schedule Interview
                    </button>

                </div>

            </form>

        </div>

    <?php endif; ?>

    <div class="footer">

        JIMS — Job & Internship Management System

    </div>

</main>

<script>

    const interviewType =
        document.getElementById("interview_type");

    const meetingLink =
        document.getElementById("meeting_link");

    const meetingHelp =
        document.getElementById("meeting_help");

    function updateMeetingLink() {

        if (interviewType.value === "Online") {

            meetingLink.required = true;

            meetingLink.placeholder =
                "https://meet.google.com/...";

            meetingHelp.textContent =
                "Meeting link is required for online interviews.";

        } else {

            meetingLink.required = false;

            meetingLink.value = "";

            meetingLink.placeholder =
                "Not required for Physical / Phone";

            meetingHelp.textContent =
                "Required only for online interviews.";

        }

    }

    interviewType.addEventListener(
        "change",
        updateMeetingLink
    );

    updateMeetingLink();

</script>

</body>

</html>