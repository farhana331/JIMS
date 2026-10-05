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


$stmt = $conn->prepare(
    "SELECT company_id, company_name
     FROM companies
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$company = $result->fetch_assoc();

$stmt->close();


if (!$company) {
    die("Company profile not found.");
}


$company_id = $company["company_id"];


$stmt = $conn->prepare(
    "SELECT

        a.application_id,
        a.student_id,
        a.job_id,
        a.status AS application_status,

        j.title AS job_title,
        j.job_type,
        j.location,

        u.name AS student_name,
        u.email AS student_email,
        u.phone AS student_phone,

        s.cv_file

     FROM applications a

     INNER JOIN jobs j
        ON a.job_id = j.job_id

     INNER JOIN students s
        ON a.student_id = s.student_id

     INNER JOIN users u
        ON s.user_id = u.user_id

     WHERE j.company_id = ?

     ORDER BY a.application_id DESC"
);


$stmt->bind_param(
    "i",
    $company_id
);

$stmt->execute();

$applications = $stmt->get_result();

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

    <title>
        Applications - JIMS
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

            background:
                linear-gradient(
                    135deg,
                    #eef5ff,
                    #f8fbff
                );

            color: #1f2937;

            min-height: 100vh;

            overflow: hidden;
        }


        .header {

            background:
                linear-gradient(
                    135deg,
                    #1e3a8a,
                    #2563eb
                );

            color: white;

            padding: 25px 7%;

            box-shadow:
                0 4px 15px
                rgba(0, 0, 0, 0.10);
        }


        .header h1 {

            font-size: 30px;

            margin-bottom: 7px;
        }


        .header p {

            color: #dbeafe;

            font-size: 15px;
        }


        .container {

            width: 86%;

            max-width: 1200px;

            height: calc(100vh - 150px);

            margin: 25px auto;

            overflow-y: auto;

            padding-bottom: 30px;
        }


        .top-bar {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            margin-bottom: 20px;
        }


        .back-button {

            display: inline-block;

            text-decoration: none;

            padding: 10px 18px;

            border-radius: 9px;

            background: #eff6ff;

            color: #1d4ed8;

            border:
                1px solid #bfdbfe;

            font-weight: 600;

            transition: 0.25s;
        }


        .back-button:hover {

            background: #2563eb;

            color: white;

            transform:
                translateY(-2px);
        }


        .count-box {

            background:
                rgba(255, 255, 255, 0.70);

            backdrop-filter:
                blur(12px);

            border-radius: 14px;

            padding: 18px 22px;

            margin-bottom: 22px;

            border:
                1px solid
                rgba(255,255,255,0.8);

            box-shadow:
                0 8px 25px
                rgba(37, 99, 235, 0.08);
        }


        .count-box strong {

            color: #1e3a8a;

            font-size: 24px;
        }


        .application-card {

            background:
                rgba(255, 255, 255, 0.72);

            backdrop-filter:
                blur(12px);

            border-radius: 16px;

            padding: 24px;

            margin-bottom: 18px;

            border:
                1px solid
                rgba(255,255,255,0.85);

            box-shadow:
                0 8px 25px
                rgba(31, 41, 55, 0.08);

            transition: 0.25s;
        }


        .application-card:hover {

            transform:
                translateY(-3px);

            box-shadow:
                0 12px 30px
                rgba(37, 99, 235, 0.12);
        }


        .job-title {

            color: #1e3a8a;

            font-size: 21px;

            margin-bottom: 8px;
        }


        .job-info {

            color: #64748b;

            font-size: 14px;

            margin-bottom: 18px;
        }


        .student-section {

            border-top:
                1px solid #e5e7eb;

            padding-top: 17px;
        }


        .student-name {

            font-size: 19px;

            font-weight: bold;

            color: #111827;

            margin-bottom: 10px;
        }


        .info {

            margin-bottom: 7px;

            font-size: 14px;

            color: #4b5563;
        }


        .info strong {

            color: #1f2937;
        }


        .status {

            display: inline-block;

            margin-top: 12px;

            padding: 7px 13px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: bold;
        }


        .status-pending {

            background: #fff7ed;

            color: #c2410c;
        }


        .status-shortlisted {

            background: #eff6ff;

            color: #1d4ed8;
        }


        .status-selected {

            background: #f0fdf4;

            color: #15803d;
        }


        .status-rejected {

            background: #fef2f2;

            color: #dc2626;
        }


        .cv-button {

            display: inline-block;

            margin-top: 15px;

            text-decoration: none;

            padding: 9px 16px;

            border-radius: 8px;

            background: #eff6ff;

            color: #1d4ed8;

            border:
                1px solid #bfdbfe;

            font-weight: 600;

            font-size: 13px;

            transition: 0.25s;
        }


        .cv-button:hover {

            background: #2563eb;

            color: white;
        }


        .view-button {

            display: inline-block;

            margin-top: 15px;

            margin-left: 8px;

            text-decoration: none;

            padding: 9px 16px;

            border-radius: 8px;

            background: #1e3a8a;

            color: white;

            font-weight: 600;

            font-size: 13px;

            transition: 0.25s;
        }


        .view-button:hover {

            background: #2563eb;

            transform:
                translateY(-2px);
        }


        .empty-box {

            text-align: center;

            padding: 60px 20px;

            background:
                rgba(255,255,255,0.70);

            backdrop-filter:
                blur(10px);

            border-radius: 16px;

            color: #64748b;

            box-shadow:
                0 8px 25px
                rgba(31,41,55,0.06);
        }


        .empty-box h2 {

            color: #1e3a8a;

            margin-bottom: 10px;
        }


        .bottom-links {

            text-align: center;

            margin-top: 25px;

            padding-bottom: 25px;
        }


        .logout {

            display: inline-block;

            text-decoration: none;

            padding: 11px 25px;

            border-radius: 9px;

            background: #eff6ff;

            color: #1d4ed8;

            border:
                1px solid #bfdbfe;

            font-weight: 600;

            transition: 0.25s;
        }


        .logout:hover {

            background: #2563eb;

            color: white;

            transform:
                translateY(-2px);
        }


    </style>

