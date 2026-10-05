<?php

session_start();

require_once "../config/database.php";

// ONLY COMPANY CAN ACCESS


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



// UPDATE INFORMATION


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim(
        $_POST["name"] ?? ""
    );

    $email = trim(
        $_POST["email"] ?? ""
    );

    $phone = trim(
        $_POST["phone"] ?? ""
    );

    $company_name = trim(
        $_POST["company_name"] ?? ""
    );

    $industry = trim(
        $_POST["industry"] ?? ""
    );

    $description = trim(
        $_POST["description"] ?? ""
    );

    $website = trim(
        $_POST["website"] ?? ""
    );

    $address = trim(
        $_POST["address"] ?? ""
    );


    
    // VALIDATION

    if (
        empty($name) ||
        empty($email) ||
        empty($company_name)
    ) {

        $message =
            "Name, email and company name are required.";

        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message =
            "Please enter a valid email address.";

        $message_type = "error";

    } else {

        
        // CHECK EMAIL

        $check = $conn->prepare(
            "SELECT user_id
             FROM users
             WHERE email = ?
             AND user_id != ?"
        );

        $check->bind_param(
            "si",
            $email,
            $user_id
        );

        $check->execute();

        $check_result = $check->get_result();


        if ($check_result->num_rows > 0) {

            $message =
                "This email is already used by another account.";

            $message_type = "error";

            $check->close();

        } else {

            $check->close();


            // START TRANSACTION

            $conn->begin_transaction();


            try {

                // UPDATE USER ACCOUNT

                $stmt = $conn->prepare(
                    "UPDATE users
                     SET name = ?,
                         email = ?,
                         phone = ?
                     WHERE user_id = ?"
                );

                $stmt->bind_param(
                    "sssi",
                    $name,
                    $email,
                    $phone,
                    $user_id
                );

                if (!$stmt->execute()) {

                    throw new Exception(
                        "Failed to update account information."
                    );
                }

                $stmt->close();


                // UPDATE COMPANY INFORMATION

                $stmt = $conn->prepare(
                    "UPDATE companies

                     SET company_name = ?,
                         industry = ?,
                         description = ?,
                         website = ?,
                         address = ?

                     WHERE user_id = ?"
                );

                $stmt->bind_param(
                    "sssssi",
                    $company_name,
                    $industry,
                    $description,
                    $website,
                    $address,
                    $user_id
                );

                if (!$stmt->execute()) {

                    throw new Exception(
                        "Failed to update company information."
                    );
                }

                $stmt->close();


                // COMMIT

                $conn->commit();


                // Update session information

                $_SESSION["name"] = $name;
                $_SESSION["email"] = $email;


                $message =
                    "Profile information updated successfully!";

                $message_type = "success";

            } catch (Exception $e) {

                $conn->rollback();

                $message =
                    "Something went wrong. Please try again.";

                $message_type = "error";
            }
        }
    }
}


// GET CURRENT PROFILE

