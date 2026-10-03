<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$error = "";
$landlord_name = "";
$landlord_phone = "";
$landlord_location = "";
$property_name = "";
$property_location = "";
$house_type = "";
$price = "";
$payment_period = "Monthly";
$deposit = "";
$available_rooms = 1;
$description = "";
$water = 0;
$electricity = 0;
$wifi = 0;
$security = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $landlord_name = trim($_POST["landlord_name"] ?? "");
    $landlord_phone = trim($_POST["landlord_phone"] ?? "");
    $landlord_location = trim($_POST["landlord_location"] ?? "");
    $property_name = trim($_POST["property_name"] ?? "");
    $property_location = trim($_POST["property_location"] ?? "");
    $house_type = trim($_POST["house_type"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $payment_period = $_POST["payment_period"] ?? "Monthly";
    $deposit = trim($_POST["deposit"] ?? "");
    $available_rooms = (int)($_POST["available_rooms"] ?? 0);
    $description = trim($_POST["description"] ?? "");
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

    if (!isset($_FILES["images"]) || empty($_FILES["images"]["name"][0])) {
        $image_error = "Select at least one property picture.";
    } else {
        foreach ($_FILES["images"]["name"] as $index => $original_name) {
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

        if ($image_error === "" && empty($images_to_upload)) {
            $image_error = "Select at least one property picture.";
        }
    }

    if (
        $landlord_name === "" ||
        $landlord_phone === "" ||
        $landlord_location === "" ||
        $property_name === "" ||
        $property_location === "" ||
        $house_type === "" ||
        !is_numeric($price) ||
        (float)$price <= 0
    ) {
        $error = "Complete all required landlord and property fields.";
    } elseif ($image_error !== "") {
        $error = $image_error;
    } elseif (!in_array($payment_period, ["Monthly", "Semester"], true)) {
        $error = "Select a valid payment period.";
    } elseif ($available_rooms < 0) {
        $error = "Available rooms cannot be negative.";
    } else {
        $conn->begin_transaction();
        $uploaded_image_paths = [];

        try {
            $stmt = $conn->prepare("SELECT id FROM landlords WHERE phone = ? LIMIT 1");
            $stmt->bind_param("s", $landlord_phone);
            $stmt->execute();
            $existing_landlord = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($existing_landlord) {
                $landlord_id = (int)$existing_landlord["id"];
                $stmt = $conn->prepare("UPDATE landlords SET name = ?, location = ? WHERE id = ?");
                $stmt->bind_param("ssi", $landlord_name, $landlord_location, $landlord_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO landlords (name, phone, location) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $landlord_name, $landlord_phone, $landlord_location);
            }

            if (!$stmt->execute()) {
                throw new Exception("Unable to save landlord information.");
            }

            if (!$existing_landlord) {
                $landlord_id = $stmt->insert_id;
            }
            $stmt->close();

            $price_value = (float)$price;
            $stmt = $conn->prepare("
                INSERT INTO properties
                    (landlord_id, name, location, house_type, price, payment_period, deposit, description)
                VALUES (?, ?, ?, ?, ?, ?, NULLIF(?, ''), ?)
            ");
            $stmt->bind_param(
                "isssdsss",
                $landlord_id,
                $property_name,
                $property_location,
                $house_type,
                $price_value,
                $payment_period,
                $deposit,
                $description
            );
            if (!$stmt->execute()) {
                throw new Exception("Unable to save property information.");
            }
            $property_id = $stmt->insert_id;
            $stmt->close();

            $status = $available_rooms > 0 ? "Available" : "Full";
            $stmt = $conn->prepare("
                INSERT INTO rooms (property_id, room_type, available_rooms, status)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("isis", $property_id, $house_type, $available_rooms, $status);
            if (!$stmt->execute()) {
                throw new Exception("Unable to save room information.");
            }
            $stmt->close();

            $stmt = $conn->prepare("
                INSERT INTO facilities (property_id, water, electricity, wifi, security)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iiiii", $property_id, $water, $electricity, $wifi, $security);
            if (!$stmt->execute()) {
                throw new Exception("Unable to save facility information.");
            }
            $stmt->close();

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
                $stmt = $conn->prepare("INSERT INTO property_images (property_id, image_path) VALUES (?, ?)");
                $stmt->bind_param("is", $property_id, $image_path);
                if (!$stmt->execute()) {
                    throw new Exception("Unable to save picture information.");
                }
                $stmt->close();
            }

            $conn->commit();
            header("Location: landlords.php");
            exit;
        } catch (Exception $exception) {
            $conn->rollback();
            foreach ($uploaded_image_paths as $uploaded_image_path) {
                if (is_file($uploaded_image_path)) {
                    unlink($uploaded_image_path);
                }
            }
            $error = $exception->getMessage();
        }
    }
}

$facility_values = [
    "water" => $water,
    "electricity" => $electricity,
    "wifi" => $wifi,
    "security" => $security,
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Landlord</title>
    <link rel="stylesheet" href="../css/styles.css?v=<?= filemtime(__DIR__ . '/../css/styles.css') ?>">
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
            <a href="landlords.php" class="cancel-button" aria-label="Back to landlords" title="Back to landlords">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                <span class="button-label">Back</span>
            </a>
        </div>
    </div>

    <div class="container">
        <div class="admin-page-title add-landlord-page-title">
            <div>
                <h1>Add Landlord</h1>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="error-message admin-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <div class="form-section">
                <h2>Landlord Details</h2>

                <label for="landlord_name">Landlord Name *</label>
                <input type="text" name="landlord_name" id="landlord_name" value="<?= htmlspecialchars($landlord_name) ?>" required>

                <label for="landlord_phone">Phone Number *</label>
                <input type="tel" name="landlord_phone" id="landlord_phone" value="<?= htmlspecialchars($landlord_phone) ?>" required>

                <label for="landlord_location">Landlord Location *</label>
                <input type="text" name="landlord_location" id="landlord_location" value="<?= htmlspecialchars($landlord_location) ?>" required>
            </div>

            <div class="form-section">
                <h2>Property Listing</h2>

                <label for="property_name">Property Name *</label>
                <input type="text" name="property_name" id="property_name" value="<?= htmlspecialchars($property_name) ?>" required>

                <label for="property_location">Property Location *</label>
                <input type="text" name="property_location" id="property_location" value="<?= htmlspecialchars($property_location) ?>" required>

                <label for="house_type">Type of House *</label>
                <div class="select-wrap">
                    <select name="house_type" id="house_type" required>
                        <option value="">Select type of housing</option>
                        <?php foreach (["Single Room", "Bedsitter", "One Bedroom", "Two Bedroom"] as $house_type_option): ?>
                            <option value="<?= htmlspecialchars($house_type_option) ?>" <?= $house_type === $house_type_option ? "selected" : "" ?>>
                                <?= htmlspecialchars($house_type_option) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <label for="price">Price *</label>
                <input type="number" name="price" id="price" min="0.01" step="0.01" value="<?= htmlspecialchars((string)$price) ?>" required>

                <label for="payment_period">Payment Period *</label>
                <div class="select-wrap">
                    <select name="payment_period" id="payment_period" required>
                        <?php foreach (["Monthly", "Semester"] as $period): ?>
                            <option value="<?= $period ?>" <?= $payment_period === $period ? "selected" : "" ?>><?= $period === "Semester" ? "Semester / 4 months" : $period ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <label for="deposit">Deposit</label>
                <input type="number" name="deposit" id="deposit" min="0" step="0.01" value="<?= htmlspecialchars($deposit) ?>">

                <label for="available_rooms">Available Rooms</label>
                <input type="number" name="available_rooms" id="available_rooms" min="0" value="<?= (int)$available_rooms ?>">

                <label for="description">Description</label>
                <textarea name="description" id="description" rows="5"><?= htmlspecialchars($description) ?></textarea>

                <label for="images">Add Pictures *</label>
                <input type="file" name="images[]" id="images" accept="image/jpeg,image/png,image/webp" multiple required>
            </div>

            <div class="form-section">
                <h2>Facilities</h2>
                <?php foreach (["water" => "Water", "electricity" => "Electricity", "wifi" => "Wi-Fi", "security" => "Security"] as $facility_key => $facility_label): ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="<?= $facility_key ?>" <?= !empty($facility_values[$facility_key]) ? "checked" : "" ?>>
                        <?= $facility_label ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="admin-button" aria-label="Register landlord" title="Register">
                    <i class="fa fa-user-plus" aria-hidden="true"></i>
                    <span class="button-label">Register</span>
                </button>
            </div>
        </form>
    </div>
</main>
<?php include __DIR__ . "/footer.php"; ?>
<script src="../js/main.js?v=<?= filemtime(__DIR__ . '/../js/main.js') ?>"></script>
</body>
</html>
