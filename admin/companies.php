<?php

session_start();

require_once "../config/database.php";


if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}



$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $company_id = isset($_POST["company_id"])
        ? (int)$_POST["company_id"]
        : 0;

    $action = $_POST["action"] ?? "";


    if ($company_id > 0) {


        if ($action === "approve") {

            $conn->begin_transaction();

            try {

                $stmt = $conn->prepare("
                    SELECT user_id
                    FROM companies
                    WHERE company_id = ?
                ");

                $stmt->bind_param("i", $company_id);
                $stmt->execute();

                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    throw new Exception("Company not found.");
                }

                $company = $result->fetch_assoc();
                $user_id = (int)$company["user_id"];

                $stmt->close();


                $stmt = $conn->prepare("
                    UPDATE companies
                    SET
                        approval_status = 'approved',
                        approval_requested = 0
                    WHERE company_id = ?
                ");

                $stmt->bind_param("i", $company_id);
                $stmt->execute();
                $stmt->close();


                $stmt = $conn->prepare("
                    UPDATE users
                    SET account_status = 'active'
                    WHERE user_id = ?
                    AND role = 'company'
                ");

                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->close();


                $conn->commit();

                $message = "Company approved successfully.";
                $message_type = "success";

            } catch (Exception $e) {

                $conn->rollback();

                $message = "Failed to approve company.";
                $message_type = "error";
            }
        }



        elseif ($action === "reject") {

            $conn->begin_transaction();

            try {

                $stmt = $conn->prepare("
                    SELECT user_id
                    FROM companies
                    WHERE company_id = ?
                ");

                $stmt->bind_param("i", $company_id);
                $stmt->execute();

                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    throw new Exception("Company not found.");
                }

                $company = $result->fetch_assoc();
                $user_id = (int)$company["user_id"];

                $stmt->close();


                $stmt = $conn->prepare("
                    UPDATE companies
                    SET
                        approval_status = 'rejected',
                        approval_requested = 0
                    WHERE company_id = ?
                ");

                $stmt->bind_param("i", $company_id);
                $stmt->execute();
                $stmt->close();


                $stmt = $conn->prepare("
                    UPDATE users
                    SET account_status = 'rejected'
                    WHERE user_id = ?
                    AND role = 'company'
                ");

                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->close();


                $conn->commit();

                $message = "Company rejected successfully.";
                $message_type = "success";

            } catch (Exception $e) {

                $conn->rollback();

                $message = "Failed to reject company.";
                $message_type = "error";
            }
        }



        elseif ($action === "delete") {

            $conn->begin_transaction();

            try {

                $stmt = $conn->prepare("
                    SELECT user_id
                    FROM companies
                    WHERE company_id = ?
                ");

                $stmt->bind_param("i", $company_id);
                $stmt->execute();

                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    throw new Exception("Company not found.");
                }

                $company = $result->fetch_assoc();
                $user_id = (int)$company["user_id"];

                $stmt->close();


                $stmt = $conn->prepare("
                    DELETE FROM companies
                    WHERE company_id = ?
                ");

                $stmt->bind_param("i", $company_id);
                $stmt->execute();
                $stmt->close();


                $stmt = $conn->prepare("
                    DELETE FROM users
                    WHERE user_id = ?
                    AND role = 'company'
                ");

                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->close();


                $conn->commit();

                $message = "Company deleted successfully.";
                $message_type = "success";

            } catch (Exception $e) {

                $conn->rollback();

                $message = "Failed to delete company.";
                $message_type = "error";
            }
        }
    }
}



$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM companies
");

$stmt->execute();
$result = $stmt->get_result();
$total_companies = (int)$result->fetch_assoc()["total"];
$stmt->close();


$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM companies
    WHERE approval_status = 'pending'
    AND approval_requested = 1
");

$stmt->execute();
$result = $stmt->get_result();
$pending_companies = (int)$result->fetch_assoc()["total"];
$stmt->close();


$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM companies
    WHERE approval_status = 'approved'
");

$stmt->execute();
$result = $stmt->get_result();
$approved_companies = (int)$result->fetch_assoc()["total"];
$stmt->close();


$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM companies
    WHERE approval_status = 'rejected'
");

$stmt->execute();
$result = $stmt->get_result();
$rejected_companies = (int)$result->fetch_assoc()["total"];
$stmt->close();



$filter = $_GET["status"] ?? "all";

$allowed_filters = [
    "all",
    "pending",
    "approved",
    "rejected"
];

if (!in_array($filter, $allowed_filters, true)) {
    $filter = "all";
}



$search = trim($_GET["search"] ?? "");



$sql = "
    SELECT
        c.company_id,
        c.company_name,
        c.user_id,
        c.approval_status,
        c.approval_requested,
        u.name,
        u.email,
        u.phone,
        u.account_status
    FROM companies c
    INNER JOIN users u
        ON c.user_id = u.user_id
    WHERE 1=1
";

$params = [];
$types = "";


if ($filter !== "all") {

    $sql .= "
        AND c.approval_status = ?
    ";

    $params[] = $filter;
    $types .= "s";
}


if ($search !== "") {

    $sql .= "
        AND (
            c.company_name LIKE ?
            OR u.name LIKE ?
            OR u.email LIKE ?
            OR u.phone LIKE ?
        )
    ";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "ssss";
}


$sql .= "
    ORDER BY c.company_id DESC
";


$stmt = $conn->prepare($sql);


if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}


$stmt->execute();

$companies = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Manage Companies - JIMS</title>


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

    color: #172033;

    background:
        radial-gradient(
            circle at 10% 10%,
            rgba(37,99,235,0.12),
            transparent 28%
        ),
        radial-gradient(
            circle at 90% 90%,
            rgba(99,102,241,0.10),
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
        rgba(255,255,255,0.65);

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

    font-size: 26px;

    font-weight: 800;

    color: #2563eb;

    text-decoration: none;

}


.brand span {
    color: #172033;
}


.admin-info {

    display: flex;

    align-items: center;

    gap: 12px;

}


.admin-avatar {

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

}


.admin-name {

    font-size: 14px;

    font-weight: 600;

    color: #344054;

}




.container {

    width: 88%;

    max-width: 1250px;

    margin: 0 auto;

    padding:
        42px 0 60px;

}




.page-header {

    display: flex;

    align-items: flex-end;

    justify-content: space-between;

    gap: 20px;

    margin-bottom: 30px;

}


.page-header h1 {

    font-size: 32px;

    color: #111827;

    margin-bottom: 7px;

}


.page-header p {

    color: #667085;

    font-size: 14px;

}


.back-button {

    text-decoration: none;

    color: #2563eb;

    background:
        rgba(255,255,255,0.65);

    border:
        1px solid
        rgba(255,255,255,0.85);

    padding:
        10px 16px;

    border-radius: 10px;

    font-size: 13px;

    font-weight: 700;

    box-shadow:
        0 5px 15px
        rgba(15,23,42,0.05);

}




.message {

    padding: 14px 17px;

    border-radius: 13px;

    margin-bottom: 22px;

    font-size: 13px;

    font-weight: 600;

}


.message.success {

    background:
        rgba(220,252,231,0.80);

    color: #166534;

    border:
        1px solid
        rgba(34,197,94,0.25);

}


.message.error {

    background:
        rgba(254,226,226,0.80);

    color: #b91c1c;

    border:
        1px solid
        rgba(239,68,68,0.25);

}




.stats {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 18px;

    margin-bottom: 28px;

}


.stat {

    padding: 21px;

    border-radius: 18px;

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
        0 10px 30px
        rgba(15,23,42,0.06);

}


.stat-label {

    color: #667085;

    font-size: 12px;

    font-weight: 700;

    margin-bottom: 8px;

}


.stat-number {

    font-size: 29px;

    font-weight: 800;

    color: #111827;

}


.stat.pending .stat-number {
    color: #ea580c;
}


.stat.approved .stat-number {
    color: #16a34a;
}


.stat.rejected .stat-number {
    color: #dc2626;
}




.toolbar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

    margin-bottom: 22px;

}


.filters {

    display: flex;

    gap: 8px;

    flex-wrap: wrap;

}


.filter {

    text-decoration: none;

    padding:
        9px 15px;

    border-radius: 10px;

    font-size: 12px;

    font-weight: 700;

    color: #64748b;

    background:
        rgba(255,255,255,0.60);

    border:
        1px solid
        rgba(255,255,255,0.80);

}


.filter:hover {

    color: #2563eb;

}


.filter.active {

    color: white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

}


.search-form {

    display: flex;

    gap: 8px;

}


.search-input {

    width: 250px;

    height: 40px;

    border-radius: 10px;

    border:
        1px solid
        rgba(148,163,184,0.40);

    background:
        rgba(255,255,255,0.65);

    padding:
        0 13px;

    outline: none;

    font-size: 13px;

}


.search-input:focus {

    border-color: #2563eb;

    box-shadow:
        0 0 0 3px
        rgba(37,99,235,0.08);

}


.search-button {

    height: 40px;

    border: none;

    border-radius: 10px;

    padding:
        0 16px;

    color: white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    font-size: 12px;

    font-weight: 700;

    cursor: pointer;

}




.table-card {

    overflow: hidden;

    border-radius: 20px;

    background:
        rgba(255,255,255,0.65);

    border:
        1px solid
        rgba(255,255,255,0.85);

    backdrop-filter:
        blur(18px);

    -webkit-backdrop-filter:
        blur(18px);

    box-shadow:
        0 15px 40px
        rgba(15,23,42,0.07);

}


.table-wrapper {

    width: 100%;

    overflow-x: auto;

}


table {

    width: 100%;

    border-collapse: collapse;

    min-width: 950px;

}


thead {

    background:
        rgba(248,250,252,0.70);

}


th {

    text-align: left;

    padding:
        16px 18px;

    font-size: 11px;

    color: #64748b;

    text-transform: uppercase;

    letter-spacing: 0.5px;

    border-bottom:
        1px solid
        rgba(226,232,240,0.75);

}


td {

    padding:
        17px 18px;

    font-size: 13px;

    color: #344054;

    border-bottom:
        1px solid
        rgba(226,232,240,0.55);

}


tbody tr:hover {

    background:
        rgba(239,246,255,0.35);

}


.company-name {

    font-weight: 700;

    color: #172033;

}


.email {

    color: #667085;

    font-size: 12px;

}


.status {

    display: inline-flex;

    align-items: center;

    padding:
        6px 10px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 700;

}


.status.pending {

    background: #fff7ed;

    color: #c2410c;

}


.status.approved {

    background: #f0fdf4;

    color: #15803d;

}


.status.rejected {

    background: #fef2f2;

    color: #b91c1c;

}




.actions {

    display: flex;

    gap: 7px;

    align-items: center;

}


.action-form {

    display: inline;

}


.action-button {

    border: none;

    border-radius: 8px;

    padding:
        7px 11px;

    font-size: 11px;

    font-weight: 700;

    cursor: pointer;

}


.approve {

    background: #dcfce7;

    color: #15803d;

}


.approve:hover {

    background: #bbf7d0;

}


.reject {

    background: #fee2e2;

    color: #b91c1c;

}


.reject:hover {

    background: #fecaca;

}


.delete {

    background: #f1f5f9;

    color: #475569;

}


.delete:hover {

    background: #e2e8f0;

}




.empty {

    text-align: center;

    padding: 60px 20px;

    color: #64748b;

}


.empty-icon {

    font-size: 40px;

    margin-bottom: 12px;

}


.empty h3 {

    color: #344054;

    margin-bottom: 5px;

}


.empty p {

    font-size: 13px;

}




.footer {

    margin-top: 28px;

    padding-top: 22px;

    border-top:
        1px solid
        rgba(148,163,184,0.25);

}


.logout {

    color: #dc2626;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;

}




@media (max-width: 950px) {

    .stats {

        grid-template-columns:
            repeat(2, 1fr);

    }


    .toolbar {

        flex-direction: column;

        align-items: stretch;

    }


    .search-form {

        width: 100%;

    }


    .search-input {

        flex: 1;

        width: auto;

    }

}


@media (max-width: 650px) {

    .navbar {

        padding: 0 5%;

    }


    .admin-name {

        display: none;

    }


    .container {

        width: 90%;

        padding-top: 30px;

    }


    .page-header {

        align-items: flex-start;

        flex-direction: column;

    }


    .page-header h1 {

        font-size: 27px;

    }


    .stats {

        grid-template-columns: 1fr;

    }


    .filters {

        width: 100%;

    }


    .filter {

        flex: 1;

        text-align: center;

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


    <div class="admin-info">

        <div class="admin-avatar">

            <?php

            echo strtoupper(
                substr(
                    $_SESSION["name"] ?? "A",
                    0,
                    1
                )
            );

            ?>

        </div>


        <span class="admin-name">

            <?php

            echo htmlspecialchars(
                $_SESSION["name"] ?? "Admin"
            );

            ?>

        </span>

    </div>

</nav>



<main class="container">




<section class="page-header">

    <div>

        <h1>
            Manage Companies
        </h1>

        <p>
            View and manage all registered companies.
        </p>

    </div>


    <a
        href="dashboard.php"
        class="back-button"
    >
        ← Dashboard
    </a>

</section>





<?php if (!empty($message)): ?>

    <div class="message <?php echo $message_type; ?>">

        <?php echo htmlspecialchars($message); ?>

    </div>

<?php endif; ?>





<div class="stats">


    <div class="stat">

        <div class="stat-label">
            TOTAL COMPANIES
        </div>

        <div class="stat-number">
            <?php echo $total_companies; ?>
        </div>

    </div>



    <div class="stat pending">

        <div class="stat-label">
            PENDING
        </div>

        <div class="stat-number">
            <?php echo $pending_companies; ?>
        </div>

    </div>



    <div class="stat approved">

        <div class="stat-label">
            APPROVED
        </div>

        <div class="stat-number">
            <?php echo $approved_companies; ?>
        </div>

    </div>



    <div class="stat rejected">

        <div class="stat-label">
            REJECTED
        </div>

        <div class="stat-number">
            <?php echo $rejected_companies; ?>
        </div>

    </div>

</div>





<div class="toolbar">


    <div class="filters">


        <a
            href="companies.php"
            class="filter <?php echo $filter === "all" ? "active" : ""; ?>"
        >
            All
        </a>


        <a
            href="companies.php?status=pending"
            class="filter <?php echo $filter === "pending" ? "active" : ""; ?>"
        >
            Pending
        </a>


        <a
            href="companies.php?status=approved"
            class="filter <?php echo $filter === "approved" ? "active" : ""; ?>"
        >
            Approved
        </a>


        <a
            href="companies.php?status=rejected"
            class="filter <?php echo $filter === "rejected" ? "active" : ""; ?>"
        >
            Rejected
        </a>


    </div>



    <form
        method="GET"
        class="search-form"
    >

        <?php if ($filter !== "all"): ?>

            <input
                type="hidden"
                name="status"
                value="<?php echo htmlspecialchars($filter); ?>"
            >

        <?php endif; ?>


        <input
            type="text"
            name="search"
            class="search-input"
            placeholder="Search company, email..."
            value="<?php echo htmlspecialchars($search); ?>"
        >


        <button
            type="submit"
            class="search-button"
        >
            Search
        </button>

    </form>

</div>





<div class="table-card">

<div class="table-wrapper">


<?php if ($companies->num_rows > 0): ?>


<table>

<thead>

<tr>

    <th>
        Company
    </th>

    <th>
        Contact Person
    </th>

    <th>
        Email
    </th>

    <th>
        Phone
    </th>

    <th>
        Status
    </th>

    <th>
        Actions
    </th>

</tr>

</thead>


<tbody>


<?php while ($company = $companies->fetch_assoc()): ?>


<tr>


    

    <td>

        <div class="company-name">

            <?php

            echo htmlspecialchars(
                $company["company_name"]
            );

            ?>

        </div>

    </td>



    

    <td>

        <?php

        echo htmlspecialchars(
            $company["name"] ?? "-"
        );

        ?>

    </td>



    

    <td>

        <div class="email">

            <?php

            echo htmlspecialchars(
                $company["email"] ?? "-"
            );

            ?>

        </div>

    </td>



    

    <td>

        <?php

        echo htmlspecialchars(
            $company["phone"] ?? "-"
        );

        ?>

    </td>



    

    <td>

        <span
            class="status
            <?php echo htmlspecialchars($company["approval_status"]); ?>"
        >

            <?php

            echo ucfirst(
                htmlspecialchars(
                    $company["approval_status"]
                )
            );

            ?>

        </span>

    </td>



    

    <td>

        <div class="actions">


            <?php if ($company["approval_status"] === "pending"): ?>


                

                <form
                    method="POST"
                    class="action-form"
                    onsubmit="return confirm('Are you sure you want to approve this company?');"
                >

                    <input
                        type="hidden"
                        name="company_id"
                        value="<?php echo (int)$company["company_id"]; ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="approve"
                    >

                    <button
                        type="submit"
                        class="action-button approve"
                    >
                        Approve
                    </button>

                </form>



                

                <form
                    method="POST"
                    class="action-form"
                    onsubmit="return confirm('Are you sure you want to reject this company?');"
                >

                    <input
                        type="hidden"
                        name="company_id"
                        value="<?php echo (int)$company["company_id"]; ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="reject"
                    >

                    <button
                        type="submit"
                        class="action-button reject"
                    >
                        Reject
                    </button>

                </form>


            <?php endif; ?>


            

            <form
                method="POST"
                class="action-form"
                onsubmit="return confirm('Delete this company permanently?');"
            >

                <input
                    type="hidden"
                    name="company_id"
                    value="<?php echo (int)$company["company_id"]; ?>"
                >

                <input
                    type="hidden"
                    name="action"
                    value="delete"
                >

                <button
                    type="submit"
                    class="action-button delete"
                >
                    Delete
                </button>

            </form>


        </div>

    </td>


</tr>


<?php endwhile; ?>


</tbody>

</table>


<?php else: ?>


<div class="empty">

    <div class="empty-icon">
        🏢
    </div>

    <h3>
        No Companies Found
    </h3>

    <p>
        There are no companies matching your current filter.
    </p>

</div>


<?php endif; ?>


</div>

</div>





<div class="footer">

    <a
        href="../auth/logout.php"
        class="logout"
    >
        Logout
    </a>

</div>


</main>


</body>

</html>

<?php

$stmt->close();

?>