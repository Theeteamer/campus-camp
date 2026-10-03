<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| GET PROPERTY ID
|--------------------------------------------------------------------------
*/

$id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: properties.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| GET PROPERTY
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        p.id,
        p.landlord_id,
        p.name,
        p.location,
        p.house_type,
        p.price,
        p.payment_period,
        p.deposit,
        p.description,

        l.name AS landlord_name,
        l.phone AS landlord_phone,
        l.location AS landlord_location,

        r.available_rooms,

        f.water,
        f.electricity,
        f.wifi,
        f.security

    FROM properties p

    INNER JOIN landlords l
        ON p.landlord_id = l.id

    LEFT JOIN rooms r
        ON p.id = r.property_id

    LEFT JOIN facilities f
        ON p.id = f.property_id

    WHERE p.id = ?

    LIMIT 1
");

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();

$property = $result->fetch_assoc();

$stmt->close();


if (!$property) {
    header("Location: properties.php");
    exit;
}


$error = "";


/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /*
    |--------------------------------------------------------------------------
    | LANDLORD
    |--------------------------------------------------------------------------
    */

    $landlord_name =
        trim($_POST["landlord_name"] ?? "");

    $landlord_phone =
        trim($_POST["landlord_phone"] ?? "");

    $landlord_location =
        trim($_POST["landlord_location"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | PROPERTY
    |--------------------------------------------------------------------------
    */

    $name =
        trim($_POST["name"] ?? "");

    $location =
        trim($_POST["location"] ?? "");

    $house_type =
        trim($_POST["house_type"] ?? "");

    $price =
        (float)($_POST["price"] ?? 0);

    $payment_period =
        $_POST["payment_period"] ?? "Monthly";

    $deposit_input =
        trim($_POST["deposit"] ?? "");

    $description =
        trim($_POST["description"] ?? "");

    $available_rooms =
        (int)($_POST["available_rooms"] ?? 0);


    /*
    |--------------------------------------------------------------------------
    | FACILITIES
    |--------------------------------------------------------------------------
    */

    $water =
        isset($_POST["water"]) ? 1 : 0;

    $electricity =
        isset($_POST["electricity"]) ? 1 : 0;

    $wifi =
        isset($_POST["wifi"]) ? 1 : 0;

    $security =
        isset($_POST["security"]) ? 1 : 0;


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

        $error =
            "Please complete all required fields.";

    }

    elseif (
        !in_array(
            $payment_period,
            ["Monthly", "Semester"],
            true
        )
    ) {

        $error =
            "Invalid payment period.";

    }

    elseif ($available_rooms < 0) {

        $error =
            "Available rooms cannot be negative.";

    }

    else {


        $conn->begin_transaction();


        try {


            /*
            |--------------------------------------------------------------------------
            | UPDATE LANDLORD
            |--------------------------------------------------------------------------
            */

            $landlord_stmt = $conn->prepare("
                UPDATE landlords

                SET
                    name = ?,
                    phone = ?,
                    location = ?

                WHERE id = ?
            ");

            $landlord_stmt->bind_param(
                "sssi",
                $landlord_name,
                $landlord_phone,
                $landlord_location,
                $property["landlord_id"]
            );


            if (!$landlord_stmt->execute()) {

                throw new Exception(
                    "Unable to update landlord."
                );

            }


            $landlord_stmt->close();


            /*
            |--------------------------------------------------------------------------
            | UPDATE PROPERTY
            |--------------------------------------------------------------------------
            */

            $property_stmt = $conn->prepare("
                UPDATE properties

                SET
                    name = ?,
                    location = ?,
                    house_type = ?,
                    price = ?,
                    payment_period = ?,
                    deposit = NULLIF(?, ''),
                    description = ?

                WHERE id = ?
            ");

            $property_stmt->bind_param(
                "sssdsssi",
                $name,
                $location,
                $house_type,
                $price,
                $payment_period,
                $deposit_input,
                $description,
                $id
            );


            if (!$property_stmt->execute()) {

                throw new Exception(
                    "Unable to update property."
                );

            }


            $property_stmt->close();


            /*
            |--------------------------------------------------------------------------
            | UPDATE ROOMS
            |--------------------------------------------------------------------------
            */

            $status =
                $available_rooms > 0
                    ? "Available"
                    : "Full";


            $room_stmt = $conn->prepare("
                UPDATE rooms

                SET
                    room_type = ?,
                    available_rooms = ?,
                    status = ?

                WHERE property_id = ?
            ");

            $room_stmt->bind_param(
                "sisi",
                $house_type,
                $available_rooms,
                $status,
                $id
            );


            if (!$room_stmt->execute()) {

                throw new Exception(
                    "Unable to update room information."
                );

            }


            $room_stmt->close();


            /*
            |--------------------------------------------------------------------------
            | UPDATE FACILITIES
            |--------------------------------------------------------------------------
            */

            $facility_stmt = $conn->prepare("
                UPDATE facilities

                SET
                    water = ?,
                    electricity = ?,
                    wifi = ?,
                    security = ?

                WHERE property_id = ?
            ");

            $facility_stmt->bind_param(
                "iiiii",
                $water,
                $electricity,
                $wifi,
                $security,
                $id
            );


            if (!$facility_stmt->execute()) {

                throw new Exception(
                    "Unable to update facilities."
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

            $error =
                $e->getMessage();

        }

    }


    $property["landlord_name"] =
        $landlord_name;

    $property["landlord_phone"] =
        $landlord_phone;

    $property["landlord_location"] =
        $landlord_location;

    $property["name"] =
        $name;

    $property["location"] =
        $location;

    $property["house_type"] =
        $house_type;

    $property["price"] =
        $price;

    $property["payment_period"] =
        $payment_period;

    $property["deposit"] =
        $deposit_input;

    $property["description"] =
        $description;

    $property["available_rooms"] =
        $available_rooms;

    $property["water"] =
        $water;

    $property["electricity"] =
        $electricity;

    $property["wifi"] =
        $wifi;

    $property["security"] =
        $security;
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
        Edit Property
    </title>


    <link
        rel="stylesheet"
        href="../css/styles.css?v=<?= filemtime(__DIR__ . '/../css/styles.css') ?>"
    >

</head>


<body>


<header class="admin-header">

    <div class="container admin-header-inner">


        <strong>

            <img
                class="brand-image"
                src="../images/ofcampus-logo.png"
                alt="Campus-Camp®"
            >

            <span class="admin-context">
                Admin
            </span>

        </strong>


        <div>
            <a href="logout.php">Log out</a>
        </div>

    </div>

</header>

<main class="admin-main">

    <div class="admin-content-actions">
        <div class="container">
            <a href="properties.php" class="cancel-button" aria-label="Back to properties" title="Back to properties">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                <span class="button-label">Back</span>
            </a>
        </div>
    </div>

    <div class="container">

        <div class="admin-page-title">

            <div>

                <h1>
                    Edit Property
                </h1>

                <p>
                    Update accommodation information. Deleting property listing is irreversible!
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

            <div class="form-section form-section-divider">

                <h2>
                    Landlord Details
                </h2>


                <label for="landlord_name">
                    Landlord Name <span class="required-mark">*</span>
                </label>

                <input
                    type="text"
                    name="landlord_name"
                    id="landlord_name"
                    value="<?= htmlspecialchars($property["landlord_name"]) ?>"
                    required
                >


                <label for="landlord_phone">
                    Phone Number <span class="required-mark">*</span>
                </label>

                <input
                    type="tel"
                    name="landlord_phone"
                    id="landlord_phone"
                    value="<?= htmlspecialchars($property["landlord_phone"]) ?>"
                    required
                >


                <label for="landlord_location">
                    Landlord Location <span class="required-mark">*</span>
                </label>

                <input
                    type="text"
                    name="landlord_location"
                    id="landlord_location"
                    value="<?= htmlspecialchars($property["landlord_location"]) ?>"
                    required
                >

            </div>


            <div class="form-section">

                <h2>
                    Property Information
                </h2>


                <label for="name">
                    Property Name <span class="required-mark">*</span>
                </label>

                <input
                    type="text"
                    name="name"
                    id="name"
                    value="<?= htmlspecialchars($property["name"]) ?>"
                    required
                >


                <label for="location">
                    Property Location <span class="required-mark">*</span>
                </label>

                <input
                    type="text"
                    name="location"
                    id="location"
                    value="<?= htmlspecialchars($property["location"]) ?>"
                    required
                >


                <label for="house_type">
                    Type of Housing <span class="required-mark">*</span>
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

                    <option
                        value="Single Room"
                        <?= $property["house_type"] === "Single Room" ? "selected" : "" ?>
                    >
                        Single Room
                    </option>


                    <option
                        value="Bedsitter"
                        <?= $property["house_type"] === "Bedsitter" ? "selected" : "" ?>
                    >
                        Bedsitter
                    </option>


                    <option
                        value="One Bedroom"
                        <?= $property["house_type"] === "One Bedroom" ? "selected" : "" ?>
                    >
                        One Bedroom
                    </option>


                    <option
                        value="Two Bedroom"
                        <?= $property["house_type"] === "Two Bedroom" ? "selected" : "" ?>
                    >
                        Two Bedroom
                    </option>

                    </select>
                </div>


                <label for="price">
                    Price <span class="required-mark">*</span>
                </label>

                <input
                    type="number"
                    name="price"
                    id="price"
                    min="0"
                    step="0.01"
                    value="<?= htmlspecialchars($property["price"]) ?>"
                    required
                >


                <label for="payment_period">
                    Payment Period <span class="required-mark">*</span>
                </label>

                <div class="select-wrap">
                    <select
                        name="payment_period"
                        id="payment_period"
                        required
                    >

                    <option
                        value="Monthly"
                        <?= $property["payment_period"] === "Monthly" ? "selected" : "" ?>
                    >
                        Monthly
                    </option>


                    <option
                        value="Semester"
                        <?= $property["payment_period"] === "Semester" ? "selected" : "" ?>
                    >
                        Semester / 4 Months
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
                    value="<?= htmlspecialchars($property["deposit"] ?? "") ?>"
                >


                <label for="available_rooms">
                    Available Rooms
                </label>

                <input
                    type="number"
                    name="available_rooms"
                    id="available_rooms"
                    min="0"
                    value="<?= (int)$property["available_rooms"] ?>"
                >


                <label for="description">
                    Description
                </label>

                <textarea
                    name="description"
                    id="description"
                    rows="5"
                ><?= htmlspecialchars($property["description"] ?? "") ?></textarea>

            </div>


            <div class="form-section">

                <h2>
                    Facilities
                </h2>


                <label class="checkbox-label">

                    <input
                        type="checkbox"
                        name="water"
                        <?= !empty($property["water"]) ? "checked" : "" ?>
                    >

                    Water

                </label>


                <label class="checkbox-label">

                    <input
                        type="checkbox"
                        name="electricity"
                        <?= !empty($property["electricity"]) ? "checked" : "" ?>
                    >

                    Electricity

                </label>


                <label class="checkbox-label">

                    <input
                        type="checkbox"
                        name="wifi"
                        <?= !empty($property["wifi"]) ? "checked" : "" ?>
                    >

                    Wi-Fi

                </label>


                <label class="checkbox-label">

                    <input
                        type="checkbox"
                        name="security"
                        <?= !empty($property["security"]) ? "checked" : "" ?>
                    >

                    Security

                </label>

            </div>


            <div class="form-actions">

                <button
                    type="submit"
                    class="admin-button"
                    aria-label="Save property"
                    title="Save property"
                >
                    <i class="fa fa-floppy-o" aria-hidden="true"></i>
                    <span class="button-label">Save</span>
                </button>

                <a
                    href="property_images.php?id=<?= (int)$property["id"] ?>"
                    class="admin-button"
                    aria-label="Add property images"
                    title="Add images"
                >
                    <i class="fa fa-picture-o" aria-hidden="true"></i>
                    <span class="button-label">Add Images</span>
                </a>
            </div>


        </form>


    </div>

</main>

<?php include __DIR__ . "/footer.php"; ?>
<script src="../js/main.js?v=<?= filemtime(__DIR__ . '/../js/main.js') ?>"></script>
</body>

</html>