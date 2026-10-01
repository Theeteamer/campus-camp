<?php

require_once __DIR__ . "/config/database.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

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
        l.phone AS landlord_phone,
        l.location AS landlord_location,

        r.room_type,
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

    WHERE p.id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

$property = $result->fetch_assoc();

$stmt->close();

if (!$property) {
    header("Location: index.php");
    exit;
}

$phone = $property['landlord_phone'];

/*
 * Remove spaces and other characters for WhatsApp.
 * This assumes a Kenyan phone number such as 0712345678.
 */
$whatsapp = preg_replace('/[^0-9]/', '', $phone);

if (substr($whatsapp, 0, 1) === '0') {
    $whatsapp = '254' . substr($whatsapp, 1);
}

$whatsapp_message = urlencode(
    "Hello, I found your accommodation listing for " .
    $property['name'] .
    " on Campus-Camp®. I am interested in the accommodation."
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($property['name']) ?> - Campus-Camp®
    </title>

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

    <section class="property-section">

        <div class="container">

            <div class="property-top-actions">
                <a
                    href="results.php?location=<?= urlencode($property['location']) ?>&house_type=<?= urlencode($property['house_type']) ?>"
                    class="back-link"
                >
                    Back to results
                </a>
                <a href="index.php" class="search-button-link">
                    New Search
                </a>
            </div>


            <!-- PROPERTY INFORMATION -->

            <div class="property-page">

                <div class="property-heading">

                    <h1>
                        <?= htmlspecialchars($property['name']) ?>
                    </h1>

                    <p>
                        <?= htmlspecialchars($property['location']) ?>
                    </p>

                </div>


                <div class="property-overview-table">

                    <div class="overview-row">

                        <div class="overview-header">
                            House Type
                        </div>

                        <div class="overview-value">
                            <?= htmlspecialchars($property['house_type']) ?>
                        </div>

                        <div class="overview-header">
                            Deposit
                        </div>

                        <div class="overview-value">

                            <?php if ($property['deposit'] !== null): ?>

                                KSh <strong><?= number_format($property['deposit'], 0) ?></strong>

                            <?php else: ?>

                                Not listed

                            <?php endif; ?>

                        </div>

                    </div>

                    <div class="overview-row">

                        <div class="overview-header">
                            Price
                        </div>

                        <div class="overview-value">
                            KSh <strong><?= number_format($property['price'], 0) ?></strong>
                            /
                            <?= htmlspecialchars($property['payment_period']) ?>
                        </div>

                        <div class="overview-header">
                            Availability
                        </div>

                        <div class="overview-value">

                            <?php if ((int)$property['available_rooms'] > 0): ?>

                                <strong><?= (int)$property['available_rooms'] ?></strong>
                                room(s) available

                            <?php else: ?>

                                Currently full

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


                <!-- FACILITIES -->

                <div class="property-block">

                    <h2>Facilities</h2>

                    <div class="facilities-list">

                        <span>
                            <?= $property['water'] ? 'Water available' : 'No water listed' ?>
                        </span>

                        <span>
                            <?= $property['electricity'] ? 'Electricity available' : 'No electricity listed' ?>
                        </span>

                        <span>
                            <?= $property['wifi'] ? 'Wi-Fi available' : 'Wi-Fi not listed' ?>
                        </span>

                        <span>
                            <?= $property['security'] ? 'Security available' : 'Security not listed' ?>
                        </span>

                    </div>

                </div>


                <!-- DESCRIPTION -->

                <?php if (!empty($property['description'])): ?>

                    <div class="property-block">

                        <h2>About the accommodation</h2>

                        <p class="full-description">

                            <?= nl2br(htmlspecialchars($property['description'])) ?>

                        </p>

                    </div>

                <?php endif; ?>


                <!-- LANDLORD -->

                <div class="landlord-box">

                    <h2>Landlord Details</h2>

                    <div class="landlord-details">

                        <div>

                            <span class="detail-label">
                                Name
                            </span>

                            <span>
                                <?= htmlspecialchars($property['landlord_name']) ?>
                            </span>

                        </div>


                        <div>

                            <span class="detail-label">
                                Phone
                            </span>

                            <span>
                                <?= htmlspecialchars($property['landlord_phone']) ?>
                            </span>

                        </div>


                        <div>

                            <span class="detail-label">
                                Location
                            </span>

                            <span>
                                <?= htmlspecialchars($property['landlord_location']) ?>
                            </span>

                        </div>

                    </div>


                    <div class="contact-buttons">

                        <a
                            href="tel:<?= htmlspecialchars($phone) ?>"
                            class="call-button"
                        >
                            Call Landlord
                        </a>


                        <a
                            href="https://wa.me/<?= htmlspecialchars($whatsapp) ?>?text=<?= $whatsapp_message ?>"
                            class="whatsapp-button"
                            target="_blank"
                            rel="noopener"
                        >
                            WhatsApp
                        </a>

                        <a
    href="property_pictures.php?id=<?= (int)$property["id"] ?>"
    class="browse-pictures-button"
>
    Browse Pictures
</a>

                    </div>

                </div>

            </div>

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