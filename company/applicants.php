<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "company") {
    header("Location: ../auth/login.php");
    exit();
}

$job_id = $_GET["job_id"] ?? $_GET["id"] ?? 0;

if (!is_numeric($job_id) || $job_id <= 0) {
    die("Invalid job ID.");
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT company_id, company_name
     FROM companies
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result_company = $stmt->get_result();
$company = $result_company->fetch_assoc();

$stmt->close();

if (!$company) {
    die("Company profile not found.");
}

$company_id = $company["company_id"];

$stmt = $conn->prepare(
    "SELECT
        job_id,
        title,
        job_type,
        location,
        deadline,
        status
     FROM jobs
     WHERE job_id = ?
     AND company_id = ?"
);

$stmt->bind_param("ii", $job_id, $company_id);
$stmt->execute();

$result = $stmt->get_result();
$job = $result->fetch_assoc();

$stmt->close();

if (!$job) {
    die("Job not found or unauthorized access.");
}

$stmt = $conn->prepare(
    "SELECT

        a.application_id,
        a.student_id,
        a.status,

        u.name AS student_name,
        u.email AS student_email,
        u.phone AS student_phone,

        s.cv_file,

        i.interview_id,
        i.interview_date,
        i.interview_time,
        i.interview_type,
        i.meeting_link,
        i.notes,
        i.status AS interview_status,
        i.result AS interview_result

     FROM applications a

     INNER JOIN students s
        ON a.student_id = s.student_id

     INNER JOIN users u
        ON s.user_id = u.user_id

     LEFT JOIN interviews i
        ON a.application_id = i.application_id

     WHERE a.job_id = ?

     ORDER BY a.application_id DESC"
);

$stmt->bind_param("i", $job_id);
$stmt->execute();

$applicants = $stmt->get_result();

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
        Applicants -
        <?php echo htmlspecialchars($job["title"]); ?>
        | JIMS
    </title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
            line-height: 1.6;
        }


        .topbar {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 18px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            position: sticky;
            top: 0;
            z-index: 100;
        }


        .brand {
            font-size: 22px;
            font-weight: 800;
            color: #4f46e5;
            letter-spacing: -0.5px;
        }


        .company-name {
            font-size: 14px;
            color: #6b7280;
        }


        .container {
            width: 92%;
            max-width: 1200px;
            margin: 35px auto 60px;
        }


        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            color: #4f46e5;
            font-weight: 600;
            margin-bottom: 25px;
        }


        .back-link:hover {
            text-decoration: underline;
        }


        .page-header {
            margin-bottom: 25px;
        }


        .page-header h1 {
            font-size: 32px;
            color: #111827;
            margin-bottom: 6px;
        }


        .page-header p {
            color: #6b7280;
        }


        .job-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 35px;
            box-shadow: 0 5px 20px rgba(15, 23, 42, 0.05);
        }


        .job-card h2 {
            color: #111827;
            font-size: 23px;
            margin-bottom: 18px;
        }


        .job-info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }


        .job-info {
            background: #f8fafc;
            padding: 14px 16px;
            border-radius: 10px;
            border: 1px solid #eef0f4;
        }


        .job-info span {
            display: block;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 3px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.4px;
        }


        .job-info strong {
            color: #1f2937;
        }


        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }


        .status-approved {
            background: #dcfce7;
            color: #166534;
        }


        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }


        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }


        .status-shortlisted {
            background: #e0e7ff;
            color: #3730a3;
        }


        .status-selected {
            background: #dcfce7;
            color: #166534;
        }


        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 15px;
        }


        .section-title h2 {
            font-size: 24px;
            color: #111827;
        }


        .application-count {
            background: #eef2ff;
            color: #4338ca;
            padding: 7px 13px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }


        .applicant-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);
            transition: 0.2s ease;
        }


        .applicant-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.08);
        }


        .applicant-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 20px;
        }


        .student-info {
            display: flex;
            align-items: center;
            gap: 14px;
        }


        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 19px;
            font-weight: 800;
            flex-shrink: 0;
        }


        .student-info h3 {
            font-size: 19px;
            color: #111827;
            margin-bottom: 2px;
        }


        .student-info p {
            color: #6b7280;
            font-size: 14px;
        }


        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }


        .detail {
            background: #f8fafc;
            padding: 12px 15px;
            border-radius: 9px;
        }


        .detail strong {
            color: #374151;
            font-size: 13px;
        }


        .detail span {
            color: #6b7280;
            font-size: 14px;
        }


        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }


        button,
        .btn {
            border: none;
            border-radius: 9px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s ease;
        }


        button:hover,
        .btn:hover {
            transform: translateY(-1px);
        }


        .btn-primary {
            background: #4f46e5;
            color: #ffffff;
        }


        .btn-primary:hover {
            background: #4338ca;
        }


        .btn-success {
            background: #16a34a;
            color: #ffffff;
        }


        .btn-success:hover {
            background: #15803d;
        }


        .btn-danger {
            background: #dc2626;
            color: #ffffff;
        }


        .btn-danger:hover {
            background: #b91c1c;
        }


        .btn-secondary {
            background: #eef2ff;
            color: #4338ca;
        }


        .btn-secondary:hover {
            background: #e0e7ff;
        }


        .interview-box {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-top: 18px;
        }


        .interview-box h4 {
            font-size: 17px;
            color: #111827;
            margin-bottom: 15px;
        }


        .interview-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }


        .interview-detail {
            font-size: 14px;
            color: #4b5563;
        }


        .interview-detail strong {
            color: #1f2937;
        }


        .meeting-link {
            color: #4f46e5;
            font-weight: 700;
            text-decoration: none;
        }


        .meeting-link:hover {
            text-decoration: underline;
        }


        .notice {
            margin-top: 15px;
            padding: 12px 15px;
            border-radius: 9px;
            background: #eef2ff;
            color: #3730a3;
            font-size: 14px;
        }


        .success-message {
            margin-top: 15px;
            padding: 12px 15px;
            border-radius: 9px;
            background: #dcfce7;
            color: #166534;
            font-weight: 600;
        }


        .danger-message {
            margin-top: 15px;
            padding: 12px 15px;
            border-radius: 9px;
            background: #fee2e2;
            color: #991b1b;
            font-weight: 600;
        }


        .empty-state {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 55px 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);
        }


        .empty-icon {
            font-size: 45px;
            margin-bottom: 12px;
        }


        .empty-state h3 {
            color: #111827;
            margin-bottom: 6px;
        }


        .empty-state p {
            color: #6b7280;
        }


        .logout {
            display: inline-block;
            margin-top: 25px;
            color: #dc2626;
            text-decoration: none;
            font-weight: 700;
        }


        .logout:hover {
            text-decoration: underline;
        }


        @media (max-width: 850px) {

            .job-info-grid {
                grid-template-columns: 1fr;
            }


            .details-grid {
                grid-template-columns: 1fr;
            }


            .interview-details {
                grid-template-columns: 1fr;
            }


            .applicant-header {
                flex-direction: column;
            }

        }


        @media (max-width: 600px) {

            .topbar {
                padding: 15px 4%;
            }


            .brand {
                font-size: 19px;
            }


            .company-name {
                display: none;
            }


            .container {
                width: 94%;
                margin-top: 25px;
            }


            .page-header h1 {
                font-size: 26px;
            }


            .job-card,
            .applicant-card {
                padding: 18px;
            }


            .section-title {
                align-items: flex-start;
                flex-direction: column;
            }


            .actions {
                flex-direction: column;
            }


            .actions button,
            .actions .btn {
                width: 100%;
            }


            .student-info {
                align-items: flex-start;
            }

        }

    </style>

