<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$property_count = 0;
$landlord_count = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM properties");

if ($result) {
    $row = $result->fetch_assoc();
    $property_count = $row["total"];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM landlords");

if ($result) {
    $row = $result->fetch_assoc();
    $landlord_count = $row["total"];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="../css/styles.css?v=<?= filemtime(__DIR__ . '/../css/styles.css') ?>">

</head>

<body>

<header class="admin-header dashboard-header">

    <div class="container admin-header-inner">

        <strong><img class="brand-image" src="../images/ofcampus-logo.png" alt="Campus-Camp®"><span class="admin-context">Admin</span></strong>

        <div>



            <a href="logout.php">Log out</a>

        </div>

    </div>

</header>


<main class="admin-main">

    <div class="container">

        <h1>Dashboard</h1>

        <p>These are accommodation listings and landlords currently listed</p>


        <div class="admin-dashboard-table" aria-label="Dashboard summary">

            <div class="admin-dashboard-row">

                <div class="admin-dashboard-label">
                    Properties
                </div>

                <div class="admin-dashboard-value">
                    <?= $property_count ?>
                </div>

                <div class="admin-dashboard-action">
                    <a href="properties.php" class="admin-dashboard-button" aria-label="Manage properties" title="Manage properties">
                        <span class="button-label">Manage</span>
                    </a>
                </div>

            </div>

            <div class="admin-dashboard-row">

                <div class="admin-dashboard-label">
                    Landlords
                </div>

                <div class="admin-dashboard-value">
                    <?= $landlord_count ?>
                </div>

                <div class="admin-dashboard-action">
                    <a href="landlords.php" class="admin-dashboard-button" aria-label="Manage landlords" title="Manage landlords">
                        <span class="button-label">Manage</span>
                    </a>
                </div>

            </div>

        </div>

    </div>

</main>

<?php include __DIR__ . "/footer.php"; ?>
<script src="../js/main.js?v=<?= filemtime(__DIR__ . '/../js/main.js') ?>"></script>
</body>

</html>