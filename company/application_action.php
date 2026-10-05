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


$user_id =
    $_SESSION["user_id"];


$stmt = $conn->prepare(
    "SELECT company_id
     FROM companies
     WHERE user_id = ?"
);


$stmt->bind_param(
    "i",
    $user_id
);


$stmt->execute();


$result =
    $stmt->get_result();


$company =
    $result->fetch_assoc();


$stmt->close();


if (!$company) {

    die(
        "Company profile not found."
    );

}


$company_id =
    $company["company_id"];


$application_id =
    $_POST["application_id"] ?? 0;


$job_id =
    $_POST["job_id"] ?? 0;


$action =
    $_POST["action"] ?? "";


if (
    !is_numeric($application_id) ||
    $application_id <= 0
) {

    die(
        "Invalid application ID."
    );

}


if (
    !is_numeric($job_id) ||
    $job_id <= 0
) {

    die(
        "Invalid job ID."
    );

}


$application_id =
    (int)$application_id;


$job_id =
    (int)$job_id;


$stmt = $conn->prepare(
    "SELECT

        a.application_id,
        a.status AS application_status,
        j.company_id,
        j.status AS job_status

     FROM applications a

     INNER JOIN jobs j
        ON a.job_id = j.job_id

     WHERE a.application_id = ?
     AND a.job_id = ?
     AND j.company_id = ?"
);


$stmt->bind_param(
    "iii",
    $application_id,
    $job_id,
    $company_id
);


$stmt->execute();


$result =
    $stmt->get_result();


$application =
    $result->fetch_assoc();


$stmt->close();


if (!$application) {

    die(
        "Unauthorized application access."
    );

}


