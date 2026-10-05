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

$search = $_GET["search"] ?? "";
$type = $_GET["type"] ?? "";
$location = $_GET["location"] ?? "";

$sql = "
    SELECT
        j.job_id,
        j.title,
        j.job_type,
        j.location,
        j.salary,
        j.deadline,
        j.description,
        j.vacancy_count,

        c.company_name,

        (
            SELECT COUNT(*)
            FROM applications a
            WHERE a.job_id = j.job_id
            AND a.status = 'selected'
        ) AS selected_count

    FROM jobs j

    INNER JOIN companies c
        ON j.company_id = c.company_id

    WHERE j.status = 'approved'
";

$params = [];
$types = "";

if ($search != "") {

    $sql .= "
        AND (
            j.title LIKE ?
            OR c.company_name LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "ss";
}

if ($type != "") {

    $sql .= "
        AND j.job_type = ?
    ";

    $params[] = $type;

    $types .= "s";
}

if ($location != "") {

    $sql .= "
        AND j.location LIKE ?
    ";

    $location_value = "%" . $location . "%";

    $params[] = $location_value;

    $types .= "s";
}

$sql .= "
    ORDER BY j.job_id DESC
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );
}

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Browse Jobs & Internships - JIMS</title>


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

    max-width: 1250px;

    margin: 0 auto;

    padding: 42px 0 60px;

}

.page-header {

    margin-bottom: 28px;

}

.page-header h1 {

    font-size: 32px;

    color: #111827;

    margin-bottom: 8px;

    letter-spacing: -0.5px;

}

.page-header p {

    color: #667085;

    font-size: 14px;

}

.search-card {

    padding: 24px;

    margin-bottom: 32px;

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

}

.search-title {

    font-size: 17px;

    font-weight: 700;

    color: #172033;

    margin-bottom: 17px;

}

.search-form {

    display: grid;

    grid-template-columns:
        2fr 1fr 1.2fr auto auto;

    gap: 12px;

    align-items: center;

}

.input-group input,
.input-group select {

    width: 100%;

    height: 44px;

    padding: 0 13px;

    border-radius: 10px;

    border:
        1px solid
        #dbe2ea;

    background:
        rgba(255,255,255,0.82);

    color: #172033;

    font-size: 13px;

    outline: none;

    transition: 0.2s ease;

}

.input-group input:focus,
.input-group select:focus {

    border-color:
        #93c5fd;

    box-shadow:
        0 0 0 3px
        rgba(37,99,235,0.08);

}

.search-btn {

    height: 44px;

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

    box-shadow:
        0 7px 18px
        rgba(37,99,235,0.20);

    transition: 0.2s ease;

}

.search-btn:hover {

    transform:
        translateY(-1px);

    box-shadow:
        0 10px 22px
        rgba(37,99,235,0.25);

}

.clear-btn {

    height: 44px;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 0 17px;

    border-radius: 10px;

    text-decoration: none;

    color: #475467;

    background:
        rgba(248,250,252,0.85);

    border:
        1px solid
        #dbe2ea;

    font-size: 13px;

    font-weight: 700;

}

.clear-btn:hover {

    background: #f1f5f9;

}

.results-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 18px;

}

.results-header h2 {

    font-size: 21px;

    color: #111827;

}

.result-count {

    font-size: 12px;

    color: #667085;

    background:
        rgba(255,255,255,0.72);

    padding: 7px 12px;

    border-radius: 20px;

    border:
        1px solid
        rgba(255,255,255,0.85);

}

.jobs-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 20px;

}

.job-card {

    position: relative;

    padding: 24px;

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

    transition:
        0.25s ease;

}

.job-card:hover {

    transform:
        translateY(-5px);

    box-shadow:
        0 18px 40px
        rgba(15,23,42,0.12);

    border-color:
        rgba(147,197,253,0.65);

}

.card-top {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 15px;

    margin-bottom: 18px;

}

.company-icon {

    width: 48px;

    height: 48px;

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

}

