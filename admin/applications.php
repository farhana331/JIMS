<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

$search = trim($_GET["search"] ?? "");
$type = trim($_GET["type"] ?? "");
$status = trim($_GET["status"] ?? "");

$sql = "SELECT
            a.application_id,
            a.status,
            a.cv_file,

            u.name AS student_name,
            u.email AS student_email,

            j.title AS job_title,
            j.job_type,

            c.company_name

        FROM applications a

        INNER JOIN students s
            ON a.student_id = s.student_id

        INNER JOIN users u
            ON s.user_id = u.user_id

        INNER JOIN jobs j
            ON a.job_id = j.job_id

        INNER JOIN companies c
            ON j.company_id = c.company_id

        WHERE 1=1";

if ($search !== "") {

    $search_safe = $conn->real_escape_string($search);

    $sql .= "
        AND (
            u.name LIKE '%$search_safe%'
            OR u.email LIKE '%$search_safe%'
            OR j.title LIKE '%$search_safe%'
            OR c.company_name LIKE '%$search_safe%'
        )
    ";
}

if ($type !== "") {

    $type_safe = $conn->real_escape_string($type);

    $sql .= "
        AND j.job_type = '$type_safe'
    ";
}

if ($status !== "") {

    $status_safe = $conn->real_escape_string($status);

    $sql .= "
        AND a.status = '$status_safe'
    ";
}

$sql .= "
    ORDER BY a.application_id DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Database error: " . $conn->error);
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

<title>Manage Applications - JIMS</title>

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
        rgba(255,255,255,0.62);

    border-bottom:
        1px solid
        rgba(255,255,255,0.75);

    backdrop-filter:
        blur(18px);

    -webkit-backdrop-filter:
        blur(18px);

    box-shadow:
        0 5px 25px
        rgba(15,23,42,0.05);

}

.brand {

    font-size: 26px;

    font-weight: 800;

    color: #2563eb;

    text-decoration: none;

}

.brand span {

    color: #172033;

}

.admin-info {

    display: flex;

    align-items: center;

    gap: 12px;

}

.admin-avatar {

    width: 42px;
    height: 42px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    color: white;

    font-weight: 700;

    box-shadow:
        0 5px 15px
        rgba(37,99,235,0.20);

}

.admin-name {

    font-size: 14px;

    font-weight: 600;

    color: #344054;

}

.container {

    width: 94%;

    max-width: 1450px;

    margin: 0 auto;

    padding:
        40px 0 60px;

}

.top-bar {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 30px;

}

.back-link {

    color: #2563eb;

    text-decoration: none;

    font-size: 14px;

    font-weight: 700;

}

.back-link:hover {

    text-decoration: underline;

}

.logout {

    color: #dc2626;

    text-decoration: none;

    font-size: 14px;

    font-weight: 700;

}

.logout:hover {

    text-decoration: underline;

}

.page-header {

    margin-bottom: 25px;

}

.page-header h1 {

    font-size: 31px;

    color: #111827;

    margin-bottom: 8px;

    letter-spacing: -0.5px;

}

.page-header p {

    color: #667085;

    font-size: 14px;

}

.filter-card {

    padding: 20px;

    margin-bottom: 22px;

    border-radius: 20px;

    background:
        rgba(255,255,255,0.62);

    border:
        1px solid
        rgba(255,255,255,0.80);

    backdrop-filter:
        blur(18px);

    -webkit-backdrop-filter:
        blur(18px);

    box-shadow:
        0 12px 35px
        rgba(15,23,42,0.06);

}

.filter-form {

    display: grid;

    grid-template-columns:
        2fr 1fr 1fr auto auto;

    gap: 12px;

    align-items: center;

}

.search-box,
.filter-select {

    width: 100%;

    height: 43px;

    padding: 0 13px;

    border-radius: 10px;

    border:
        1px solid
        rgba(148,163,184,0.35);

    background:
        rgba(255,255,255,0.72);

    color: #172033;

    font-size: 13px;

    outline: none;

}

.search-box:focus,
.filter-select:focus {

    border-color: #93c5fd;

    box-shadow:
        0 0 0 3px
        rgba(37,99,235,0.08);

}

.search-btn {

    height: 43px;

    padding: 0 20px;

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

    font-weight: 700;

    cursor: pointer;

    transition: 0.2s ease;

}

.search-btn:hover {

    transform: translateY(-1px);

    box-shadow:
        0 8px 18px
        rgba(37,99,235,0.20);

}

.reset-btn {

    height: 43px;

    padding: 0 17px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 10px;

    background:
        rgba(255,255,255,0.70);

    border:
        1px solid
        rgba(148,163,184,0.30);

    color: #475467;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;

}

.reset-btn:hover {

    background: white;

}

.result-info {

    margin-bottom: 15px;

    color: #667085;

    font-size: 13px;

}

.result-info strong {

    color: #172033;

}

