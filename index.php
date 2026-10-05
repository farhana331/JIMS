<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>JIMS - Job & Internship Management System</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: "Segoe UI", Arial, sans-serif;

            min-height: 100vh;

            color: #172033;

            background:
                radial-gradient(
                    circle at 10% 10%,
                    rgba(59, 130, 246, 0.13),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 90% 20%,
                    rgba(147, 197, 253, 0.16),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 50% 90%,
                    rgba(191, 219, 254, 0.14),
                    transparent 35%
                ),
                #f8fafc;

            line-height: 1.6;
        }

        a {
            text-decoration: none;
        }

        nav {
            width: 100%;

            min-height: 76px;

            padding: 14px 7%;

            display: flex;
            align-items: center;
            justify-content: space-between;

            position: sticky;
            top: 0;

            z-index: 1000;

            background: rgba(255, 255, 255, 0.48);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            border-bottom: 1px solid rgba(255, 255, 255, 0.65);

            box-shadow:
                0 8px 30px rgba(15, 23, 42, 0.06);
        }

        .logo {
            font-size: 28px;
            font-weight: 800;

            letter-spacing: -1px;

            color: #2563eb;
        }

        .logo span {
            color: #172033;
        }

        .nav-links {
            display: flex;

            align-items: center;

            gap: 26px;
        }

        .nav-links a {
            color: #475569;

            font-size: 14px;

            font-weight: 600;

            transition: 0.25s ease;
        }

        .nav-links a:hover {
            color: #2563eb;
        }

        .login-btn {
            padding: 9px 19px;

            border: 1px solid rgba(37, 99, 235, 0.35);

            border-radius: 10px;

            color: #2563eb !important;

            background: rgba(255, 255, 255, 0.35);

            backdrop-filter: blur(10px);

            transition: 0.25s ease;
        }

        .login-btn:hover {
            background: rgba(239, 246, 255, 0.8);

            transform: translateY(-2px);
        }

        .register-btn {
            padding: 10px 20px;

            background: rgba(37, 99, 235, 0.90);

            color: white !important;

            border-radius: 10px;

            box-shadow:
                0 8px 20px rgba(37, 99, 235, 0.20);

            transition: 0.25s ease;
        }

        .register-btn:hover {
            background: #1d4ed8;

            transform: translateY(-2px);
        }

        .hero {
            min-height: 650px;

            padding: 80px 8%;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 70px;

            background: transparent;
        }

        .hero-content {
            flex: 1;

            max-width: 680px;
        }

        .hero-badge {
            display: inline-block;

            padding: 8px 15px;

            margin-bottom: 22px;

            background: rgba(239, 246, 255, 0.58);

            color: #2563eb;

            border: 1px solid rgba(191, 219, 254, 0.7);

            border-radius: 30px;

            font-size: 13px;

            font-weight: 700;

            backdrop-filter: blur(10px);
        }

        .hero-content h1 {
            font-size: clamp(42px, 5vw, 64px);

            line-height: 1.08;

            letter-spacing: -2px;

            margin-bottom: 24px;

            color: #111827;
        }

        .hero-content h1 span {
            color: #2563eb;
        }

        .hero-content p {
            font-size: 17px;

            color: #64748b;

            line-height: 1.8;

            max-width: 620px;

            margin-bottom: 34px;
        }

        .hero-buttons {
            display: flex;

            gap: 14px;

            align-items: center;

            flex-wrap: wrap;
        }

        .primary-btn,
        .secondary-btn {
            padding: 13px 25px;

            border-radius: 11px;

            font-size: 14px;

            font-weight: 700;

            transition: 0.25s ease;
        }

        .primary-btn {
            background: rgba(37, 99, 235, 0.92);

            color: white;

            box-shadow:
                0 10px 25px rgba(37, 99, 235, 0.20);
        }

        .primary-btn:hover {
            background: #1d4ed8;

            transform: translateY(-2px);
        }

        .secondary-btn {
            background: rgba(255, 255, 255, 0.45);

            color: #2563eb;

            border: 1px solid rgba(191, 219, 254, 0.8);

            backdrop-filter: blur(12px);
        }

        .secondary-btn:hover {
            background: rgba(239, 246, 255, 0.75);

            transform: translateY(-2px);
        }

        .hero-card {
            width: 380px;

            padding: 30px;

            background: rgba(255, 255, 255, 0.42);

            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);

            border: 1px solid rgba(255, 255, 255, 0.70);

            border-radius: 22px;

            box-shadow:
                0 20px 50px rgba(15, 23, 42, 0.10);

            position: relative;
        }

        .hero-card::before {
            content: "";

            position: absolute;

            width: 90px;
            height: 90px;

            background: rgba(147, 197, 253, 0.25);

            border-radius: 50%;

            top: -35px;
            right: -25px;

            z-index: -1;

            filter: blur(2px);
        }

        .hero-card h3 {
            font-size: 19px;

            margin-bottom: 22px;

            color: #172033;
        }

        .card-item {
            display: flex;

            align-items: center;

            gap: 14px;

            padding: 16px;

            margin-bottom: 12px;

            background: rgba(255, 255, 255, 0.48);

            border: 1px solid rgba(255, 255, 255, 0.65);

            border-radius: 14px;

            transition: 0.25s ease;
        }

        .card-item:hover {
            transform: translateX(5px);

            background: rgba(255, 255, 255, 0.68);

            box-shadow:
                0 8px 20px rgba(15, 23, 42, 0.06);
        }

        .card-icon {
            width: 44px;
            height: 44px;

            display: flex;

            align-items: center;
            justify-content: center;

            background: rgba(239, 246, 255, 0.70);

            border: 1px solid rgba(191, 219, 254, 0.6);

            border-radius: 12px;

            font-size: 21px;

            flex-shrink: 0;
        }

        .card-item strong {
            display: block;

            margin-bottom: 2px;

            font-size: 14px;

            color: #1f2937;
        }

        .card-item small {
            color: #64748b;

            font-size: 12px;
        }

        .section-title {
            font-size: 34px;

            line-height: 1.2;

            color: #111827;

            margin-bottom: 12px;

            letter-spacing: -0.7px;
        }

        .section-subtitle {
            color: #64748b;

            font-size: 15px;

            max-width: 600px;

            margin: 0 auto 45px;
        }

        .features {
            padding: 90px 8%;

            background: transparent;

            text-align: center;
        }

        .feature-container {
            max-width: 1150px;

            margin: auto;

            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 22px;
        }

        .feature-card {
            text-align: left;

            padding: 28px;

            background: rgba(255, 255, 255, 0.42);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            border: 1px solid rgba(255, 255, 255, 0.72);

            border-radius: 18px;

            box-shadow:
                0 12px 35px rgba(15, 23, 42, 0.06);

            transition: 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-6px);

            background: rgba(255, 255, 255, 0.58);

            border-color: rgba(191, 219, 254, 0.8);

            box-shadow:
                0 18px 40px rgba(15, 23, 42, 0.09);
        }

        .feature-icon {
            width: 52px;
            height: 52px;

            display: flex;

            align-items: center;
            justify-content: center;

            background: rgba(239, 246, 255, 0.68);

            border: 1px solid rgba(191, 219, 254, 0.55);

            border-radius: 13px;

            font-size: 25px;

            margin-bottom: 18px;
        }

        .feature-card h3 {
            font-size: 17px;

            margin-bottom: 9px;

            color: #172033;
        }

        .feature-card p {
            color: #64748b;

            font-size: 14px;

            line-height: 1.7;
        }

        .user-section {
            padding: 90px 8%;

            background: transparent;

            text-align: center;
        }

        .user-container {
            max-width: 1050px;

            margin: auto;

            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 24px;
        }

        .user-card {
            background: rgba(255, 255, 255, 0.42);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            padding: 32px 27px;

            border-radius: 18px;

            border: 1px solid rgba(255, 255, 255, 0.72);

            box-shadow:
                0 12px 35px rgba(15, 23, 42, 0.06);

            transition: 0.3s ease;
        }

        .user-card:hover {
            transform: translateY(-6px);

            background: rgba(255, 255, 255, 0.58);

            box-shadow:
                0 18px 40px rgba(15, 23, 42, 0.09);
        }

        .user-card .feature-icon {
            margin: 0 auto 17px;
        }

        .user-card h3 {
            font-size: 18px;

            margin-bottom: 10px;

            color: #172033;
        }

        .user-card p {
            color: #64748b;

            font-size: 14px;

            line-height: 1.7;
        }

        .cta {
            margin: 40px 8% 70px;

            padding: 75px 8%;

            text-align: center;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    rgba(37, 99, 235, 0.94),
                    rgba(29, 78, 216, 0.90)
                );

            backdrop-filter: blur(18px);

            border: 1px solid rgba(255, 255, 255, 0.25);

            border-radius: 25px;

            box-shadow:
                0 20px 45px rgba(37, 99, 235, 0.18);
        }

        .cta h2 {
            font-size: 35px;

            line-height: 1.2;

            margin-bottom: 14px;
        }

        .cta p {
            color: #dbeafe;

            margin-bottom: 28px;

            font-size: 15px;
        }

        .cta a {
            display: inline-block;

            background: rgba(255, 255, 255, 0.95);

            color: #2563eb;

            padding: 13px 26px;

            border-radius: 10px;

            font-size: 14px;

            font-weight: 700;

            transition: 0.25s ease;
        }

        .cta a:hover {
            transform: translateY(-2px);

            box-shadow:
                0 10px 25px rgba(0, 0, 0, 0.15);
        }

        footer {
            background: rgba(255, 255, 255, 0.35);

            backdrop-filter: blur(18px);

            -webkit-backdrop-filter: blur(18px);

            color: #172033;

            text-align: center;

            padding: 30px 20px;

            border-top: 1px solid rgba(255, 255, 255, 0.65);
        }

        footer strong {
            font-size: 15px;
        }

        footer p {
            color: #64748b;

            margin-top: 8px;

            font-size: 13px;
        }

        @media (max-width: 950px) {

            nav {
                padding: 14px 5%;
            }

            .hero {
                padding: 70px 5%;

                gap: 45px;
            }

            .features,
            .user-section {
                padding-left: 5%;
                padding-right: 5%;
            }

            .feature-container {
                grid-template-columns: repeat(2, 1fr);
            }

            .user-container {
                grid-template-columns: 1fr;
            }

            .cta {
                margin-left: 5%;
                margin-right: 5%;
            }
        }

        @media (max-width: 700px) {

            nav {
                min-height: auto;

                padding: 17px 5%;
            }

            .nav-links {
                gap: 8px;
            }

            .nav-links a:not(.login-btn):not(.register-btn) {
                display: none;
            }

            .login-btn,
            .register-btn {
                padding: 8px 12px;

                font-size: 12px !important;
            }

            .hero {
                flex-direction: column;

                text-align: center;

                padding: 65px 5%;
            }

            .hero-content {
                width: 100%;
            }

            .hero-content h1 {
                font-size: 42px;

                letter-spacing: -1px;
            }

            .hero-content p {
                font-size: 15px;
            }

            .hero-buttons {
                justify-content: center;

                flex-wrap: wrap;
            }

            .hero-card {
                width: 100%;

                max-width: 390px;
            }

            .feature-container {
                grid-template-columns: 1fr;
            }

            .section-title {
                font-size: 29px;
            }

            .cta {
                padding: 60px 6%;
            }

            .cta h2 {
                font-size: 28px;
            }
        }

        @media (max-width: 430px) {

            .logo {
                font-size: 23px;
            }

            .hero-content h1 {
                font-size: 36px;
            }

            .primary-btn,
            .secondary-btn {
                width: 100%;
            }

            .hero-buttons {
                width: 100%;
            }

            .cta {
                margin-left: 4%;
                margin-right: 4%;
            }
        }

    </style>

