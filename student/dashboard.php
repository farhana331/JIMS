<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student") {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT university, department, graduation_year, cgpa
     FROM students
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$student = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Student Dashboard - JIMS</title>

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
                rgba(255, 255, 255, 0.62);

            border-bottom:
                1px solid
                rgba(255, 255, 255, 0.75);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);

            box-shadow:
                0 5px 25px
                rgba(15, 23, 42, 0.05);

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

        .student-info {

            display: flex;

            align-items: center;

            gap: 12px;

        }

        .student-avatar {

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
                rgba(37, 99, 235, 0.20);

        }

        .student-name {

            font-size: 14px;

            font-weight: 600;

            color: #344054;

        }

        .container {

            width: 88%;

            max-width: 1200px;

            margin: 0 auto;

            padding:
                42px 0 60px;

        }

        .welcome {

            margin-bottom: 28px;

        }

        .welcome h1 {

            font-size: 32px;

            color: #111827;

            margin-bottom: 8px;

            letter-spacing: -0.5px;

        }

        .welcome p {

            color: #667085;

            font-size: 14px;

        }

        .academic-card {

            padding: 26px;

            margin-bottom: 30px;

            border-radius: 22px;

            background:
                rgba(255, 255, 255, 0.62);

            border:
                1px solid
                rgba(255, 255, 255, 0.80);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);

            box-shadow:
                0 12px 35px
                rgba(15, 23, 42, 0.07);

        }

        .academic-header {

            display: flex;

            align-items: center;

            gap: 14px;

            margin-bottom: 22px;

        }

        .academic-icon {

            width: 48px;
            height: 48px;

            border-radius: 14px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 22px;

            background:
                rgba(219, 234, 254, 0.75);

            border:
                1px solid
                rgba(147, 197, 253, 0.35);

        }

        .academic-header h2 {

            font-size: 20px;

            color: #111827;

            margin-bottom: 4px;

        }

        .academic-header p {

            color: #667085;

            font-size: 12px;

        }

        .academic-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

        }

        .academic-item {

            padding: 17px;

            border-radius: 15px;

            background:
                rgba(248, 250, 252, 0.72);

            border:
                1px solid
                rgba(226, 232, 240, 0.80);

        }

        .academic-item span {

            display: block;

            color: #667085;

            font-size: 11px;

            font-weight: 600;

            margin-bottom: 7px;

        }

        .academic-item strong {

            color: #172033;

            font-size: 14px;

        }

        .section-title {

            font-size: 21px;

            color: #111827;

            margin-bottom: 18px;

        }

        .menu-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 18px;

        }

        .menu-card {

            text-decoration: none;

            color: inherit;

            padding: 23px;

            border-radius: 20px;

            background:
                rgba(255, 255, 255, 0.62);

            border:
                1px solid
                rgba(255, 255, 255, 0.80);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);

            box-shadow:
                0 12px 35px
                rgba(15, 23, 42, 0.06);

            transition:
                0.25s ease;

        }

        .menu-card:hover {

            transform:
                translateY(-5px);

            box-shadow:
                0 18px 40px
                rgba(15, 23, 42, 0.11);

            border-color:
                rgba(147, 197, 253, 0.70);

        }

        .menu-icon {

            width: 48px;
            height: 48px;

            border-radius: 14px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 21px;

            background:
                rgba(219, 234, 254, 0.75);

            border:
                1px solid
                rgba(147, 197, 253, 0.35);

            margin-bottom: 17px;

        }

        .menu-card h3 {

            font-size: 15px;

            color: #172033;

            margin-bottom: 6px;

        }

        .menu-card p {

            font-size: 12px;

            color: #667085;

            line-height: 1.5;

        }

        .menu-arrow {

            display: block;

            margin-top: 15px;

            color: #2563eb;

            font-size: 12px;

            font-weight: 700;

        }

        .logout-area {

            margin-top: 38px;

            padding-top: 25px;

            border-top:
                1px solid
                rgba(148, 163, 184, 0.25);

            display: flex;

            justify-content: flex-end;

        }

        .logout-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding:
                11px 19px;

            border-radius: 11px;

            background:
                rgba(254, 226, 226, 0.78);

            border:
                1px solid
                rgba(248, 113, 113, 0.25);

            color: #dc2626;

            text-decoration: none;

            font-size: 13px;

            font-weight: 700;

            box-shadow:
                0 5px 15px
                rgba(220, 38, 38, 0.06);

            transition:
                0.25s ease;

        }

        .logout-btn:hover {

            background:
                rgba(254, 202, 202, 0.90);

            transform:
                translateY(-2px);

            box-shadow:
                0 8px 20px
                rgba(220, 38, 38, 0.12);

        }

        .logout-icon {

            font-size: 15px;

        }

        @media (max-width: 950px) {

            .academic-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

            .menu-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }

        @media (max-width: 650px) {

            .navbar {

                padding: 0 5%;

            }

            .student-name {

                display: none;

            }

            .container {

                width: 90%;

                padding-top: 30px;

            }

            .welcome h1 {

                font-size: 26px;

            }

            .academic-grid,
            .menu-grid {

                grid-template-columns: 1fr;

            }

            .academic-card,
            .menu-card {

                padding: 20px;

            }

            .logout-area {

                justify-content: center;

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

    <div class="student-info">

        <div class="student-avatar">

            <?php

            echo strtoupper(
                substr(
                    $_SESSION["name"] ?? "S",
                    0,
                    1
                )
            );

            ?>

        </div>

        <span class="student-name">

            <?php

            echo htmlspecialchars(
                $_SESSION["name"] ?? "Student"
            );

            ?>

        </span>

    </div>

</nav>

<main class="container">

    <section class="welcome">

        <h1>
            Student Dashboard
        </h1>

        <p>

            Welcome back,
            <strong>

                <?php

                echo htmlspecialchars(
                    $_SESSION["name"] ?? "Student"
                );

                ?>

            </strong>.

            Explore opportunities and manage your profile.

        </p>

    </section>

    <?php if ($student): ?>

        <section class="academic-card">

            <div class="academic-header">

                <div class="academic-icon">
                    🎓
                </div>

                <div>

                    <h2>
                        My Academic Information
                    </h2>

                    <p>
                        Your current academic profile
                    </p>

                </div>

            </div>

            <div class="academic-grid">

                <div class="academic-item">

                    <span>
                        UNIVERSITY
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $student["university"] ?? "Not added"
                        );

                        ?>

                    </strong>

                </div>

                <div class="academic-item">

                    <span>
                        DEPARTMENT
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $student["department"] ?? "Not added"
                        );

                        ?>

                    </strong>

                </div>

                <div class="academic-item">

                    <span>
                        GRADUATION YEAR
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $student["graduation_year"] ?? "Not added"
                        );

                        ?>

                    </strong>

                </div>

                <div class="academic-item">

                    <span>
                        CGPA
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $student["cgpa"] ?? "Not added"
                        );

                        ?>

                    </strong>

                </div>

            </div>

        </section>

    <?php endif; ?>

    <h2 class="section-title">
        Student Menu
    </h2>

    <div class="menu-grid">

        <a
            href="profile.php"
            class="menu-card"
        >

            <div class="menu-icon">
                👤
            </div>

            <h3>
                My Profile
            </h3>

            <p>
                View and update your personal and academic information.
            </p>

            <span class="menu-arrow">
                Manage Profile →
            </span>

        </a>

        <a
            href="skills.php"
            class="menu-card"
        >

            <div class="menu-icon">
                🛠️
            </div>

            <h3>
                My Skills
            </h3>

            <p>
                Add and manage your technical and professional skills.
            </p>

            <span class="menu-arrow">
                Manage Skills →
            </span>

        </a>

        <a
            href="cv.php"
            class="menu-card"
        >

            <div class="menu-icon">
                📄
            </div>

            <h3>
                My CV
            </h3>

            <p>
                Upload and manage your latest CV for applications.
            </p>

            <span class="menu-arrow">
                Manage CV →
            </span>

        </a>

        <a
            href="jobs.php"
            class="menu-card"
        >

            <div class="menu-icon">
                💼
            </div>

            <h3>
                Browse Jobs & Internships
            </h3>

            <p>
                Discover available jobs and internship opportunities.
            </p>

            <span class="menu-arrow">
                Explore Opportunities →
            </span>

        </a>

        <a
            href="applications.php"
            class="menu-card"
        >

            <div class="menu-icon">
                📋
            </div>

            <h3>
                My Applications
            </h3>

            <p>
                Track the jobs and internships you have applied for.
            </p>

            <span class="menu-arrow">
                View Applications →
            </span>

        </a>

        <a
            href="change_password.php"
            class="menu-card"
        >

            <div class="menu-icon">
                🔐
            </div>

            <h3>
                Change Password
            </h3>

            <p>
                Update your account password and keep your account secure.
            </p>

            <span class="menu-arrow">
                Change Password →
            </span>

        </a>

    </div>

    <div class="logout-area">

        <a
            href="../auth/logout.php"
            class="logout-btn"
            onclick="return confirm('Are you sure you want to logout?');"
        >

            <span class="logout-icon">
                ↪
            </span>

            Logout

        </a>

    </div>

</main>

</body>

</html>