<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT
        u.user_id,
        u.name,
        u.email,
        u.phone,
        u.account_status,
        s.student_id,
        s.university,
        s.department,
        s.graduation_year,
        s.cgpa
    FROM users u
    LEFT JOIN students s
        ON u.user_id = s.user_id
    WHERE u.role = 'student'
    ORDER BY u.user_id DESC
");

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

<title>Manage Students - JIMS</title>

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

    width: 92%;

    max-width: 1350px;

    margin: 0 auto;

    padding: 40px 0 60px;

}

.top-bar {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 28px;

}

.back-link {

    text-decoration: none;

    color: #2563eb;

    font-size: 14px;

    font-weight: 700;

}

.back-link:hover {

    text-decoration: underline;

}

.logout {

    text-decoration: none;

    color: #dc2626;

    font-size: 13px;

    font-weight: 700;

}

.logout:hover {

    text-decoration: underline;

}

.page-header {

    margin-bottom: 25px;

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

.toolbar {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    margin-bottom: 20px;

}

.search-box {

    flex: 1;

    max-width: 500px;

    position: relative;

}

.search-box input {

    width: 100%;

    height: 46px;

    padding:
        0 16px
        0 44px;

    border-radius: 13px;

    border:
        1px solid
        rgba(148,163,184,0.25);

    background:
        rgba(255,255,255,0.68);

    backdrop-filter:
        blur(15px);

    -webkit-backdrop-filter:
        blur(15px);

    outline: none;

    color: #172033;

    font-size: 13px;

    box-shadow:
        0 5px 20px
        rgba(15,23,42,0.04);

}

.search-box input:focus {

    border-color:
        rgba(37,99,235,0.45);

    box-shadow:
        0 0 0 3px
        rgba(37,99,235,0.08);

}

.search-icon {

    position: absolute;

    left: 15px;

    top: 50%;

    transform:
        translateY(-50%);

    font-size: 17px;

    color: #64748b;

}

.filter-box select {

    height: 46px;

    min-width: 150px;

    padding:
        0 14px;

    border-radius: 13px;

    border:
        1px solid
        rgba(148,163,184,0.25);

    background:
        rgba(255,255,255,0.68);

    backdrop-filter:
        blur(15px);

    -webkit-backdrop-filter:
        blur(15px);

    outline: none;

    color: #344054;

    font-size: 13px;

    cursor: pointer;

}

.table-card {

    background:
        rgba(255,255,255,0.62);

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

    border-collapse: collapse;

    min-width: 1050px;

}

thead th {

    background:
        rgba(248,250,252,0.75);

    text-align: left;

    padding: 15px 14px;

    font-size: 12px;

    color: #475467;

    font-weight: 700;

    border-bottom:
        1px solid
        rgba(226,232,240,0.8);

    white-space: nowrap;

}

tbody td {

    padding: 15px 14px;

    border-bottom:
        1px solid
        rgba(226,232,240,0.55);

    font-size: 13px;

    color: #344054;

    white-space: nowrap;

}

tbody tr {

    transition:
        0.18s ease;

}

tbody tr:hover {

    background:
        rgba(239,246,255,0.55);

}

tbody tr:last-child td {

    border-bottom: none;

}

.student-id {

    color: #2563eb;

    font-weight: 700;

}

.student-name {

    color: #111827;

    font-weight: 700;

}

.status {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding:
        6px 11px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 700;

}

.status::before {

    content: "";

    width: 6px;

    height: 6px;

    border-radius: 50%;

}

.active {

    background:
        rgba(220,252,231,0.80);

    color: #166534;

}

.active::before {

    background: #22c55e;

}

.inactive {

    background:
        rgba(254,226,226,0.80);

    color: #991b1b;

}

.inactive::before {

    background: #ef4444;

}

.action-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding:
        8px 13px;

    border-radius: 9px;

    text-decoration: none;

    font-size: 11px;

    font-weight: 700;

    transition:
        0.2s ease;

}

.deactivate {

    background:
        rgba(254,226,226,0.85);

    color: #b91c1c;

    border:
        1px solid
        rgba(248,113,113,0.18);

}

.deactivate:hover {

    background: #fee2e2;

    transform:
        translateY(-1px);

}

.activate {

    background:
        rgba(220,252,231,0.85);

    color: #166534;

    border:
        1px solid
        rgba(74,222,128,0.18);

}

.activate:hover {

    background: #dcfce7;

    transform:
        translateY(-1px);

}

.empty {

    text-align: center;

    padding: 60px 20px;

    color: #667085;

}

.empty-icon {

    width: 60px;

    height: 60px;

    margin:
        0 auto 15px;

    border-radius: 16px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        rgba(219,234,254,0.7);

    font-size: 27px;

}

.empty h3 {

    color: #344054;

    margin-bottom: 6px;

    font-size: 17px;

}

.empty p {

    font-size: 13px;

}

.result-count {

    margin-bottom: 12px;

    color: #667085;

    font-size: 12px;

}

@media (max-width: 800px) {

    .navbar {

        padding: 0 5%;

    }

    .admin-name {

        display: none;

    }

    .container {

        width: 94%;

        padding-top: 30px;

    }

    .toolbar {

        flex-direction: column;

        align-items: stretch;

    }

    .search-box {

        max-width: none;

    }

    .filter-box select {

        width: 100%;

    }

}