$stmt = $conn->prepare(
    "SELECT

        u.name,
        u.email,
        u.phone,

        c.company_name,
        c.industry,
        c.description,
        c.website,
        c.address,
        c.approval_status

     FROM users u

     INNER JOIN companies c
        ON u.user_id = c.user_id

     WHERE u.user_id = ?"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$company = $result->fetch_assoc();

$stmt->close();


if (!$company) {

    die("Company profile not found.");

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
        Update Company Profile - JIMS
    </title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {

            font-family: Arial, sans-serif;

            min-height: 100vh;

            background:
                linear-gradient(
                    135deg,
                    #eef2ff,
                    #f8fafc,
                    #e0f2fe
                );

            color: #1e293b;

        }


        .container {

            width: 90%;

            max-width: 1000px;

            margin: 40px auto;

        }


        .top-bar {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            margin-bottom: 25px;

        }


        .back-btn {

            text-decoration: none;

            color: #475569;

            font-weight: 600;

        }


        .back-btn:hover {

            color: #2563eb;

        }


        .logout {

            text-decoration: none;

            color: #ef4444;

            font-weight: 600;

        }


        .card {

            background:
                rgba(255, 255, 255, 0.55);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);

            border:
                1px solid
                rgba(255,255,255,0.7);

            border-radius: 22px;

            padding: 35px;

            box-shadow:
                0 15px 40px
                rgba(15,23,42,0.10);

        }


        .header {

            margin-bottom: 30px;

        }


        .header h1 {

            font-size: 30px;

            color: #0f172a;

        }


        .header p {

            margin-top: 8px;

            color: #64748b;

        }


        /* Message */

        .message {

            padding: 14px 18px;

            border-radius: 10px;

            margin-bottom: 25px;

            font-weight: 600;

        }


        .success {

            background: #dcfce7;

            color: #15803d;

        }


        .error {

            background: #fee2e2;

            color: #dc2626;

        }


        /* Section */

        .section {

            background:
                rgba(255,255,255,0.45);

            border:
                1px solid
                rgba(255,255,255,0.7);

            border-radius: 16px;

            padding: 25px;

            margin-bottom: 22px;

        }


        .section h2 {

            font-size: 20px;

            margin-bottom: 20px;

            color: #0f172a;

        }


        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;

        }


        .form-group {

            display: flex;

            flex-direction: column;

        }


        .form-group.full {

            grid-column: 1 / -1;

        }


        label {

            font-size: 14px;

            font-weight: 600;

            margin-bottom: 8px;

            color: #475569;

        }


        input,
        textarea {

            width: 100%;

            padding: 13px 14px;

            border-radius: 10px;

            border:
                1px solid
                rgba(148,163,184,0.5);

            background:
                rgba(255,255,255,0.75);

            font-size: 15px;

            color: #1e293b;

            outline: none;

            transition: 0.2s;

        }


        input:focus,
        textarea:focus {

            border-color: #2563eb;

            box-shadow:
                0 0 0 3px
                rgba(37,99,235,0.10);

        }


        textarea {

            resize: vertical;

            min-height: 120px;

        }


        /* Buttons */

        .actions {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            margin-top: 25px;

            gap: 15px;

        }


        .cancel-btn {

            text-decoration: none;

            padding: 12px 20px;

            border-radius: 10px;

            background:
                rgba(255,255,255,0.7);

            color: #475569;

            font-weight: 600;

            border:
                1px solid
                rgba(148,163,184,0.4);

        }


        .save-btn {

            border: none;

            padding: 13px 24px;

            border-radius: 10px;

            background: #2563eb;

            color: white;

            font-size: 15px;

            font-weight: 700;

            cursor: pointer;

            transition: 0.2s;

        }


        .save-btn:hover {

            background: #1d4ed8;

            transform:
                translateY(-2px);

        }


        @media (max-width: 700px) {

            .container {

                width: 94%;

            }

            .card {

                padding: 22px;

            }

            .form-grid {

                grid-template-columns: 1fr;

            }

            .form-group.full {

                grid-column: auto;

            }

            .actions {

                flex-direction: column-reverse;

                align-items: stretch;

            }

            .cancel-btn,
            .save-btn {

                text-align: center;

                width: 100%;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <!-- TOP BAR -->

    <div class="top-bar">

        <a
            href="profile.php"
            class="back-btn"
        >
            ← Back to Profile
        </a>


        <a
            href="../auth/logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>


    <!-- MAIN CARD -->

    <div class="card">


        <!-- HEADER -->

        <div class="header">

            <h1>
                Update Information
            </h1>

            <p>
                Update your account and company information.
            </p>

        </div>


        <!-- MESSAGE -->

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


        <form method="POST">


           

            <div class="section">

                <h2>
                    👤 Account Information
                </h2>


                <div class="form-grid">


                    <div class="form-group">

                        <label>
                            Full Name *
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="<?php
                                echo htmlspecialchars(
                                    $company["name"]
                                );
                            ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Email *
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="<?php
                                echo htmlspecialchars(
                                    $company["email"]
                                );
                            ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Phone
                        </label>

                        <input
                            type="text"
                            name="phone"
                            value="<?php
                                echo htmlspecialchars(
                                    $company["phone"] ?? ""
                                );
                            ?>"
                            placeholder="01XXXXXXXXX"
                        >

                    </div>


                </div>

            </div>


            

            <div class="section">

                <h2>
                    🏢 Company Information
                </h2>


                <div class="form-grid">


                    <div class="form-group">

                        <label>
                            Company Name *
                        </label>

                        <input
                            type="text"
                            name="company_name"
                            value="<?php
                                echo htmlspecialchars(
                                    $company["company_name"]
                                );
                            ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Industry
                        </label>

                        <input
                            type="text"
                            name="industry"
                            value="<?php
                                echo htmlspecialchars(
                                    $company["industry"] ?? ""
                                );
                            ?>"
                            placeholder="Software / IT"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Website
                        </label>

                        <input
                            type="url"
                            name="website"
                            value="<?php
                                echo htmlspecialchars(
                                    $company["website"] ?? ""
                                );
                            ?>"
                            placeholder="https://example.com"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Address
                        </label>

                        <input
                            type="text"
                            name="address"
                            value="<?php
                                echo htmlspecialchars(
                                    $company["address"] ?? ""
                                );
                            ?>"
                            placeholder="Dhaka, Bangladesh"
                        >

                    </div>


                    <div class="form-group full">

                        <label>
                            Company Description
                        </label>

                        <textarea
                            name="description"
                            placeholder="Tell us about your company..."
                        ><?php
                            echo htmlspecialchars(
                                $company["description"] ?? ""
                            );
                        ?></textarea>

                    </div>


                </div>

            </div>


            <!-- ACTIONS -->

            <div class="actions">

                <a
                    href="profile.php"
                    class="cancel-btn"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="save-btn"
                >
                    💾 Save Changes
                </button>

            </div>


        </form>

    </div>

</div>


</body>

</html>