<?php

require_once __DIR__ . "/config/database.php";

$location = trim($_GET["location"] ?? "");
$house_type = trim($_GET["house_type"] ?? "");

$properties = [];


/*
|--------------------------------------------------------------------------
| Build search
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Location filter
|--------------------------------------------------------------------------
*/

if ($location !== "") {

    $sql .= "
        AND p.location = ?
    ";

    $params[] = $location;
    $types .= "s";
}


/*
|--------------------------------------------------------------------------
| House type filter
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Bind dynamic parameters
|--------------------------------------------------------------------------
*/

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

    <title>Accommodation - Campus-Camp®</title>

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


<main>


    <!-- SEARCH BAR -->

    <section class="results-search">

        <div class="container">

            <form
                action="search.php"
                method="GET"
                class="results-search-form"
            >

                <div>

                    <label for="location">
                        Location
                    </label>

                    <input
                        type="text"
                        name="location"
                        id="location"
                        value="<?= htmlspecialchars($location) ?>"
                        placeholder="Any location"
                    >

                </div>


                <div>

                    <label for="house_type">
                        House Type
                    </label>

                    <div class="select-wrap">
                        <select
                            name="house_type"
                            id="house_type"
                        >

                            <option value="">
                                Any type
                            </option>

                        <option value="Single Room"
                            <?= $house_type === "Single Room" ? "selected" : "" ?>>
                            Single Room
                        </option>

                        <option value="Bedsitter"
                            <?= $house_type === "Bedsitter" ? "selected" : "" ?>>
                            Bedsitter
                        </option>

                        <option value="One Bedroom"
                            <?= $house_type === "One Bedroom" ? "selected" : "" ?>>
                            One Bedroom
                        </option>

                        <option value="Two Bedroom"
                            <?= $house_type === "Two Bedroom" ? "selected" : "" ?>>
                            Two Bedroom
                        </option>

                        </select>
                    </div>

                </div>


                <button
                    type="submit"
                    class="search-button"
                >
                    SEARCH
                </button>

            </form>

        </div>

    </section>


    <!-- RESULTS -->

    <section class="results-section">

        <div class="container">


            <div class="results-heading">

                <?php if ($location !== "" || $house_type !== ""): ?>

                    <h1>
                        Accommodation
                    </h1>

                    <p>

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

                    <p>
                        All current listings
                    </p>

                <?php endif; ?>

            </div>


            <?php if (empty($properties)): ?>

                <div class="no-results">

                    <h2>
                        No accommodation found
                    </h2>

                    <p>
                        Try another location or house type.
                    </p>

                    <a
                        href="index.php"
                        class="search-button-link"
                    >
                        New Search
                    </a>

                </div>


            <?php else: ?>


                <div class="property-list">

                    <?php foreach ($properties as $property): ?>


                        <article class="property-result">


                            <div class="property-main">


                                <div class="property-information">

                                    <h2>
                                        <?= htmlspecialchars(
                                            $property["name"]
                                        ) ?>
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

                                        <?php if (
                                            (int)$property["available_rooms"] > 0
                                        ): ?>

                                            <strong><?= (int)$property[
                                                "available_rooms"
                                            ] ?></strong>

                                            rooms available

                                        <?php else: ?>

                                            Fully occupied

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </div>


                            <div class="property-action">

                                <a
                                    href="property.php?id=<?= (int)$property["id"] ?>"
                                    class="go-button"
                                >
                                    GO NOW
                                </a>

                                <a
                                    href="property_pictures.php?id=<?= (int)$property["id"] ?>"
                                    class="browse-pictures-button"
                                >
                                    BROWSE PICTURES
                                </a>

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


</body>

</html>