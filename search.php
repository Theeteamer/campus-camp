<?php

require_once __DIR__ . "/includes/partial_response.php";
start_partial_response();

require_once __DIR__ . "/config/database.php";

$location = trim($_GET["location"] ?? "");
$house_type = trim($_GET["house_type"] ?? "");

$properties = [];



$sql = "
    SELECT
        p.id,
        p.name,
        p.location,
        p.house_type,
        p.price,
        p.payment_period,
        p.deposit,
        p.description,

        l.name AS landlord_name,

        r.available_rooms,
        r.status,

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

    WHERE 1 = 1
";


$params = [];
$types = "";


if ($location !== "") {

    $sql .= "
        AND p.location = ?
    ";

    $params[] = $location;
    $types .= "s";
}



if ($house_type !== "") {

    $sql .= "
        AND p.house_type = ?
    ";

    $params[] = $house_type;
    $types .= "s";
}


$sql .= "
    ORDER BY p.id DESC
";


$stmt = $conn->prepare($sql);



if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );
}


$stmt->execute();

$result = $stmt->get_result();


while ($row = $result->fetch_assoc()) {

    $properties[] = $row;

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

    <meta
        name="description"
        content="Student accommodation search results."
    >

    <title>Accommodation</title>

    <link
        rel="stylesheet"
        href="css/styles.css?v=<?= filemtime(__DIR__ . '/css/styles.css') ?>"
    >

</head>

<body>


<header class="site-header">

    <div class="container site-header-inner">

        <a
            href="index.php"
            class="site-logo"
        >
            <img class="brand-image" src="images/ofcampus-logo.png" alt="Campus-Camp®">
        </a>

    </div>

</header>


<main class="search-page">

 

    <section class="results-section">

        <div class="container">


            <div class="results-heading">

                <?php if ($location !== "" || $house_type !== ""): ?>

                    <h1>
                        Accommodation
                    </h1>

                    <p class="search-term-banner">

                        <?php if ($location !== ""): ?>

                            <?= htmlspecialchars($location) ?>

                        <?php endif; ?>


                        <?php if (
                            $location !== "" &&
                            $house_type !== ""
                        ): ?>

                            ·

                        <?php endif; ?>


                        <?php if ($house_type !== ""): ?>

                            <?= htmlspecialchars($house_type) ?>

                        <?php endif; ?>

                    </p>

                <?php else: ?>

                    <h1>
                        Available Accommodation
                    </h1>

                    <p class="search-term-banner">
                        All current listings
                    </p>

                <?php endif; ?>

                <a href="index.php" class="search-button-link" aria-label="Search" title="Search">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <span class="button-label">Search</span>
                </a>
            </div>


            <?php if (empty($properties)): ?>

                <div class="no-results">

                    <h2>
                        No accommodation found
                    </h2>

                    <p>
                        Try another location or house type.
                    </p>

                </div>


            <?php else: ?>


                <div class="property-list search-property-list">

                    <?php foreach ($properties as $property): ?>

                        <?php $available_rooms = max(0, (int)($property["available_rooms"] ?? 0)); ?>

                        <article class="property-result">

                            <div class="property-main">


                                <div class="property-information">

                                    <h2>
                                        <?= htmlspecialchars(
                                            $property["name"]
                                        ) ?>
                                        <?php if ($available_rooms === 0): ?>
                                            <span class="rented-out-label" role="status">&middot; Full</span>
                                        <?php endif; ?>
                                    </h2>


                                    <div class="property-location">
                                        <?= htmlspecialchars(
                                            $property["location"]
                                        ) ?>
                                    </div>


                                    <div class="property-type">

                                        <?= htmlspecialchars(
                                            $property["house_type"]
                                        ) ?>

                                    </div>


                                    <div class="property-price">

                                        KSh
                                        <strong><?= number_format(
                                            $property["price"],
                                            0
                                        ) ?></strong>

                                        <span>
                                            /
                                            <?= htmlspecialchars(
                                                $property["payment_period"]
                                            ) ?>
                                        </span>

                                    </div>


                                    <div class="property-availability">
                                        <strong><?= $available_rooms ?></strong>
                                        <?= $available_rooms === 1 ? "room" : "rooms" ?> available
                                    </div>

                                </div>

                            </div>


                            <div class="property-action">

                                <?php if ($available_rooms > 0): ?>
                                    <a
                                        href="property.php?id=<?= (int)$property["id"] ?>"
                                        class="go-button"
                                        aria-label="Details for <?= htmlspecialchars($property["name"], ENT_QUOTES, "UTF-8") ?>"
                                        title="Details"
                                    >
                                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                                        <span class="button-label">Details</span>
                                    </a>

                                    <a
                                        href="property_pictures.php?<?= htmlspecialchars(http_build_query([
                                            "id" => (int)$property["id"],
                                            "return_to" => "search",
                                            "return_location" => $location,
                                            "return_house_type" => $house_type
                                        ]), ENT_QUOTES, "UTF-8") ?>"
                                        class="browse-pictures-button"
                                        aria-label="View pictures of <?= htmlspecialchars($property["name"], ENT_QUOTES, "UTF-8") ?>"
                                        title="View pictures"
                                    >
                                        <i class="fa fa-picture-o" aria-hidden="true"></i>
                                        <span class="button-label">Pictures</span>
                                    </a>
                                <?php else: ?>
                                    <span class="go-button is-disabled" role="img" aria-label="Details unavailable" title="Details unavailable"><i class="fa fa-info-circle" aria-hidden="true"></i><span class="button-label">Details</span></span>
                                    <span class="browse-pictures-button is-disabled" role="img" aria-label="Pictures unavailable" title="Pictures unavailable"><i class="fa fa-picture-o" aria-hidden="true"></i><span class="button-label">Pictures</span></span>
                                <?php endif; ?>

                            </div>


                        </article>


                    <?php endforeach; ?>

                </div>


            <?php endif; ?>


        </div>

    </section>