</head>


<body>


<header class="header">

    <h1>
        Applications
    </h1>

    <p>
        <?php
        echo htmlspecialchars(
            $company["company_name"]
        );
        ?>
        — All Received Applications
    </p>

</header>



<div class="container">


    <div class="top-bar">

        <h2>
            All Applications
        </h2>


        <a
            href="dashboard.php"
            class="back-button"
        >
            ← Dashboard
        </a>

    </div>



    <div class="count-box">

        <p>
            Total Applications:
            <strong>
                <?php
                echo $applications->num_rows;
                ?>
            </strong>
        </p>

    </div>



    <?php if ($applications->num_rows > 0): ?>


        <?php while (
            $application =
            $applications->fetch_assoc()
        ): ?>


            <div class="application-card">


                <h3 class="job-title">

                    <?php
                    echo htmlspecialchars(
                        $application["job_title"]
                    );
                    ?>

                </h3>


                <p class="job-info">

                    <?php
                    echo htmlspecialchars(
                        $application["job_type"]
                    );
                    ?>

                    &nbsp; • &nbsp;

                    <?php
                    echo htmlspecialchars(
                        $application["location"]
                        ?? "Location not specified"
                    );
                    ?>

                </p>



                <div class="student-section">


                    <p class="student-name">

                        <?php
                        echo htmlspecialchars(
                            $application["student_name"]
                        );
                        ?>

                    </p>


                    <p class="info">

                        <strong>
                            Email:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $application["student_email"]
                        );
                        ?>

                    </p>


                    <p class="info">

                        <strong>
                            Phone:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $application["student_phone"]
                            ?? "Not provided"
                        );
                        ?>

                    </p>



                    <?php

                    $status =
                        $application[
                            "application_status"
                        ];

                    if ($status == "pending"):

                    ?>

                        <span
                            class="status status-pending"
                        >
                            Pending ⏳
                        </span>


                    <?php
                    elseif (
                        $status == "shortlisted"
                    ):
                    ?>

                        <span
                            class="status status-shortlisted"
                        >
                            Shortlisted ⭐
                        </span>


                    <?php
                    elseif (
                        $status == "selected"
                    ):
                    ?>

                        <span
                            class="status status-selected"
                        >
                            Selected 🎉
                        </span>


                    <?php
                    elseif (
                        $status == "rejected"
                    ):
                    ?>

                        <span
                            class="status status-rejected"
                        >
                            Rejected ❌
                        </span>


                    <?php else: ?>

                        <span class="status">

                            <?php
                            echo htmlspecialchars(
                                $status
                            );
                            ?>

                        </span>

                    <?php endif; ?>



<?php if (
    !empty(
        $application["cv_file"]
    )
): ?>

    <br>

    <a
        href="../uploads/cvs/<?php
        echo htmlspecialchars(
            $application["cv_file"]
        );
        ?>"
        target="_blank"
        class="cv-button"
    >
        View CV
    </a>

<?php endif; ?>



                    <a
                        href="applicants.php?job_id=<?php
                        echo $application["job_id"];
                        ?>"
                        class="view-button"
                    >
                        View Application →
                    </a>


                </div>


            </div>


        <?php endwhile; ?>


    <?php else: ?>


        <div class="empty-box">

            <h2>
                No Applications Yet
            </h2>

            <p>
                No students have applied
                to your jobs or internships yet.
            </p>

        </div>


    <?php endif; ?>



    <div class="bottom-links">

        <a
            href="../auth/logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>


</div>


</body>

</html>