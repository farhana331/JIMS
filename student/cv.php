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

$uploadDir = "../uploads/cvs/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["cv"])) {

    $file = $_FILES["cv"];

    if ($file["error"] !== UPLOAD_ERR_OK) {

        $message = "Please select a valid CV.";
        $message_type = "error";

    } else {

        $fileName = $file["name"];
        $fileTmpName = $file["tmp_name"];
        $fileSize = $file["size"];

        $fileExtension = strtolower(
            pathinfo($fileName, PATHINFO_EXTENSION)
        );

        $allowedExtensions = ["pdf", "doc", "docx"];

        $maxSize = 5 * 1024 * 1024;

        if (!in_array($fileExtension, $allowedExtensions)) {

            $message = "Only PDF, DOC and DOCX files are allowed.";
            $message_type = "error";

        } elseif ($fileSize > $maxSize) {

            $message = "File size must be less than 5 MB.";
            $message_type = "error";

        } else {

            $stmt = $conn->prepare(
                "SELECT cv_file
                 FROM students
                 WHERE user_id = ?"
            );

            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $result = $stmt->get_result();
            $student = $result->fetch_assoc();

            $oldCv = $student["cv_file"] ?? "";

            $stmt->close();

            $newFileName =
                "cv_" . $user_id . "_" . time() . "." . $fileExtension;

            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpName, $destination)) {

                $stmt = $conn->prepare(
                    "UPDATE students
                     SET cv_file = ?
                     WHERE user_id = ?"
                );

                $stmt->bind_param(
                    "si",
                    $newFileName,
                    $user_id
                );

                if ($stmt->execute()) {

                    if (!empty($oldCv)) {

                        $oldFilePath = $uploadDir . $oldCv;

                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }

                    $message = "CV uploaded successfully.";
                    $message_type = "success";

                } else {

                    if (file_exists($destination)) {
                        unlink($destination);
                    }

                    $message = "Failed to save CV information.";
                    $message_type = "error";
                }

                $stmt->close();

            } else {

                $message = "Failed to upload CV.";
                $message_type = "error";
            }
        }
    }
}

