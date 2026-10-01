<?php

require_once __DIR__ . "/config/database.php";

$property_id = (int)($_GET["id"] ?? 0);

if ($property_id <= 0) {
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get property
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Get pictures
|--------------------------------------------------------------------------
*/

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

    <style>

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0;
        }

        body {
            background: #000;
            color: #fff;
            font-family: Arial, sans-serif;
            overflow: hidden;
        }


        .gallery {

            width: 100%;
            height: 100vh;

            display: flex;
            flex-direction: column;

        }


        .gallery-header {

            height: 60px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 25px;

            background: #111;

        }


        .gallery-title {

            font-size: 16px;
            font-weight: bold;

        }


        .gallery-location {

            margin-top: 3px;

            color: #aaa;

            font-size: 12px;

        }


        .return-button {

            display: inline-flex;
            min-height: 34px;
            align-items: center;
            justify-content: center;
            padding: 7px 12px;

            border: 1px solid #1d4d3b;

            background: #d9f1d5;

            color: #1d4d3b;

            text-decoration: none;

            font-family: "Lato", Arial, sans-serif;
            font-size: 14px;

            font-weight: 800;

            line-height: 1;

            transition: color 160ms ease, background-color 160ms ease, border-color 160ms ease;

        }


        .return-button:hover,
        .return-button:focus-visible {

            background: #1d4d3b;

            color: #ffffff;

        }


        .gallery-main {

            position: relative;

            flex: 1;

            display: flex;

            align-items: center;

            justify-content: center;

            overflow: hidden;

            padding: 28px 80px 40px;

        }


        .gallery-image {

            width: 100%;
            max-width: 100%;
            height: 100%;
            max-height: 100%;

            object-fit: contain;

            opacity: 1;

            border-radius: 22px;

            box-shadow: 0 18px 42px rgba(0, 0, 0, 0.32);

            background: rgba(255, 255, 255, 0.02);

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

            position: absolute;

            top: 50%;

            transform: translateY(-50%);

            width: 48px;

            height: 48px;

            border: 1px solid #1d4d3b;
            border-radius: 50%;

            background: #d9f1d5;

            color: #1d4d3b;

            font-size: 30px;

            line-height: 1;

            cursor: pointer;

            box-shadow: 0 10px 18px rgba(0, 0, 0, 0.22);

            transition: color 160ms ease, background-color 160ms ease, border-color 160ms ease;

            display: inline-flex;
            align-items: center;
            justify-content: center;

        }


        .gallery-button:hover,
        .gallery-button:focus-visible {

            border-color: #1d4d3b;
            background: #1d4d3b;
            color: #ffffff;

        }


        .previous {

            left: 22px;

        }


        .next {

            right: 22px;

        }


        .gallery-counter {

            position: absolute;

            bottom: 20px;

            left: 50%;

            transform: translateX(-50%);

            padding: 7px 12px;

            background: rgba(0, 0, 0, 0.65);

            font-size: 12px;

        }


        .no-images {

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #aaa;
            min-height: 220px;
            max-width: 540px;
            padding: 24px;

        }


        .no-images strong {

            display: block;

            margin-bottom: 8px;

            color: #fff;

            font-size: 20px;

        }

    </style>

</head>


<body>


<div class="gallery">


    <!-- HEADER -->

    <header class="gallery-header">

        <div>

            <div class="gallery-title">

                <?= htmlspecialchars($property["name"]) ?>

            </div>

            <div class="gallery-location">

                <?= htmlspecialchars($property["location"]) ?>

                ·

                <?= htmlspecialchars($property["house_type"]) ?>

            </div>

        </div>


        <a
            href="property.php?id=<?= $property_id ?>"
            class="return-button"
        >
            RETURN
        </a>

    </header>



    <!-- GALLERY -->

    <main class="gallery-main">


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
                    onclick="previousImage()"
                >
                    ‹
                </button>


                <button
                    type="button"
                    class="gallery-button next"
                    onclick="nextImage()"
                >
                    ›
                </button>


                <div class="gallery-counter">

                    <span id="currentNumber">
                        1
                    </span>

                    /

                    <?= count($images) ?>

                </div>


            <?php endif; ?>


        <?php endif; ?>


    </main>


</div>


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


/*
|--------------------------------------------------------------------------
| Keyboard controls
|--------------------------------------------------------------------------
*/

document.addEventListener(
    "keydown",
    function (event) {

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


</body>

</html>