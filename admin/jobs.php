<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

$search = trim($_GET["search"] ?? "");
$type = trim($_GET["type"] ?? "all");

$sql = "
    SELECT
        j.job_id,
        j.title,
        j.job_type,
        j.location,
        j.salary,
        j.deadline,
        j.status,
        c.company_name

    FROM jobs j

    INNER JOIN companies c
        ON j.company_id = c.company_id

    WHERE 1=1
";

if ($search !== "") {

    $sql .= "
        AND (
            j.title LIKE ?
            OR c.company_name LIKE ?
            OR j.location LIKE ?
        )
    ";
}

if ($type === "job") {

    $sql .= "
        AND LOWER(j.job_type) = 'job'
    ";

} elseif ($type === "internship") {

    $sql .= "
        AND LOWER(j.job_type) = 'internship'
    ";
}

$sql .= "
    ORDER BY j.job_id DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

if ($search !== "") {

    $searchValue = "%" . $search . "%";

    $stmt->bind_param(
        "sss",
        $searchValue,
        $searchValue,
        $searchValue
    );
}

$stmt->execute();

$result = $stmt->get_result();

$total_displayed = $result->num_rows;

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Manage Jobs - JIMS</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    font-family:
        "Segoe UI",
        Arial,
        Helvetica,
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


.container {

    width: 94%;

    max-width: 1300px;

    margin: 0 auto;

    padding:
        35px 0 60px;

}


.top-bar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 30px;

}


.back-link {

    text-decoration: none;

    color: #2563eb;

    font-weight: 700;

    font-size: 14px;

}


.logout {

    text-decoration: none;

    color: #dc2626;

    font-weight: 700;

    font-size: 14px;

}


.back-link:hover,
.logout:hover {

    text-decoration: underline;

}


.page-header {

    margin-bottom: 25px;

}


.page-header h1 {

    margin: 0 0 8px;

    font-size: 30px;

    color: #111827;

}


.page-header p {

    margin: 0;

    color: #667085;

    font-size: 14px;

}


.filter-card {

    padding: 20px;

    margin-bottom: 25px;

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
        rgba(15,23,42,0.07);

}


.filter-form {

    display: flex;

    align-items: center;

    gap: 12px;

    flex-wrap: wrap;

}


.search-wrapper {

    flex: 1;

    min-width: 260px;

    position: relative;

}


.search-icon {

    position: absolute;

    left: 14px;

    top: 50%;

    transform:
        translateY(-50%);

    font-size: 16px;

}


.search-input {

    width: 100%;

    height: 45px;

    padding:
        0 15px 0 42px;

    border:
        1px solid
        #dbe3ef;

    border-radius: 11px;

    outline: none;

    background:
        rgba(255,255,255,0.75);

    color: #172033;

    font-size: 13px;

    transition: 0.2s ease;

}


.search-input:focus {

    border-color:
        #93c5fd;

    box-shadow:
        0 0 0 3px
        rgba(37,99,235,0.10);

}


.filter-buttons {

    display: flex;

    gap: 8px;

}


.filter-btn {

    height: 43px;

    padding:
        0 18px;

    border-radius: 10px;

    border:
        1px solid
        #dbe3ef;

    background:
        rgba(255,255,255,0.70);

    color: #475467;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;

    display: flex;

    align-items: center;

    justify-content: center;

    transition: 0.2s ease;

}


.filter-btn:hover {

    border-color:
        #93c5fd;

    color: #2563eb;

}


.filter-btn.active {

    background:
        #2563eb;

    border-color:
        #2563eb;

    color: white;

}


.search-btn {

    height: 43px;

    padding:
        0 20px;

    border: none;

    border-radius: 10px;

    background:
        #2563eb;

    color: white;

    font-size: 13px;

    font-weight: 700;

    cursor: pointer;

    transition: 0.2s ease;

}


.search-btn:hover {

    background:
        #1d4ed8;

}


.clear-btn {

    height: 43px;

    padding:
        0 16px;

    border-radius: 10px;

    border:
        1px solid
        #dbe3ef;

    background:
        rgba(255,255,255,0.70);

    color: #475467;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;

    display: flex;

    align-items: center;

}


.clear-btn:hover {

    color: #dc2626;

    border-color:
        #fecaca;

}


