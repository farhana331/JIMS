<?php

session_start();

require_once "../config/database.php";

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] != "student"
) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$studentStmt = $conn->prepare("
    SELECT student_id
    FROM students
    WHERE user_id = ?
    LIMIT 1
");

$studentStmt->bind_param("i", $user_id);
$studentStmt->execute();

$studentResult = $studentStmt->get_result();

if ($studentResult->num_rows === 0) {
    die("Student profile not found.");
}

$student = $studentResult->fetch_assoc();
$student_id = $student["student_id"];

$studentStmt->close();

$sql = "
    SELECT
        a.application_id,
        a.job_id,
        a.student_id,
        a.cover_letter,
        a.cv_file,
        a.status,
        a.applied_at,

        j.title,
        j.job_type,
        j.location,
        j.salary,
        j.deadline,

        c.company_name,

        i.interview_id,
        i.interview_date,
        i.interview_time,
        i.interview_type,
        i.meeting_link,
        i.notes,
        i.status AS interview_status

    FROM applications a

    INNER JOIN jobs j
        ON a.job_id = j.job_id

    INNER JOIN companies c
        ON j.company_id = c.company_id

    LEFT JOIN interviews i
        ON a.application_id = i.application_id
        AND i.status = 'scheduled'

    WHERE a.student_id = ?

    ORDER BY a.applied_at DESC, a.application_id DESC
";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $student_id);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Applications - JIMS</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, Helvetica, sans-serif;

    background:
        radial-gradient(
            circle at top left,
            rgba(37, 99, 235, 0.10),
            transparent 35%
        ),
        radial-gradient(
            circle at bottom right,
            rgba(79, 70, 229, 0.08),
            transparent 35%
        ),
        linear-gradient(
            135deg,
            #eef4ff,
            #f8fafc,
            #eef2ff
        );

    color: #172033;
    min-height: 100vh;
}

.header {
    height: 72px;

    position: sticky;
    top: 0;
    z-index: 100;

    display: flex;
    justify-content: space-between;
    align-items: center;

    padding: 0 6%;

    background: rgba(255, 255, 255, 0.68);

    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);

    border-bottom: 1px solid rgba(255, 255, 255, 0.8);

    box-shadow:
        0 5px 20px rgba(15, 23, 42, 0.05);
}

.logo {
    font-size: 25px;
    font-weight: 800;
    color: #2563eb;
}

.logo span {
    color: #172033;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.dashboard-btn {
    text-decoration: none;

    color: #2563eb;

    background: rgba(239, 246, 255, 0.9);

    border: 1px solid rgba(191, 219, 254, 0.8);

    padding: 9px 15px;

    border-radius: 9px;

    font-size: 13px;
    font-weight: 700;

    transition: 0.2s ease;
}

.dashboard-btn:hover {
    background: #dbeafe;
}

.logout-btn {
    text-decoration: none;

    color: #dc2626;

    background: rgba(254, 242, 242, 0.9);

    border: 1px solid rgba(254, 202, 202, 0.8);

    padding: 9px 15px;

    border-radius: 9px;

    font-size: 13px;
    font-weight: 700;

    transition: 0.2s ease;
}

.logout-btn:hover {
    background: #fee2e2;
}

.container {
    width: 88%;
    max-width: 1250px;

    margin: 0 auto;

    padding: 42px 0 60px;
}

.page-header {
    margin-bottom: 28px;
}

.page-header h1 {
    font-size: 30px;
    color: #111827;
    margin-bottom: 8px;
}

.page-header p {
    color: #667085;
    font-size: 14px;
}

.application-count {
    display: inline-block;

    margin-top: 14px;

    padding: 7px 12px;

    border-radius: 20px;

    background: #eff6ff;

    color: #1d4ed8;

    font-size: 12px;
    font-weight: 700;
}

.application-grid {
    display: grid;

    grid-template-columns: repeat(2, 1fr);

    gap: 20px;
}