.type-badge {

    padding: 7px 11px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 800;

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

.job-title {

    font-size: 19px;

    color: #111827;

    margin-bottom: 7px;

}

.company-name {

    color: #667085;

    font-size: 13px;

    font-weight: 600;

    margin-bottom: 18px;

}

.job-info {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 11px;

    margin-bottom: 18px;

}

.info-item {

    display: flex;

    align-items: center;

    gap: 8px;

    color: #475467;

    font-size: 12px;

}

.info-icon {

    font-size: 15px;

}

.description {

    color: #667085;

    font-size: 13px;

    line-height: 1.6;

    margin-bottom: 18px;

}

.vacancy-box {

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 11px 13px;

    border-radius: 11px;

    background:
        rgba(248,250,252,0.78);

    border:
        1px solid
        #edf0f4;

    margin-bottom: 18px;

}

.vacancy-label {

    color: #667085;

    font-size: 12px;

}

.vacancy-number {

    font-size: 13px;

    font-weight: 800;

    color: #2563eb;

}

.details-btn {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 100%;

    height: 43px;

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

    font-weight: 700;

    box-shadow:
        0 7px 18px
        rgba(37,99,235,0.18);

    transition: 0.2s ease;

}

.details-btn:hover {

    transform:
        translateY(-1px);

    box-shadow:
        0 10px 22px
        rgba(37,99,235,0.25);

}

.full-vacancy {

    text-align: center;

    padding: 11px;

    border-radius: 10px;

    background:
        #fff7ed;

    color:
        #c2410c;

    font-size: 12px;

    font-weight: 700;

}

.empty-card {

    padding: 65px 25px;

    text-align: center;

    border-radius: 20px;

    background:
        rgba(255,255,255,0.68);

    border:
        1px solid
        rgba(255,255,255,0.82);

    backdrop-filter:
        blur(18px);

    box-shadow:
        0 12px 35px
        rgba(15,23,42,0.06);

}

.empty-icon {

    font-size: 42px;

    margin-bottom: 14px;

}

.empty-card h3 {

    color: #172033;

    font-size: 20px;

    margin-bottom: 8px;

}

.empty-card p {

    color: #667085;

    font-size: 13px;

}

@media (max-width: 950px) {

    .search-form {

        grid-template-columns:
            1fr 1fr;

    }

    .jobs-grid {

        grid-template-columns:
            1fr;

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

        padding-top: 30px;

    }

    .page-header h1 {

        font-size: 26px;

    }

    .search-form {

        grid-template-columns:
            1fr;

    }

    .search-btn,
    .clear-btn {

        width: 100%;

    }

    .job-info {

        grid-template-columns:
            1fr;

    }

    .results-header {

        align-items: flex-start;

        gap: 10px;

        flex-direction: column;

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

    <section class="page-header">

        <h1>
            Browse Jobs & Internships
        </h1>

        <p>
            Find the right opportunity and start your career journey.
        </p>

    </section>

    <section class="search-card">

        <div class="search-title">
            🔎 Find Your Opportunity
        </div>

        <form
            method="GET"
            class="search-form"
        >

            <div class="input-group">

                <input
                    type="text"
                    name="search"
                    placeholder="Job title or company..."
                    value="<?php
                    echo htmlspecialchars($search);
                    ?>"
                >

            </div>

            <div class="input-group">

                <select name="type">

                    <option value="">
                        All Types
                    </option>

                    <option
                        value="Job"
                        <?php
                        echo ($type == "Job")
                            ? "selected"
                            : "";
                        ?>
                    >
                        Jobs
                    </option>

                    <option
                        value="Internship"
                        <?php
                        echo ($type == "Internship")
                            ? "selected"
                            : "";
                        ?>
                    >
                        Internships
                    </option>

                </select>

            </div>

            <div class="input-group">

                <input
                    type="text"
                    name="location"
                    placeholder="Location e.g. Dhaka"
                    value="<?php
                    echo htmlspecialchars($location);
                    ?>"
                >

            </div>

            <button
                type="submit"
                class="search-btn"
            >
                Search
            </button>

            <a
                href="jobs.php"
                class="clear-btn"
            >
                Clear
            </a>

        </form>

    </section>

    <div class="results-header">

        <h2>
            Available Opportunities
        </h2>

        <span class="result-count">

            <?php echo $result->num_rows; ?>

            opportunity

            <?php
            echo ($result->num_rows != 1)
                ? "ies"
                : "y";
            ?>

        </span>

    </div>

    <?php if ($result->num_rows > 0): ?>

        <div class="jobs-grid">

        <?php while ($job = $result->fetch_assoc()): ?>

            <?php

            $vacancy_count =
                (int)$job["vacancy_count"];

            $selected_count =
                (int)$job["selected_count"];

            $remaining =
                $vacancy_count - $selected_count;

            if ($remaining < 0) {
                $remaining = 0;
            }

            ?>

            <article class="job-card">

                <div class="card-top">

                    <div class="company-icon">

                        <?php
                        echo ($job["job_type"] == "Internship")
                            ? "🎓"
                            : "💼";
                        ?>

                    </div>

                    <?php if ($job["job_type"] == "Internship"): ?>

                        <span class="type-badge internship-type">
                            Internship
                        </span>

                    <?php else: ?>

                        <span class="type-badge job-type">
                            Job
                        </span>

                    <?php endif; ?>

                </div>

                <h3 class="job-title">

                    <?php
                    echo htmlspecialchars(
                        $job["title"]
                    );
                    ?>

                </h3>

                <p class="company-name">

                    🏢

                    <?php
                    echo htmlspecialchars(
                        $job["company_name"]
                    );
                    ?>

                </p>

                <div class="job-info">

                    <div class="info-item">

                        <span class="info-icon">
                            📍
                        </span>

                        <span>

                            <?php
                            echo htmlspecialchars(
                                $job["location"]
                                ?? "Not specified"
                            );
                            ?>

                        </span>

                    </div>

                    <div class="info-item">

                        <span class="info-icon">
                            💰
                        </span>

                        <span>

                            <?php
                            echo htmlspecialchars(
                                $job["salary"]
                                ?? "Not specified"
                            );
                            ?>

                        </span>

                    </div>

                    <div class="info-item">

                        <span class="info-icon">
                            📅
                        </span>

                        <span>

                            <?php
                            echo htmlspecialchars(
                                $job["deadline"]
                            );
                            ?>

                        </span>

                    </div>

                    <div class="info-item">

                        <span class="info-icon">
                            👥
                        </span>

                        <span>

                            <?php
                            echo $remaining;
                            ?>

                            position(s) left

                        </span>

                    </div>

                </div>

                <p class="description">

                    <?php

                    $description =
                        $job["description"]
                        ?? "";

                    if (
                        strlen($description) > 150
                    ) {

                        echo htmlspecialchars(
                            substr(
                                $description,
                                0,
                                150
                            )
                        ) . "...";

                    } else {

                        echo htmlspecialchars(
                            $description
                        );

                    }

                    ?>

                </p>

                <div class="vacancy-box">

                    <span class="vacancy-label">
                        Available Positions
                    </span>

                    <span class="vacancy-number">

                        <?php echo $remaining; ?>

                        /

                        <?php echo $vacancy_count; ?>

                    </span>

                </div>

                <?php if ($remaining > 0): ?>

                    <a
                        href="job_details.php?id=<?php
                        echo $job["job_id"];
                        ?>"
                        class="details-btn"
                    >

                        View Details & Apply →

                    </a>

                <?php else: ?>

                    <div class="full-vacancy">

                        🔒 This vacancy is no longer accepting applications.

                    </div>

                <?php endif; ?>

            </article>

        <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty-card">

            <div class="empty-icon">
                🔍
            </div>

            <h3>
                No Opportunities Found
            </h3>

            <p>
                Try changing your search or filter options.
            </p>

        </div>

    <?php endif; ?>

</main>

</body>

</html>

<?php

$stmt->close();

?>