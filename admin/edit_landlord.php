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

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $location = trim($_POST["location"] ?? "");

    if ($name === "" || $phone === "" || $location === "") {
        $error = "Please complete all landlord fields.";
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

            <a href="dashboard.php">Dashboard</a>
            &nbsp; | &nbsp;
            <a href="landlords.php">Landlords</a>
            &nbsp; | &nbsp;
            <a href="logout.php">Logout</a>

        </div>

    </div>

</header>

<main class="admin-main">

    <div class="container">

        <div class="admin-page-title">

            <div>

                <h1>Edit Landlord</h1>

                <p>
                    Update landlord information.
                </p>

            </div>

        </div>

        <?php if ($error): ?>
            <div class="error-message admin-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="admin-form">

            <div class="form-section">

                <h2>Landlord Details</h2>

                <label for="name">Landlord Name *</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($landlord["name"]) ?>" required>

                <label for="phone">Phone Number *</label>
                <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($landlord["phone"]) ?>" required>

                <label for="location">Location *</label>
                <input type="text" name="location" id="location" value="<?= htmlspecialchars($landlord["location"]) ?>" required>

            </div>

            <div class="form-actions">

                <button type="submit" class="admin-button">
                    SAVE CHANGES
                </button>

                <a href="landlords.php" class="cancel-button">
                    CANCEL
                </a>

            </div>

        </form>

    </div>

</main>

</body>

</html>
