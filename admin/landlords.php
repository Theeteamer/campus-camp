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

    <title>Landlords - Campus-Camp®</title>

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

            <a href="dashboard.php">
                Dashboard
            </a>

            &nbsp; | &nbsp;

            <a href="logout.php">
                Logout
            </a>

        </div>

    </div>

</header>

<main class="admin-main">

    <div class="container">

        <div class="admin-page-title">

            <div>

                <h1>
                    Landlords
                </h1>

                <p>
                    Manage landlords and contact information.
                </p>

            </div>

            <a
                href="add_landlord.php"
                class="admin-button"
            >
                + Add Landlord
            </a>

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
                                <?= htmlspecialchars($landlord["phone"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($landlord["location"]) ?>
                            </td>

                            <td>
                                <?= (int)$landlord["property_count"] ?>
                            </td>

                            <td class="action-links">

                                <a href="edit_landlord.php?id=<?= (int)$landlord["id"] ?>">
                                    Edit
                                </a>

                                <a href="delete_landlord.php?id=<?= (int)$landlord["id"] ?>"
                                   onclick="return confirm('Delete this landlord?');">
                                    Delete
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

                <a href="add_landlord.php" class="search-button-link">
                    Add Landlord
                </a>

            </div>

        <?php endif; ?>

    </div>

</main>

</body>

</html>