.result-info {

    margin-top: 18px;

    padding-top: 15px;

    border-top:
        1px solid
        rgba(148,163,184,0.20);

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.result-count {

    font-size: 13px;

    color: #667085;

}


.result-count strong {

    color: #172033;

}


.current-filter {

    font-size: 12px;

    color: #2563eb;

    font-weight: 700;

}


.table-card {

    background:
        rgba(255,255,255,0.68);

    border:
        1px solid
        rgba(255,255,255,0.80);

    border-radius: 20px;

    padding: 20px;

    backdrop-filter:
        blur(18px);

    -webkit-backdrop-filter:
        blur(18px);

    box-shadow:
        0 12px 35px
        rgba(15,23,42,0.07);

    overflow-x: auto;

}


table {

    width: 100%;

    min-width: 1100px;

    border-collapse: collapse;

}


th {

    background:
        rgba(248,250,252,0.80);

    color: #475467;

    font-size: 12px;

    font-weight: 700;

    text-align: left;

    padding:
        14px 12px;

    border-bottom:
        1px solid
        #e5e7eb;

    white-space: nowrap;

}


td {

    padding:
        15px 12px;

    font-size: 13px;

    border-bottom:
        1px solid
        rgba(226,232,240,0.75);

    vertical-align: middle;

}


tr:last-child td {

    border-bottom: none;

}


tr:hover {

    background:
        rgba(248,250,252,0.65);

}


.job-title {

    font-weight: 700;

    color: #172033;

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


.status {

    display: inline-block;

    padding:
        6px 11px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 700;

    white-space: nowrap;

}


.status-pending {

    background:
        #fff7ed;

    color:
        #c2410c;

}


.status-approved {

    background:
        #dcfce7;

    color:
        #166534;

}


.status-rejected {

    background:
        #fee2e2;

    color:
        #991b1b;

}


.status-closed {

    background:
        #e5e7eb;

    color:
        #374151;

}


.action-group {

    display: flex;

    gap: 7px;

    align-items: center;

}


.action-btn {

    display: inline-block;

    border: none;

    border-radius: 7px;

    padding:
        8px 12px;

    font-size: 11px;

    font-weight: 700;

    text-decoration: none;

    cursor: pointer;

    white-space: nowrap;

}


.approve-btn {

    background:
        #16a34a;

    color: white;

}


.approve-btn:hover {

    background:
        #15803d;

}


.reject-btn {

    background:
        #fee2e2;

    color:
        #b91c1c;

}


.reject-btn:hover {

    background:
        #fecaca;

}


.no-action {

    color:
        #98a2b3;

    font-size:
        12px;

}


.empty-card {

    background:
        rgba(255,255,255,0.68);

    border:
        1px solid
        rgba(255,255,255,0.80);

    border-radius:
        20px;

    padding:
        60px 25px;

    text-align:
        center;

    backdrop-filter:
        blur(18px);

    box-shadow:
        0 12px 35px
        rgba(15,23,42,0.06);

}


.empty-icon {

    font-size:
        45px;

    margin-bottom:
        12px;

}


.empty-card h2 {

    margin:
        0 0 8px;

    font-size:
        21px;

}


.empty-card p {

    margin:
        0;

    color:
        #667085;

    font-size:
        14px;

}


@media (max-width: 850px) {

    .filter-form {

        align-items:
            stretch;

    }


    .search-wrapper {

        min-width:
            100%;

    }


    .filter-buttons {

        width:
            100%;

    }


    .filter-btn {

        flex:
            1;

    }


    .search-btn,
    .clear-btn {

        flex:
            1;

    }

}


@media (max-width: 600px) {

    .container {

        width:
            94%;

        padding-top:
            25px;

    }


    .top-bar {

        flex-direction:
            column;

        align-items:
            flex-start;

        gap:
            15px;

    }


    .page-header h1 {

        font-size:
            25px;

    }


    .filter-card {

        padding:
            15px;

    }


    .filter-buttons {

        flex-wrap:
            wrap;

    }


    .filter-btn {

        min-width:
            30%;

    }


    .table-card {

        padding:
            12px;

    }

}

</style>

</head>


<body>


<div class="container">


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
        Manage Jobs & Internships
    </h1>

    <p>
        Review and manage job and internship postings submitted by companies.
    </p>

</div>


<div class="filter-card">


<form
    method="GET"
    action="jobs.php"
    class="filter-form"
>


    <div class="search-wrapper">

        <span class="search-icon">
            🔎
        </span>

        <input
            type="text"
            name="search"
            class="search-input"
            placeholder="Search by job title, company or location..."
            value="<?php echo htmlspecialchars($search); ?>"
        >

    </div>


    <div class="filter-buttons">

        <a
            href="jobs.php<?php echo $search !== '' ? '?search=' . urlencode($search) : ''; ?>"
            class="filter-btn <?php echo $type === 'all' ? 'active' : ''; ?>"
        >
            All
        </a>


        <a
            href="jobs.php?type=job<?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>"
            class="filter-btn <?php echo $type === 'job' ? 'active' : ''; ?>"
        >
            💼 Jobs
        </a>


        <a
            href="jobs.php?type=internship<?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>"
            class="filter-btn <?php echo $type === 'internship' ? 'active' : ''; ?>"
        >
            🎓 Internships
        </a>

    </div>


    <button
        type="submit"
        class="search-btn"
    >
        Search
    </button>


    <?php if ($search !== "" || $type !== "all"): ?>

        <a
            href="jobs.php"
            class="clear-btn"
        >
            Clear
        </a>

    <?php endif; ?>


    <input
        type="hidden"
        name="type"
        value="<?php echo htmlspecialchars($type); ?>"
    >


</form>


<div class="result-info">

    <div class="result-count">

        Showing
        <strong>
            <?php echo $total_displayed; ?>
        </strong>
        result<?php echo $total_displayed != 1 ? "s" : ""; ?>

    </div>


    <div class="current-filter">

        <?php

        if ($type === "job") {

            echo "Showing: Jobs";

        } elseif ($type === "internship") {

            echo "Showing: Internships";

        } else {

            echo "Showing: All";

        }

        ?>

    </div>

</div>


</div>


<?php if ($result->num_rows > 0): ?>


<div class="table-card">


<table>


<thead>

<tr>

    <th>
        ID
    </th>

    <th>
        Job Title
    </th>

    <th>
        Company
    </th>

    <th>
        Type
    </th>

    <th>
        Location
    </th>

    <th>
        Salary
    </th>

    <th>
        Deadline
    </th>

    <th>
        Status
    </th>

    <th>
        Action
    </th>

</tr>

</thead>


<tbody>


<?php while ($job = $result->fetch_assoc()): ?>


<tr>


<td>

    <?php

    echo htmlspecialchars(
        $job["job_id"]
    );

    ?>

</td>


<td>

    <span class="job-title">

        <?php

        echo htmlspecialchars(
            $job["title"]
        );

        ?>

    </span>

</td>


<td>

    <span class="company-name">

        <?php

        echo htmlspecialchars(
            $job["company_name"]
        );

        ?>

    </span>

</td>


<td>

    <?php

    $jobType = strtolower(
        trim($job["job_type"] ?? "")
    );

    ?>


    <?php if ($jobType === "internship"): ?>

        <span class="type-badge internship-type">

            🎓 Internship

        </span>

    <?php else: ?>

        <span class="type-badge job-type">

            💼 Job

        </span>

    <?php endif; ?>


</td>


<td>

    <?php

    echo htmlspecialchars(
        $job["location"] ?? "Not specified"
    );

    ?>

</td>


<td>

    <?php

    echo htmlspecialchars(
        $job["salary"] ?? "Not specified"
    );

    ?>

</td>


<td>

    <?php

    echo htmlspecialchars(
        $job["deadline"] ?? "Not specified"
    );

    ?>

</td>


<td>


<?php

$status = strtolower(
    trim($job["status"] ?? "")
);

?>


<?php if ($status === "pending"): ?>

    <span class="status status-pending">
        Pending ⏳
    </span>


<?php elseif ($status === "approved"): ?>

    <span class="status status-approved">
        Approved ✓
    </span>


<?php elseif ($status === "rejected"): ?>

    <span class="status status-rejected">
        Rejected ✕
    </span>


<?php elseif ($status === "closed"): ?>

    <span class="status status-closed">
        Closed
    </span>


<?php else: ?>

    <span class="status status-closed">

        <?php

        echo htmlspecialchars(
            $job["status"] ?? "Unknown"
        );

        ?>

    </span>

<?php endif; ?>


</td>


<td>


<?php if ($status === "pending"): ?>


    <div class="action-group">


        <a
            href="job_action.php?id=<?php echo $job["job_id"]; ?>&action=approve"
            class="action-btn approve-btn"
            onclick="return confirm('Approve this job/internship?');"
        >
            ✓ Approve
        </a>


        <a
            href="job_action.php?id=<?php echo $job["job_id"]; ?>&action=reject"
            class="action-btn reject-btn"
            onclick="return confirm('Reject this job/internship?');"
        >
            ✕ Reject
        </a>


    </div>


<?php else: ?>


    <span class="no-action">
        No Action
    </span>


<?php endif; ?>


</td>


</tr>


<?php endwhile; ?>


</tbody>


</table>


</div>


<?php else: ?>


<div class="empty-card">

    <div class="empty-icon">

        <?php

        if ($type === "internship") {

            echo "🎓";

        } elseif ($type === "job") {

            echo "💼";

        } else {

            echo "🔎";

        }

        ?>

    </div>


    <h2>

        <?php

        if ($search !== "") {

            echo "No Matching Results";

        } elseif ($type === "internship") {

            echo "No Internships Found";

        } elseif ($type === "job") {

            echo "No Jobs Found";

        } else {

            echo "No Jobs or Internships Found";

        }

        ?>

    </h2>


    <p>

        <?php

        if ($search !== "") {

            echo "No job or internship matches your search.";

        } elseif ($type === "internship") {

            echo "There are currently no internship postings.";

        } elseif ($type === "job") {

            echo "There are currently no job postings.";

        } else {

            echo "There are currently no job or internship postings.";

        }

        ?>

    </p>

</div>


<?php endif; ?>


</div>


</body>

</html>


<?php

$stmt->close();

?>