if ($action == "shortlist") {


    if (
        $application["application_status"]
        != "pending"
    ) {

        die(
            "Only pending applications can be shortlisted."
        );

    }


    if (
        $application["job_status"]
        == "closed"
    ) {

        die(
            "This vacancy is closed."
        );

    }


    $new_status =
        "shortlisted";


    $stmt = $conn->prepare(
        "UPDATE applications

         SET status = ?

         WHERE application_id = ?"
    );


    $stmt->bind_param(
        "si",
        $new_status,
        $application_id
    );


    $stmt->execute();


    $stmt->close();


} elseif ($action == "reject") {


    $new_status =
        "rejected";


    $stmt = $conn->prepare(
        "UPDATE applications

         SET status = ?

         WHERE application_id = ?"
    );


    $stmt->bind_param(
        "si",
        $new_status,
        $application_id
    );


    $stmt->execute();


    $stmt->close();


} elseif ($action == "select") {

    if (
        $application["job_status"]
        == "closed"
    ) {

        die(
            "This vacancy is already closed."
        );

    }


    $stmt = $conn->prepare(
        "SELECT

            j.vacancy_count,

            (
                SELECT COUNT(*)

                FROM applications a2

                WHERE a2.job_id = j.job_id

                AND a2.status = 'selected'

            ) AS selected_count

         FROM jobs j

         WHERE j.job_id = ?
         AND j.company_id = ?"
    );


    $stmt->bind_param(
        "ii",
        $job_id,
        $company_id
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    $vacancy =
        $result->fetch_assoc();


    $stmt->close();


    if (!$vacancy) {

        die(
            "Job not found."
        );

    }


    $vacancy_count =
        (int)$vacancy["vacancy_count"];


    $selected_count =
        (int)$vacancy["selected_count"];


    if (
        $selected_count
        >=
        $vacancy_count
    ) {

        die(
            "All available positions are already filled."
        );

    }


    $new_status =
        "selected";


    $stmt = $conn->prepare(
        "UPDATE applications

         SET status = ?

         WHERE application_id = ?"
    );


    $stmt->bind_param(
        "si",
        $new_status,
        $application_id
    );


    $stmt->execute();


    $stmt->close();


    $selected_count++;


    if (
        $selected_count
        >=
        $vacancy_count
    ) {


        $stmt = $conn->prepare(
            "UPDATE jobs

             SET status = 'closed'

             WHERE job_id = ?
             AND company_id = ?"
        );


        $stmt->bind_param(
            "ii",
            $job_id,
            $company_id
        );


        $stmt->execute();


        $stmt->close();

    }


} elseif (
    $action == "complete_interview"
) {


    $stmt = $conn->prepare(
        "SELECT interview_id

         FROM interviews

         WHERE application_id = ?

         AND status = 'scheduled'

         ORDER BY interview_id DESC

         LIMIT 1"
    );


    $stmt->bind_param(
        "i",
        $application_id
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    $interview =
        $result->fetch_assoc();


    $stmt->close();


    if (!$interview) {

        die(
            "No scheduled interview found."
        );

    }


    $interview_status =
        "completed";


    $stmt = $conn->prepare(
        "UPDATE interviews

         SET status = ?,
             result = 'pending'

         WHERE interview_id = ?"
    );


    $stmt->bind_param(
        "si",
        $interview_status,
        $interview["interview_id"]
    );


    $stmt->execute();


    $stmt->close();



} elseif (
    $action == "interview_select"
) {


    $stmt = $conn->prepare(
        "SELECT interview_id

         FROM interviews

         WHERE application_id = ?

         AND status = 'completed'

         ORDER BY interview_id DESC

         LIMIT 1"
    );


    $stmt->bind_param(
        "i",
        $application_id
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    $interview =
        $result->fetch_assoc();


    $stmt->close();


    if (!$interview) {

        die(
            "Interview must be completed before final selection."
        );

    }


    $stmt = $conn->prepare(
        "SELECT

            j.vacancy_count,

            (
                SELECT COUNT(*)

                FROM applications a2

                WHERE a2.job_id = j.job_id

                AND a2.status = 'selected'

            ) AS selected_count

         FROM jobs j

         WHERE j.job_id = ?
         AND j.company_id = ?"
    );


    $stmt->bind_param(
        "ii",
        $job_id,
        $company_id
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    $vacancy =
        $result->fetch_assoc();


    $stmt->close();


    if (!$vacancy) {

        die(
            "Job not found."
        );

    }


    $vacancy_count =
        (int)$vacancy["vacancy_count"];


    $selected_count =
        (int)$vacancy["selected_count"];


    if (
        $selected_count
        >=
        $vacancy_count
    ) {

        die(
            "All available positions are already filled."
        );

    }


    $result_status =
        "selected";


    $stmt = $conn->prepare(
        "UPDATE interviews

         SET result = ?

         WHERE interview_id = ?"
    );


    $stmt->bind_param(
        "si",
        $result_status,
        $interview["interview_id"]
    );


    $stmt->execute();


    $stmt->close();


    $application_status =
        "selected";


    $stmt = $conn->prepare(
        "UPDATE applications

         SET status = ?

         WHERE application_id = ?"
    );


    $stmt->bind_param(
        "si",
        $application_status,
        $application_id
    );


    $stmt->execute();


    $stmt->close();


    $selected_count++;


    if (
        $selected_count
        >=
        $vacancy_count
    ) {


        $stmt = $conn->prepare(
            "UPDATE jobs

             SET status = 'closed'

             WHERE job_id = ?
             AND company_id = ?"
        );


        $stmt->bind_param(
            "ii",
            $job_id,
            $company_id
        );


        $stmt->execute();


        $stmt->close();

    }



} elseif (
    $action == "interview_reject"
) {


    $stmt = $conn->prepare(
        "SELECT interview_id

         FROM interviews

         WHERE application_id = ?

         AND status = 'completed'

         ORDER BY interview_id DESC

         LIMIT 1"
    );


    $stmt->bind_param(
        "i",
        $application_id
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    $interview =
        $result->fetch_assoc();


    $stmt->close();


    if (!$interview) {

        die(
            "Interview must be completed before rejection."
        );

    }


    $result_status =
        "rejected";


    $stmt = $conn->prepare(
        "UPDATE interviews

         SET result = ?

         WHERE interview_id = ?"
    );


    $stmt->bind_param(
        "si",
        $result_status,
        $interview["interview_id"]
    );


    $stmt->execute();


    $stmt->close();


    $application_status =
        "rejected";


    $stmt = $conn->prepare(
        "UPDATE applications

         SET status = ?

         WHERE application_id = ?"
    );


    $stmt->bind_param(
        "si",
        $application_status,
        $application_id
    );


    $stmt->execute();


    $stmt->close();



} else {

    die(
        "Invalid action."
    );

}


header(
    "Location: applicants.php?job_id="
    . $job_id
);

exit();

?>