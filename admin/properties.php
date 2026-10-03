<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $property_id = (int)($_POST["property_id"] ?? 0);
    $change = (int)($_POST["change"] ?? 0);

    if ($property_id <= 0 || !in_array($change, [-1, 1], true)) {
        http_response_code(400);
        exit("Invalid availability update.");
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("SELECT house_type FROM properties WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $property_id);
        $stmt->execute();
        $property_row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$property_row) {
            throw new RuntimeException("Property not found.");
        }

        $stmt = $conn->prepare("SELECT available_rooms FROM rooms WHERE property_id = ? FOR UPDATE");
        $stmt->bind_param("i", $property_id);
        $stmt->execute();
        $room_row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $current_count = max(0, (int)($room_row["available_rooms"] ?? 0));
        $new_count = max(0, $current_count + $change);
        $status = $new_count > 0 ? "Available" : "Full";

        if ($room_row) {
            $stmt = $conn->prepare("UPDATE rooms SET available_rooms = ?, status = ? WHERE property_id = ?");
            $stmt->bind_param("isi", $new_count, $status, $property_id);
        } elseif ($new_count > 0) {
            $stmt = $conn->prepare("INSERT INTO rooms (property_id, room_type, available_rooms, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isis", $property_id, $property_row["house_type"], $new_count, $status);
        }

        if (isset($stmt)) {
            if (!$stmt->execute()) {
                throw new RuntimeException("Unable to update room availability.");
            }
            $stmt->close();
        }

        $conn->commit();
    } catch (Throwable $exception) {
        $conn->rollback();
        $_SESSION["properties_error"] = "Unable to update room availability.";
    }

    header("Location: properties.php");
    exit;
}

$availability_error = $_SESSION["properties_error"] ?? "";
unset($_SESSION["properties_error"]);
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

    <title>Properties</title>

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
                    Properties
                </h1>

                <p>
                    Management accommodation listings. CAUTION: Deleting a property is irreversible!
                </p>

            </div>


            <div class="admin-page-actions">
                <a href="add_property.php" class="admin-button">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                    <span class="button-label">List Property</span>
                </a>
            </div>

        </div>

        <?php if ($availability_error !== ""): ?>
            <div class="error-message admin-error">
                <?= htmlspecialchars($availability_error) ?>
            </div>
        <?php endif; ?>

        <?php if ($result && $result->num_rows > 0): ?>

            <div class="admin-table-container">

                <table class="admin-table properties-admin-table">


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


                            <td>

                                <?= htmlspecialchars(
                                    $property["name"]
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $property["location"]
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $property["house_type"]
                                ) ?>

                            </td>


                            <td>

                                KSh

                                <?= number_format($property["price"], 0) ?>

                                /

                                <?= htmlspecialchars($property["payment_period"]) ?>

                            </td>



                            <td>

                                <?php $available = (int)$property["available_rooms"]; ?>

                                <div class="availability-control">
                                    <form method="POST" class="availability-change-form">
                                        <input type="hidden" name="property_id" value="<?= (int)$property["id"] ?>">
                                        <input type="hidden" name="change" value="-1">
                                        <button
                                            type="submit"
                                            class="availability-adjust"
                                            aria-label="Reduce available rooms"
                                            title="Reduce available rooms"
                                            <?= $available <= 0 ? "disabled" : "" ?>
                                        ><i class="fa fa-minus" aria-hidden="true"></i></button>
                                    </form>

                                    <?php if ($available > 0): ?>
                                        <span class="status-available"><?= $available ?> available</span>
                                    <?php else: ?>
                                        <span class="status-rented-out" role="status">Full</span>
                                    <?php endif; ?>

                                    <form method="POST" class="availability-change-form">
                                        <input type="hidden" name="property_id" value="<?= (int)$property["id"] ?>">
                                        <input type="hidden" name="change" value="1">
                                        <button
                                            type="submit"
                                            class="availability-adjust"
                                            aria-label="Add available room"
                                            title="Add available room"
                                        ><i class="fa fa-plus" aria-hidden="true"></i></button>
                                    </form>
                                </div>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $property["landlord_name"]
                                ) ?>

                            </td>

                            <td class="action-links">


                                <a
                                    href="edit_property.php?id=<?= (int)$property["id"] ?>"
                                    aria-label="Edit property"
                                    title="Edit property"
                                >
                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                </a>


                                <a
                                    href="delete_property.php?id=<?= (int)$property["id"] ?>"
                                    data-confirm-message="Deleting this property also removes its rooms and facilities. This action cannot be undone. Continue?"
                                    aria-label="Delete property"
                                    title="Delete property"
                                >
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
                    No properties found
                </h2>

                <p>
                    Add your first accommodation property.
                </p>


                <a
                    href="add_property.php"
                    class="search-button-link"
                    aria-label="Add property"
                    title="Add property"
                >
                    <i class="fa fa-plus" aria-hidden="true"></i>
                    <span class="button-label">Add Property</span>
                </a>

            </div>


        <?php endif; ?>


    </div>

</main>

<?php include __DIR__ . "/footer.php"; ?>
<script src="../js/main.js?v=<?= filemtime(__DIR__ . '/../js/main.js') ?>"></script>
</body>

</html>