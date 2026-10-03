<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$sql = "
    SELECT
        l.id,
        l.name,
        l.phone,
        l.location,
        COUNT(p.id) AS property_count

    FROM landlords l

    LEFT JOIN properties p
        ON p.landlord_id = l.id

    GROUP BY l.id, l.name, l.phone, l.location

    ORDER BY l.name ASC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Landlords</title>

    <link
        rel="stylesheet"
        href="../css/styles.css?v=<?= filemtime(__DIR__ . '/../css/styles.css') ?>"
    >

</head>

<body>

<header class="admin-header">

    <div class="container admin-header-inner">

        <strong>
            <img class="brand-image" src="../images/ofcampus-logo.png" alt="Campus-Camp®"><span class="admin-context">Admin</span>
        </strong>

        <div>

            <a href="logout.php">Log out</a>

        </div>

    </div>

</header>

<main class="admin-main">

    <div class="admin-content-actions">
        <div class="container">
            <a href="dashboard.php" class="cancel-button" aria-label="Back to dashboard" title="Back to dashboard">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                <span class="button-label">Back</span>
            </a>
        </div>
    </div>

    <div class="container">

        <div class="admin-page-title">

            <div>

                <h1>
                    Owners of listed property.
                </h1>

                <p>
                    Manage landlords and contact information. CAUTION: Deletings are irreversible!
                </p>

            </div>

            <div class="admin-page-actions">
                <a href="add_landlord.php" class="admin-button">
                    <i class="fa fa-user-plus" aria-hidden="true"></i>
                    <span class="button-label">Add</span>
                </a>
            </div>

        </div>

        <?php if ($result && $result->num_rows > 0): ?>

            <div class="admin-table-container">

                <table class="admin-table">

                    <thead>

                        <tr>

                            <th>
                                Name
                            </th>

                            <th>
                                Phone
                            </th>

                            <th>
                                Location
                            </th>

                            <th>
                                Properties
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php while ($landlord = $result->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($landlord["name"]) ?>
                            </td>

                            <td>
                                <span class="contact-phone">
                                    <?= htmlspecialchars($landlord["phone"]) ?>
                                </span>
                            </td>

                            <td>
                                <?= htmlspecialchars($landlord["location"]) ?>
                            </td>

                            <td>
                                <?= (int)$landlord["property_count"] ?>
                            </td>

                            <td class="action-links">

                                <a href="edit_landlord.php?id=<?= (int)$landlord["id"] ?>"
                                   aria-label="Edit landlord"
                                   title="Edit landlord">
                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                </a>

                                <a href="delete_landlord.php?id=<?= (int)$landlord["id"] ?>"
                                              data-confirm-message="This action cannot be undone. Delete this landlord?"
                                   aria-label="Delete landlord"
                                   title="Delete landlord">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="no-results">

                <h2>
                    No landlords found
                </h2>

                <p>
                    Add your first landlord profile.
                </p>

                <a href="add_landlord.php" class="search-button-link" aria-label="List a property" title="List a property">
                    <i class="fa fa-home" aria-hidden="true"></i>
                    <span class="button-label">List Property</span>
                </a>

            </div>

        <?php endif; ?>

    </div>

</main>

<?php include __DIR__ . "/footer.php"; ?>
<script src="../js/main.js?v=<?= filemtime(__DIR__ . '/../js/main.js') ?>"></script>
</body>

</html>