</head>

<body>

<nav>

    <div class="logo">
        JIMS<span>.</span>
    </div>

    <div class="nav-links">

        <a href="#home">
            Home
        </a>

        <a href="#features">
            Features
        </a>

        <a href="#about">
            About
        </a>

        <a
            href="auth/login.php"
            class="login-btn"
        >
            Login
        </a>

        <a
            href="auth/register.php"
            class="register-btn"
        >
            Register
        </a>

    </div>

</nav>

<section
    class="hero"
    id="home"
>

    <div class="hero-content">

        <span class="hero-badge">
            ✦ Job & Internship Management Platform
        </span>

        <h1>

            Find Your Next
            <span>Opportunity.</span>

        </h1>

        <p>

            JIMS is a Job & Internship Management System
            that connects students with companies and helps
            manage the complete recruitment process efficiently.

        </p>

        <div class="hero-buttons">

            <a
                href="auth/login.php"
                class="primary-btn"
            >
                Get Started →
            </a>

            <a
                href="auth/register.php"
                class="secondary-btn"
            >
                Create Account
            </a>

        </div>

    </div>

    <div class="hero-card">

        <h3>
            What can you do with JIMS?
        </h3>

        <div class="card-item">

            <div class="card-icon">
                🎓
            </div>

            <div>

                <strong>
                    Students
                </strong>

                <small>
                    Find jobs, apply and track applications.
                </small>

            </div>

        </div>

        <div class="card-item">

            <div class="card-icon">
                🏢
            </div>

            <div>

                <strong>
                    Companies
                </strong>

                <small>
                    Post vacancies and manage candidates.
                </small>

            </div>

        </div>

        <div class="card-item">

            <div class="card-icon">
                📅
            </div>

            <div>

                <strong>
                    Interviews
                </strong>

                <small>
                    Schedule and manage interviews easily.
                </small>

            </div>

        </div>

    </div>

