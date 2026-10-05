<?php

session_start();

require_once "../config/database.php";


if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] != "company"
) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$message = "";
$message_type = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $current_password =
        $_POST["current_password"] ?? "";

    $new_password =
        $_POST["new_password"] ?? "";

    $confirm_password =
        $_POST["confirm_password"] ?? "";


    if (
        empty($current_password) ||
        empty($new_password) ||
        empty($confirm_password)
    ) {

        $message =
            "All fields are required.";

        $message_type = "error";

    } elseif ($new_password != $confirm_password) {

        $message =
            "New passwords do not match.";

        $message_type = "error";

    } elseif (strlen($new_password) < 6) {

        $message =
            "Password must be at least 6 characters.";

        $message_type = "error";

    } else {

        $stmt = $conn->prepare(
            "SELECT password
             FROM users
             WHERE user_id = ?"
        );

        $stmt->bind_param(
            "i",
            $user_id
        );

        $stmt->execute();

        $result =
            $stmt->get_result();

        $user =
            $result->fetch_assoc();

        $stmt->close();


        if (!$user) {

            $message =
                "User not found.";

            $message_type = "error";

        } elseif (
            !password_verify(
                $current_password,
                $user["password"]
            )
        ) {

            $message =
                "Current password is incorrect.";

            $message_type = "error";

        } else {

            $hashed_password =
                password_hash(
                    $new_password,
                    PASSWORD_DEFAULT
                );


            $stmt = $conn->prepare(
                "UPDATE users
                 SET password = ?
                 WHERE user_id = ?"
            );

            $stmt->bind_param(
                "si",
                $hashed_password,
                $user_id
            );


            if ($stmt->execute()) {

                $message =
                    "Password changed successfully.";

                $message_type = "success";

            } else {

                $message =
                    "Failed to change password.";

                $message_type = "error";

            }

            $stmt->close();
        }
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
        Change Password - JIMS
    </title>


    <style>

        * {

            box-sizing: border-box;

            margin: 0;

            padding: 0;
        }


        body {

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            min-height: 100vh;

            color: #1f2937;

            background:
                linear-gradient(
                    135deg,
                    #eef2ff 0%,
                    #f8fafc 45%,
                    #e0f2fe 100%
                );

            overflow: hidden;

            display: flex;

            justify-content: center;

            align-items: center;

            position: relative;
        }


        body::before {

            content: "";

            position: fixed;

            width: 360px;

            height: 360px;

            background:
                rgba(37, 99, 235, 0.12);

            border-radius: 50%;

            top: -120px;

            left: -110px;

            filter: blur(5px);

            z-index: -1;
        }


        body::after {

            content: "";

            position: fixed;

            width: 400px;

            height: 400px;

            background:
                rgba(96, 165, 250, 0.12);

            border-radius: 50%;

            bottom: -170px;

            right: -130px;

            filter: blur(5px);

            z-index: -1;
        }


        .page-container {

            width: 90%;

            max-width: 620px;

            max-height: 94vh;
        }


        .top-bar {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 18px;
        }


        .back-btn {

            text-decoration: none;

            color: #475569;

            font-weight: 600;

            padding: 9px 15px;

            border-radius: 9px;

            background:
                rgba(255, 255, 255, 0.35);

            border:
                1px solid
                rgba(255, 255, 255, 0.65);

            backdrop-filter: blur(10px);

            transition: 0.25s;
        }


        .back-btn:hover {

            color: #2563eb;

            background:
                rgba(255, 255, 255, 0.65);

            transform: translateX(-2px);
        }


        .logout {

            text-decoration: none;

            color: #dc2626;

            font-weight: 600;

            padding: 9px 16px;

            border-radius: 9px;

            background:
                rgba(255, 255, 255, 0.35);

            border:
                1px solid
                rgba(254, 202, 202, 0.65);

            backdrop-filter: blur(10px);

            transition: 0.25s;
        }


        .logout:hover {

            background: #dc2626;

            color: white;

            transform: translateY(-2px);
        }


        .password-card {

            background:
                rgba(255, 255, 255, 0.43);

            backdrop-filter: blur(18px);

            -webkit-backdrop-filter: blur(18px);

            border:
                1px solid
                rgba(255, 255, 255, 0.7);

            border-radius: 22px;

            padding: 35px;

            box-shadow:
                0 20px 45px
                rgba(15, 23, 42, 0.10);
        }


        .card-header {

            margin-bottom: 25px;
        }


        .card-header h1 {

            font-size: 28px;

            color: #0f172a;

            margin-bottom: 7px;
        }


        .card-header p {

            color: #64748b;

            font-size: 14px;

            line-height: 1.5;
        }


        .message {

            padding: 13px 15px;

            border-radius: 10px;

            margin-bottom: 20px;

            font-size: 14px;

            font-weight: 600;

            line-height: 1.4;
        }


        .message.success {

            background:
                rgba(220, 252, 231, 0.85);

            border:
                1px solid
                rgba(134, 239, 172, 0.7);

            color: #15803d;
        }


        .message.error {

            background:
                rgba(254, 226, 226, 0.85);

            border:
                1px solid
                rgba(252, 165, 165, 0.7);

            color: #dc2626;
        }


        .form-section {

            background:
                rgba(255, 255, 255, 0.38);

            border:
                1px solid
                rgba(255, 255, 255, 0.65);

            border-radius: 16px;

            padding: 22px;
        }


        .form-section h2 {

            font-size: 18px;

            color: #0f172a;

            margin-bottom: 18px;
        }


        .form-group {

            margin-bottom: 17px;
        }


        .form-group label {

            display: block;

            font-size: 13px;

            font-weight: 600;

            color: #475569;

            margin-bottom: 7px;
        }


        .form-group input {

            width: 100%;

            padding: 12px 14px;

            border-radius: 10px;

            border:
                1px solid
                rgba(203, 213, 225, 0.8);

            background:
                rgba(255, 255, 255, 0.65);

            color: #1e293b;

            font-size: 14px;

            outline: none;

            transition: 0.2s;
        }


        .form-group input:focus {

            border-color: #2563eb;

            background:
                rgba(255, 255, 255, 0.85);

            box-shadow:
                0 0 0 3px
                rgba(37, 99, 235, 0.10);
        }


        .password-hint {

            font-size: 12px;

            color: #64748b;

            margin-top: 5px;
        }


        .button-area {

            margin-top: 22px;
        }


        .change-btn {

            width: 100%;

            border: none;

            cursor: pointer;

            padding: 13px 18px;

            border-radius: 10px;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #1d4ed8
                );

            color: white;

            font-size: 14px;

            font-weight: 700;

            box-shadow:
                0 7px 17px
                rgba(37, 99, 235, 0.20);

            transition: 0.25s;
        }


        .change-btn:hover {

            transform: translateY(-2px);

            box-shadow:
                0 11px 22px
                rgba(37, 99, 235, 0.28);
        }


        .footer-links {

            display: flex;

            justify-content: center;

            gap: 22px;

            margin-top: 22px;

            flex-wrap: wrap;
        }


        .footer-links a {

            text-decoration: none;

            color: #64748b;

            font-size: 13px;

            font-weight: 600;

            transition: 0.2s;
        }


        .footer-links a:hover {

            color: #2563eb;
        }


        @media (max-width: 600px) {

            body {

                overflow: auto;

                align-items: flex-start;

                padding-top: 25px;

                padding-bottom: 25px;
            }


            .page-container {

                width: 92%;

                max-height: none;
            }


            .password-card {

                padding: 22px;
            }


            .card-header h1 {

                font-size: 24px;
            }


            .form-section {

                padding: 18px;
            }
        }

    </style>

