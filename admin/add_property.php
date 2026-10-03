<?php
require_once __DIR__ . "/../includes/partial_response.php";
start_partial_response();

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

    $images_to_upload = [];
    $image_error = "";
    $allowed_image_types = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp",
    ];

    if (isset($_FILES["images"])) {
        foreach ($_FILES["images"]["name"] as $index => $original_name) {
            if ($original_name === "" && $_FILES["images"]["error"][$index] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($_FILES["images"]["error"][$index] !== UPLOAD_ERR_OK) {
                $image_error = "Each selected picture must upload successfully.";
                break;
            }

            $temporary_path = $_FILES["images"]["tmp_name"][$index];
            $mime_type = mime_content_type($temporary_path);
            if (!isset($allowed_image_types[$mime_type])) {
                $image_error = "Pictures must be JPG, PNG, or WebP files.";
                break;
            }

            $images_to_upload[] = [
                "temporary_path" => $temporary_path,
                "extension" => $allowed_image_types[$mime_type],
            ];
        }
    }


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

    } elseif ($image_error !== "") {

        $error = $image_error;

    } elseif (!in_array($payment_period, ["Monthly", "Semester"], true)) {

        $error = "Invalid payment period.";

    } elseif ($available_rooms < 0) {

        $error = "Available rooms cannot be negative.";

    } else {

        $conn->begin_transaction();
        $uploaded_image_paths = [];

        try {

            

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


            if ($existing_landlord) {

                $landlord_id =
                    (int)$existing_landlord["id"];

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

            if (!empty($images_to_upload)) {
                $upload_directory = __DIR__ . "/../uploads/properties/" . $property_id . "/";
                $database_directory = "uploads/properties/" . $property_id . "/";
                if (!is_dir($upload_directory) && !mkdir($upload_directory, 0755, true) && !is_dir($upload_directory)) {
                    throw new Exception("Unable to create the property picture directory.");
                }

                foreach ($images_to_upload as $image) {
                    $filename = uniqid("property_", true) . "." . $image["extension"];
                    $destination = $upload_directory . $filename;
                    if (!move_uploaded_file($image["temporary_path"], $destination)) {
                        throw new Exception("Unable to save a property picture.");
                    }
                    $uploaded_image_paths[] = $destination;

                    $image_path = $database_directory . $filename;
                    $image_stmt = $conn->prepare("
                        INSERT INTO property_images (property_id, image_path)
                        VALUES (?, ?)
                    ");
                    $image_stmt->bind_param("is", $property_id, $image_path);
                    if (!$image_stmt->execute()) {
                        throw new Exception("Unable to save picture information.");
                    }
                    $image_stmt->close();
                }
            }


            $conn->commit();

            header("Location: properties.php");
            exit;

        } catch (Exception $e) {

            $conn->rollback();
            foreach ($uploaded_image_paths as $uploaded_image_path) {
                if (is_file($uploaded_image_path)) {
                    unlink($uploaded_image_path);
                }
            }

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

    <title>Add Property </title>

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

        <div class="admin-page-title add-property-page-title">

            <div>

                <h1>Add Property</h1>


            </div>

        </div>


        <?php if ($error): ?>

            <div class="error-message admin-error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            enctype="multipart/form-data"
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
                
                    required
                >


                <label for="landlord_phone">
                    Phone Number *
                </label>

                <input
                    type="tel"
                    name="landlord_phone"
                    id="landlord_phone"
                
                    required
                >


                <label for="landlord_location">
                    Landlord Location *
                </label>

                <input
                    type="text"
                    name="landlord_location"
                    id="landlord_location"
      
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
                    required
                >


                <label for="location">
                    Property Location *
                </label>

                <input
                    type="text"
                    name="location"
                    id="location"
            
                    required
                >


                <label for="house_type">
                    Type of Housing *
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

            <div class="form-section">

                <h2>Pictures</h2>

                <label for="images">
                    Add Pictures
                </label>

                <input
                    type="file"
                    name="images[]"
                    id="images"
                    class="admin-file-input"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                >

                <p>Select JPG, PNG, or WebP pictures. You can choose multiple files.</p>

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

            </div>

        </form>

    </div>

</main>

<?php include __DIR__ . "/footer.php"; ?>
<script src="../js/main.js?v=<?= filemtime(__DIR__ . '/../js/main.js') ?>"></script>
</body>

</html>