</section>

<section
    class="features"
    id="features"
>

    <h2 class="section-title">
        Everything You Need
    </h2>

    <p class="section-subtitle">
        A complete platform for modern recruitment management.
    </p>

    <div class="feature-container">

        <div class="feature-card">

            <div class="feature-icon">
                🔍
            </div>

            <h3>
                Find Opportunities
            </h3>

            <p>
                Students can browse and search available
                jobs and internships based on their interests.
            </p>

        </div>

        <div class="feature-card">

            <div class="feature-icon">
                📄
            </div>

            <h3>
                Easy Applications
            </h3>

            <p>
                Apply for suitable positions and keep track
                of your application status from one place.
            </p>

        </div>

        <div class="feature-card">

            <div class="feature-icon">
                🏢
            </div>

            <h3>
                Company Management
            </h3>

            <p>
                Companies can post vacancies, review applicants
                and manage their recruitment process.
            </p>

        </div>

        <div class="feature-card">

            <div class="feature-icon">
                📅
            </div>

            <h3>
                Interview Management
            </h3>

            <p>
                Schedule interviews and track interview results
                throughout the recruitment process.
            </p>

        </div>

        <div class="feature-card">

            <div class="feature-icon">
                🔐
            </div>

            <h3>
                Secure Access
            </h3>

            <p>
                Role-based access keeps student, company and
                administrator information protected.
            </p>

        </div>

        <div class="feature-card">

            <div class="feature-icon">
                📊
            </div>

            <h3>
                Application Tracking
            </h3>

            <p>
                Monitor applications from submission through
                interview and final selection.
            </p>

        </div>

    </div>

