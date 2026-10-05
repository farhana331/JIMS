<?php

require_once "../config/database.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "";
    $phone = trim($_POST["phone"] ?? "");

    if (empty($name) || empty($email) || empty($password) || empty($role)) {

        $message = "Please fill in all required fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $message_type = "error";

    } elseif (!empty($phone) && !preg_match('/^[0-9]{11}$/', $phone)) {

        $message = "Phone number must be exactly 11 digits.";
        $message_type = "error";

    } elseif (!in_array($role, ["student", "company"])) {

        $message = "Invalid account role.";
        $message_type = "error";

    } else {

        $check = $conn->prepare(
            "SELECT user_id FROM users WHERE email = ?"
        );

        $check->bind_param("s", $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Email already exists.";
            $message_type = "error";

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $account_status = ($role === "company")
                ? "pending"
                : "active";

            $conn->begin_transaction();

            try {

                $stmt = $conn->prepare(
                    "INSERT INTO users
                    (
                        name,
                        email,
                        password,
                        role,
                        phone,
                        account_status
                    )
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    "ssssss",
                    $name,
                    $email,
                    $hashedPassword,
                    $role,
                    $phone,
                    $account_status
                );

                if (!$stmt->execute()) {
                    throw new Exception("User registration failed.");
                }

                $user_id = $stmt->insert_id;

                $stmt->close();


                if ($role === "student") {

                    $university = "";
                    $department = "";

                    $studentStmt = $conn->prepare(
                        "INSERT INTO students
                        (
                            user_id,
                            university,
                            department
                        )
                        VALUES (?, ?, ?)"
                    );

                    $studentStmt->bind_param(
                        "iss",
                        $user_id,
                        $university,
                        $department
                    );

                    if (!$studentStmt->execute()) {
                        throw new Exception("Student profile creation failed.");
                    }

                    $studentStmt->close();

                    $conn->commit();

                    $message =
                        "Registration successful! You can login now.";

                    $message_type = "success";
                }


                elseif ($role === "company") {

                    $companyName = $name;
                    $approvalStatus = "pending";
                    $approvalRequested = 1;

                    $companyStmt = $conn->prepare(
                        "INSERT INTO companies
                        (
                            user_id,
                            company_name,
                            approval_status,
                            approval_requested
                        )
                        VALUES (?, ?, ?, ?)"
                    );

                    $companyStmt->bind_param(
                        "issi",
                        $user_id,
                        $companyName,
                        $approvalStatus,
                        $approvalRequested
                    );

                    if (!$companyStmt->execute()) {
                        throw new Exception(
                            "Company profile creation failed."
                        );
                    }

                    $companyStmt->close();

                    $conn->commit();

                    $message =
                        "Registration submitted successfully. Your account is pending admin approval.";

                    $message_type = "pending";
                }

            } catch (Exception $e) {

                $conn->rollback();

                $message =
                    "Registration failed: " . $e->getMessage();

                $message_type = "error";
            }
        }

        $check->close();
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

<title>Create Account - JIMS</title>

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

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 30px;

    color: #172033;

    background:
        radial-gradient(
            circle at 15% 20%,
            rgba(37, 99, 235, 0.18),
            transparent 30%
        ),
        radial-gradient(
            circle at 85% 80%,
            rgba(99, 102, 241, 0.16),
            transparent 30%
        ),
        linear-gradient(
            135deg,
            #eff6ff,
            #f8fafc 45%,
            #eef2ff
        );
}

.register-wrapper {

    width: 100%;
    max-width: 1050px;

    display: grid;
    grid-template-columns: 0.9fr 1.1fr;

    overflow: hidden;

    border-radius: 28px;

    background:
        rgba(255, 255, 255, 0.58);

    border:
        1px solid rgba(255, 255, 255, 0.75);

    backdrop-filter:
        blur(22px);

    -webkit-backdrop-filter:
        blur(22px);

    box-shadow:
        0 30px 80px
        rgba(15, 23, 42, 0.13);
}

.register-intro {

    padding: 60px 48px;

    background:
        linear-gradient(
            145deg,
            rgba(37, 99, 235, 0.93),
            rgba(79, 70, 229, 0.86)
        );

    color: white;

    position: relative;

    overflow: hidden;
}

.register-intro::before {

    content: "";

    position: absolute;

    width: 230px;
    height: 230px;

    border-radius: 50%;

    background:
        rgba(255,255,255,0.09);

    top: -90px;
    right: -80px;
}