$stmt = $conn->prepare(
    "SELECT cv_file
     FROM students
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$student = $result->fetch_assoc();

$currentCv = $student["cv_file"] ?? "";

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

    <title>My CV - JIMS</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {

            min-height: 100vh;

            font-family:
                "Segoe UI",
                Arial,
                sans-serif;

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
                rgba(255,255,255,0.62);

            border-bottom:
                1px solid
                rgba(255,255,255,0.75);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);

            box-shadow:
                0 5px 25px
                rgba(15,23,42,0.05);

        }

        .brand {

            text-decoration: none;

            font-size: 26px;

            font-weight: 800;

            color: #2563eb;

        }

        .brand span {

            color: #172033;

        }

        .student-info {

            display: flex;

            align-items: center;

            gap: 11px;

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
                rgba(37,99,235,0.20);

        }

        .student-name {

            font-size: 14px;

            font-weight: 600;

            color: #344054;

        }

        .container {

            width: 88%;

            max-width: 1000px;

            margin: 0 auto;

            padding: 42px 0 60px;

        }

        .top-bar {

            margin-bottom: 25px;

        }

        .back-link {

            text-decoration: none;

            color: #2563eb;

            font-size: 13px;

            font-weight: 700;

            transition: 0.2s ease;

        }

        .back-link:hover {

            color: #1d4ed8;

        }

        .page-header {

            margin-bottom: 28px;

        }

        .page-header h1 {

            font-size: 32px;

            color: #111827;

            margin-bottom: 8px;

            letter-spacing: -0.5px;

        }

        .page-header p {

            color: #667085;

            font-size: 14px;

        }

        .message {

            padding: 14px 18px;

            border-radius: 14px;

            margin-bottom: 22px;

            font-size: 13px;

            font-weight: 600;

            backdrop-filter:
                blur(15px);

        }

        .message.success {

            background:
                rgba(220,252,231,0.75);

            border:
                1px solid
                rgba(134,239,172,0.55);

            color: #166534;

        }

        .message.error {

            background:
                rgba(254,226,226,0.75);

            border:
                1px solid
                rgba(252,165,165,0.55);

            color: #991b1b;

        }

        .cv-card {

            padding: 30px;

            border-radius: 22px;

            background:
                rgba(255,255,255,0.62);

            border:
                1px solid
                rgba(255,255,255,0.80);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);

            box-shadow:
                0 14px 40px
                rgba(15,23,42,0.08);

        }

        .upload-area {

            text-align: center;

            padding: 38px 25px;

            border:
                2px dashed
                rgba(37,99,235,0.25);

            border-radius: 18px;

            background:
                rgba(239,246,255,0.42);

            transition: 0.25s ease;

        }

        .upload-area:hover {

            border-color:
                rgba(37,99,235,0.55);

            background:
                rgba(239,246,255,0.65);

        }

        .upload-icon {

            width: 68px;
            height: 68px;

            margin: 0 auto 17px;

            border-radius: 18px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 30px;

            background:
                rgba(219,234,254,0.80);

            border:
                1px solid
                rgba(147,197,253,0.40);

        }

        .upload-area h2 {

            font-size: 20px;

            color: #172033;

            margin-bottom: 7px;

        }

        .upload-area p {

            color: #667085;

            font-size: 13px;

            margin-bottom: 22px;

        }

        .file-input {

            width: 100%;

            max-width: 430px;

            padding: 11px;

            border-radius: 10px;

            border:
                1px solid
                rgba(148,163,184,0.35);

            background:
                rgba(255,255,255,0.75);

            font-size: 13px;

            color: #475467;

            cursor: pointer;

        }

        .file-input:focus {

            outline: none;

            border-color: #60a5fa;

        }

        .upload-btn {

            margin-top: 18px;

            padding: 12px 24px;

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
                rgba(37,99,235,0.20);

            transition: 0.25s ease;

        }

        .upload-btn:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 10px 24px
                rgba(37,99,235,0.28);

        }

        .current-section {

            margin-top: 25px;

            padding: 22px;

            border-radius: 17px;

            background:
                rgba(255,255,255,0.52);

            border:
                1px solid
                rgba(255,255,255,0.70);

        }

        .current-section h3 {

            font-size: 16px;

            margin-bottom: 15px;

            color: #172033;

        }

        .cv-file {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            padding: 15px;

            border-radius: 13px;

            background:
                rgba(239,246,255,0.65);

            border:
                1px solid
                rgba(147,197,253,0.30);

        }

        .file-info {

            display: flex;

            align-items: center;

            gap: 12px;

            min-width: 0;

        }

        .file-icon {

            width: 43px;
            height: 43px;

            border-radius: 11px;

            display: flex;

            align-items: center;

            justify-content: center;

            background:
                rgba(219,234,254,0.85);

            font-size: 21px;

            flex-shrink: 0;

        }

        .file-name {

            font-size: 13px;

            font-weight: 700;

            color: #172033;

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;

        }

        .file-label {

            font-size: 11px;

            color: #667085;

            margin-top: 3px;

        }

        .cv-actions {

            display: flex;

            align-items: center;

            gap: 8px;

            flex-shrink: 0;

        }

        .view-btn,
        .delete-btn {

            display: inline-block;

            padding: 9px 13px;

            border-radius: 9px;

            text-decoration: none;

            font-size: 12px;

            font-weight: 700;

            transition: 0.2s ease;

        }

        .view-btn {

            background:
                #dbeafe;

            color: #1d4ed8;

        }

        .view-btn:hover {

            background:
                #bfdbfe;

        }

        .delete-btn {

            background:
                #fee2e2;

            color: #b91c1c;

        }

        .delete-btn:hover {

            background:
                #fecaca;

        }

        .no-cv {

            text-align: center;

            padding: 20px;

            color: #667085;

            font-size: 13px;

            background:
                rgba(248,250,252,0.55);

            border-radius: 12px;

        }

        .info-box {

            margin-top: 22px;

            padding: 17px;

            border-radius: 14px;

            background:
                rgba(248,250,252,0.60);

            border:
                1px solid
                rgba(226,232,240,0.70);

            color: #667085;

            font-size: 12px;

            line-height: 1.6;

        }

        .info-box strong {

            color: #344054;

        }

        .logout-area {

            margin-top: 30px;

            padding-top: 24px;

            border-top:
                1px solid
                rgba(148,163,184,0.25);

            text-align: right;

        }

        .logout {

            display: inline-block;

            padding: 10px 18px;

            border-radius: 10px;

            text-decoration: none;

            background:
                rgba(254,226,226,0.75);

            color: #dc2626;

            border:
                1px solid
                rgba(252,165,165,0.35);

            font-size: 12px;

            font-weight: 700;

            transition: 0.2s ease;

        }

        .logout:hover {

            background:
                rgba(254,202,202,0.85);

            transform:
                translateY(-2px);

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

            .page-header h1 {

                font-size: 27px;

            }

            .cv-card {

                padding: 20px;

            }

            .upload-area {

                padding: 30px 15px;

            }

            .cv-file {

                align-items: flex-start;

                flex-direction: column;

            }

            .cv-actions {

                width: 100%;

            }

            .view-btn,
            .delete-btn {

                text-align: center;

                flex: 1;

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

    </div>

    <div class="page-header">

        <h1>
            My CV
        </h1>

        <p>
            Upload and manage your CV for job and internship applications.
        </p>

    </div>

    <?php if (!empty($message)): ?>

        <div
            class="message <?php echo $message_type; ?>"
        >

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>

    <div class="cv-card">

        <div class="upload-area">

            <div class="upload-icon">
                📄
            </div>

            <h2>
                Upload Your CV
            </h2>

            <p>
                Keep your CV updated to apply for jobs and internships.
            </p>

            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <input
                    type="file"
                    name="cv"
                    class="file-input"
                    accept=".pdf,.doc,.docx"
                    required
                >

                <br>

                <button
                    type="submit"
                    class="upload-btn"
                >
                    ⬆ Upload CV
                </button>

            </form>

        </div>

        <div class="current-section">

            <h3>
                Current CV
            </h3>

            <?php if (!empty($currentCv)): ?>

                <div class="cv-file">

                    <div class="file-info">

                        <div class="file-icon">
                            📄
                        </div>

                        <div>

                            <div class="file-name">

                                <?php

                                echo htmlspecialchars(
                                    $currentCv
                                );

                                ?>

                            </div>

                            <div class="file-label">
                                Your currently uploaded CV
                            </div>

                        </div>

                    </div>

                    <div class="cv-actions">

                        <a
                            href="../uploads/cvs/<?php echo htmlspecialchars($currentCv); ?>"
                            target="_blank"
                            class="view-btn"
                        >
                            View CV
                        </a>

                        <a
                            href="delete_cv.php"
                            class="delete-btn"
                            onclick="return confirm('Are you sure you want to delete your CV?');"
                        >
                            Delete
                        </a>

                    </div>

                </div>

            <?php else: ?>

                <div class="no-cv">

                    📭 You have not uploaded a CV yet.

                </div>

            <?php endif; ?>

        </div>

        <div class="info-box">

            <strong>CV Requirements:</strong>

            PDF, DOC or DOCX format only.
            Maximum file size is 5 MB.
            Uploading a new CV will replace your previous CV.

        </div>

    </div>

    <div class="logout-area">

        <a
            href="../auth/logout.php"
            class="logout"
        >
            ↪ Logout
        </a>

    </div>

</main>

</body>

</html>