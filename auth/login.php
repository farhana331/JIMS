<?php

session_start();

require_once "../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

        $message = "Please enter your email and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT
                user_id,
                name,
                email,
                password,
                role,
                account_status
             FROM users
             WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();

            if ($user["account_status"] != "active") {

                $message = "Your account is not active.";

            }

            elseif (password_verify($password, $user["password"])) {

                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];

                if ($user["role"] == "admin") {

                    header("Location: ../admin/dashboard.php");
                    exit();

                } elseif ($user["role"] == "student") {

                    header("Location: ../student/dashboard.php");
                    exit();

                } elseif ($user["role"] == "company") {

                    header("Location: ../company/dashboard.php");
                    exit();

                } else {

                    $message = "Invalid user role.";
                }

            } else {

                $message = "Invalid email or password.";
            }

        } else {

            $message = "Invalid email or password.";
        }

        $stmt->close();
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

    <title>Login - JIMS</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {

            font-family: "Segoe UI", Arial, sans-serif;

            min-height: 100vh;

            color: #172033;

            background:
                radial-gradient(
                    circle at 10% 20%,
                    rgba(37, 99, 235, 0.14),
                    transparent 28%
                ),
                radial-gradient(
                    circle at 90% 80%,
                    rgba(99, 102, 241, 0.12),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #eef4ff 0%,
                    #f8fafc 48%,
                    #eef2ff 100%
                );

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 35px 20px;

            overflow-x: hidden;
        }



        body::before {

            content: "";

            position: fixed;

            width: 260px;
            height: 260px;

            border-radius: 50%;

            background: rgba(37, 99, 235, 0.10);

            top: -80px;
            left: -80px;

            filter: blur(5px);

            pointer-events: none;
        }


        body::after {

            content: "";

            position: fixed;

            width: 300px;
            height: 300px;

            border-radius: 50%;

            background: rgba(129, 140, 248, 0.10);

            bottom: -110px;
            right: -90px;

            filter: blur(5px);

            pointer-events: none;
        }


        .login-wrapper {

            position: relative;

            z-index: 2;

            width: 100%;

            max-width: 1020px;

            min-height: 590px;

            display: grid;

            grid-template-columns: 0.95fr 1.05fr;

            overflow: hidden;

            border-radius: 26px;

            background: rgba(255, 255, 255, 0.38);

            backdrop-filter: blur(22px);

            -webkit-backdrop-filter: blur(22px);

            border: 1px solid rgba(255, 255, 255, 0.75);

            box-shadow:
                0 25px 70px rgba(15, 23, 42, 0.12),
                inset 0 1px 0 rgba(255, 255, 255, 0.65);
        }




        .login-intro {

            position: relative;

            padding: 58px;

            display: flex;

            flex-direction: column;

            justify-content: center;

            color: #172033;

            background:
                linear-gradient(
                    145deg,
                    rgba(255,255,255,0.32),
                    rgba(219,234,254,0.28)
                );

            border-right: 1px solid rgba(255,255,255,0.55);

            overflow: hidden;
        }


        .login-intro::before {

            content: "";

            position: absolute;

            width: 190px;
            height: 190px;

            border-radius: 50%;

            background: rgba(37, 99, 235, 0.10);

            top: -70px;
            right: -70px;
        }


        .login-intro::after {

            content: "";

            position: absolute;

            width: 140px;
            height: 140px;

            border-radius: 50%;

            background: rgba(99, 102, 241, 0.08);

            bottom: -55px;
            left: -50px;
        }


        .brand {

            position: relative;

            z-index: 2;

            font-size: 34px;

            font-weight: 800;

            letter-spacing: -1.5px;

            margin-bottom: 38px;

            color: #2563eb;
        }


        .brand span {
            color: #172033;
        }


        .login-intro h1 {

            position: relative;

            z-index: 2;

            font-size: 42px;

            line-height: 1.12;

            margin-bottom: 18px;

            letter-spacing: -1.3px;

            color: #111827;
        }


        .login-intro p {

            position: relative;

            z-index: 2;

            color: #64748b;

            font-size: 15px;

            line-height: 1.8;

            max-width: 410px;
        }



        .intro-points {

            position: relative;

            z-index: 2;

            margin-top: 32px;

            display: flex;

            flex-direction: column;

            gap: 15px;
        }


        .intro-point {

            display: flex;

            align-items: center;

            gap: 12px;

            color: #475569;

            font-size: 14px;

            font-weight: 600;
        }


        .check {

            width: 29px;
            height: 29px;

            flex-shrink: 0;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            background: rgba(37, 99, 235, 0.10);

            border: 1px solid rgba(37, 99, 235, 0.16);

            color: #2563eb;

            font-size: 13px;

            font-weight: 800;
        }



        .login-form-section {

            padding: 58px;

            display: flex;

            flex-direction: column;

            justify-content: center;

            background: rgba(255,255,255,0.25);
        }


        .back-home {

            display: inline-block;

            width: fit-content;

            color: #64748b;

            font-size: 13px;

            margin-bottom: 30px;

            text-decoration: none;

            transition: 0.2s ease;
        }


        .back-home:hover {
            color: #2563eb;
            transform: translateX(-2px);
        }


        .form-title {
            margin-bottom: 27px;
        }


        .form-title h2 {

            font-size: 30px;

            color: #111827;

            margin-bottom: 7px;

            letter-spacing: -0.6px;
        }


        .form-title p {

            color: #64748b;

            font-size: 13px;
        }


        .message {

            background: rgba(254, 226, 226, 0.65);

            backdrop-filter: blur(10px);

            -webkit-backdrop-filter: blur(10px);

            border: 1px solid rgba(248, 113, 113, 0.35);

            color: #b91c1c;

            border-radius: 11px;

            padding: 12px 14px;

            font-size: 13px;

            margin-bottom: 20px;
        }


        .form-group {
            margin-bottom: 20px;
        }


        .form-group label {

            display: block;

            font-size: 13px;

            font-weight: 700;

            color: #344054;

            margin-bottom: 8px;
        }


        .input-wrapper {
            position: relative;
        }


        .input-icon {

            position: absolute;

            left: 15px;

            top: 50%;

            transform: translateY(-50%);

            font-size: 15px;

            color: #94a3b8;

            pointer-events: none;
        }


        .form-group input {

            width: 100%;

            height: 50px;

            border: 1px solid rgba(148,163,184,0.40);

            border-radius: 12px;

            padding: 0 15px 0 44px;

            font-size: 14px;

            color: #172033;

            outline: none;

            background: rgba(255,255,255,0.48);

            backdrop-filter: blur(10px);

            -webkit-backdrop-filter: blur(10px);

            transition: 0.25s ease;

            box-shadow:
                inset 0 1px 2px rgba(15,23,42,0.02);
        }


        .form-group input:hover {

            border-color: rgba(37,99,235,0.35);
        }


        .form-group input:focus {

            border-color: rgba(37,99,235,0.75);

            background: rgba(255,255,255,0.65);

            box-shadow:
                0 0 0 4px rgba(37,99,235,0.09);
        }


        .form-group input::placeholder {
            color: #94a3b8;
        }


        .login-btn {

            width: 100%;

            height: 50px;

            border: none;

            border-radius: 12px;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #3b82f6
                );

            color: #ffffff;

            font-size: 14px;

            font-weight: 700;

            cursor: pointer;

            margin-top: 5px;

            transition: 0.25s ease;

            box-shadow:
                0 9px 22px rgba(37,99,235,0.20);
        }


        .login-btn:hover {

            transform: translateY(-2px);

            box-shadow:
                0 13px 28px rgba(37,99,235,0.28);
        }


        .login-btn:active {
            transform: translateY(0);
        }



        .register-text {

            text-align: center;

            margin-top: 25px;

            color: #64748b;

            font-size: 13px;
        }


        .register-text a {

            color: #2563eb;

            font-weight: 700;

            text-decoration: none;
        }


        .register-text a:hover {
            text-decoration: underline;
        }


        @media (max-width: 850px) {

            body {
                padding: 25px 18px;
            }


            .login-wrapper {

                max-width: 560px;

                grid-template-columns: 1fr;

                min-height: auto;
            }


            .login-intro {

                padding: 40px;

                text-align: center;

                border-right: none;

                border-bottom: 1px solid rgba(255,255,255,0.55);
            }


            .brand {
                margin-bottom: 20px;
            }


            .login-intro h1 {
                font-size: 32px;
            }


            .login-intro p {
                margin: auto;
            }


            .intro-points {
                display: none;
            }


            .login-form-section {
                padding: 42px;
            }


            .back-home {
                margin-bottom: 25px;
            }
        }


        @media (max-width: 500px) {

            body {
                padding: 12px;
            }


            .login-wrapper {
                border-radius: 20px;
            }


            .login-intro {
                padding: 32px 24px;
            }


            .brand {
                font-size: 28px;
            }


            .login-intro h1 {
                font-size: 27px;
            }


            .login-intro p {
                font-size: 13px;
            }


            .login-form-section {
                padding: 30px 24px;
            }


            .form-title h2 {
                font-size: 26px;
            }


            .form-group input {
                height: 48px;
            }


            .login-btn {
                height: 48px;
            }
        }

    </style>