.register-intro::after {

    content: "";

    position: absolute;

    width: 170px;
    height: 170px;

    border-radius: 50%;

    background:
        rgba(255,255,255,0.07);

    bottom: -70px;
    left: -60px;
}

.logo {

    font-size: 30px;
    font-weight: 800;

    letter-spacing: -1px;

    margin-bottom: 70px;

    position: relative;
    z-index: 1;
}

.logo span {
    color: #bfdbfe;
}

.register-intro h1 {

    font-size: 42px;

    line-height: 1.12;

    letter-spacing: -1.5px;

    margin-bottom: 20px;

    position: relative;
    z-index: 1;
}

.register-intro p {

    color: #dbeafe;

    font-size: 15px;

    line-height: 1.8;

    max-width: 400px;

    position: relative;
    z-index: 1;
}

.features {

    margin-top: 38px;

    position: relative;
    z-index: 1;
}

.feature {

    display: flex;

    align-items: center;

    gap: 13px;

    margin-bottom: 18px;

    font-size: 14px;

    color: #eff6ff;
}

.feature-icon {

    width: 31px;
    height: 31px;

    border-radius: 50%;

    display: flex;

    align-items: center;
    justify-content: center;

    background:
        rgba(255,255,255,0.16);

    border:
        1px solid rgba(255,255,255,0.22);
}

.register-form-area {

    padding: 48px 55px;

    background:
        rgba(255,255,255,0.46);
}

.back-home {

    display: inline-flex;

    color: #64748b;

    font-size: 13px;

    text-decoration: none;

    margin-bottom: 27px;
}

.back-home:hover {
    color: #2563eb;
}

.form-title {

    font-size: 30px;

    color: #111827;

    margin-bottom: 7px;
}

.form-subtitle {

    color: #64748b;

    font-size: 14px;

    margin-bottom: 25px;
}

.message {

    padding: 14px 16px;

    border-radius: 12px;

    margin-bottom: 22px;

    font-size: 13px;

    line-height: 1.6;
}

.message.success {

    background:
        rgba(220,252,231,0.75);

    border:
        1px solid rgba(34,197,94,0.25);

    color: #166534;
}

.message.pending {

    background:
        rgba(219,234,254,0.75);

    border:
        1px solid rgba(37,99,235,0.20);

    color: #1d4ed8;
}

.message.error {

    background:
        rgba(254,226,226,0.75);

    border:
        1px solid rgba(239,68,68,0.22);

    color: #b91c1c;
}

.form-group {
    margin-bottom: 17px;
}

.form-group label {

    display: block;

    font-size: 13px;

    font-weight: 700;

    color: #344054;

    margin-bottom: 7px;
}

.required {
    color: #ef4444;
}

.form-group input,
.form-group select {

    width: 100%;

    height: 47px;

    border-radius: 11px;

    border:
        1px solid rgba(148,163,184,0.45);

    background:
        rgba(255,255,255,0.58);

    padding:
        0 14px;

    font-size: 14px;

    color: #172033;

    outline: none;

    transition: 0.2s;
}

.form-group input:focus,
.form-group select:focus {

    border-color: #2563eb;

    background:
        rgba(255,255,255,0.85);

    box-shadow:
        0 0 0 4px
        rgba(37,99,235,0.09);
}

.form-row {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 14px;
}

.register-button {

    width: 100%;

    height: 49px;

    border: none;

    border-radius: 11px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    color: white;

    font-size: 14px;

    font-weight: 700;

    cursor: pointer;

    margin-top: 5px;

    transition: 0.25s;

    box-shadow:
        0 10px 25px
        rgba(37,99,235,0.20);
}

.register-button:hover {

    transform: translateY(-2px);

    box-shadow:
        0 14px 30px
        rgba(37,99,235,0.25);
}

.flow {

    margin-top: 28px;

    padding: 18px;

    border-radius: 15px;

    background:
        rgba(248,250,252,0.60);

    border:
        1px solid rgba(226,232,240,0.75);
}

.flow-title {

    font-size: 12px;

    font-weight: 700;

    color: #64748b;

    margin-bottom: 14px;
}

.flow-steps {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 5px;
}

.step {

    text-align: center;

    font-size: 10px;

    color: #64748b;

    flex: 1;
}

.step-circle {

    width: 28px;
    height: 28px;

    margin: 0 auto 6px;

    border-radius: 50%;

    display: flex;

    align-items: center;
    justify-content: center;

    background: #dbeafe;

    color: #2563eb;

    font-size: 11px;

    font-weight: 700;
}

.flow-line {

    height: 1px;

    flex: 0.5;

    background: #cbd5e1;
}