.application-card {
    background: rgba(255, 255, 255, 0.68);

    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);

    border: 1px solid rgba(255, 255, 255, 0.85);

    border-radius: 20px;

    padding: 24px;

    box-shadow:
        0 10px 30px rgba(15, 23, 42, 0.07);

    transition: 0.2s ease;
}

.application-card:hover {
    transform: translateY(-3px);

    box-shadow:
        0 15px 35px rgba(15, 23, 42, 0.10);
}

.card-top {
    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 15px;

    margin-bottom: 16px;
}

.job-title {
    font-size: 20px;

    font-weight: 750;

    color: #111827;

    margin-bottom: 7px;
}

.company {
    color: #475467;

    font-size: 14px;

    font-weight: 600;
}

.type-badge {
    display: inline-block;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 700;

    white-space: nowrap;
}

.job-badge {
    background: #eff6ff;
    color: #1d4ed8;
}

.internship-badge {
    background: #f5f3ff;
    color: #6d28d9;
}

.status-badge {
    display: inline-block;

    padding: 7px 12px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 700;

    margin-top: 5px;
}

.status-pending {
    background: #fff7ed;
    color: #c2410c;
}

.status-shortlisted {
    background: #eff6ff;
    color: #1d4ed8;
}

.status-selected {
    background: #ecfdf3;
    color: #15803d;
}

.status-rejected {
    background: #fef2f2;
    color: #b91c1c;
}

.status-default {
    background: #f3f4f6;
    color: #374151;
}

.info-grid {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 10px;

    margin: 18px 0;
}

.info-item {
    background: rgba(248, 250, 252, 0.85);

    border-radius: 10px;

    padding: 11px 12px;
}

.info-label {
    display: block;

    font-size: 11px;

    color: #98a2b3;

    margin-bottom: 4px;
}

.info-value {
    font-size: 13px;

    font-weight: 600;

    color: #344054;
}

.applied-date {
    color: #667085;

    font-size: 12px;

    margin-top: 15px;

    padding-top: 14px;

    border-top: 1px solid rgba(226, 232, 240, 0.8);
}

.interview-box {
    margin-top: 20px;

    padding: 17px;

    border-radius: 14px;

    background:
        linear-gradient(
            135deg,
            rgba(239, 246, 255, 0.95),
            rgba(238, 242, 255, 0.95)
        );

    border: 1px solid #dbeafe;
}

.interview-header {
    display: flex;

    align-items: center;

    gap: 9px;

    margin-bottom: 14px;
}

.interview-icon {
    width: 32px;
    height: 32px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 9px;

    background: #2563eb;

    color: white;

    font-size: 15px;
}

.interview-title {
    color: #1e3a8a;

    font-size: 15px;

    font-weight: 750;
}

.interview-grid {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 10px;
}

.interview-item {
    background: rgba(255, 255, 255, 0.72);

    border-radius: 9px;

    padding: 10px;
}

.interview-label {
    display: block;

    color: #64748b;

    font-size: 10px;

    margin-bottom: 4px;
}

.interview-value {
    color: #1e293b;

    font-size: 12px;

    font-weight: 700;
}

.meeting-link {
    display: inline-block;

    margin-top: 12px;

    text-decoration: none;

    background: #2563eb;

    color: white;

    padding: 9px 13px;

    border-radius: 8px;

    font-size: 11px;

    font-weight: 700;

    transition: 0.2s ease;
}

.meeting-link:hover {
    background: #1d4ed8;
}

.interview-notes {
    margin-top: 12px;

    padding: 10px 12px;

    background: rgba(255, 255, 255, 0.72);

    border-radius: 9px;

    color: #475467;

    font-size: 12px;

    line-height: 1.5;
}

.interview-notes strong {
    color: #344054;
}

.cv-link {
    display: inline-block;

    margin-top: 15px;

    text-decoration: none;

    color: #2563eb;

    font-size: 12px;

    font-weight: 700;
}

.cv-link:hover {
    text-decoration: underline;
}

