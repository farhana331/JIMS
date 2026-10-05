<?php

session_start();

require_once "../config/database.php";


if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../auth/login.php");
    exit();
}



$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'student'"
);

$total_students = $result->fetch_assoc()["total"];



$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM companies
     WHERE approval_status = 'approved'"
);

$total_companies = $result->fetch_assoc()["total"];



$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM jobs"
);

$total_jobs = $result->fetch_assoc()["total"];



$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM applications"
);

$total_applications = $result->fetch_assoc()["total"];



$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM companies
     WHERE approval_status = 'pending'
     AND approval_requested = 1"
);

$pending_companies = $result->fetch_assoc()["total"];



$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM jobs
     WHERE status = 'pending'"
);

$pending_jobs = $result->fetch_assoc()["total"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Dashboard - JIMS</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f5f7fb;
            color: #172033;
            min-height: 100vh;
        }


        

        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            height: 72px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 6%;

            position: sticky;
            top: 0;
            z-index: 100;
        }


        .brand {
            font-size: 25px;
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
            gap: 14px;
        }


        .admin-avatar {
            width: 40px;
            height: 40px;

            border-radius: 50%;

            background: #dbeafe;
            color: #2563eb;

            display: flex;
            align-items: center;
            justify-content: center;

            font-weight: 700;
        }


        .admin-name {
            font-size: 14px;
            font-weight: 600;
            color: #344054;
        }


        

        .container {
            width: 88%;
            max-width: 1250px;

            margin: 0 auto;

            padding: 40px 0 60px;
        }


        .welcome {
            margin-bottom: 32px;
        }


        .welcome h1 {
            font-size: 30px;
            color: #111827;
            margin-bottom: 8px;
        }


        .welcome p {
            color: #667085;
            font-size: 14px;
        }


        

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);

            gap: 20px;
            margin-bottom: 35px;
        }


        .stat-card {
            background: #ffffff;

            border: 1px solid #e5e7eb;
            border-radius: 15px;

            padding: 24px;

            text-decoration: none;
            color: inherit;

            transition: 0.25s ease;

            box-shadow:
                0 3px 12px
                rgba(15, 23, 42, 0.04);
        }


        .stat-card:hover {
            transform: translateY(-4px);

            border-color: #bfdbfe;

            box-shadow:
                0 12px 25px
                rgba(15, 23, 42, 0.09);
        }


        .stat-top {
            display: flex;

            align-items: center;
            justify-content: space-between;

            margin-bottom: 20px;
        }


        .stat-icon {
            width: 46px;
            height: 46px;

            border-radius: 12px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 21px;

            background: #eff6ff;
        }


        .stat-arrow {
            color: #98a2b3;
            font-size: 18px;
        }


        .stat-card h3 {
            font-size: 30px;
            color: #111827;

            margin-bottom: 5px;
        }


        .stat-card p {
            color: #667085;

            font-size: 13px;
            font-weight: 600;
        }


        .click-text {
            margin-top: 15px;

            color: #2563eb;

            font-size: 12px;
            font-weight: 600;
        }


        

        .section-title {
            font-size: 20px;
            color: #111827;

            margin-bottom: 17px;
        }


        .pending-grid {
            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 20px;

            margin-bottom: 35px;
        }


        .pending-card {
            background: #ffffff;

            border: 1px solid #e5e7eb;
            border-radius: 15px;

            padding: 24px;

            text-decoration: none;
            color: inherit;

            display: flex;

            align-items: center;
            justify-content: space-between;

            transition: 0.25s ease;
        }


        .pending-card:hover {
            transform: translateY(-3px);

            border-color: #bfdbfe;

            box-shadow:
                0 10px 25px
                rgba(15, 23, 42, 0.07);
        }


        .pending-left {
            display: flex;

            align-items: center;

            gap: 15px;
        }


        .pending-icon {
            width: 45px;
            height: 45px;

            border-radius: 11px;

            background: #fff7ed;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 20px;
        }


        .pending-left h3 {
            font-size: 15px;
            margin-bottom: 4px;
        }


        .pending-left p {
            color: #667085;
            font-size: 12px;
        }


        .pending-number {
            font-size: 25px;

            font-weight: 800;

            color: #f97316;
        }


        

        .logout-area {
            margin-top: 35px;

            padding-top: 25px;

            border-top: 1px solid #e5e7eb;
        }


        .logout {
            display: inline-block;

            color: #dc2626;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;
        }


        .logout:hover {
            text-decoration: underline;
        }


        

        @media (max-width: 950px) {

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
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
                width: 90%;
                padding-top: 30px;
            }


            .welcome h1 {
                font-size: 25px;
            }


            .stats-grid,
            .pending-grid {
                grid-template-columns: 1fr;
            }


            .stat-card {
                padding: 20px;
            }

        }

    </style>