</head>


<body>


<div class="page-container">


    <div class="top-bar">

        <a
            href="dashboard.php"
            class="back-btn"
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


    <div class="password-card">


        <div class="card-header">

            <h1>
                🔒 Change Password
            </h1>

            <p>
                Update your account password to keep
                your company account secure.
            </p>

        </div>


        <?php if (!empty($message)): ?>

            <div
                class="message
                <?php
                echo $message_type;
                ?>"
            >

                <?php

                echo htmlspecialchars(
                    $message
                );

                ?>

            </div>

        <?php endif; ?>


        <div class="form-section">

            <h2>
                Password Information
            </h2>


            <form method="POST">


                <div class="form-group">

                    <label>
                        Current Password
                    </label>

                    <input
                        type="password"
                        name="current_password"
                        placeholder="Enter your current password"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        New Password
                    </label>

                    <input
                        type="password"
                        name="new_password"
                        minlength="6"
                        placeholder="Enter your new password"
                        required
                    >

                    <p class="password-hint">
                        Password must be at least 6 characters.
                    </p>

                </div>


                <div class="form-group">

                    <label>
                        Confirm New Password
                    </label>

                    <input
                        type="password"
                        name="confirm_password"
                        minlength="6"
                        placeholder="Confirm your new password"
                        required
                    >

                </div>


                <div class="button-area">

                    <button
                        type="submit"
                        class="change-btn"
                    >
                        🔐 Change Password
                    </button>

                </div>


            </form>

        </div>


        <div class="footer-links">

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="profile.php">
                Company Profile
            </a>

            <a href="jobs.php">
                My Jobs
            </a>

        </div>


    </div>

</div>


</body>

</html>