</head>


<body>


<header class="topbar">

    <div class="brand">
        JIMS
    </div>

    <div class="company-name">
        <?php echo htmlspecialchars($company["company_name"]); ?>
    </div>

</header>



<main class="container">


    <div class="page-header">

        <a href="jobs.php" class="back-link">
            ← Back to My Jobs
        </a>

        <h1>
            Applicants
        </h1>

        <p>
            Review and manage applications for this job.
        </p>

    </div>



    <div class="job-card">

        <h2>
            <?php
            echo htmlspecialchars($job["title"]);
            ?>
        </h2>


        <div class="job-info-grid">

            <div class="job-info">

                <span>Type</span>

                <strong>
                    <?php
                    echo htmlspecialchars($job["job_type"]);
                    ?>
                </strong>

            </div>


            <div class="job-info">

                <span>Location</span>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $job["location"] ?? "Not specified"
                    );
                    ?>
                </strong>

            </div>


            <div class="job-info">

                <span>Deadline</span>

                <strong>
                    <?php
                    echo htmlspecialchars($job["deadline"]);
                    ?>
                </strong>

            </div>


            <div class="job-info">

                <span>Status</span>

                <?php if ($job["status"] == "approved"): ?>

                    <span class="status status-approved">
                        Approved ✅
                    </span>

                <?php elseif ($job["status"] == "pending"): ?>

                    <span class="status status-pending">
                        Pending ⏳
                    </span>

                <?php elseif ($job["status"] == "rejected"): ?>

                    <span class="status status-rejected">
                        Rejected ❌
                    </span>

                <?php else: ?>

                    <strong>
                        <?php
                        echo htmlspecialchars($job["status"]);
                        ?>
                    </strong>

                <?php endif; ?>

            </div>

        </div>

    </div>



    <div class="section-title">

        <h2>
            Applications
        </h2>

        <span class="application-count">
            <?php echo $applicants->num_rows; ?>
            Applicant(s)
        </span>

    </div>



    <?php if ($applicants->num_rows > 0): ?>


        <?php while ($applicant = $applicants->fetch_assoc()): ?>


            <div class="applicant-card">


                <div class="applicant-header">

                    <div class="student-info">

                        <div class="avatar">

                            <?php
                            echo strtoupper(
                                substr(
                                    $applicant["student_name"],
                                    0,
                                    1
                                )
                            );
                            ?>

                        </div>


                        <div>

                            <h3>
                                <?php
                                echo htmlspecialchars(
                                    $applicant["student_name"]
                                );
                                ?>
                            </h3>

                            <p>
                                Applicant ID:
                                #<?php
                                echo htmlspecialchars(
                                    $applicant["application_id"]
                                );
                                ?>
                            </p>

                        </div>

                    </div>


                    <?php if ($applicant["status"] == "pending"): ?>

                        <span class="status status-pending">
                            Pending ⏳
                        </span>

                    <?php elseif ($applicant["status"] == "shortlisted"): ?>

                        <span class="status status-shortlisted">
                            Shortlisted ⭐
                        </span>

                    <?php elseif ($applicant["status"] == "selected"): ?>

                        <span class="status status-selected">
                            Selected 🎉
                        </span>

                    <?php elseif ($applicant["status"] == "rejected"): ?>

                        <span class="status status-rejected">
                            Rejected ❌
                        </span>

                    <?php else: ?>

                        <span class="status">
                            <?php
                            echo htmlspecialchars(
                                $applicant["status"]
                            );
                            ?>
                        </span>

                    <?php endif; ?>

                </div>



                <div class="details-grid">

                    <div class="detail">

                        <strong>
                            Email
                        </strong>

                        <br>

                        <span>
                            <?php
                            echo htmlspecialchars(
                                $applicant["student_email"]
                            );
                            ?>
                        </span>

                    </div>


                    <div class="detail">

                        <strong>
                            Phone
                        </strong>

                        <br>

                        <span>
                            <?php
                            echo htmlspecialchars(
                                $applicant["student_phone"]
                                ?? "Not provided"
                            );
                            ?>
                        </span>

                    </div>

                </div>



                <?php if (!empty($applicant["cv_file"])): ?>

    <a
        class="btn btn-secondary"
        href="../uploads/cvs/<?php echo htmlspecialchars($applicant["cv_file"]); ?>"
        target="_blank"
    >
        📄 View CV
    </a>

