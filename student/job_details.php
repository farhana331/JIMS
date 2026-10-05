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

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {
    header("Location: jobs.php");
    exit();
}

$job_id = intval($_GET["id"]);

$stmt = $conn->prepare(
    "SELECT
        j.job_id,
        j.title,
        j.job_type,
        j.description,
        j.requirements,
        j.salary,
        j.location,
        j.deadline,
        j.vacancy_count,

        c.company_name,
        c.industry,
        c.website

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

    header("Location: jobs.php");

    exit();

}

$count_stmt = $conn->prepare(
    "SELECT COUNT(*) AS selected_count
     FROM applications
     WHERE job_id = ?
     AND status = 'selected'"
);

$count_stmt->bind_param(
    "i",
    $job_id
);

$count_stmt->execute();

$count_result =
    $count_stmt->get_result();

$count_data =
    $count_result->fetch_assoc();

$count_stmt->close();

$vacancy_count =
    (int)$job["vacancy_count"];

$selected_count =
    (int)$count_data["selected_count"];

$remaining =
    $vacancy_count - $selected_count;

if ($remaining < 0) {

    $remaining = 0;

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

<?php
echo htmlspecialchars($job["title"]);
?>

- JIMS

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

    max-width: 1150px;

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

.job-header {

    padding: 28px;

    border-radius: 20px;

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
        0 12px 35px
        rgba(15,23,42,0.07);

    margin-bottom: 20px;

}

.header-top {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 20px;

}

.company-icon {

    width: 58px;

    height: 58px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 16px;

    background:
        rgba(219,234,254,0.75);

    border:
        1px solid
        rgba(147,197,253,0.35);

    font-size: 26px;

    margin-bottom: 17px;

}

.job-title {

    font-size: 29px;

    color: #111827;

    line-height: 1.25;

    margin-bottom: 8px;

    letter-spacing: -0.4px;

}

.company-name {

    color: #667085;

    font-size: 14px;

    font-weight: 600;

}

.type-badge {

    padding: 8px 13px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 800;

    white-space: nowrap;

}

.job-type {

    background:
        #eff6ff;

    color:
        #1d4ed8;

}

.internship-type {

    background:
        #f5f3ff;

    color:
        #6d28d9;

}

.quick-info {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 12px;

    margin-top: 25px;

}

.info-box {

    padding: 14px;

    border-radius: 12px;

    background:
        rgba(248,250,252,0.78);

    border:
        1px solid
        #edf0f4;

}

.info-label {

    color: #98a2b3;

    font-size: 10px;

    font-weight: 800;

    margin-bottom: 6px;

    text-transform: uppercase;

}

.info-value {

    color: #344054;

    font-size: 12px;

    font-weight: 650;

    line-height: 1.4;

}

.content-grid {

    display: grid;

    grid-template-columns:
        minmax(0, 1fr) 320px;

    gap: 20px;

    align-items: start;

}

.card {

    padding: 25px;

    border-radius: 20px;

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
        0 12px 35px
        rgba(15,23,42,0.07);

    margin-bottom: 20px;

}

.card-title {

    font-size: 18px;

    color: #172033;

    margin-bottom: 17px;

}

.card-title::after {

    content: "";

    display: block;

    width: 32px;

    height: 3px;

    border-radius: 5px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    margin-top: 8px;

}

.description {

    color: #667085;

    font-size: 13px;

    line-height: 1.8;

}

.requirements {

    color: #667085;

    font-size: 13px;

    line-height: 1.8;

}

.company-card {

    padding: 25px;

    border-radius: 20px;

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
        0 12px 35px
        rgba(15,23,42,0.07);

    margin-bottom: 20px;

}

.company-card-icon {

    width: 52px;

    height: 52px;

    border-radius: 14px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        rgba(219,234,254,0.75);

    border:
        1px solid
        rgba(147,197,253,0.35);

    font-size: 22px;

    margin-bottom: 14px;

}

.company-card h3 {

    color: #172033;

    font-size: 17px;

    margin-bottom: 6px;

}

.company-industry {

    color: #667085;

    font-size: 12px;

    margin-bottom: 16px;

}

.website-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    width: 100%;

    min-height: 40px;

    padding: 9px 13px;

    border-radius: 10px;

    text-decoration: none;

    color: #2563eb;

    background:
        rgba(239,246,255,0.85);

    border:
        1px solid
        rgba(147,197,253,0.35);

    font-size: 12px;

    font-weight: 700;

    transition: 0.2s ease;

}

.website-btn:hover {

    background:
        #dbeafe;

}

.apply-card {

    padding: 25px;

    border-radius: 20px;

    background:
        linear-gradient(
            135deg,
            rgba(37,99,235,0.96),
            rgba(79,70,229,0.96)
        );

    box-shadow:
        0 15px 35px
        rgba(37,99,235,0.20);

    position: sticky;

    top: 92px;

}

.apply-card h2 {

    color: white;

    font-size: 20px;

    margin-bottom: 8px;

}

.apply-card p {

    color:
        rgba(255,255,255,0.84);

    font-size: 12px;

    line-height: 1.65;

    margin-bottom: 19px;

}

