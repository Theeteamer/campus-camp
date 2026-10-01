<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get all properties
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        p.id,
        p.name,
        p.location,
        p.house_type,
        p.price,
        p.payment_period,
        p.deposit,
        r.available_rooms,
        l.name AS landlord_name

    FROM properties p

    INNER JOIN landlords l
        ON p.landlord_id = l.id

    LEFT JOIN rooms r
        ON p.id = r.property_id

    ORDER BY p.id DESC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Properties - Campus-Camp®</title>

    <link
        rel="stylesheet"
        href="../css/styles.css?v=<?= filemtime(__DIR__ . '/../css/styles.css') ?>"
    >

</head>


<body>


<!-- =========================================================
     ADMIN HEADER
     ========================================================= -->

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


<!-- =========================================================
     MAIN CONTENT
     ========================================================= -->

<main class="admin-main">

    <div class="container">


        <!-- PAGE TITLE -->

        <div class="admin-page-title">

            <div>

                <h1>
                    Properties
                </h1>

                <p>
                    Manage accommodation listings.
                </p>

            </div>


            <a
                href="add_property.php"
                class="admin-button"
            >
                + Add Property
            </a>

        </div>


        <!-- =================================================
             PROPERTY TABLE
             ================================================= -->

        <?php if ($result && $result->num_rows > 0): ?>

            <div class="admin-table-container">

                <table class="admin-table">


                    <thead>

                        <tr>

                            <th>
                                Property
                            </th>

                            <th>
                                Location
                            </th>

                            <th>
                                Type
                            </th>

                            <th>
                                Price
                            </th>

                            <th>
                                Available
                            </th>

                            <th>
                                Landlord
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while ($property = $result->fetch_assoc()): ?>


                        <tr>


                            <!-- PROPERTY -->

                            <td>

                                <?= htmlspecialchars(
                                    $property["name"]
                                ) ?>

                            </td>


                            <!-- LOCATION -->

                            <td>

                                <?= htmlspecialchars(
                                    $property["location"]
                                ) ?>

                            </td>


                            <!-- HOUSE TYPE -->

                            <td>

                                <?= htmlspecialchars(
                                    $property["house_type"]
                                ) ?>

                            </td>


                            <!-- PRICE -->

                            <td>

                                KSh

                                <?= number_format(
                                    $property["price"],
                                    0
                                ) ?>

                                /

                                <?= htmlspecialchars(
                                    $property["payment_period"]
                                ) ?>

                            </td>


                            <!-- AVAILABILITY -->

                            <td>

                                <?php

                                $available =
                                    (int)$property[
                                        "available_rooms"
                                    ];

                                ?>


                                <?php if ($available > 0): ?>

                                    <span class="status-available">

                                        <?= $available ?>

                                        available

                                    </span>

                                <?php else: ?>

                                    <span class="status-full">

                                        Full

                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- LANDLORD -->

                            <td>

                                <?= htmlspecialchars(
                                    $property["landlord_name"]
                                ) ?>

                            </td>


                            <!-- ACTIONS -->

                            <td class="action-links">


                                <a
                                    href="edit_property.php?id=<?= (int)$property["id"] ?>"
                                >
                                    Edit
                                </a>


                                <a
                                    href="delete_property.php?id=<?= (int)$property["id"] ?>"
                                    onclick="return confirm('Delete this property? This will also remove its rooms and facilities.');"
                                >
                                    Delete
                                </a>


                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <!-- =================================================
                 NO PROPERTIES
                 ================================================= -->

            <div class="no-results">

                <h2>
                    No properties found
                </h2>

                <p>
                    Add your first accommodation property.
                </p>


                <a
                    href="add_property.php"
                    class="search-button-link"
                >
                    Add Property
                </a>

            </div>


        <?php endif; ?>


    </div>

</main>


</body>

</html>