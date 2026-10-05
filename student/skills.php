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

$stmt = $conn->prepare(
    "SELECT student_id
     FROM students
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$student = $result->fetch_assoc();

$stmt->close();

$student_id = $student["student_id"] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_skill"])) {

    $skill_name = trim($_POST["skill_name"] ?? "");

    if (empty($skill_name)) {

        $message = "Please enter a skill.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare(
            "SELECT skill_id
             FROM skills
             WHERE skill_name = ?"
        );

        $stmt->bind_param("s", $skill_name);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $skill = $result->fetch_assoc();
            $skill_id = $skill["skill_id"];

            $stmt->close();

        } else {

            $stmt->close();

            $stmt = $conn->prepare(
                "INSERT INTO skills (skill_name)
                 VALUES (?)"
            );

            $stmt->bind_param("s", $skill_name);
            $stmt->execute();

            $skill_id = $stmt->insert_id;

            $stmt->close();
        }

        $stmt = $conn->prepare(
            "SELECT student_id
             FROM student_skills
             WHERE student_id = ?
             AND skill_id = ?"
        );

        $stmt->bind_param(
            "ii",
            $student_id,
            $skill_id
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $message = "You already have this skill.";
            $message_type = "error";

            $stmt->close();

        } else {

            $stmt->close();

            $stmt = $conn->prepare(
                "INSERT INTO student_skills
                 (student_id, skill_id)
                 VALUES (?, ?)"
            );

            $stmt->bind_param(
                "ii",
                $student_id,
                $skill_id
            );

            if ($stmt->execute()) {

                $message = "Skill added successfully.";
                $message_type = "success";

            } else {

                $message = "Failed to add skill.";
                $message_type = "error";
            }

            $stmt->close();
        }
    }
}

if (
    $_SERVER["REQUEST_METHOD"] == "POST"
    && isset($_POST["remove_skill"])
) {

    $skill_id = intval($_POST["skill_id"]);

    $stmt = $conn->prepare(
        "DELETE FROM student_skills
         WHERE student_id = ?
         AND skill_id = ?"
    );

    $stmt->bind_param(
        "ii",
        $student_id,
        $skill_id
    );

    if ($stmt->execute()) {

        $message = "Skill removed successfully.";
        $message_type = "success";

    } else {

        $message = "Failed to remove skill.";
        $message_type = "error";
    }

    $stmt->close();
}

$stmt = $conn->prepare(
    "SELECT s.skill_id, s.skill_name
     FROM skills s
     INNER JOIN student_skills ss
        ON s.skill_id = ss.skill_id
     WHERE ss.student_id = ?
     ORDER BY s.skill_name"
);