.login-link {

    text-align: center;

    margin-top: 22px;

    color: #64748b;

    font-size: 13px;
}

.login-link a {

    color: #2563eb;

    font-weight: 700;

    text-decoration: none;
}

.footer-text {

    text-align: center;

    font-size: 11px;

    color: #94a3b8;

    margin-top: 18px;
}

@media(max-width: 850px) {

    .register-wrapper {
        grid-template-columns: 1fr;
        max-width: 560px;
    }

    .register-intro {
        padding: 40px;
    }

    .logo {
        margin-bottom: 35px;
    }

    .register-intro h1 {
        font-size: 32px;
    }

    .register-form-area {
        padding: 40px;
    }
}

@media(max-width: 520px) {

    body {
        padding: 12px;
    }

    .register-wrapper {
        border-radius: 20px;
    }

    .register-intro {
        padding: 32px 25px;
    }

    .register-form-area {
        padding: 30px 24px;
    }

    .register-intro h1 {
        font-size: 27px;
    }

    .form-title {
        font-size: 25px;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .flow-steps {
        gap: 2px;
    }
}

</style>

</head>

<body>

<div class="register-wrapper">

    <section class="register-intro">

        <div class="logo">
            JIMS<span>.</span>
        </div>

        <h1>
            Start Your Career Journey
        </h1>

        <p>
            Create your account and connect with
            students, companies and career opportunities
            through JIMS.
        </p>

        <div class="features">

            <div class="feature">
                <div class="feature-icon">✓</div>
                Find jobs and internships
            </div>

            <div class="feature">
                <div class="feature-icon">✓</div>
                Manage your applications
            </div>

            <div class="feature">
                <div class="feature-icon">✓</div>
                Connect with companies
            </div>

            <div class="feature">
                <div class="feature-icon">✓</div>
                Secure role-based access
            </div>

        </div>

    </section>


    <section class="register-form-area">

        <a
            href="../index.php"
            class="back-home"
        >
            ← Back to Home
        </a>

        <h2 class="form-title">
            Create Account
        </h2>

        <p class="form-subtitle">
            Register to get started with JIMS.
        </p>


        <?php if (!empty($message)): ?>

            <div class="message <?php echo $message_type; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-group">

                <label>
                    Full Name
                    <span class="required">*</span>
                </label>

                <input
                    type="text"
                    name="name"
                    placeholder="Enter your full name"
                    value="<?php echo htmlspecialchars($_POST["name"] ?? ""); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Email Address
                    <span class="required">*</span>
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="example@email.com"
                    value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>"
                    required
                >

            </div>


            <div class="form-row">

                <div class="form-group">

                    <label>
                        Password
                        <span class="required">*</span>
                    </label>

                    <input
                        type="password"
                        name="password"
                        placeholder="Minimum 6 characters"
                        minlength="6"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Phone
                    </label>

                    <input
                        type="tel"
                        name="phone"
                        placeholder="01XXXXXXXXX"
                        maxlength="11"
                        pattern="[0-9]{11}"
                        title="Please enter exactly 11 digits"
                        value="<?php echo htmlspecialchars($_POST["phone"] ?? ""); ?>"
                    >

                </div>

            </div>


            <div class="form-group">

                <label>
                    Register As
                    <span class="required">*</span>
                </label>

                <select
                    name="role"
                    required
                >

                    <option value="">
                        Select your role
                    </option>

                    <option value="student">
                        Student
                    </option>

                    <option value="company">
                        Company
                    </option>

                </select>

            </div>


            <button
                type="submit"
                class="register-button"
            >
                Create Account →
            </button>

        </form>


        <div class="flow">

            <div class="flow-title">
                COMPANY ACCOUNT APPROVAL FLOW
            </div>

            <div class="flow-steps">

                <div class="step">
                    <div class="step-circle">1</div>
                    Register
                </div>

                <div class="flow-line"></div>

                <div class="step">
                    <div class="step-circle">2</div>
                    Pending
                </div>

                <div class="flow-line"></div>

                <div class="step">
                    <div class="step-circle">3</div>
                    Admin Review
                </div>

                <div class="flow-line"></div>

                <div class="step">
                    <div class="step-circle">4</div>
                    Active
                </div>

                <div class="flow-line"></div>

                <div class="step">
                    <div class="step-circle">5</div>
                    Login
                </div>

            </div>

        </div>


        <div class="login-link">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </div>


        <div class="footer-text">

            © <?php echo date("Y"); ?> JIMS.
            All rights reserved.

        </div>

    </section>

</div>

</body>

</html>