</main>


<footer class="site-footer">

    <div class="container">
        <div class="site-footer-legal" aria-label="Legal notice">
            <span class="footer-legal-line"><span class="footer-asterisk">*</span> By using this website, you agree to our</span>
            <span class="footer-legal-line"><span class="footer-asterisk">*</span> <a href="terms.php" class="footer-link">Terms &amp; Conditions</a> and <a href="privacy.php" class="footer-link">Privacy Policy</a></span>
        </div>
    </div>

</footer>

<script>
const locationSelect = document.getElementById("location");
const houseTypeSelect = document.getElementById("house_type");
const allHouseTypes = <?= json_encode(
    $house_types,
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
) ?>;
const selectedHouseType = <?= json_encode(
    $house_type,
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
) ?>;

function setHouseTypeOptions(types, selectedType) {
    houseTypeSelect.innerHTML = "";

    const anyOption = document.createElement("option");
    anyOption.value = "";
    anyOption.textContent = "Any type";
    houseTypeSelect.appendChild(anyOption);

    types.forEach(function (type) {
        const option = document.createElement("option");
        option.value = type;
        option.textContent = type;
        houseTypeSelect.appendChild(option);
    });

    if (types.includes(selectedType)) {
        houseTypeSelect.value = selectedType;
    }
}

function loadHouseTypes(location, selectedType) {
    if (location === "") {
        setHouseTypeOptions(allHouseTypes, selectedType);
        return;
    }

    houseTypeSelect.innerHTML = "";
    const loadingOption = document.createElement("option");
    loadingOption.value = "";
    loadingOption.textContent = "Loading...";
    loadingOption.disabled = true;
    houseTypeSelect.appendChild(loadingOption);

    fetch("get_house_types.php?location=" + encodeURIComponent(location))
        .then(function (response) {
            if (!response.ok) {
                throw new Error("Network response was not OK");
            }
            return response.json();
        })
        .then(function (types) {
            setHouseTypeOptions(types, selectedType);
        })
        .catch(function (error) {
            console.error("House type loading error:", error);
            houseTypeSelect.innerHTML = "";
            const errorOption = document.createElement("option");
            errorOption.value = "";
            errorOption.textContent = "Unable to load types";
            errorOption.disabled = true;
            houseTypeSelect.appendChild(errorOption);
        });
}

locationSelect.addEventListener("change", function () {
    loadHouseTypes(this.value, "");
});

loadHouseTypes(locationSelect.value, selectedHouseType);
</script>

<script src="js/main.js?v=<?= filemtime(__DIR__ . '/js/main.js') ?>"></script>
</body>

</html>