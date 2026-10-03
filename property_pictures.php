<?php

require_once __DIR__ . "/includes/partial_response.php";
start_partial_response();

require_once __DIR__ . "/config/database.php";

$property_id = (int)($_GET["id"] ?? 0);
$return_to = $_GET["return_to"] ?? "";
$return_location = $_GET["return_location"] ?? "";
$return_house_type = $_GET["return_house_type"] ?? "";

if ($property_id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        location,
        house_type
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
    header("Location: index.php");
    exit;
}

$return_url = "property.php?id=" . $property_id;

if (
    is_string($return_to) &&
    is_string($return_location) &&
    is_string($return_house_type)
) {
    if ($return_to === "search") {
        $return_url = "search.php?" . http_build_query([
            "location" => $return_location,
            "house_type" => $return_house_type
        ]);
    } elseif ($return_to === "results") {
        $return_url = "results.php?" . http_build_query([
            "location" => $return_location,
            "house_type" => $return_house_type
        ]);
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

$stmt->bind_param("i", $property_id);

$stmt->execute();

$result = $stmt->get_result();

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
        Pictures - <?= htmlspecialchars($property["name"]) ?>
    </title>

    <link rel="stylesheet" href="css/styles.css?v=<?= filemtime(__DIR__ . '/css/styles.css') ?>">

    <style>

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
            margin: 0;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin-inline: auto;
            overflow: auto;
            background: var(--surface);
            color: var(--ink);
            font-family: "Lato", Arial, sans-serif;
        }


        .gallery-page {
            display: flex;
            flex: 0 0 auto;
            flex-direction: column;
            min-height: 0;
            background: #ffffff;
        }


        .gallery {

            width: min(800px, 100%);
            height: auto;
            max-height: 600px;
            flex: 0 1 600px;
            min-height: 0;
            margin: 0 auto;

            display: flex;
            flex-direction: column;
            background: #ffffff;

        }


        .gallery-return {
            position: absolute;
            top: 16px;
            left: 16px;
            z-index: 1;
            margin-bottom: 0;
        }


        .gallery-main {

            position: relative;

            flex: 1;
            min-height: 0;

            display: grid;
            grid-template-columns: minmax(38px, 1fr) minmax(0, auto) minmax(38px, 1fr);
            grid-template-rows: minmax(0, 1fr);
            align-items: center;
            justify-items: center;
            column-gap: 8px;

            overflow: hidden;

            padding: 16px;
            background: #ffffff;

        }


        .gallery-image {

            grid-column: 2;
            grid-row: 1;
            min-width: 0;
            min-height: 0;
            width: auto;
            height: auto;
            max-width: 100%;
            max-height: 100%;

            object-fit: contain;

            opacity: 1;

            border-radius: 0;

        }


        .gallery-image.fade-out {

            animation: fadeOut 0.38s ease forwards;

        }


        .gallery-image.fade-in {

            animation: fadeIn 0.42s ease forwards;

        }


        @keyframes fadeOut {

            from {
                opacity: 1;
                transform: scale(1);
            }

            to {
                opacity: 0;
                transform: scale(0.985);
            }

        }


        @keyframes fadeIn {

            from {
                opacity: 0;
                transform: scale(0.985);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }

        }


        .gallery-button {

            grid-row: 1;
            width: 38px;
            height: 34px;
            min-height: 34px;
            padding: 0;

            border: 1px solid #1d4d3b;
            border-radius: 0;

            background: #d9f1d5;

            color: #000000;

            font-size: 22px;
            font-weight: 800;

            line-height: 1;

            cursor: pointer;

            transition: color 160ms ease, background-color 160ms ease, border-color 160ms ease;

            display: inline-flex;
            align-items: center;
            justify-content: center;

        }


        .gallery-button.previous {
            grid-column: 1;
            justify-self: end;
        }


        .gallery-button.next {
            grid-column: 3;
            justify-self: start;
        }


        .gallery-button:hover,
        .gallery-button:focus-visible,
        .gallery-button:active {

            border-color: #1d4d3b;
            background: #add8e6;
            color: #000000;

        }


        .gallery-controls {

            position: relative;
            min-height: 54px;

            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;

            padding: 8px 20px;

            color: #000000;
            background: #f5f5f5;
            border-top: 1px solid #d7d7d7;

        }


        .gallery-counter {

            min-width: 46px;
            color: #000000;
            font-size: 14px;
            font-weight: 800;
            text-align: center;

        }


        .no-images {

            grid-column: 1 / -1;
            grid-row: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #555555;
            min-height: 0;
            max-width: 540px;
            padding: 24px;

        }


        .no-images strong {

            display: block;

            margin-bottom: 8px;

            color: #000000;

            font-size: 20px;

        }

    </style>

</head>


<body>

<header class="site-header">
    <div class="container site-header-inner">
        <a href="index.php" class="site-logo">
            <img class="brand-image" src="images/ofcampus-logo.png" alt="Campus-Camp&reg;">
        </a>
    </div>
</header>

<main class="gallery-page">

<div class="gallery">

    <!-- GALLERY -->

    <section
        class="gallery-main"
        aria-label="Property photo viewer"
        data-gallery-images="<?= htmlspecialchars(json_encode(array_column($images, "image_path"), JSON_UNESCAPED_SLASHES), ENT_QUOTES, "UTF-8") ?>"
    >

        <a
            href="<?= htmlspecialchars($return_url, ENT_QUOTES, "UTF-8") ?>"
            class="back-link gallery-return"
            aria-label="Return to listing"
            title="Return to listing"
        >
            <i class="fa fa-arrow-left" aria-hidden="true"></i>
        </a>

        <?php if (empty($images)): ?>


            <div class="no-images">

                <strong>
                    Pictures not Available for this listing
                </strong>

            </div>


        <?php else: ?>


            <img
                id="galleryImage"
                class="gallery-image"
                src="<?= htmlspecialchars($images[0]["image_path"]) ?>"
                alt="<?= htmlspecialchars($property["name"]) ?>"
            >

            <?php if (count($images) > 1): ?>
                <button
                    type="button"
                    class="gallery-button previous"
                    data-gallery-action="previous"
                    aria-label="Previous picture"
                    title="Previous picture"
                ><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
                <button
                    type="button"
                    class="gallery-button next"
                    data-gallery-action="next"
                    aria-label="Next picture"
                    title="Next picture"
                ><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
            <?php endif; ?>


        <?php endif; ?>


    </section>

    <div class="gallery-controls" aria-label="Picture navigation">
        <?php if (!empty($images)): ?>
            <div class="gallery-counter" aria-live="polite">
                <span id="currentNumber">1</span> / <?= count($images) ?>
            </div>
        <?php endif; ?>

    </div>


</div>

</main>

<footer class="site-footer">
    <div class="container">
        <div class="site-footer-legal" aria-label="Legal notice">
            <span class="footer-legal-line"><span class="footer-asterisk">*</span> By using this website, you agree to our</span>
            <span class="footer-legal-line"><span class="footer-asterisk">*</span> <a href="terms.php" class="footer-link">Terms &amp; Conditions</a> and <a href="privacy.php" class="footer-link">Privacy Policy</a></span>
        </div>
    </div>
</footer>


<?php if (!empty($images)): ?>

<script>

const images = <?= json_encode(
    array_column($images, "image_path"),
    JSON_UNESCAPED_SLASHES
) ?>;


let currentIndex = 0;


const galleryImage =
    document.getElementById("galleryImage");

const currentNumber =
    document.getElementById("currentNumber");


function showImage(index) {

    galleryImage.classList.remove("fade-in");

    galleryImage.classList.add("fade-out");


    setTimeout(function () {

        galleryImage.src = images[index];

        currentIndex = index;


        if (currentNumber) {

            currentNumber.textContent =
                currentIndex + 1;

        }


        galleryImage.classList.remove("fade-out");

        galleryImage.classList.add("fade-in");

    }, 250);

}


function nextImage() {

    const nextIndex =
        (currentIndex + 1) % images.length;

    showImage(nextIndex);

}


function previousImage() {

    const previousIndex =
        (currentIndex - 1 + images.length)
        % images.length;

    showImage(previousIndex);

}



document.addEventListener(
    "keydown",
    function (event) {
        if (!galleryImage.isConnected) {
            return;
        }

        if (event.key === "ArrowRight") {

            nextImage();

        }

        if (event.key === "ArrowLeft") {

            previousImage();

        }

        if (event.key === "Escape") {

            window.location.href =
                "property.php?id=<?= $property_id ?>";

        }

    }
);

</script>

<?php endif; ?>


<script src="js/main.js?v=<?= filemtime(__DIR__ . '/js/main.js') ?>"></script>
</body>

</html>