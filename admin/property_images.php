<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}


$property_id = (int)($_GET["id"] ?? 0);

if ($property_id <= 0) {
    header("Location: properties.php");
    exit;
}


$stmt = $conn->prepare("
    SELECT
        id,
        name,
        location
    FROM properties
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $property_id);

$stmt->execute();

$result = $stmt->get_result();

$property = $result->fetch_assoc();

$stmt->close();


if (!$property) {
    header("Location: properties.php");
    exit;
}


$message = "";
$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {


    if (
        !isset($_FILES["images"]) ||
        empty($_FILES["images"]["name"][0])
    ) {

        $error = "Please select at least one image.";

    } else {


        $upload_directory =
            __DIR__ .
            "/../uploads/properties/" .
            $property_id .
            "/";


        $database_directory =
            "uploads/properties/" .
            $property_id .
            "/";


        if (!is_dir($upload_directory)) {

            mkdir(
                $upload_directory,
                0755,
                true
            );

        }


        $allowed_types = [
            "image/jpeg",
            "image/png",
            "image/webp"
        ];


        $total =
            count($_FILES["images"]["name"]);


        for ($i = 0; $i < $total; $i++) {


            if (
                $_FILES["images"]["error"][$i]
                !== UPLOAD_ERR_OK
            ) {
                continue;
            }


            $tmp_name =
                $_FILES["images"]["tmp_name"][$i];


            $file_type =
                mime_content_type($tmp_name);


            if (
                !in_array(
                    $file_type,
                    $allowed_types,
                    true
                )
            ) {
                continue;
            }


            $extension = match ($file_type) {

                "image/jpeg" => "jpg",

                "image/png" => "png",

                "image/webp" => "webp",

                default => "jpg"

            };


            $filename =
                uniqid("property_", true)
                . "."
                . $extension;


            $destination =
                $upload_directory .
                $filename;


            if (
                move_uploaded_file(
                    $tmp_name,
                    $destination
                )
            ) {

                $image_path =
                    $database_directory .
                    $filename;


                $stmt = $conn->prepare("
                    INSERT INTO property_images
                    (
                        property_id,
                        image_path
                    )
                    VALUES
                    (?, ?)
                ");


                $stmt->bind_param(
                    "is",
                    $property_id,
                    $image_path
                );


                $stmt->execute();

                $stmt->close();

            }

        }


        $message =
            "Images uploaded successfully.";

    }

}

if (
    isset($_GET["delete"])
) {

    $image_id =
        (int)$_GET["delete"];


    $stmt = $conn->prepare("
        SELECT image_path
        FROM property_images
        WHERE id = ?
          AND property_id = ?
        LIMIT 1
    ");


    $stmt->bind_param(
        "ii",
        $image_id,
        $property_id
    );


    $stmt->execute();

    $result =
        $stmt->get_result();

    $image =
        $result->fetch_assoc();

    $stmt->close();


    if ($image) {


        $file =
            __DIR__ .
            "/../" .
            $image["image_path"];


        if (file_exists($file)) {

            unlink($file);

        }


        $stmt = $conn->prepare("
            DELETE FROM property_images
            WHERE id = ?
              AND property_id = ?
        ");


        $stmt->bind_param(
            "ii",
            $image_id,
            $property_id
        );


        $stmt->execute();

        $stmt->close();


        header(
            "Location: property_images.php?id="
            . $property_id
        );

        exit;

    }

}
$stmt = $conn->prepare("
    SELECT
        id,
        image_path
    FROM property_images
    WHERE property_id = ?
    ORDER BY id ASC
");

$stmt->bind_param(
    "i",
    $property_id
);

$stmt->execute();

$result =
    $stmt->get_result();

$images = [];

while ($row = $result->fetch_assoc()) {

    $images[] = $row;

}

$stmt->close();

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
        Property Images
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
            <a href="edit_property.php?id=<?= $property_id ?>" class="cancel-button" aria-label="Back to property" title="Back to property">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                <span class="button-label">Back</span>
            </a>
        </div>
    </div>

    <div class="container">


        <div class="admin-page-title">

            <div>

                <h1>
                    These are the Images by the Accommodation Owner for this property.
                </h1>

                <p>

                    <?= htmlspecialchars(
                        $property["name"]
                    ) ?>

                    ·

                    <?= htmlspecialchars(
                        $property["location"]
                    ) ?>

                </p>

            </div>

        </div>


        <?php if ($message): ?>

            <div class="success-message admin-error">

                <?= htmlspecialchars($message) ?>

            </div>

        <?php endif; ?>


        <?php if ($error): ?>

            <div class="error-message admin-error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            enctype="multipart/form-data"
            class="admin-form admin-image-form"
        >

            <div class="form-section form-section-divider">

                <h2>
                    Upload New Images
                </h2>

                <label for="images">
                    Select image files
                </label>

                <input
                    type="file"
                    id="images"
                    name="images[]"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    class="admin-file-input"
                    required
                >

                <div class="form-actions">
                    <button
                        type="submit"
                        class="admin-button"
                        aria-label="Upload images"
                        title="Upload images"
                    >
                        <i class="fa fa-upload" aria-hidden="true"></i>
                        <span class="button-label">Upload Images</span>
                    </button>
                </div>

            </div>

        </form>


        <?php if (!empty($images)): ?>

            <div class="admin-gallery-block">

                <h2>
                    Current Pictures
                </h2>

                <div class="image-admin-grid">

                    <?php foreach ($images as $image): ?>

                        <div class="image-admin-item">

                            <img
                                src="../<?= htmlspecialchars(
                                    $image["image_path"]
                                ) ?>"
                                alt="Property picture"
                            >

                            <a
                                href="property_images.php?id=<?= $property_id ?>&delete=<?= (int)$image["id"] ?>"
                                data-confirm-message="This action cannot be undone. Delete this picture?"
                                class="delete-image-link"
                                aria-label="Delete image"
                                title="Delete image"
                            >
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </a>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        <?php else: ?>

            <div class="no-results">
                <h2>
                    No pictures have been added yet.
                </h2>
                <p>
                    Add your first property image above.
                </p>
            </div>

        <?php endif; ?>

    </div>

</main>

<?php include __DIR__ . "/footer.php"; ?>
<script src="../js/main.js?v=<?= filemtime(__DIR__ . '/../js/main.js') ?>"></script>
</body>

</html>