.table-card {

    border-radius: 20px;

    overflow: hidden;

    background:
        rgba(255,255,255,0.62);

    border:
        1px solid
        rgba(255,255,255,0.80);

    backdrop-filter:
        blur(18px);

    -webkit-backdrop-filter:
        blur(18px);

    box-shadow:
        0 12px 35px
        rgba(15,23,42,0.07);

}

.table-wrapper {

    width: 100%;

    overflow-x: auto;

}

table {

    width: 100%;

    min-width: 1100px;

    border-collapse: collapse;

}

th {

    background:
        rgba(248,250,252,0.75);

    color: #475467;

    font-size: 12px;

    font-weight: 700;

    text-align: left;

    padding: 15px 14px;

    border-bottom:
        1px solid
        rgba(226,232,240,0.80);

    white-space: nowrap;

}

td {

    padding: 16px 14px;

    border-bottom:
        1px solid
        rgba(226,232,240,0.65);

    font-size: 13px;

    vertical-align: middle;

}

tbody tr {

    transition: 0.2s ease;

}

tbody tr:hover {

    background:
        rgba(239,246,255,0.48);

}

tbody tr:last-child td {

    border-bottom: none;

}

.application-id {

    color: #2563eb;

    font-weight: 800;

}

.student-name {

    color: #111827;

    font-weight: 700;

    margin-bottom: 4px;

}

.student-email {

    color: #667085;

    font-size: 11px;

}

.job-title {

    color: #111827;

    font-weight: 700;

    margin-bottom: 5px;

}

.job-type-small {

    color: #667085;

    font-size: 11px;

}

.company-name {

    color: #475467;

    font-weight: 600;

}

.type-badge {

    display: inline-block;

    padding:
        6px 10px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 700;

    white-space: nowrap;

}

.type-job {

    background:
        rgba(219,234,254,0.80);

    color: #1d4ed8;

}

.type-internship {

    background:
        rgba(237,233,254,0.85);

    color: #6d28d9;

}

.cv-btn {

    display: inline-block;

    padding:
        8px 13px;

    border-radius: 8px;

    background:
        rgba(219,234,254,0.75);

    color: #2563eb;

    text-decoration: none;

    font-size: 11px;

    font-weight: 700;

    border:
        1px solid
        rgba(147,197,253,0.30);

    transition: 0.2s ease;

}

.cv-btn:hover {

    background:
        rgba(191,219,254,0.90);

}

.no-cv {

    color: #98a2b3;

    font-size: 12px;

}

.status {

    display: inline-flex;

    align-items: center;

    padding:
        6px 11px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 700;

    white-space: nowrap;

}

.status-pending {

    background:
        rgba(255,237,213,0.85);

    color: #c2410c;

}

.status-shortlisted {

    background:
        rgba(237,233,254,0.85);

    color: #6d28d9;

}

.status-selected {

    background:
        rgba(220,252,231,0.85);

    color: #166534;

}

.status-rejected {

    background:
        rgba(254,226,226,0.85);

    color: #991b1b;

}

.status-unknown {

    background:
        rgba(226,232,240,0.75);

    color: #475569;

}

.empty-state {

    text-align: center;

    padding: 70px 20px;

}

.empty-icon {

    width: 65px;

    height: 65px;

    margin:
        0 auto 18px;

    border-radius: 18px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 30px;

    background:
        rgba(219,234,254,0.70);

}

.empty-state h2 {

    color: #374151;

    font-size: 20px;

    margin-bottom: 8px;

}

.empty-state p {

    color: #667085;

    font-size: 13px;

}

.bottom-actions {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-top: 25px;

}

.dashboard-link {

    color: #2563eb;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;

}

.logout-link {

    color: #dc2626;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;

}

@media (max-width: 950px) {

    .filter-form {

        grid-template-columns:
            1fr 1fr;

    }

}

