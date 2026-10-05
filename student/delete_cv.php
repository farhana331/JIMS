<?php

session_start();

require_once "../config/database.php";


if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] != "student"
) {
    header("Location: ../auth/login.php");
    exit();
}


$user_id = $_SESSION["user_id"];

// GET CV FILE


$stmt = $conn->prepare(
    "SELECT cv_file
     FROM students
     WHERE user_id = ?"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result =
    $stmt->get_result();

$student =
    $result->fetch_assoc();

$stmt->close();


if (!$student) {
    die("Student profile not found.");
}


$cv_file =
    $student["cv_file"];



if (!empty($cv_file)) {

    $file_path =
        "../uploads/cv/" . $cv_file;


    if (file_exists($file_path)) {

        unlink($file_path);

    }



    $stmt = $conn->prepare(
        "UPDATE students
         SET cv_file = NULL
         WHERE user_id = ?"
    );

    $stmt->bind_param(
        "i",
        $user_id
    );

    $stmt->execute();

    $stmt->close();

}


header("Location: profile.php");

exit();

?>