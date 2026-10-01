<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* LANDLORD */
    $landlord_name = trim($_POST["landlord_name"] ?? "");
    $landlord_phone = trim($_POST["landlord_phone"] ?? "");
    $landlord_location = trim($_POST["landlord_location"] ?? "");

    /* PROPERTY */
    $name = trim($_POST["name"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $house_type = trim($_POST["house_type"] ?? "");
    $price = (float)($_POST["price"] ?? 0);

    $payment_period = $_POST["payment_period"] ?? "Monthly";

    $deposit_input = trim($_POST["deposit"] ?? "");

    $description = trim($_POST["description"] ?? "");

    $available_rooms = (int)($_POST["available_rooms"] ?? 0);

    /* FACILITIES */
    $water = isset($_POST["water"]) ? 1 : 0;
    $electricity = isset($_POST["electricity"]) ? 1 : 0;
    $wifi = isset($_POST["wifi"]) ? 1 : 0;
    $security = isset($_POST["security"]) ? 1 : 0;


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        $landlord_name === "" ||
        $landlord_phone === "" ||
        $landlord_location === "" ||
        $name === "" ||
        $location === "" ||
        $house_type === "" ||
        $price <= 0
    ) {

        $error = "Please complete all required fields.";

    } elseif (!in_array($payment_period, ["Monthly", "Semester"], true)) {

        $error = "Invalid payment period.";

    } elseif ($available_rooms < 0) {

        $error = "Available rooms cannot be negative.";

    } else {

        $conn->begin_transaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | 1. CHECK WHETHER LANDLORD ALREADY EXISTS
            |--------------------------------------------------------------------------
            |
            | Phone number is used as the main identifier.
            |
            */

            $landlord_stmt = $conn->prepare("
                SELECT id
                FROM landlords
                WHERE phone = ?
                LIMIT 1
            ");

            $landlord_stmt->bind_param(
                "s",
                $landlord_phone
            );

            $landlord_stmt->execute();

            $landlord_result =
                $landlord_stmt->get_result();

            $existing_landlord =
                $landlord_result->fetch_assoc();

            $landlord_stmt->close();


            /*
            |--------------------------------------------------------------------------
            | 2. USE EXISTING LANDLORD OR CREATE NEW ONE
            |--------------------------------------------------------------------------
            */

            if ($existing_landlord) {

                $landlord_id =
                    (int)$existing_landlord["id"];


                /*
                | Update the landlord's details in case
                | the administrator corrected them.
                */

                $update_landlord = $conn->prepare("
                    UPDATE landlords

                    SET
                        name = ?,
                        location = ?

                    WHERE id = ?
                ");

                $update_landlord->bind_param(
                    "ssi",
                    $landlord_name,
                    $landlord_location,
                    $landlord_id
                );

                if (!$update_landlord->execute()) {
                    throw new Exception(
                        "Unable to update landlord."
                    );
                }

                $update_landlord->close();

            } else {

                /*
                | Create a new landlord.
                */

                $create_landlord = $conn->prepare("
                    INSERT INTO landlords
                    (
                        name,
                        phone,
                        location
                    )
                    VALUES (?, ?, ?)
                ");

                $create_landlord->bind_param(
                    "sss",
                    $landlord_name,
                    $landlord_phone,
                    $landlord_location
                );

                if (!$create_landlord->execute()) {
                    throw new Exception(
                        "Unable to save landlord."
                    );
                }

                $landlord_id =
                    $create_landlord->insert_id;

                $create_landlord->close();
            }


            /*
            |--------------------------------------------------------------------------
            | 3. CREATE PROPERTY
            |--------------------------------------------------------------------------
            */

            $property_stmt = $conn->prepare("
                INSERT INTO properties
                (
                    landlord_id,
                    name,
                    location,
                    house_type,
                    price,
                    payment_period,
                    deposit,
                    description
                )
                VALUES (?, ?, ?, ?, ?, ?, NULLIF(?, ''), ?)
            ");

            $property_stmt->bind_param(
                "isssdsss",
                $landlord_id,
                $name,
                $location,
                $house_type,
                $price,
                $payment_period,
                $deposit_input,
                $description
            );

            if (!$property_stmt->execute()) {
                throw new Exception(
                    "Unable to save property."
                );
            }

            $property_id =
                $property_stmt->insert_id;

            $property_stmt->close();


            /*
            |--------------------------------------------------------------------------
            | 4. CREATE ROOM RECORD
            |--------------------------------------------------------------------------
            */

            $status =
                $available_rooms > 0
                    ? "Available"
                    : "Full";

            $room_stmt = $conn->prepare("
                INSERT INTO rooms
                (
                    property_id,
                    room_type,
                    available_rooms,
                    status
                )
                VALUES (?, ?, ?, ?)
            ");

            $room_stmt->bind_param(
                "isis",
                $property_id,
                $house_type,
                $available_rooms,
                $status
            );

            if (!$room_stmt->execute()) {
                throw new Exception(
                    "Unable to save room information."
                );
            }

            $room_stmt->close();


            /*
            |--------------------------------------------------------------------------
            | 5. CREATE FACILITIES
            |--------------------------------------------------------------------------
            */

            $facility_stmt = $conn->prepare("
                INSERT INTO facilities
                (
                    property_id,
                    water,
                    electricity,
                    wifi,
                    security
                )
                VALUES (?, ?, ?, ?, ?)
            ");

            $facility_stmt->bind_param(
                "iiiii",
                $property_id,
                $water,
                $electricity,
                $wifi,
                $security
            );

            if (!$facility_stmt->execute()) {
                throw new Exception(
                    "Unable to save facilities."
                );
            }

            $facility_stmt->close();


            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            $conn->commit();

            header("Location: properties.php");
            exit;

        } catch (Exception $e) {

            $conn->rollback();

            $error = $e->getMessage();
        }
    }
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

    <title>Add Property - Campus-Camp®</title>

    <link
        rel="stylesheet"
        href="../css/styles.css?v=<?= filemtime(__DIR__ . '/../css/styles.css') ?>"
    >

</head>

<body>

<header class="admin-header">

    <div class="container admin-header-inner">

        <strong><img class="brand-image" src="../images/ofcampus-logo.png" alt="Campus-Camp®"><span class="admin-context">Admin</span></strong>

        <div>

            <a href="dashboard.php">Dashboard</a>

            &nbsp; | &nbsp;

            <a href="properties.php">Properties</a>

            &nbsp; | &nbsp;

            <a href="logout.php">Logout</a>

        </div>

    </div>

</header>


<main class="admin-main">

    <div class="container">

        <div class="admin-page-title">

            <div>

                <h1>Add Property</h1>

                <p>
                    Add accommodation and landlord information.
                </p>

            </div>

        </div>


        <?php if ($error): ?>

            <div class="error-message admin-error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            class="admin-form"
        >

            <!-- LANDLORD -->

            <div class="form-section">

                <h2>Landlord Details</h2>

                <label for="landlord_name">
                    Landlord Name *
                </label>

                <input
                    type="text"
                    name="landlord_name"
                    id="landlord_name"
                    placeholder="e.g. John Otieno"
                    required
                >


                <label for="landlord_phone">
                    Phone Number *
                </label>

                <input
                    type="tel"
                    name="landlord_phone"
                    id="landlord_phone"
                    placeholder="e.g. 0712345678"
                    required
                >


                <label for="landlord_location">
                    Landlord Location *
                </label>

                <input
                    type="text"
                    name="landlord_location"
                    id="landlord_location"
                    placeholder="e.g. Nyanchwa"
                    required
                >

            </div>


            <!-- PROPERTY -->

            <div class="form-section">

                <h2>Property Information</h2>

                <label for="name">
                    Property Name *
                </label>

                <input
                    type="text"
                    name="name"
                    id="name"
                    placeholder="e.g. Sunrise Bedsitters"
                    required
                >


                <label for="location">
                    Property Location *
                </label>

                <input
                    type="text"
                    name="location"
                    id="location"
                    placeholder="e.g. Nyanchwa"
                    required
                >


                <label for="house_type">
                    House Type *
                </label>

                <div class="select-wrap">
                    <select
                        name="house_type"
                        id="house_type"
                        required
                    >

                    <option value="">
                        Select house type
                    </option>

                    <option value="Single Room">
                        Single Room
                    </option>

                    <option value="Bedsitter">
                        Bedsitter
                    </option>

                    <option value="One Bedroom">
                        One Bedroom
                    </option>

                    <option value="Two Bedroom">
                        Two Bedroom
                    </option>

                    </select>
                </div>


                <label for="price">
                    Price *
                </label>

                <input
                    type="number"
                    name="price"
                    id="price"
                    min="0"
                    step="0.01"
                    placeholder="4500"
                    required
                >


                <label for="payment_period">
                    Payment Period *
                </label>

                <div class="select-wrap">
                    <select
                        name="payment_period"
                        id="payment_period"
                        required
                    >

                    <option value="Monthly">
                        Monthly
                    </option>

                    <option value="Semester">
                        Semester
                    </option>

                    </select>
                </div>


                <label for="deposit">
                    Deposit
                </label>

                <input
                    type="number"
                    name="deposit"
                    id="deposit"
                    min="0"
                    step="0.01"
                    placeholder="Leave blank if not listed"
                >


                <label for="available_rooms">
                    Available Rooms
                </label>

                <input
                    type="number"
                    name="available_rooms"
                    id="available_rooms"
                    min="0"
                    value="1"
                >


                <label for="description">
                    Description
                </label>

                <textarea
                    name="description"
                    id="description"
                    rows="5"
                    placeholder="Short description..."
                ></textarea>

            </div>


            <!-- FACILITIES -->

            <div class="form-section">

                <h2>Facilities</h2>

                <label class="checkbox-label">
                    <input type="checkbox" name="water">
                    Water
                </label>

                <label class="checkbox-label">
                    <input type="checkbox" name="electricity">
                    Electricity
                </label>

                <label class="checkbox-label">
                    <input type="checkbox" name="wifi">
                    Wi-Fi
                </label>

                <label class="checkbox-label">
                    <input type="checkbox" name="security">
                    Security
                </label>

            </div>


            <div class="form-actions">

                <button
                    type="submit"
                    class="admin-button"
                >
                    SAVE PROPERTY
                </button>

                <a
                    href="properties.php"
                    class="cancel-button"
                >
                    CANCEL
                </a>

            </div>

        </form>

    </div>

</main>

</body>

</html>