.empty-card {
    background: rgba(255, 255, 255, 0.68);

    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);

    border: 1px solid rgba(255, 255, 255, 0.85);

    border-radius: 20px;

    padding: 70px 20px;

    text-align: center;

    box-shadow:
        0 10px 30px rgba(15, 23, 42, 0.07);
}

.empty-icon {
    font-size: 48px;

    margin-bottom: 15px;
}

.empty-card h3 {
    color: #111827;

    margin-bottom: 8px;
}

.empty-card p {
    color: #667085;

    font-size: 14px;

    margin-bottom: 20px;
}

.browse-btn {
    display: inline-block;

    text-decoration: none;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    color: white;

    padding: 11px 17px;

    border-radius: 9px;

    font-size: 12px;

    font-weight: 700;
}

@media (max-width: 950px) {

    .application-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 650px) {

    .header {
        padding: 0 5%;
    }

    .dashboard-btn {
        display: none;
    }

    .container {
        width: 94%;

        padding-top: 30px;
    }

    .page-header h1 {
        font-size: 25px;
    }

    .card-top {
        flex-direction: column;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .interview-grid {
        grid-template-columns: 1fr;
    }

    .application-card {
        padding: 20px;
    }
}

</style>

</head>

<body>

<header class="header">

    <div class="logo">
        J<span>IMS</span>
    </div>

    <div class="header-right">

        <a
            href="dashboard.php"
            class="dashboard-btn"
        >
            Dashboard
        </a>

        <a
            href="../auth/logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

</header>

<div class="container">

    <div class="page-header">

        <h1>
            My Applications
        </h1>

        <p>
            Track your job and internship applications and interview details.
        </p>

        <span class="application-count">
            <?php echo $result->num_rows; ?>
            Application<?php echo ($result->num_rows != 1) ? "s" : ""; ?>
        </span>

    </div>

    <?php if ($result->num_rows > 0): ?>

        <div class="application-grid">

        <?php while ($application = $result->fetch_assoc()): ?>

            <?php

            $status = strtolower(
                trim($application["status"] ?? "")
            );

            switch ($status) {

                case "pending":
                    $statusClass = "status-pending";
                    break;

                case "shortlisted":
                    $statusClass = "status-shortlisted";
                    break;

                case "selected":
                    $statusClass = "status-selected";
                    break;

                case "rejected":
                    $statusClass = "status-rejected";
                    break;

                default:
                    $statusClass = "status-default";
                    break;
            }

            $appliedDate = "Not available";

            if (!empty($application["applied_at"])) {

                $appliedDate = date(
                    "d M Y, h:i A",
                    strtotime($application["applied_at"])
                );
            }

            $hasInterview =
                !empty($application["interview_id"]);

            ?>

            <div class="application-card">

                <div class="card-top">

                    <div>

                        <div class="job-title">

                            <?php
                            echo htmlspecialchars(
                                $application["title"]
                            );
                            ?>

                        </div>

                        <div class="company">

                            🏢

                            <?php
                            echo htmlspecialchars(
                                $application["company_name"]
                            );
                            ?>

                        </div>

                        <span
                            class="status-badge <?php echo $statusClass; ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                ucfirst($status)
                            );
                            ?>

                        </span>

                    </div>

                    <?php if ($application["job_type"] == "Internship"): ?>

                        <span class="type-badge internship-badge">
                            Internship
                        </span>

                    <?php else: ?>

                        <span class="type-badge job-badge">
                            Job
                        </span>

                    <?php endif; ?>

                </div>

                <div class="info-grid">

                    <div class="info-item">

                        <span class="info-label">
                            Location
                        </span>

                        <span class="info-value">

                            📍

                            <?php
                            echo htmlspecialchars(
                                $application["location"]
                                ?? "Not specified"
                            );
                            ?>

                        </span>

                    </div>

                    <div class="info-item">

                        <span class="info-label">
                            Salary
                        </span>

                        <span class="info-value">

                            💰

                            <?php
                            echo htmlspecialchars(
                                $application["salary"]
                                ?? "Not specified"
                            );
                            ?>

                        </span>

                    </div>

                    <div class="info-item">

                        <span class="info-label">
                            Deadline
                        </span>

                        <span class="info-value">

                            🗓️

                            <?php
                            echo htmlspecialchars(
                                $application["deadline"]
                                ?? "Not specified"
                            );
                            ?>

                        </span>

                    </div>

                    <div class="info-item">

                        <span class="info-label">
                            Application ID
                        </span>

                        <span class="info-value">

                            #<?php
                            echo htmlspecialchars(
                                $application["application_id"]
                            );
                            ?>

                        </span>

                    </div>

                </div>

                <div class="applied-date">

                    Applied on:

                    <strong>
                        <?php echo htmlspecialchars($appliedDate); ?>
                    </strong>

                </div>