</head>


<body>




<nav class="navbar">

    <a href="dashboard.php" class="brand">
        JIMS<span>.</span>
    </a>


    <div class="admin-info">

        <div class="admin-avatar">

            <?php

            echo strtoupper(
                substr($_SESSION["name"], 0, 1)
            );

            ?>

        </div>


        <span class="admin-name">

            <?php

            echo htmlspecialchars(
                $_SESSION["name"]
            );

            ?>

        </span>

    </div>

</nav>



<main class="container">


    

    <section class="welcome">

        <h1>
            Admin Dashboard
        </h1>

        <p>

            Welcome back,

            <strong>

                <?php

                echo htmlspecialchars(
                    $_SESSION["name"]
                );

                ?>

            </strong>.

            Here's an overview of the JIMS platform.

        </p>

    </section>



    

    <div class="stats-grid">


        

        <a
            href="students.php"
            class="stat-card"
        >

            <div class="stat-top">

                <div class="stat-icon">
                    🎓
                </div>

                <span class="stat-arrow">
                    →
                </span>

            </div>


            <h3>

                <?php

                echo $total_students;

                ?>

            </h3>


            <p>
                Total Students
            </p>


            <div class="click-text">
                View Students →
            </div>

        </a>



        

        <a
            href="companies.php"
            class="stat-card"
        >

            <div class="stat-top">

                <div class="stat-icon">
                    🏢
                </div>

                <span class="stat-arrow">
                    →
                </span>

            </div>


            <h3>

                <?php

                echo $total_companies;

                ?>

            </h3>


            <p>
                Active Companies
            </p>


            <div class="click-text">
                Manage Companies →
            </div>

        </a>



        

        <a
            href="jobs.php"
            class="stat-card"
        >

            <div class="stat-top">

                <div class="stat-icon">
                    💼
                </div>

                <span class="stat-arrow">
                    →
                </span>

            </div>


            <h3>

                <?php

                echo $total_jobs;

                ?>

            </h3>


            <p>
                Total Jobs
            </p>


            <div class="click-text">
                Manage Jobs →
            </div>

        </a>



        

        <a
            href="applications.php"
            class="stat-card"
        >

            <div class="stat-top">

                <div class="stat-icon">
                    📄
                </div>

                <span class="stat-arrow">
                    →
                </span>

            </div>


            <h3>

                <?php

                echo $total_applications;

                ?>

            </h3>


            <p>
                Total Applications
            </p>


            <div class="click-text">
                View Applications →
            </div>

        </a>


    </div>



    

    <h2 class="section-title">
        Pending Approvals
    </h2>


    <div class="pending-grid">


        

        <a
            href="companies.php"
            class="pending-card"
        >

            <div class="pending-left">

                <div class="pending-icon">
                    🏢
                </div>


                <div>

                    <h3>
                        Pending Company Approvals
                    </h3>

                    <p>
                        Companies waiting for admin approval
                    </p>

                </div>

            </div>


            <div class="pending-number">

                <?php

                echo $pending_companies;

                ?>

            </div>

        </a>



        

        <a
            href="jobs.php"
            class="pending-card"
        >

            <div class="pending-left">

                <div class="pending-icon">
                    💼
                </div>


                <div>

                    <h3>
                        Pending Job Approvals
                    </h3>

                    <p>
                        Jobs waiting for admin approval
                    </p>

                </div>

            </div>


            <div class="pending-number">

                <?php

                echo $pending_jobs;

                ?>

            </div>

        </a>


    </div>



    

    <div class="logout-area">

        <a
            href="../auth/logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>


</main>


</body>

</html>