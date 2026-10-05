<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student") {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $university = trim($_POST["university"] ?? "");
    $department = trim($_POST["department"] ?? "");
    $graduation_year = $_POST["graduation_year"] ?? "";
    $cgpa = $_POST["cgpa"] ?? "";
    $bio = trim($_POST["bio"] ?? "");

    if ($cgpa !== "" && ($cgpa < 0 || $cgpa > 4)) {

        $message = "CGPA must be between 0 and 4.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare(
            "UPDATE students
             SET university = ?,
                 department = ?,
                 graduation_year = ?,
                 cgpa = ?,
                 bio = ?
             WHERE user_id = ?"
        );

        $stmt->bind_param(
            "sssssi",
            $university,
            $department,
            $graduation_year,
            $cgpa,
            $bio,
            $user_id
        );

        if ($stmt->execute()) {

            $message = "Profile updated successfully.";
            $message_type = "success";

        } else {

            $message = "Failed to update profile.";
            $message_type = "error";

        }

        $stmt->close();
    }
}

$stmt = $conn->prepare(
    "SELECT 
        u.name,
        u.email,
        u.phone,
        s.university,
        s.department,
        s.graduation_year,
        s.cgpa,
        s.bio
     FROM users u
     INNER JOIN students s
        ON u.user_id = s.user_id
     WHERE u.user_id = ?"
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

    <title>My Profile - JIMS</title>

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

            max-width: 1050px;

            margin: 0 auto;

            padding:
                40px 0 60px;

        }

        .top-bar {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 28px;

        }

        .back-link {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            text-decoration: none;

            color: #2563eb;

            font-size: 13px;

            font-weight: 700;

            transition: 0.2s ease;

        }

        .back-link:hover {

            transform:
                translateX(-3px);

        }

        .page-label {

            color: #667085;

            font-size: 12px;

            font-weight: 600;

        }

        .page-header {

            margin-bottom: 25px;

        }

        .page-header h1 {

            font-size: 31px;

            color: #111827;

            margin-bottom: 7px;

            letter-spacing: -0.5px;

        }

        .page-header p {

            color: #667085;

            font-size: 14px;

        }

        .message {

            padding: 13px 16px;

            border-radius: 12px;

            margin-bottom: 22px;

            font-size: 13px;

            font-weight: 600;

            border: 1px solid;

        }

        .message.success {

            background:
                rgba(220, 252, 231, 0.75);

            color: #166534;

            border-color:
                rgba(74, 222, 128, 0.30);

        }

        .message.error {

            background:
                rgba(254, 226, 226, 0.75);

            color: #991b1b;

            border-color:
                rgba(248, 113, 113, 0.30);

        }

        .card {

            padding: 26px;

            margin-bottom: 22px;

            border-radius: 21px;

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

        .card-header {

            display: flex;

            align-items: center;

            gap: 14px;

            margin-bottom: 23px;

        }

        .card-icon {

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

        }

        .card-header h2 {

            font-size: 19px;

            color: #111827;

            margin-bottom: 4px;

        }

        .card-header p {

            color: #667085;

            font-size: 12px;

        }

        .personal-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 15px;

        }

        .info-item {

            padding: 17px;

            border-radius: 14px;

            background:
                rgba(248, 250, 252, 0.72);

            border:
                1px solid
                rgba(226, 232, 240, 0.80);

        }

        .info-label {

            display: block;

            color: #667085;

            font-size: 11px;

            font-weight: 700;

            margin-bottom: 7px;

            text-transform: uppercase;

            letter-spacing: 0.3px;

        }

        .info-value {

            color: #172033;

            font-size: 14px;

            font-weight: 600;

            word-break: break-word;

        }

        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;

        }

        .form-group {

            display: flex;

            flex-direction: column;

        }

        .form-group.full {

            grid-column: 1 / -1;

        }

        label {

            color: #344054;

            font-size: 12px;

            font-weight: 700;

            margin-bottom: 8px;

        }

        input,
        textarea {

            width: 100%;

            border: 1px solid
                rgba(203, 213, 225, 0.90);

            border-radius: 11px;

            background:
                rgba(255, 255, 255, 0.72);

            color: #172033;

            font-family:
                "Segoe UI",
                Arial,
                sans-serif;

            font-size: 13px;

            outline: none;

            transition: 0.2s ease;

        }

        input {

            height: 45px;

            padding: 0 13px;

        }

        textarea {

            min-height: 120px;

            padding: 12px 13px;

            resize: vertical;

            line-height: 1.5;

        }

        input:focus,
        textarea:focus {

            border-color:
                rgba(37, 99, 235, 0.65);

            background:
                rgba(255, 255, 255, 0.90);

            box-shadow:
                0 0 0 3px
                rgba(37, 99, 235, 0.08);

        }

        .field-note {

            margin-top: 6px;

            color: #98a2b3;

            font-size: 11px;

        }

        .form-actions {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-top: 24px;

            padding-top: 21px;

            border-top:
                1px solid
                rgba(148, 163, 184, 0.20);

        }

        .cancel-link {

            color: #667085;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;

        }

        .cancel-link:hover {

            color: #344054;

        }

        .update-btn {

            border: none;

            border-radius: 11px;

            padding:
                12px 20px;

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
                rgba(37, 99, 235, 0.20);

            transition:
                0.25s ease;

        }

        .update-btn:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 10px 23px
                rgba(37, 99, 235, 0.27);

        }

        .logout-area {

            display: flex;

            justify-content: flex-end;

            margin-top: 8px;

        }

        .logout-btn {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding:
                10px 17px;

            border-radius: 10px;

            background:
                rgba(254, 226, 226, 0.75);

            border:
                1px solid
                rgba(248, 113, 113, 0.25);

            color: #dc2626;

            text-decoration: none;

            font-size: 12px;

            font-weight: 700;

            transition: 0.25s ease;

        }

        .logout-btn:hover {

            background:
                rgba(254, 202, 202, 0.90);

            transform:
                translateY(-2px);

        }

        @media (max-width: 800px) {

            .personal-grid {

                grid-template-columns:
                    1fr;

            }

            .form-grid {

                grid-template-columns:
                    1fr;

            }

            .form-group.full {

                grid-column: auto;

            }

        }

        @media (max-width: 600px) {

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

            .top-bar {

                align-items: flex-start;

            }

            .page-label {

                display: none;

            }

            .page-header h1 {

                font-size: 26px;

            }

            .card {

                padding: 20px;

            }

            .form-actions {

                flex-direction: column-reverse;

                align-items: stretch;

                gap: 12px;

            }

            .update-btn {

                width: 100%;

            }

            .cancel-link {

                text-align: center;

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

    <div class="top-bar">

        <a
            href="dashboard.php"
            class="back-link"
        >
            ← Back to Dashboard
        </a>

        <span class="page-label">
            Student Profile
        </span>

    </div>

    <div class="page-header">

        <h1>
            My Profile
        </h1>

        <p>
            View your personal information and keep your academic profile up to date.
        </p>

    </div>

    <?php if (!empty($message)): ?>

        <div
            class="message <?php echo $message_type; ?>"
        >

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>

    <section class="card">

        <div class="card-header">

            <div class="card-icon">
                👤
            </div>

            <div>

                <h2>
                    Personal Information
                </h2>

                <p>
                    Your registered account information
                </p>

            </div>

        </div>

        <div class="personal-grid">

            <div class="info-item">

                <span class="info-label">
                    Full Name
                </span>

                <span class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $student["name"] ?? "Not available"
                    );

                    ?>

                </span>

            </div>

            <div class="info-item">

                <span class="info-label">
                    Email
                </span>

                <span class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $student["email"] ?? "Not available"
                    );

                    ?>

                </span>

            </div>

            <div class="info-item">

                <span class="info-label">
                    Phone
                </span>

                <span class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $student["phone"] ?? "Not added"
                    );

                    ?>

                </span>

            </div>

        </div>

    </section>

    <section class="card">

        <div class="card-header">

            <div class="card-icon">
                🎓
            </div>

            <div>

                <h2>
                    Academic Information
                </h2>

                <p>
                    Update your educational background and profile bio
                </p>

            </div>

        </div>

        <form method="POST">

            <div class="form-grid">

                <div class="form-group">

                    <label for="university">
                        University
                    </label>

                    <input
                        type="text"
                        id="university"
                        name="university"
                        value="<?php
                            echo htmlspecialchars(
                                $student["university"] ?? ""
                            );
                        ?>"
                        placeholder="Enter your university"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="department">
                        Department
                    </label>

                    <input
                        type="text"
                        id="department"
                        name="department"
                        value="<?php
                            echo htmlspecialchars(
                                $student["department"] ?? ""
                            );
                        ?>"
                        placeholder="Enter your department"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="graduation_year">
                        Graduation Year
                    </label>

                    <input
                        type="number"
                        id="graduation_year"
                        name="graduation_year"
                        min="2000"
                        max="2100"
                        value="<?php
                            echo htmlspecialchars(
                                $student["graduation_year"] ?? ""
                            );
                        ?>"
                        placeholder="e.g. 2027"
                    >

                </div>

                <div class="form-group">

                    <label for="cgpa">
                        CGPA
                    </label>

                    <input
                        type="number"
                        id="cgpa"
                        name="cgpa"
                        min="0"
                        max="4"
                        step="0.01"
                        value="<?php
                            echo htmlspecialchars(
                                $student["cgpa"] ?? ""
                            );
                        ?>"
                        placeholder="e.g. 3.85"
                    >

                    <span class="field-note">
                        CGPA must be between 0 and 4.
                    </span>

                </div>

                <div class="form-group full">

                    <label for="bio">
                        About Me
                    </label>

                    <textarea
                        id="bio"
                        name="bio"
                        placeholder="Write a short introduction about yourself..."
                    ><?php
                        echo htmlspecialchars(
                            $student["bio"] ?? ""
                        );
                    ?></textarea>

                </div>

            </div>

            <div class="form-actions">

                <a
                    href="dashboard.php"
                    class="cancel-link"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="update-btn"
                >
                    ✓ Update Profile
                </button>

            </div>

        </form>

    </section>

    <div class="logout-area">

        <a
            href="../auth/logout.php"
            class="logout-btn"
            onclick="return confirm('Are you sure you want to logout?');"
        >

            ↪ Logout

        </a>

    </div>

</main>

</body>

</html>