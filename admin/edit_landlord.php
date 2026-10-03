<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: landlords.php");
    exit;
}

$stmt = $conn->prepare("SELECT id, name, phone, location FROM landlords WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$landlord = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$landlord) {
    header("Location: landlords.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT id, name, location
    FROM properties
    WHERE landlord_id = ?
    ORDER BY name ASC
");
$stmt->bind_param("i", $id);
$stmt->execute();
$property_result = $stmt->get_result();
$properties = [];
while ($row = $property_result->fetch_assoc()) {
    $properties[] = $row;
}
$stmt->close();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $location = trim($_POST["location"] ?? "");

    if ($name === "" || $phone === "" || $location === "") {
        $error = "Please complete all landlord fields.";
    } elseif (empty($properties)) {
        $error = "At least one property listing is required before saving this landlord.";
    } else {
        $check = $conn->prepare("SELECT id FROM landlords WHERE phone = ? AND id != ? LIMIT 1");
        $check->bind_param("si", $phone, $id);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        $check->close();

        if ($existing) {
            $error = "A landlord with this phone number already exists.";
        } else {
            $stmt = $conn->prepare("UPDATE landlords SET name = ?, phone = ?, location = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $phone, $location, $id);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: landlords.php");
                exit;
            }

            $error = "Unable to update landlord.";
            $stmt->close();
        }
    }

    $landlord["name"] = $name;
    $landlord["phone"] = $phone;
    $landlord["location"] = $location;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Landlord - Campus-Camp®</title>

    <link rel="stylesheet" href="../css/styles.css?v=<?= filemtime(__DIR__ . '/../css/styles.css') ?>">

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
            <a href="landlords.php" class="cancel-button" aria-label="Back to landlords" title="Back to landlords">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                <span class="button-label">Back</span>
            </a>
        </div>
    </div>

    <div class="container">

        <div class="admin-page-title edit-landlord-page-title">

            <div>

                <h1>Make changes to Accomodation Owners.</h1>

                <p>
                    Update landlord details.
                </p>

            </div>

        </div>

        <?php if ($error): ?>
            <div class="error-message admin-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($properties)): ?>
            <form method="GET" action="edit_property.php" class="property-selection-form">
                <label for="property_id">
                    Property listings
                    <select name="id" id="property_id" required>
                        <?php foreach ($properties as $property): ?>
                            <option value="<?= (int)$property["id"] ?>">
                                <?= htmlspecialchars($property["name"] . " · " . $property["location"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <button type="submit" class="admin-button" aria-label="Edit landlord property listing" title="Edit">
                    <i class="fa fa-pencil" aria-hidden="true"></i>
                    <span class="button-label">Edit</span>
                </button>
            </form>
        <?php else: ?>
            <div class="error-message admin-error">
                At least one property listing is required before this landlord can be saved.
                <a href="add_property.php">List a property</a>
            </div>
        <?php endif; ?>

        <form method="POST" class="admin-form landlord-edit-form">
            <div class="form-section">

                <h2>Landlord Details</h2>

                <label for="name">Name *</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($landlord["name"]) ?>" required>

                <label for="phone">Phone Number *</label>
                <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($landlord["phone"]) ?>" required>

                <label for="location">Location *</label>
                <input type="text" name="location" id="location" value="<?= htmlspecialchars($landlord["location"]) ?>" required>

            </div>

            <div class="form-actions">

                <button type="submit" class="admin-button" aria-label="Save landlord" title="Save" <?= empty($properties) ? "disabled" : "" ?>>
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
