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

    <title>Admin Dashboard - Campus-Camp®</title>

    <link rel="stylesheet" href="../css/styles.css?v=<?= filemtime(__DIR__ . '/../css/styles.css') ?>">

</head>

<body>

<header class="admin-header">

    <div class="container admin-header-inner">

        <strong><img class="brand-image" src="../images/ofcampus-logo.png" alt="Campus-Camp®"><span class="admin-context">Admin</span></strong>

        <div>

            <span>
                <?= htmlspecialchars($_SESSION["admin_username"]) ?>
            </span>

            &nbsp; | &nbsp;

            <a href="logout.php">
                Logout
            </a>

        </div>

    </div>

</header>


<main class="admin-main">

    <div class="container">

        <h1>Dashboard</h1>

        <p>Manage accommodation listings and landlords.</p>


        <div class="admin-dashboard-table" aria-label="Dashboard summary">

            <div class="admin-dashboard-row">

                <div class="admin-dashboard-label">
                    Properties
                </div>

                <div class="admin-dashboard-value">
                    <?= $property_count ?>
                </div>

                <div class="admin-dashboard-action">
                    <a href="properties.php" class="admin-dashboard-button">
                        Manage Properties
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
                    <a href="landlords.php" class="admin-dashboard-button">
                        Manage Landlords
                    </a>
                </div>

            </div>

        </div>

    </div>

</main>

</body>

</html>