</head>


<body>


<div class="login-wrapper">



    <section class="login-intro">


        <div class="brand">
            JIMS<span>.</span>
        </div>


        <h1>
            Welcome Back!
        </h1>


        <p>
            Sign in to your JIMS account and continue
            managing your jobs, internships and
            applications.
        </p>


        <div class="intro-points">


            <div class="intro-point">

                <span class="check">
                    ✓
                </span>

                Find jobs and internships

            </div>


            <div class="intro-point">

                <span class="check">
                    ✓
                </span>

                Manage your applications

            </div>


            <div class="intro-point">

                <span class="check">
                    ✓
                </span>

                Connect students and companies

            </div>


        </div>

    </section>


    <section class="login-form-section">


        <a
            href="../index.php"
            class="back-home"
        >
            ← Back to Home
        </a>


        <div class="form-title">

            <h2>
                Sign in
            </h2>

            <p>
                Enter your account details to continue.
            </p>

        </div>


        <?php if (!empty($message)): ?>

            <div class="message">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="form-group">

                <label for="email">
                    Email Address
                </label>


                <div class="input-wrapper">

                    <span class="input-icon">
                        ✉
                    </span>


                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>"
                        required
                    >

                </div>

            </div>


            <div class="form-group">

                <label for="password">
                    Password
                </label>


                <div class="input-wrapper">

                    <span class="input-icon">
                        🔒
                    </span>


                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >

                </div>

            </div>


            <button
                type="submit"
                class="login-btn"
            >
                Login to JIMS
            </button>


        </form>


        <p class="register-text">

            Don't have an account?

            <a href="register.php">
                Create an account
            </a>

        </p>


    </section>


</div>


</body>

</html>