$stmt->bind_param("i", $student_id);
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

    <title>My Skills - JIMS</title>

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

            max-width: 950px;

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

        .add-form {

            display: flex;

            gap: 12px;

        }

        .skill-input {

            flex: 1;

            height: 46px;

            padding: 0 14px;

            border:
                1px solid
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

        .skill-input:focus {

            border-color:
                rgba(37, 99, 235, 0.65);

            background:
                rgba(255, 255, 255, 0.90);

            box-shadow:
                0 0 0 3px
                rgba(37, 99, 235, 0.08);

        }

        .add-btn {

            height: 46px;

            padding:
                0 20px;

            border: none;

            border-radius: 11px;

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

            transition: 0.25s ease;

        }

        .add-btn:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 10px 23px
                rgba(37, 99, 235, 0.27);

        }

        .skills-heading {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 20px;

        }

        .skills-count {

            min-width: 34px;

            height: 34px;

            padding: 0 10px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 10px;

            background:
                rgba(219, 234, 254, 0.80);

            color: #2563eb;

            font-size: 13px;

            font-weight: 800;

        }

        .skills-list {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 13px;

        }

        .skill-item {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 10px;

            padding:
                13px 14px;

            border-radius: 13px;

            background:
                rgba(248, 250, 252, 0.72);

            border:
                1px solid
                rgba(226, 232, 240, 0.85);

            transition: 0.2s ease;

        }

        .skill-item:hover {

            transform:
                translateY(-2px);

            background:
                rgba(255, 255, 255, 0.82);

            box-shadow:
                0 6px 18px
                rgba(15, 23, 42, 0.06);

        }

        .skill-name {

            display: flex;

            align-items: center;

            gap: 9px;

            color: #172033;

            font-size: 13px;

            font-weight: 700;

            min-width: 0;

        }

        .skill-dot {

            width: 8px;
            height: 8px;

            flex-shrink: 0;

            border-radius: 50%;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #6366f1
                );

            box-shadow:
                0 2px 7px
                rgba(37, 99, 235, 0.25);

        }

        .remove-btn {

            border: none;

            width: 30px;
            height: 30px;

            flex-shrink: 0;

            border-radius: 8px;

            background:
                rgba(254, 226, 226, 0.75);

            color: #dc2626;

            font-size: 14px;

            font-weight: 800;

            cursor: pointer;

            transition: 0.2s ease;

        }

        .remove-btn:hover {

            background:
                rgba(254, 202, 202, 0.95);

            transform:
                scale(1.05);

        }

        .empty-state {

            text-align: center;

            padding:
                35px 20px;

        }

        .empty-icon {

            width: 58px;
            height: 58px;

            margin: 0 auto 14px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 17px;

            background:
                rgba(219, 234, 254, 0.70);

            font-size: 25px;

        }

        .empty-state h3 {

            font-size: 17px;

            color: #172033;

            margin-bottom: 6px;

        }

        .empty-state p {

            color: #667085;

            font-size: 12px;

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

            .skills-list {

                grid-template-columns:
                    repeat(2, 1fr);

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

            .page-header h1 {

                font-size: 26px;

            }

            .page-label {

                display: none;

            }

            .card {

                padding: 20px;

            }

            .add-form {

                flex-direction: column;

            }

            .add-btn {

                width: 100%;

            }

            .skills-list {

                grid-template-columns: 1fr;

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
            Student Skills
        </span>

    </div>

    <div class="page-header">

        <h1>
            My Skills
        </h1>

        <p>
            Add the technical and professional skills that represent your abilities.
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
                🛠️
            </div>

            <div>

                <h2>
                    Add a Skill
                </h2>

                <p>
                    Add a skill to your student profile
                </p>

            </div>

        </div>

        <form
            method="POST"
            class="add-form"
        >

            <input
                type="text"
                name="skill_name"
                class="skill-input"
                placeholder="Example: PHP, Java, Selenium, React..."
                maxlength="100"
                required
            >

            <button
                type="submit"
                name="add_skill"
                class="add-btn"
            >
                + Add Skill
            </button>

        </form>

    </section>

    <section class="card">

        <div class="skills-heading">

            <div class="card-header" style="margin-bottom: 0;">

                <div class="card-icon">
                    ⭐
                </div>

                <div>

                    <h2>
                        My Skills
                    </h2>

                    <p>
                        Skills currently added to your profile
                    </p>

                </div>

            </div>

            <div class="skills-count">

                <?php echo $result->num_rows; ?>

            </div>

        </div>

        <?php if ($result->num_rows > 0): ?>

            <div class="skills-list">

                <?php while ($skill = $result->fetch_assoc()): ?>

                    <div class="skill-item">

                        <div class="skill-name">

                            <span class="skill-dot"></span>

                            <span>
                                <?php
                                echo htmlspecialchars(
                                    $skill["skill_name"]
                                );
                                ?>
                            </span>

                        </div>

                        <form
                            method="POST"
                            onsubmit="return confirm('Remove this skill from your profile?');"
                        >

                            <input
                                type="hidden"
                                name="skill_id"
                                value="<?php
                                    echo $skill["skill_id"];
                                ?>"
                            >

                            <button
                                type="submit"
                                name="remove_skill"
                                class="remove-btn"
                                title="Remove skill"
                            >
                                ×
                            </button>

                        </form>

                    </div>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div class="empty-state">

                <div class="empty-icon">
                    🛠️
                </div>

                <h3>
                    No Skills Added Yet
                </h3>

                <p>
                    Add your first skill above to build your profile.
                </p>

            </div>

        <?php endif; ?>

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

<?php

$stmt->close();

?>