@media (max-width: 650px) {

    .navbar {

        padding: 0 5%;

    }

    .admin-name {

        display: none;

    }

    .container {

        width: 92%;

        padding-top: 30px;

    }

    .top-bar {

        align-items: flex-start;

        gap: 15px;

    }

    .page-header h1 {

        font-size: 26px;

    }

    .filter-form {

        grid-template-columns: 1fr;

    }

    .search-btn,
    .reset-btn {

        width: 100%;

    }

    .bottom-actions {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

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

    <div class="admin-info">

        <div class="admin-avatar">

            <?php

            echo strtoupper(
                substr(
                    $_SESSION["name"] ?? "A",
                    0,
                    1
                )
            );

            ?>

        </div>

        <span class="admin-name">

            <?php

            echo htmlspecialchars(
                $_SESSION["name"] ?? "Admin"
            );

            ?>

        </span>

    </div>

</nav>

<main class="container">

<div class="top-bar">

    <a
        href="dashboard.php"
        class="back-link"
    >
        ← Back to Admin Dashboard
    </a>

    <a
        href="../auth/logout.php"
        class="logout"
    >
        Logout
    </a>

</div>

<div class="page-header">

    <h1>
        Manage Applications
    </h1>

    <p>
        View and monitor all student job and internship applications.
    </p>

</div>

<div class="filter-card">

    <form
        method="GET"
        action="applications.php"
        class="filter-form"
    >

        <input
            type="text"
            name="search"
            class="search-box"
            placeholder="Search student, job, company..."
            value="<?php echo htmlspecialchars($search); ?>"
        >

        <select
            name="type"
            class="filter-select"
        >

            <option value="">
                All Types
            </option>

            <option
                value="Job"
                <?php echo ($type === "Job") ? "selected" : ""; ?>
            >
                Jobs
            </option>

            <option
                value="Internship"
                <?php echo ($type === "Internship") ? "selected" : ""; ?>
            >
                Internships
            </option>

        </select>

        <select
            name="status"
            class="filter-select"
        >

            <option value="">
                All Status
            </option>

            <option
                value="pending"
                <?php echo ($status === "pending") ? "selected" : ""; ?>
            >
                Pending
            </option>

            <option
                value="shortlisted"
                <?php echo ($status === "shortlisted") ? "selected" : ""; ?>
            >
                Shortlisted
            </option>

            <option
                value="selected"
                <?php echo ($status === "selected") ? "selected" : ""; ?>
            >
                Selected
            </option>

            <option
                value="rejected"
                <?php echo ($status === "rejected") ? "selected" : ""; ?>
            >
                Rejected
            </option>

        </select>

        <button
            type="submit"
            class="search-btn"
        >
            🔍 Search
        </button>

        <a
            href="applications.php"
            class="reset-btn"
        >
            Reset
        </a>

    </form>

</div>

<div class="result-info">

    Showing
    <strong>
        <?php echo $result->num_rows; ?>
    </strong>
    application(s)

    <?php if ($type !== ""): ?>

        for
        <strong>
            <?php echo htmlspecialchars($type); ?>
        </strong>

    <?php endif; ?>

</div>

<div class="table-card">

<?php if ($result->num_rows > 0): ?>

<div class="table-wrapper">

<table>

<thead>

<tr>

    <th>
        Application ID
    </th>

    <th>
        Student
    </th>

    <th>
        Job / Internship
    </th>

    <th>
        Company
    </th>

    <th>
        Type
    </th>

    <th>
        CV
    </th>

    <th>
        Status
    </th>

</tr>

</thead>

<tbody>

<?php while ($application = $result->fetch_assoc()): ?>

<tr>

<td>

    <span class="application-id">

        #
        <?php
        echo htmlspecialchars(
            $application["application_id"]
        );
        ?>

    </span>

</td>

<td>

    <div class="student-name">

        <?php
        echo htmlspecialchars(
            $application["student_name"]
        );
        ?>

    </div>

    <div class="student-email">

        <?php
        echo htmlspecialchars(
            $application["student_email"]
        );
        ?>

    </div>

</td>

<td>

    <div class="job-title">

        <?php
        echo htmlspecialchars(
            $application["job_title"]
        );
        ?>

    </div>

    <div class="job-type-small">

        <?php
        echo htmlspecialchars(
            $application["job_type"]
        );
        ?>

    </div>

</td>

<td>

    <span class="company-name">

        <?php
        echo htmlspecialchars(
            $application["company_name"]
        );
        ?>

    </span>

</td>

<td>

<?php if (
    strtolower($application["job_type"]) === "internship"
): ?>

    <span class="type-badge type-internship">
        Internship
    </span>

<?php else: ?>

    <span class="type-badge type-job">
        Job
    </span>

<?php endif; ?>

</td>

<td>

<?php if (!empty($application["cv_file"])): ?>

    <a
        href="../uploads/cv/<?php
        echo htmlspecialchars(
            $application["cv_file"]
        );
        ?>"
        target="_blank"
        class="cv-btn"
    >
        📄 View CV
    </a>

<?php else: ?>

    <span class="no-cv">
        Not Available
    </span>

<?php endif; ?>

</td>

<td>

<?php

if ($application["status"] === "pending") {

    echo '
        <span class="status status-pending">
            ⏳ Pending
        </span>
    ';

}

elseif ($application["status"] === "shortlisted") {

    echo '
        <span class="status status-shortlisted">
            ⭐ Shortlisted
        </span>
    ';

}

elseif ($application["status"] === "selected") {

    echo '
        <span class="status status-selected">
            🎉 Selected
        </span>
    ';

}

elseif ($application["status"] === "rejected") {

    echo '
        <span class="status status-rejected">
            ❌ Rejected
        </span>
    ';

}

else {

    echo '
        <span class="status status-unknown">
            Unknown
        </span>
    ';

}

?>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

<?php else: ?>

<div class="empty-state">

    <div class="empty-icon">
        📭
    </div>

    <h2>
        No Applications Found
    </h2>

    <p>
        No applications match your current search or filter.
    </p>

</div>

<?php endif; ?>

</div>

</main>

</body>

</html>