<?php if (!empty($application["cv_file"])): ?>

    <a
        href="../uploads/cvs/<?php echo htmlspecialchars($application["cv_file"]); ?>"
        target="_blank"
        class="cv-link"
    >
        📄 View Submitted CV →
    </a>

<?php endif; ?>

                <?php if ($hasInterview): ?>

                    <div class="interview-box">

                        <div class="interview-header">

                            <div class="interview-icon">
                                🎤
                            </div>

                            <div class="interview-title">
                                Interview Scheduled
                            </div>

                        </div>

                        <div class="interview-grid">

                            <div class="interview-item">

                                <span class="interview-label">
                                    Date
                                </span>

                                <span class="interview-value">

                                    📅

                                    <?php
                                    echo htmlspecialchars(
                                        $application["interview_date"]
                                    );
                                    ?>

                                </span>

                            </div>

                            <div class="interview-item">

                                <span class="interview-label">
                                    Time
                                </span>

                                <span class="interview-value">

                                    🕐

                                    <?php
                                    echo htmlspecialchars(
                                        $application["interview_time"]
                                    );
                                    ?>

                                </span>

                            </div>

                            <div class="interview-item">

                                <span class="interview-label">
                                    Interview Type
                                </span>

                                <span class="interview-value">

                                    <?php
                                    if (
                                        strtolower(
                                            $application["interview_type"]
                                        ) == "online"
                                    ) {
                                        echo "💻 Online";
                                    } else {
                                        echo "🏢 " .
                                            htmlspecialchars(
                                                $application["interview_type"]
                                            );
                                    }
                                    ?>

                                </span>

                            </div>

                            <div class="interview-item">

                                <span class="interview-label">
                                    Interview Status
                                </span>

                                <span class="interview-value">

                                    <?php
                                    echo htmlspecialchars(
                                        ucfirst(
                                            $application["interview_status"]
                                        )
                                    );
                                    ?>

                                </span>

                            </div>

                        </div>

                        <?php
                        if (
                            strtolower(
                                $application["interview_type"]
                            ) == "online"
                            &&
                            !empty(
                                $application["meeting_link"]
                            )
                        ):
                        ?>

                            <a
                                href="<?php echo htmlspecialchars($application["meeting_link"]); ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="meeting-link"
                            >
                                🔗 Join Interview →
                            </a>

                        <?php endif; ?>

                        <?php if (!empty($application["notes"])): ?>

                            <div class="interview-notes">

                                <strong>
                                    📝 Interview Notes:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $application["notes"]
                                );
                                ?>

                            </div>

                        <?php endif; ?>

                    </div>

                <?php endif; ?>

            </div>

        <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty-card">

            <div class="empty-icon">
                📄
            </div>

            <h3>
                No Applications Yet
            </h3>

            <p>
                You haven't applied for any jobs or internships yet.
            </p>

            <a
                href="jobs.php"
                class="browse-btn"
            >
                Browse Jobs →
            </a>

        </div>

    <?php endif; ?>

</div>

</body>

</html>

<?php

$stmt->close();
$conn->close();

?>