<?php else: ?>

    <p class="danger-message">
        CV not available.
    </p>

<?php endif; ?>



                <?php if ($applicant["status"] == "pending"): ?>


                    <div class="actions">

                        <form
                            method="POST"
                            action="application_action.php"
                        >

                            <input
                                type="hidden"
                                name="application_id"
                                value="<?php
                                echo $applicant["application_id"];
                                ?>"
                            >

                            <input
                                type="hidden"
                                name="job_id"
                                value="<?php
                                echo $job_id;
                                ?>"
                            >

                            <button
                                type="submit"
                                name="action"
                                value="shortlist"
                                class="btn-success"
                            >
                                ⭐ Shortlist
                            </button>

                        </form>


                        <form
                            method="POST"
                            action="application_action.php"
                        >

                            <input
                                type="hidden"
                                name="application_id"
                                value="<?php
                                echo $applicant["application_id"];
                                ?>"
                            >

                            <input
                                type="hidden"
                                name="job_id"
                                value="<?php
                                echo $job_id;
                                ?>"
                            >

                            <button
                                type="submit"
                                name="action"
                                value="reject"
                                class="btn-danger"
                            >
                                ❌ Reject
                            </button>

                        </form>

                    </div>



                <?php elseif ($applicant["status"] == "shortlisted"): ?>


                    <?php if (empty($applicant["interview_id"])): ?>


                        <div class="notice">
                            ⭐ Applicant has been shortlisted.
                            You can now schedule an interview.
                        </div>


                        <div class="actions">

                            <a
                                class="btn btn-primary"
                                href="schedule_interview.php?application_id=<?php
                                echo $applicant["application_id"];
                                ?>&job_id=<?php
                                echo $job_id;
                                ?>"
                            >
                                📅 Schedule Interview
                            </a>

                        </div>


                    <?php elseif ($applicant["interview_status"] == "scheduled"): ?>


                        <div class="interview-box">

                            <h4>
                                📅 Interview Scheduled
                            </h4>


                            <div class="interview-details">

                                <div class="interview-detail">

                                    <strong>
                                        Date:
                                    </strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $applicant["interview_date"]
                                    );
                                    ?>

                                </div>


                                <div class="interview-detail">

                                    <strong>
                                        Time:
                                    </strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $applicant["interview_time"]
                                    );
                                    ?>

                                </div>


                                <div class="interview-detail">

                                    <strong>
                                        Type:
                                    </strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $applicant["interview_type"]
                                    );
                                    ?>

                                </div>


                                <?php if (!empty($applicant["meeting_link"])): ?>

                                    <div class="interview-detail">

                                        <strong>
                                            Meeting:
                                        </strong>

                                        <a
                                            class="meeting-link"
                                            href="<?php
                                            echo htmlspecialchars(
                                                $applicant["meeting_link"]
                                            );
                                            ?>"
                                            target="_blank"
                                        >
                                            Open Meeting ↗
                                        </a>

                                    </div>

                                <?php endif; ?>

                            </div>


                            <?php if (!empty($applicant["notes"])): ?>

                                <div class="notice">

                                    <strong>
                                        Instructions:
                                    </strong>

                                    <br>

                                    <?php
                                    echo nl2br(
                                        htmlspecialchars(
                                            $applicant["notes"]
                                        )
                                    );
                                    ?>

                                </div>

                            <?php endif; ?>


                            <div class="actions">

                                <form
                                    method="POST"
                                    action="application_action.php"
                                >

                                    <input
                                        type="hidden"
                                        name="application_id"
                                        value="<?php
                                        echo $applicant["application_id"];
                                        ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="job_id"
                                        value="<?php
                                        echo $job_id;
                                        ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="action"
                                        value="complete_interview"
                                        class="btn-success"
                                    >
                                        ✅ Mark Interview Completed
                                    </button>

                                </form>

                            </div>

                        </div>


                    <?php elseif ($applicant["interview_status"] == "completed"): ?>


                        <div class="interview-box">

                            <h4>
                                ✅ Interview Completed
                            </h4>

                            <div class="success-message">
                                Interview has been completed.
                            </div>


                            <?php if ($applicant["interview_result"] == "pending"): ?>


                                <div class="notice">

                                    <strong>
                                        Final Decision:
                                    </strong>

                                    Pending

                                </div>


                                <div class="actions">


                                    <form
                                        method="POST"
                                        action="application_action.php"
                                    >

                                        <input
                                            type="hidden"
                                            name="application_id"
                                            value="<?php
                                            echo $applicant["application_id"];
                                            ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="job_id"
                                            value="<?php
                                            echo $job_id;
                                            ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="action"
                                            value="interview_select"
                                            class="btn-success"
                                        >
                                            🎉 Select Candidate
                                        </button>

                                    </form>


                                    <form
                                        method="POST"
                                        action="application_action.php"
                                    >

                                        <input
                                            type="hidden"
                                            name="application_id"
                                            value="<?php
                                            echo $applicant["application_id"];
                                            ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="job_id"
                                            value="<?php
                                            echo $job_id;
                                            ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="action"
                                            value="interview_reject"
                                            class="btn-danger"
                                        >
                                            ❌ Reject Candidate
                                        </button>

                                    </form>


                                </div>


                            <?php endif; ?>

                        </div>


                    <?php endif; ?>



                <?php elseif ($applicant["status"] == "selected"): ?>


                    <div class="success-message">
                        🎉 Candidate Selected.
                    </div>


                    <?php if (!empty($applicant["interview_date"])): ?>

                        <div class="notice">
                            Interview completed successfully.
                        </div>

                    <?php endif; ?>



                <?php elseif ($applicant["status"] == "rejected"): ?>


                    <div class="danger-message">
                        ❌ Application Rejected.
                    </div>


                <?php endif; ?>


            </div>


        <?php endwhile; ?>


    <?php else: ?>


        <div class="empty-state">

            <div class="empty-icon">
                📭
            </div>

            <h3>
                No Applications Yet
            </h3>

            <p>
                No students have applied for this job or internship yet.
            </p>

        </div>


    <?php endif; ?>


    <a
        href="../auth/logout.php"
        class="logout"
    >
        Logout
    </a>


</main>


</body>

</html>