@media (max-width: 600px) {

    .top-bar {

        align-items: flex-start;

        flex-direction: column;

        gap: 12px;

    }

    .page-header h1 {

        font-size: 26px;

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
            ← Back to Dashboard
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
            Student Management
        </h1>

        <p>
            View registered students and manage their account status.
        </p>

    </div>


    <div class="toolbar">

        <div class="search-box">

            <span class="search-icon">
                🔍
            </span>

            <input
                type="text"
                id="studentSearch"
                placeholder="Search by name, email, student ID..."
                onkeyup="filterStudents()"
            >

        </div>


        <div class="filter-box">

            <select
                id="statusFilter"
                onchange="filterStudents()"
            >

                <option value="all">
                    All Status
                </option>

                <option value="active">
                    Active
                </option>

                <option value="inactive">
                    Deactivated
                </option>

            </select>

        </div>

    </div>


    <?php if ($result->num_rows > 0): ?>


        <div class="result-count">

            Showing
            <strong id="visibleCount">
                <?php echo $result->num_rows; ?>
            </strong>
            student(s)

        </div>


        <div class="table-card">

            <table id="studentTable">

                <thead>

                    <tr>

                        <th>
                            Student ID
                        </th>

                        <th>
                            Name
                        </th>

                        <th>
                            Email
                        </th>

                        <th>
                            Phone
                        </th>

                        <th>
                            University
                        </th>

                        <th>
                            Department
                        </th>

                        <th>
                            Graduation
                        </th>

                        <th>
                            CGPA
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


                <?php while ($student = $result->fetch_assoc()): ?>


                    <?php

                    $accountStatus =
                        strtolower(
                            trim(
                                $student["account_status"] ?? "inactive"
                            )
                        );

                    ?>


                    <tr
                        data-status="<?php echo htmlspecialchars($accountStatus); ?>"
                    >


                        <td class="student-id">

                            <?php

                            echo htmlspecialchars(
                                $student["student_id"] ?? "N/A"
                            );

                            ?>

                        </td>


                        <td class="student-name">

                            <?php

                            echo htmlspecialchars(
                                $student["name"] ?? "N/A"
                            );

                            ?>

                        </td>


                        <td>

                            <?php

                            echo htmlspecialchars(
                                $student["email"] ?? "N/A"
                            );

                            ?>

                        </td>


                        <td>

                            <?php

                            echo htmlspecialchars(
                                $student["phone"] ?? "N/A"
                            );

                            ?>

                        </td>


                        <td>

                            <?php

                            echo htmlspecialchars(
                                $student["university"] ?? "N/A"
                            );

                            ?>

                        </td>


                        <td>

                            <?php

                            echo htmlspecialchars(
                                $student["department"] ?? "N/A"
                            );

                            ?>

                        </td>


                        <td>

                            <?php

                            echo htmlspecialchars(
                                $student["graduation_year"] ?? "N/A"
                            );

                            ?>

                        </td>


                        <td>

                            <?php

                            echo htmlspecialchars(
                                $student["cgpa"] ?? "N/A"
                            );

                            ?>

                        </td>


                        <td>

                            <?php if ($accountStatus === "active"): ?>

                                <span class="status active">
                                    Active
                                </span>

                            <?php else: ?>

                                <span class="status inactive">
                                    Deactivated
                                </span>

                            <?php endif; ?>

                        </td>


                        <td>

                            <?php if ($accountStatus === "active"): ?>

                                <a
                                    href="student_action.php?id=<?php echo (int)$student["user_id"]; ?>&action=deactivate"
                                    class="action-btn deactivate"
                                    onclick="return confirm('Are you sure you want to deactivate this student account?');"
                                >
                                    Deactivate
                                </a>

                            <?php else: ?>

                                <a
                                    href="student_action.php?id=<?php echo (int)$student["user_id"]; ?>&action=activate"
                                    class="action-btn activate"
                                    onclick="return confirm('Activate this student account?');"
                                >
                                    Activate
                                </a>

                            <?php endif; ?>

                        </td>


                    </tr>


                <?php endwhile; ?>


                </tbody>

            </table>

        </div>


    <?php else: ?>


        <div class="table-card">

            <div class="empty">

                <div class="empty-icon">
                    🎓
                </div>

                <h3>
                    No Students Found
                </h3>

                <p>
                    There are currently no registered students.
                </p>

            </div>

        </div>


    <?php endif; ?>


</main>


<script>

function filterStudents() {

    const searchInput =
        document.getElementById("studentSearch");

    const statusFilter =
        document.getElementById("statusFilter");

    const table =
        document.getElementById("studentTable");

    const visibleCount =
        document.getElementById("visibleCount");

    if (!table) {
        return;
    }

    const search =
        searchInput.value
            .toLowerCase()
            .trim();

    const selectedStatus =
        statusFilter.value;

    const rows =
        table
            .querySelectorAll("tbody tr");

    let count = 0;


    rows.forEach(function(row) {

        const rowText =
            row.innerText.toLowerCase();

        const rowStatus =
            row.getAttribute("data-status");


        const matchesSearch =
            rowText.includes(search);


        const matchesStatus =
            selectedStatus === "all"
            ||
            rowStatus === selectedStatus;


        if (
            matchesSearch
            &&
            matchesStatus
        ) {

            row.style.display = "";

            count++;

        } else {

            row.style.display = "none";

        }

    });


    if (visibleCount) {

        visibleCount.textContent =
            count;

    }

}

</script>


</body>

</html>


<?php

$stmt->close();

?>