.available-box {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 11px 13px;

    border-radius: 10px;

    background:
        rgba(255,255,255,0.13);

    border:
        1px solid
        rgba(255,255,255,0.16);

    margin-bottom: 15px;

}

.available-label {

    color:
        rgba(255,255,255,0.75);

    font-size: 11px;

}

.available-number {

    color: white;

    font-size: 13px;

    font-weight: 800;

}

.apply-btn {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 100%;

    height: 44px;

    border-radius: 10px;

    background: white;

    color: #2563eb;

    text-decoration: none;

    font-size: 13px;

    font-weight: 800;

    transition: 0.2s ease;

}

.apply-btn:hover {

    transform:
        translateY(-1px);

    box-shadow:
        0 8px 20px
        rgba(0,0,0,0.15);

}

.full-vacancy {

    text-align: center;

    padding: 12px;

    border-radius: 10px;

    background:
        rgba(255,255,255,0.15);

    color: white;

    font-size: 12px;

    font-weight: 700;

}

.footer {

    text-align: center;

    color: #98a2b3;

    font-size: 11px;

    padding-top: 20px;

}

@media (max-width: 950px) {

    .content-grid {

        grid-template-columns:
            1fr;

    }

    .apply-card {

        position: static;

    }

    .quick-info {

        grid-template-columns:
            1fr 1fr;

    }

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

    .header-top {

        flex-direction: column;

    }

    .job-title {

        font-size: 24px;

    }

    .quick-info {

        grid-template-columns:
            1fr;

    }

    .job-header,
    .card,
    .company-card,
    .apply-card {

        padding: 21px;

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
        href="jobs.php"
        class="back-link"
    >

        ← Back to Jobs

    </a>

    <section class="job-header">

        <div class="header-top">

            <div>

                <div class="company-icon">

                    <?php

                    echo ($job["job_type"] == "Internship")
                        ? "🎓"
                        : "💼";

                    ?>

                </div>

                <h1 class="job-title">

                    <?php

                    echo htmlspecialchars(
                        $job["title"]
                    );

                    ?>

                </h1>

                <p class="company-name">

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

        <div class="quick-info">

            <div class="info-box">

                <div class="info-label">

                    Location

                </div>

                <div class="info-value">

                    📍

                    <?php

                    echo htmlspecialchars(
                        $job["location"]
                        ?? "Not specified"
                    );

                    ?>

                </div>

            </div>

            <div class="info-box">

                <div class="info-label">

                    Salary

                </div>

                <div class="info-value">

                    💰

                    <?php

                    echo htmlspecialchars(
                        $job["salary"]
                        ?? "Not specified"
                    );

                    ?>

                </div>

            </div>

            <div class="info-box">

                <div class="info-label">

                    Deadline

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

            <div class="info-box">

                <div class="info-label">

                    Positions Left

                </div>

                <div class="info-value">

                    👥

                    <?php echo $remaining; ?>

                    / <?php echo $vacancy_count; ?>

                </div>

            </div>

        </div>

    </section>

    <div class="content-grid">

        <div>

            <section class="card">

                <h2 class="card-title">

                    Job Description

                </h2>

                <div class="description">

                    <?php

                    echo nl2br(
                        htmlspecialchars(
                            $job["description"]
                        )
                    );

                    ?>

                </div>

            </section>

            <section class="card">

                <h2 class="card-title">

                    Requirements

                </h2>

                <div class="requirements">

                    <?php

                    echo nl2br(
                        htmlspecialchars(
                            $job["requirements"]
                            ??
                            "No specific requirements mentioned."
                        )
                    );

                    ?>

                </div>

            </section>

        </div>

        <aside>

            <section class="company-card">

                <div class="company-card-icon">

                    🏢

                </div>

                <h3>

                    <?php

                    echo htmlspecialchars(
                        $job["company_name"]
                    );

                    ?>

                </h3>

                <p class="company-industry">

                    <?php

                    echo htmlspecialchars(
                        $job["industry"]
                        ??
                        "Industry not specified"
                    );

                    ?>

                </p>

                <?php if (!empty($job["website"])): ?>

                    <a
                        href="<?php
                        echo htmlspecialchars(
                            $job["website"]
                        );
                        ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="website-btn"
                    >

                        Visit Company Website ↗

                    </a>

                <?php endif; ?>

            </section>

            <section class="apply-card">

                <h2>

                    Ready to Apply?

                </h2>

                <p>

                    Submit your application through
                    JIMS and take the next step toward
                    your career.

                </p>

                <div class="available-box">

                    <span class="available-label">

                        Positions Available

                    </span>

                    <span class="available-number">

                        <?php echo $remaining; ?>

                        /

                        <?php echo $vacancy_count; ?>

                    </span>

                </div>

                <?php if ($remaining > 0): ?>

                    <a
                        href="apply.php?job_id=<?php
                        echo $job["job_id"];
                        ?>"
                        class="apply-btn"
                    >

                        Apply Now →

                    </a>

                <?php else: ?>

                    <div class="full-vacancy">

                        🔒 Vacancy Full

                    </div>

                <?php endif; ?>

            </section>

        </aside>

    </div>

    <div class="footer">

        © <?php echo date("Y"); ?>

        JIMS — Job & Internship Management System

    </div>

</main>

</body>

</html>