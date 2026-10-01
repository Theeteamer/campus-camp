<?php
require_once __DIR__ . "/config/database.php";

$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$house_type = isset($_GET['house_type']) ? trim($_GET['house_type']) : '';

$properties = [];

if ($location !== '' && $house_type !== '') {

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
            r.available_rooms,
            f.water,
            f.electricity,
            f.wifi,
            f.security
        FROM properties p

        LEFT JOIN rooms r
            ON p.id = r.property_id

        LEFT JOIN facilities f
            ON p.id = f.property_id

        WHERE p.location = ?
        AND p.house_type = ?
        AND r.available_rooms > 0

        ORDER BY p.price ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $location, $house_type);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Accommodation Results - Campus-Camp®</title>

    <link rel="stylesheet" href="css/styles.css?v=<?= filemtime(__DIR__ . '/css/styles.css') ?>">

</head>

<body>

<header class="site-header">

    <div class="container header-inner">

        <a href="index.php" class="logo">
            <img class="brand-image" src="images/ofcampus-logo.png" alt="Campus-Camp®">
        </a>

    </div>

</header>


<main>

    <section class="results-section">

        <div class="container">

            <div class="results-header">

                <div>

                    <h1>Accommodation Results</h1>

                    <p>
                        <?= htmlspecialchars($location) ?>
                        —
                        <?= htmlspecialchars($house_type) ?>
                    </p>

                </div>

                <a href="index.php" class="back-button">
                    New Search
                </a>

            </div>


            <?php if (empty($properties)): ?>

                <div class="no-results">

                    <h2>No accommodation found</h2>

                    <p>
                        There are currently no available
                        <?= htmlspecialchars($house_type) ?>
                        rooms in
                        <?= htmlspecialchars($location) ?>.
                    </p>

                    <a href="index.php" class="search-button-link">
                        Try Another Search
                    </a>

                </div>

            <?php else: ?>

                <p class="result-count">
                    <strong><?= count($properties) ?></strong> accommodation option(s) found
                </p>


                <div class="property-list">

                    <?php foreach ($properties as $property): ?>

                        <article class="property-card">

                            <div class="property-main">

                                <h2>
                                    <?= htmlspecialchars($property['name']) ?>
                                </h2>

                                <div class="property-info">

                                    <span>
                                        <?= htmlspecialchars($property['location']) ?>
                                    </span>

                                    <span>
                                        <?= htmlspecialchars($property['house_type']) ?>
                                    </span>

                                    <span>
                                        KSh <strong><?= number_format($property['price'], 0) ?></strong>
                                        /
                                        <?= htmlspecialchars($property['payment_period']) ?>
                                    </span>

                                </div>


                                <div class="property-details">

                                    <span>
                                        <strong><?= (int)$property['available_rooms'] ?></strong>
                                        room(s) available
                                    </span>

                                    <span>
                                        <?= $property['water'] ? 'Water' : 'No Water' ?>
                                    </span>

                                    <span>
                                        <?= $property['electricity'] ? 'Electricity' : 'No Electricity' ?>
                                    </span>

                                    <span>
                                        <?= $property['wifi'] ? 'Wi-Fi' : 'No Wi-Fi' ?>
                                    </span>

                                    <span>
                                        <?= $property['security'] ? 'Security' : 'No Security' ?>
                                    </span>

                                </div>


                                <?php if (!empty($property['description'])): ?>

                                    <p class="property-description">

                                        <?= htmlspecialchars($property['description']) ?>

                                    </p>

                                <?php endif; ?>

                            </div>


                            <div class="property-action">

                                <a
                                    href="property.php?id=<?= (int)$property['id'] ?>"
                                    class="go-button"
                                >
                                    GO NOW
                                </a>

                                <a
                                    href="property_pictures.php?id=<?= (int)$property['id'] ?>"
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