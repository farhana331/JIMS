
<?php

session_start();

require_once "../config/database.php";


// ONLY STUDENT CAN ACCESS

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] != "student"
) {
    header("Location: ../auth/login.php");
    exit();
}


$user_id = $_SESSION["user_id"];


// CHECK JOB ID


if (
    !isset($_GET["job_id"]) ||
    !is_numeric($_GET["job_id"])
) {
    header("Location: jobs.php");
    exit();
}


$job_id = intval($_GET["job_id"]);

$message = "";
$message_type = "";


// GET STUDENT ID AND CV


$stmt = $conn->prepare(
    "SELECT student_id, cv_file
     FROM students
     WHERE user_id = ?"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$student = $result->fetch_assoc();

$stmt->close();


if (!$student) {

    die("Student profile not found.");

}


$student_id = $student["student_id"];

$cv_file = $student["cv_file"];


// GET JOB


$stmt = $conn->prepare(
    "SELECT
        j.job_id,
        j.title,
        j.job_type,
        j.deadline,
        c.company_name
     FROM jobs j
     INNER JOIN companies c
        ON j.company_id = c.company_id
     WHERE j.job_id = ?
     AND j.status = 'approved'"
);

$stmt->bind_param(<?php

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

if (
    !isset($_GET["job_id"]) ||
    !is_numeric($_GET["job_id"])
) {
    header("Location: jobs.php");
    exit();
}

$job_id = intval($_GET["job_id"]);

$message = "";
$message_type = "";

$stmt = $conn->prepare(
    "SELECT student_id, cv_file
     FROM students
     WHERE user_id = ?"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$student = $result->fetch_assoc();

$stmt->close();

if (!$student) {
    die("Student profile not found.");
}

$student_id = $student["student_id"];

$cv_file = $student["cv_file"];

$stmt = $conn->prepare(
    "SELECT
        j.job_id,
        j.title,
        j.job_type,
        j.deadline,
        c.company_name
     FROM jobs j
     INNER JOIN companies c
        ON j.company_id = c.company_id
     WHERE j.job_id = ?
     AND j.status = 'approved'"
);

$stmt->bind_param(
    "i",
    $job_id
);

$stmt->execute();

$result = $stmt->get_result();

$job = $result->fetch_assoc();

$stmt->close();

if (!$job) {
    die("Job or internship not found.");
}

if ($job["deadline"] < date("Y-m-d")) {
    die("The application deadline has passed.");
}

$stmt = $conn->prepare(
    "SELECT application_id
     FROM applications
     WHERE student_id = ?
     AND job_id = ?"
);

$stmt->bind_param(
    "ii",
    $student_id,
    $job_id
);

$stmt->execute();

$result = $stmt->get_result();

$alreadyApplied =
    ($result->num_rows > 0);

$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if ($alreadyApplied) {

        $message =
            "You have already applied for this opportunity.";

        $message_type = "warning";

    }

    elseif (empty($cv_file)) {

        $message =
            "Please upload your CV before applying.";

        $message_type = "error";

    }

    else {

        $status = "pending";

        $stmt = $conn->prepare(
            "INSERT INTO applications
            (
                student_id,
                job_id,
                cv_file,
                status
            )
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "iiss",
            $student_id,
            $job_id,
            $cv_file,
            $status
        );

        if ($stmt->execute()) {

            $message =
                "Application submitted successfully!";

            $message_type = "success";

            $alreadyApplied = true;

        }

        else {

            $message =
                "Failed to submit application.";

            $message_type = "error";

        }

        $stmt->close();

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

<title>
    Apply - JIMS
</title>

<style>

* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;

}

body {

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    min-height: 100vh;

    color: #172033;

    background:

        radial-gradient(
            circle at 10% 10%,
            rgba(37, 99, 235, 0.12),
            transparent 28%
        ),

        radial-gradient(
            circle at 90% 90%,
            rgba(99, 102, 241, 0.10),
            transparent 28%
        ),

        linear-gradient(
            135deg,
            #eef4ff,
            #f8fafc 45%,
            #eef2ff
        );

}

.navbar {

    height: 72px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 6%;

    position: sticky;

    top: 0;

    z-index: 100;

    background:
        rgba(255,255,255,0.68);

    border-bottom:
        1px solid
        rgba(255,255,255,0.8);

    backdrop-filter:
        blur(18px);

    -webkit-backdrop-filter:
        blur(18px);

    box-shadow:
        0 5px 25px
        rgba(15,23,42,0.05);

}

.brand {

    text-decoration: none;

    font-size: 26px;

    font-weight: 800;

    color: #2563eb;

}

.brand span {

    color: #172033;

}

.nav-right {

    display: flex;

    align-items: center;

    gap: 15px;

}

.dashboard-btn {

    text-decoration: none;

    color: #2563eb;

    font-size: 13px;

    font-weight: 700;

    padding: 9px 15px;

    border-radius: 9px;

    background:
        rgba(219,234,254,0.65);

    border:
        1px solid
        rgba(147,197,253,0.35);

    transition: 0.2s ease;

}

.dashboard-btn:hover {

    background:
        rgba(219,234,254,0.95);

}

.logout-btn {

    text-decoration: none;

    color: #dc2626;

    font-size: 13px;

    font-weight: 700;

    padding: 9px 15px;

    border-radius: 9px;

    background:
        rgba(254,226,226,0.70);

    border:
        1px solid
        rgba(252,165,165,0.35);

    transition: 0.2s ease;

}

.logout-btn:hover {

    background:
        rgba(254,226,226,0.95);

}

.container {

    width: 88%;

    max-width: 850px;

    margin: 0 auto;

    padding: 38px 0 60px;

}

.back-link {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    text-decoration: none;

    color: #2563eb;

    font-size: 13px;

    font-weight: 700;

    margin-bottom: 20px;

}

.back-link:hover {

    color: #1d4ed8;

}

.apply-container {

    padding: 30px;

    border-radius: 22px;

    background:
        rgba(255,255,255,0.68);

    border:
        1px solid
        rgba(255,255,255,0.82);

    backdrop-filter:
        blur(18px);

    -webkit-backdrop-filter:
        blur(18px);

    box-shadow:
        0 14px 40px
        rgba(15,23,42,0.08);

}

.page-header {

    margin-bottom: 26px;

}

.page-header h1 {

    font-size: 28px;

    color: #111827;

    margin-bottom: 7px;

    letter-spacing: -0.4px;

}

.page-header p {

    color: #667085;

    font-size: 13px;

}

.job-summary {

    padding: 20px;

    border-radius: 16px;

    background:
        rgba(239,246,255,0.65);

    border:
        1px solid
        rgba(147,197,253,0.30);

    margin-bottom: 23px;

}

.job-summary-top {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 15px;

}

.job-icon {

    width: 48px;

    height: 48px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 13px;

    background:
        rgba(219,234,254,0.85);

    font-size: 22px;

    margin-bottom: 12px;

}

.job-summary h2 {

    font-size: 20px;

    color: #172033;

    margin-bottom: 5px;

}

.company {

    color: #667085;

    font-size: 13px;

    font-weight: 600;

}

.type-badge {

    padding: 7px 11px;

    border-radius: 20px;

    font-size: 10px;

    font-weight: 800;

    white-space: nowrap;

}

.job-type {

    background:
        #dbeafe;

    color:
        #1d4ed8;

}

.internship-type {

    background:
        #ede9fe;

    color:
        #6d28d9;

}

.job-info {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 10px;

    margin-top: 17px;

}

.info-item {

    padding: 11px 13px;

    border-radius: 10px;

    background:
        rgba(255,255,255,0.70);

    border:
        1px solid
        rgba(255,255,255,0.85);

}

.info-label {

    color: #98a2b3;

    font-size: 10px;

    font-weight: 800;

    text-transform: uppercase;

    margin-bottom: 4px;

}

.info-value {

    color: #344054;

    font-size: 12px;

    font-weight: 650;

}

.message {

    padding: 13px 15px;

    border-radius: 11px;

    font-size: 12px;

    font-weight: 650;

    margin-bottom: 20px;

}

.message.success {

    background:
        #ecfdf3;

    color:
        #027a48;

    border:
        1px solid
        #abefc6;

}

.message.warning {

    background:
        #fffaeb;

    color:
        #b54708;

    border:
        1px solid
        #fedf89;

}

.message.error {

    background:
        #fef3f2;

    color:
        #b42318;

    border:
        1px solid
        #fecdca;

}

.application-card {

    padding: 22px;

    border-radius: 16px;

    background:
        rgba(248,250,252,0.72);

    border:
        1px solid
        #edf0f4;

}

.application-card h3 {

    font-size: 17px;

    color: #172033;

    margin-bottom: 8px;

}

.application-card p {

    color: #667085;

    font-size: 13px;

    line-height: 1.65;

    margin-bottom: 16px;

}

.cv-box {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 14px;

    border-radius: 12px;

    background:
        rgba(255,255,255,0.82);

    border:
        1px solid
        #e4e7ec;

    margin-bottom: 18px;

}

.cv-icon {

    width: 42px;

    height: 42px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 11px;

    background:
        #eff6ff;

    font-size: 20px;

}

.cv-info {

    min-width: 0;

}

.cv-label {

    color: #98a2b3;

    font-size: 10px;

    font-weight: 800;

    margin-bottom: 3px;

    text-transform: uppercase;

}

.cv-name {

    color: #344054;

    font-size: 12px;

    font-weight: 650;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

}

.primary-btn {

    width: 100%;

    height: 45px;

    border: none;

    border-radius: 10px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    color: white;

    font-size: 13px;

    font-weight: 800;

    cursor: pointer;

    box-shadow:
        0 7px 18px
        rgba(37,99,235,0.20);

    transition: 0.2s ease;

}

.primary-btn:hover {

    transform:
        translateY(-1px);

    box-shadow:
        0 10px 22px
        rgba(37,99,235,0.27);

}

.upload-btn {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 100%;

    height: 44px;

    border-radius: 10px;

    text-decoration: none;

    background:
        rgba(219,234,254,0.75);

    border:
        1px solid
        rgba(147,197,253,0.40);

    color: #2563eb;

    font-size: 13px;

    font-weight: 750;

    transition: 0.2s ease;

}

.upload-btn:hover {

    background:
        #dbeafe;

}

.applications-btn {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 100%;

    height: 44px;

    border-radius: 10px;

    text-decoration: none;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    color: white;

    font-size: 13px;

    font-weight: 750;

    box-shadow:
        0 7px 18px
        rgba(37,99,235,0.18);

}

.applications-btn:hover {

    box-shadow:
        0 10px 22px
        rgba(37,99,235,0.25);

}

.state-box {

    text-align: center;

    padding: 25px 15px;

}

.state-icon {

    width: 58px;

    height: 58px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    margin: 0 auto 15px;

    font-size: 27px;

}

.state-icon.warning {

    background:
        #fff7ed;

}

.state-icon.success {

    background:
        #ecfdf3;

}

.state-box h3 {

    color: #172033;

    font-size: 18px;

    margin-bottom: 7px;

}

.state-box p {

    color: #667085;

    font-size: 12px;

    margin-bottom: 18px;

}

.footer {

    text-align: center;

    color: #98a2b3;

    font-size: 11px;

    margin-top: 22px;

}

@media (max-width: 650px) {

    .navbar {

        padding: 0 5%;

    }

    .nav-right {

        gap: 7px;

    }

    .dashboard-btn,
    .logout-btn {

        padding:
            8px 10px;

        font-size: 11px;

    }

    .container {

        width: 90%;

        padding-top: 28px;

    }

    .apply-container {

        padding: 21px;

    }

    .job-summary {

        padding: 17px;

    }

    .job-summary-top {

        flex-direction: column;

    }

    .job-info {

        grid-template-columns:
            1fr;

    }

    .page-header h1 {

        font-size: 25px;

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

    <div class="nav-right">

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

</nav>

<main class="container">

    <a
        href="job_details.php?id=<?php
        echo $job_id;
        ?>"
        class="back-link"
    >

        ← Back to Job Details

    </a>

    <section class="apply-container">

        <div class="page-header">

            <h1>
                Apply for Opportunity
            </h1>

            <p>
                Review the opportunity details and
                submit your application.
            </p>

        </div>

        <div class="job-summary">

            <div class="job-summary-top">

                <div>

                    <div class="job-icon">

                        <?php

                        echo ($job["job_type"] == "Internship")
                            ? "🎓"
                            : "💼";

                        ?>

                    </div>

                    <h2>

                        <?php

                        echo htmlspecialchars(
                            $job["title"]
                        );

                        ?>

                    </h2>

                    <p class="company">

                        🏢

                        <?php

                        echo htmlspecialchars(
                            $job["company_name"]
                        );

                        ?>

                    </p>

                </div>

                <?php if ($job["job_type"] == "Internship"): ?>

                    <span
                        class="type-badge internship-type"
                    >

                        Internship

                    </span>

                <?php else: ?>

                    <span
                        class="type-badge job-type"
                    >

                        Job

                    </span>

                <?php endif; ?>

            </div>

            <div class="job-info">

                <div class="info-item">

                    <div class="info-label">

                        Job Type

                    </div>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $job["job_type"]
                        );

                        ?>

                    </div>

                </div>

                <div class="info-item">

                    <div class="info-label">

                        Application Deadline

                    </div>

                    <div class="info-value">

                        📅

                        <?php

                        echo htmlspecialchars(
                            $job["deadline"]
                        );

                        ?>

                    </div>

                </div>

            </div>

        </div>

        <?php if (!empty($message)): ?>

            <div
                class="message
                <?php echo $message_type; ?>"
            >

                <?php

                echo htmlspecialchars(
                    $message
                );

                ?>

            </div>

        <?php endif; ?>

        <div class="application-card">

            <?php if (empty($cv_file)): ?>

                <div class="state-box">

                    <div class="state-icon warning">

                        ⚠️

                    </div>

                    <h3>

                        CV Required

                    </h3>

                    <p>

                        You haven't uploaded a CV yet.
                        Please upload your CV before
                        applying for this opportunity.

                    </p>

                    <a
                        href="cv.php"
                        class="upload-btn"
                    >

                        Upload CV →

                    </a>

                </div>

            <?php elseif ($alreadyApplied): ?>

                <div class="state-box">

                    <div class="state-icon success">

                        ✓

                    </div>

                    <h3>

                        Application Already Submitted

                    </h3>

                    <p>

                        You have already applied for this
                        opportunity. You can track your
                        application status from your
                        applications page.

                    </p>

                    <a
                        href="applications.php"
                        class="applications-btn"
                    >

                        View My Applications →

                    </a>

                </div>

            <?php else: ?>

                <h3>

                    Your Application

                </h3>

                <p>

                    Your CV is ready to be submitted
                    with this application. Please review
                    the CV information below before
                    confirming your application.

                </p>

                <div class="cv-box">

                    <div class="cv-icon">

                        📄

                    </div>

                    <div class="cv-info">

                        <div class="cv-label">

                            Attached CV

                        </div>

                        <div class="cv-name">

                            <?php

                            echo htmlspecialchars(
                                $cv_file
                            );

                            ?>

                        </div>

                    </div>

                </div>

                <form
                    method="POST"
                >

                    <button
                        type="submit"
                        class="primary-btn"
                    >

                        Confirm & Submit Application →

                    </button>

                </form>

            <?php endif; ?>

        </div>

    </section>

    <div class="footer">

        © <?php echo date("Y"); ?>

        JIMS — Job & Internship Management System

    </div>

</main>

</body>

</html>
    "i",
    $job_id
);

$stmt->execute();

$result = $stmt->get_result();

$job = $result->fetch_assoc();

$stmt->close();


// Job not found

if (!$job) {

    die("Job or internship not found.");

}


// CHECK DEADLINE

if ($job["deadline"] < date("Y-m-d")) {

    die("The application deadline has passed.");

}


// CHECK EXISTING APPLICATION


$stmt = $conn->prepare(
    "SELECT application_id
     FROM applications
     WHERE student_id = ?
     AND job_id = ?"
);

$stmt->bind_param(
    "ii",
    $student_id,
    $job_id
);

$stmt->execute();

$result = $stmt->get_result();

$alreadyApplied =
    ($result->num_rows > 0);

$stmt->close();

// SUBMIT APPLICATION

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if ($alreadyApplied) {

        $message =
            "You have already applied for this opportunity.";

        $message_type = "warning";

    }

    elseif (empty($cv_file)) {

        $message =
            "Please upload your CV before applying.";

        $message_type = "error";

    }

    else {

        $status = "pending";


        $stmt = $conn->prepare(
            "INSERT INTO applications
            (
                student_id,
                job_id,
                cv_file,
                status
            )
            VALUES (?, ?, ?, ?)"
        );


        $stmt->bind_param(
            "iiss",
            $student_id,
            $job_id,
            $cv_file,
            $status
        );


        if ($stmt->execute()) {

            $message =
                "Application submitted successfully!";

            $message_type = "success";

            $alreadyApplied = true;

        }

        else {

            $message =
                "Failed to submit application.";

            $message_type = "error";

        }


        $stmt->close();

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

<title>
    Apply - JIMS
</title>


<style>

/* ========================================
   RESET
======================================== */

* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;

}


/* ========================================
 //  BODY
======================================== */

body {

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    min-height: 100vh;

    color: #172033;

    background:

        radial-gradient(
            circle at 10% 10%,
            rgba(37, 99, 235, 0.12),
            transparent 28%
        ),

        radial-gradient(
            circle at 90% 90%,
            rgba(99, 102, 241, 0.10),
            transparent 28%
        ),

        linear-gradient(
            135deg,
            #eef4ff,
            #f8fafc 45%,
            #eef2ff
        );

}


/* ========================================
   NAVBAR
======================================== */

.navbar {

    height: 72px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 6%;

    position: sticky;

    top: 0;

    z-index: 100;

    background:
        rgba(255,255,255,0.68);

    border-bottom:
        1px solid
        rgba(255,255,255,0.8);

    backdrop-filter:
        blur(18px);

    -webkit-backdrop-filter:
        blur(18px);

    box-shadow:
        0 5px 25px
        rgba(15,23,42,0.05);

}


/* BRAND */

.brand {

    text-decoration: none;

    font-size: 26px;

    font-weight: 800;

    color: #2563eb;

}


.brand span {

    color: #172033;

}


/* NAV RIGHT */

.nav-right {

    display: flex;

    align-items: center;

    gap: 15px;

}


/* DASHBOARD */

.dashboard-btn {

    text-decoration: none;

    color: #2563eb;

    font-size: 13px;

    font-weight: 700;

    padding: 9px 15px;

    border-radius: 9px;

    background:
        rgba(219,234,254,0.65);

    border:
        1px solid
        rgba(147,197,253,0.35);

    transition: 0.2s ease;

}


.dashboard-btn:hover {

    background:
        rgba(219,234,254,0.95);

}


/* LOGOUT */

.logout-btn {

    text-decoration: none;

    color: #dc2626;

    font-size: 13px;

    font-weight: 700;

    padding: 9px 15px;

    border-radius: 9px;

    background:
        rgba(254,226,226,0.70);

    border:
        1px solid
        rgba(252,165,165,0.35);

    transition: 0.2s ease;

}


.logout-btn:hover {

    background:
        rgba(254,226,226,0.95);

}


/* ========================================
   MAIN CONTAINER
======================================== */

.container {

    width: 88%;

    max-width: 850px;

    margin: 0 auto;

    padding: 38px 0 60px;

}


/* ========================================
   BACK LINK
======================================== */

.back-link {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    text-decoration: none;

    color: #2563eb;

    font-size: 13px;

    font-weight: 700;

    margin-bottom: 20px;

}


.back-link:hover {

    color: #1d4ed8;

}


/* ========================================
   MAIN CARD
======================================== */

.apply-container {

    padding: 30px;

    border-radius: 22px;

    background:
        rgba(255,255,255,0.68);

    border:
        1px solid
        rgba(255,255,255,0.82);

    backdrop-filter:
        blur(18px);

    -webkit-backdrop-filter:
        blur(18px);

    box-shadow:
        0 14px 40px
        rgba(15,23,42,0.08);

}


/* ========================================
   PAGE HEADER
======================================== */

.page-header {

    margin-bottom: 26px;

}


.page-header h1 {

    font-size: 28px;

    color: #111827;

    margin-bottom: 7px;

    letter-spacing: -0.4px;

}


.page-header p {

    color: #667085;

    font-size: 13px;

}


/* ========================================
   JOB SUMMARY
======================================== */

.job-summary {

    padding: 20px;

    border-radius: 16px;

    background:
        rgba(239,246,255,0.65);

    border:
        1px solid
        rgba(147,197,253,0.30);

    margin-bottom: 23px;

}


.job-summary-top {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 15px;

}


.job-icon {

    width: 48px;

    height: 48px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 13px;

    background:
        rgba(219,234,254,0.85);

    font-size: 22px;

    margin-bottom: 12px;

}


.job-summary h2 {

    font-size: 20px;

    color: #172033;

    margin-bottom: 5px;

}


.company {

    color: #667085;

    font-size: 13px;

    font-weight: 600;

}


/* TYPE BADGE */

.type-badge {

    padding: 7px 11px;

    border-radius: 20px;

    font-size: 10px;

    font-weight: 800;

    white-space: nowrap;

}


.job-type {

    background:
        #dbeafe;

    color:
        #1d4ed8;

}


.internship-type {

    background:
        #ede9fe;

    color:
        #6d28d9;

}


/* ========================================
   JOB INFO
======================================== */

.job-info {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 10px;

    margin-top: 17px;

}


.info-item {

    padding: 11px 13px;

    border-radius: 10px;

    background:
        rgba(255,255,255,0.70);

    border:
        1px solid
        rgba(255,255,255,0.85);

}


.info-label {

    color: #98a2b3;

    font-size: 10px;

    font-weight: 800;

    text-transform: uppercase;

    margin-bottom: 4px;

}


.info-value {

    color: #344054;

    font-size: 12px;

    font-weight: 650;

}


/* ========================================
   MESSAGE
======================================== */

.message {

    padding: 13px 15px;

    border-radius: 11px;

    font-size: 12px;

    font-weight: 650;

    margin-bottom: 20px;

}


.message.success {

    background:
        #ecfdf3;

    color:
        #027a48;

    border:
        1px solid
        #abefc6;

}


.message.warning {

    background:
        #fffaeb;

    color:
        #b54708;

    border:
        1px solid
        #fedf89;

}


.message.error {

    background:
        #fef3f2;

    color:
        #b42318;

    border:
        1px solid
        #fecdca;

}


/* ========================================
   APPLICATION SECTION
======================================== */

.application-card {

    padding: 22px;

    border-radius: 16px;

    background:
        rgba(248,250,252,0.72);

    border:
        1px solid
        #edf0f4;

}


.application-card h3 {

    font-size: 17px;

    color: #172033;

    margin-bottom: 8px;

}


.application-card p {

    color: #667085;

    font-size: 13px;

    line-height: 1.65;

    margin-bottom: 16px;

}


/* ========================================
   CV READY BOX
======================================== */

.cv-box {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 14px;

    border-radius: 12px;

    background:
        rgba(255,255,255,0.82);

    border:
        1px solid
        #e4e7ec;

    margin-bottom: 18px;

}


.cv-icon {

    width: 42px;

    height: 42px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 11px;

    background:
        #eff6ff;

    font-size: 20px;

}


.cv-info {

    min-width: 0;

}


.cv-label {

    color: #98a2b3;

    font-size: 10px;

    font-weight: 800;

    margin-bottom: 3px;

    text-transform: uppercase;

}


.cv-name {

    color: #344054;

    font-size: 12px;

    font-weight: 650;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

}


/* ========================================
   BUTTONS
======================================== */

.primary-btn {

    width: 100%;

    height: 45px;

    border: none;

    border-radius: 10px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    color: white;

    font-size: 13px;

    font-weight: 800;

    cursor: pointer;

    box-shadow:
        0 7px 18px
        rgba(37,99,235,0.20);

    transition: 0.2s ease;

}


.primary-btn:hover {

    transform:
        translateY(-1px);

    box-shadow:
        0 10px 22px
        rgba(37,99,235,0.27);

}


/* UPLOAD CV */

.upload-btn {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 100%;

    height: 44px;

    border-radius: 10px;

    text-decoration: none;

    background:
        rgba(219,234,254,0.75);

    border:
        1px solid
        rgba(147,197,253,0.40);

    color: #2563eb;

    font-size: 13px;

    font-weight: 750;

    transition: 0.2s ease;

}


.upload-btn:hover {

    background:
        #dbeafe;

}


/* APPLICATIONS */

.applications-btn {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 100%;

    height: 44px;

    border-radius: 10px;

    text-decoration: none;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    color: white;

    font-size: 13px;

    font-weight: 750;

    box-shadow:
        0 7px 18px
        rgba(37,99,235,0.18);

}


.applications-btn:hover {

    box-shadow:
        0 10px 22px
        rgba(37,99,235,0.25);

}


/* ========================================
   WARNING / SUCCESS STATE
======================================== */

.state-box {

    text-align: center;

    padding: 25px 15px;

}


.state-icon {

    width: 58px;

    height: 58px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    margin: 0 auto 15px;

    font-size: 27px;

}


.state-icon.warning {

    background:
        #fff7ed;

}


.state-icon.success {

    background:
        #ecfdf3;

}


.state-box h3 {

    color: #172033;

    font-size: 18px;

    margin-bottom: 7px;

}


.state-box p {

    color: #667085;

    font-size: 12px;

    margin-bottom: 18px;

}


/* ========================================
   FOOTER
======================================== */

.footer {

    text-align: center;

    color: #98a2b3;

    font-size: 11px;

    margin-top: 22px;

}


/* ========================================
   RESPONSIVE
======================================== */

@media (max-width: 650px) {

    .navbar {

        padding: 0 5%;

    }


    .nav-right {

        gap: 7px;

    }


    .dashboard-btn,
    .logout-btn {

        padding:
            8px 10px;

        font-size: 11px;

    }


    .container {

        width: 90%;

        padding-top: 28px;

    }


    .apply-container {

        padding: 21px;

    }


    .job-summary {

        padding: 17px;

    }


    .job-summary-top {

        flex-direction: column;

    }


    .job-info {

        grid-template-columns:
            1fr;

    }


    .page-header h1 {

        font-size: 25px;

    }

}

</style>

</head>


<body>


<!-- ========================================
     NAVBAR
======================================== -->

<nav class="navbar">


    <a
        href="dashboard.php"
        class="brand"
    >

        JIMS<span>.</span>

    </a>


    <div class="nav-right">


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


</nav>



<!-- ========================================
     MAIN
======================================== -->

<main class="container">


    <!-- BACK -->

    <a
        href="job_details.php?id=<?php
        echo $job_id;
        ?>"
        class="back-link"
    >

        ← Back to Job Details

    </a>



    <!-- MAIN CONTAINER -->

    <section class="apply-container">


        <!-- =================================
             HEADER
        ================================== -->

        <div class="page-header">

            <h1>
                Apply for Opportunity
            </h1>

            <p>
                Review the opportunity details and
                submit your application.
            </p>

        </div>



        <!-- =================================
             JOB SUMMARY
        ================================== -->

        <div class="job-summary">


            <div class="job-summary-top">


                <div>


                    <div class="job-icon">

                        <?php

                        echo ($job["job_type"] == "Internship")
                            ? "🎓"
                            : "💼";

                        ?>

                    </div>


                    <h2>

                        <?php

                        echo htmlspecialchars(
                            $job["title"]
                        );

                        ?>

                    </h2>


                    <p class="company">

                        🏢

                        <?php

                        echo htmlspecialchars(
                            $job["company_name"]
                        );

                        ?>

                    </p>


                </div>



                <?php if ($job["job_type"] == "Internship"): ?>


                    <span
                        class="type-badge internship-type"
                    >

                        Internship

                    </span>


                <?php else: ?>


                    <span
                        class="type-badge job-type"
                    >

                        Job

                    </span>


                <?php endif; ?>


            </div>



            <!-- JOB INFO -->

            <div class="job-info">


                <div class="info-item">

                    <div class="info-label">

                        Job Type

                    </div>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $job["job_type"]
                        );

                        ?>

                    </div>

                </div>



                <div class="info-item">

                    <div class="info-label">

                        Application Deadline

                    </div>

                    <div class="info-value">

                        📅

                        <?php

                        echo htmlspecialchars(
                            $job["deadline"]
                        );

                        ?>

                    </div>

                </div>


            </div>


        </div>



        <!-- =================================
             MESSAGE
        ================================== -->

        <?php if (!empty($message)): ?>


            <div
                class="message
                <?php echo $message_type; ?>"
            >

                <?php

                echo htmlspecialchars(
                    $message
                );

                ?>

            </div>


        <?php endif; ?>



        <!-- =================================
             APPLICATION AREA
        ================================== -->

        <div class="application-card">


            <?php if (empty($cv_file)): ?>


                <!-- NO CV -->

                <div class="state-box">


                    <div class="state-icon warning">

                        ⚠️

                    </div>


                    <h3>

                        CV Required

                    </h3>


                    <p>

                        You haven't uploaded a CV yet.
                        Please upload your CV before
                        applying for this opportunity.

                    </p>


                    <a
                        href="cv.php"
                        class="upload-btn"
                    >

                        Upload CV →

                    </a>


                </div>


            <?php elseif ($alreadyApplied): ?>


                <!-- ALREADY APPLIED -->

                <div class="state-box">


                    <div class="state-icon success">

                        ✓

                    </div>


                    <h3>

                        Application Already Submitted

                    </h3>


                    <p>

                        You have already applied for this
                        opportunity. You can track your
                        application status from your
                        applications page.

                    </p>


                    <a
                        href="applications.php"
                        class="applications-btn"
                    >

                        View My Applications →

                    </a>


                </div>


            <?php else: ?>


                <!-- READY TO APPLY -->


                <h3>

                    Your Application

                </h3>


                <p>

                    Your CV is ready to be submitted
                    with this application. Please review
                    the CV information below before
                    confirming your application.

                </p>



                <!-- CV -->

                <div class="cv-box">


                    <div class="cv-icon">

                        📄

                    </div>


                    <div class="cv-info">


                        <div class="cv-label">

                            Attached CV

                        </div>


                        <div class="cv-name">

                            <?php

                            echo htmlspecialchars(
                                $cv_file
                            );

                            ?>

                        </div>


                    </div>


                </div>



                <!-- SUBMIT FORM -->

                <form
                    method="POST"
                >

                    <button
                        type="submit"
                        class="primary-btn"
                    >

                        Confirm & Submit Application →

                    </button>

                </form>


            <?php endif; ?>


        </div>


    </section>



    <!-- FOOTER -->

    <div class="footer">

        © <?php echo date("Y"); ?>

        JIMS — Job & Internship Management System

    </div>


</main>


</body>

</html>

