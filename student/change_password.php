<?php

session_start();

require_once "../config/database.php";



// ONLY STUDENT CAN ACCESS


if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] != "student"
) {
    header("Location: ../auth/login.php");
    exit();
}


$user_id = $_SESSION["user_id"];

$message = "";
$messageType = "";



// CHANGE PASSWORD

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $current_password = trim($_POST["current_password"] ?? "");
    $new_password = trim($_POST["new_password"] ?? "");
    $confirm_password = trim($_POST["confirm_password"] ?? "");


    // VALIDATION

    if (
        empty($current_password) ||
        empty($new_password) ||
        empty($confirm_password)
    ) {

        $message = "All fields are required.";
        $messageType = "error";

    } elseif ($new_password !== $confirm_password) {

        $message = "New passwords do not match.";
        $messageType = "error";

    } elseif (strlen($new_password) < 6) {

        $message = "New password must be at least 6 characters.";
        $messageType = "error";

    } elseif ($current_password === $new_password) {

        $message = "New password must be different from your current password.";
        $messageType = "error";

    } else {
        
        // GET CURRENT PASSWORD

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

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        $stmt->close();


        if (!$user) {

            $message = "User account not found.";
            $messageType = "error";

        } else {


            // VERIFY CURRENT PASSWORD
           

            if (
                !password_verify(
                    $current_password,
                    $user["password"]
                )
            ) {

                $message = "Current password is incorrect.";
                $messageType = "error";

            } else {


               
                // HASH NEW PASSWORD
        

                $hashed_password = password_hash(
                    $new_password,
                    PASSWORD_DEFAULT
                );

                // UPDATE PASSWORD

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

                    $message = "Password changed successfully.";
                    $messageType = "success";

                } else {

                    $message = "Failed to change password. Please try again.";
                    $messageType = "error";

                }


                $stmt->close();

            }

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

            background: #f5f7fb;

            color: #172033;

            min-height: 100vh;

        }


        /* =========================
           TOP BAR
        ========================= */

        .top-bar {

            background: #ffffff;

            border-bottom: 1px solid #e5e7eb;

            padding: 18px 6%;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        .brand {

            font-size: 23px;

            font-weight: 800;

            color: #172033;

        }


        .brand span {

            color: #2563eb;

        }


        .logout {

            text-decoration: none;

            color: #dc2626;

            font-size: 14px;

            font-weight: 600;

        }


        .logout:hover {

            text-decoration: underline;

        }


        /* =========================
           CONTAINER
        ========================= */

        .container {

            width: 92%;

            max-width: 600px;

            margin: 45px auto;

        }


        /* =========================
           BACK BUTTON
        ========================= */

        .back-link {

            display: inline-block;

            text-decoration: none;

            color: #2563eb;

            font-size: 14px;

            font-weight: 600;

            margin-bottom: 20px;

        }


        .back-link:hover {

            text-decoration: underline;

        }


        /* =========================
           CARD
        ========================= */

        .card {

            background: #ffffff;

            border-radius: 18px;

            padding: 32px;

            border: 1px solid #e5e7eb;

            box-shadow:
                0 8px 25px
                rgba(15, 23, 42, 0.07);

        }


        .card-header {

            margin-bottom: 25px;

        }


        .icon {

            width: 52px;

            height: 52px;

            background: #eff6ff;

            color: #2563eb;

            border-radius: 12px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 24px;

            margin-bottom: 18px;

        }


        .card-header h1 {

            font-size: 27px;

            margin-bottom: 8px;

            color: #111827;

        }


        .card-header p {

            color: #667085;

            font-size: 14px;

            line-height: 1.6;

        }


        /* =========================
           MESSAGE
        ========================= */

        .message {

            padding: 13px 15px;

            border-radius: 9px;

            font-size: 14px;

            font-weight: 600;

            margin-bottom: 22px;

        }


        .message.success {

            background: #dcfce7;

            color: #166534;

            border: 1px solid #bbf7d0;

        }


        .message.error {

            background: #fee2e2;

            color: #991b1b;

            border: 1px solid #fecaca;

        }


        /* =========================
           FORM
        ========================= */

        .form-group {

            margin-bottom: 20px;

        }


        .form-group label {

            display: block;

            margin-bottom: 8px;

            font-size: 14px;

            font-weight: 700;

            color: #344054;

        }


        .password-wrapper {

            position: relative;

        }


        .password-wrapper input {

            width: 100%;

            padding: 13px 45px 13px 14px;

            border: 1px solid #d0d5dd;

            border-radius: 9px;

            font-size: 14px;

            outline: none;

            transition: 0.2s;

        }


        .password-wrapper input:focus {

            border-color: #2563eb;

            box-shadow:
                0 0 0 3px
                rgba(37, 99, 235, 0.10);

        }


        .show-password {

            position: absolute;

            right: 13px;

            top: 50%;

            transform: translateY(-50%);

            border: none;

            background: transparent;

            cursor: pointer;

            font-size: 15px;

            color: #667085;

        }


        .hint {

            display: block;

            margin-top: 6px;

            font-size: 12px;

            color: #98a2b3;

        }


        /* =========================
           BUTTON
        ========================= */

        .submit-btn {

            width: 100%;

            border: none;

            background: #2563eb;

            color: white;

            padding: 13px;

            border-radius: 9px;

            font-size: 14px;

            font-weight: 700;

            cursor: pointer;

            transition: 0.2s;

            margin-top: 5px;

        }


        .submit-btn:hover {

            background: #1d4ed8;

        }


        /* =========================
           SECURITY NOTE
        ========================= */

        .security-note {

            margin-top: 22px;

            padding: 14px;

            background: #f8fafc;

            border-radius: 9px;

            border: 1px solid #e5e7eb;

            font-size: 12px;

            color: #667085;

            line-height: 1.6;

        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 600px) {

            .top-bar {

                padding: 16px 5%;

            }


            .container {

                width: 94%;

                margin: 30px auto;

            }


            .card {

                padding: 24px 20px;

            }


            .card-header h1 {

                font-size: 24px;

            }

        }

    </style>

</head>


<body>


<!-- =========================
     TOP BAR
========================= -->

<header class="top-bar">

    <div class="brand">
        J<span>IMS</span>
    </div>


    <a
        href="../auth/logout.php"
        class="logout"
    >
        Logout
    </a>

</header>


<!-- =========================
     MAIN
========================= -->

<main class="container">


    <a
        href="dashboard.php"
        class="back-link"
    >
        ← Back to Dashboard
    </a>


    <div class="card">


        <!-- HEADER -->

        <div class="card-header">

            <div class="icon">
                🔐
            </div>

            <h1>
                Change Password
            </h1>

            <p>
                Update your JIMS account password to keep your account secure.
            </p>

        </div>


        <!-- MESSAGE -->

        <?php if (!empty($message)): ?>

            <div
                class="message <?php echo $messageType; ?>"
            >

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <!-- FORM -->

        <form method="POST">


            <!-- CURRENT PASSWORD -->

            <div class="form-group">

                <label for="current_password">
                    Current Password
                </label>


                <div class="password-wrapper">

                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        placeholder="Enter your current password"
                        required
                    >


                    <button
                        type="button"
                        class="show-password"
                        onclick="togglePassword('current_password', this)"
                    >
                        👁
                    </button>

                </div>

            </div>


            <!-- NEW PASSWORD -->

            <div class="form-group">

                <label for="new_password">
                    New Password
                </label>


                <div class="password-wrapper">

                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        minlength="6"
                        placeholder="Enter your new password"
                        required
                    >


                    <button
                        type="button"
                        class="show-password"
                        onclick="togglePassword('new_password', this)"
                    >
                        👁
                    </button>

                </div>


                <small class="hint">
                    Password must contain at least 6 characters.
                </small>

            </div>


            <!-- CONFIRM PASSWORD -->

            <div class="form-group">

                <label for="confirm_password">
                    Confirm New Password
                </label>


                <div class="password-wrapper">

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        minlength="6"
                        placeholder="Re-enter your new password"
                        required
                    >


                    <button
                        type="button"
                        class="show-password"
                        onclick="togglePassword('confirm_password', this)"
                    >
                        👁
                    </button>

                </div>

            </div>


            <!-- SUBMIT -->

            <button
                type="submit"
                class="submit-btn"
            >
                🔒 Change Password
            </button>


        </form>


        <div class="security-note">

            🔒 <strong>Security Tip:</strong>
            Never share your password with anyone.
            Use a strong password that is difficult to guess.

        </div>


    </div>


</main>


<script>

function togglePassword(id, button) {

    const input = document.getElementById(id);

    if (input.type === "password") {

        input.type = "text";

        button.textContent = "🙈";

    } else {

        input.type = "password";

        button.textContent = "👁";

    }

}

</script>


</body>

</html>