</section>

<section
    class="user-section"
    id="about"
>

    <h2 class="section-title">
        Built For Everyone
    </h2>

    <p class="section-subtitle">
        JIMS provides dedicated features for every user.
    </p>

    <div class="user-container">

        <div class="user-card">

            <div class="feature-icon">
                🎓
            </div>

            <h3>
                Students
            </h3>

            <p>
                Create your profile, upload your CV,
                discover opportunities and apply for
                jobs or internships.
            </p>

        </div>

        <div class="user-card">

            <div class="feature-icon">
                🏢
            </div>

            <h3>
                Companies
            </h3>

            <p>
                Create a company profile, publish vacancies,
                review applicants and conduct interviews.
            </p>

        </div>

        <div class="user-card">

            <div class="feature-icon">
                🛡️
            </div>

            <h3>
                Administrators
            </h3>

            <p>
                Manage users, approve companies and job posts,
                and maintain the overall platform.
            </p>

        </div>

    </div>

</section>

<section class="cta">

    <h2>
        Ready to Find Your Next Opportunity?
    </h2>

    <p>
        Join JIMS and make your job search and recruitment process easier.
    </p>

    <a href="auth/register.php">
        Create Your Account
    </a>

</section>

<footer>

    <strong>
        JIMS - Job & Internship Management System
    </strong>

    <p>
        © <?php echo date("Y"); ?> JIMS. All rights reserved.
    </p>

</footer>

</body>

</html>