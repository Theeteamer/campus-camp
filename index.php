<?php

require_once __DIR__ . "/includes/partial_response.php";
start_partial_response();

require_once __DIR__ . "/config/database.php";




$locations = [];

$result = $conn->query("
    SELECT DISTINCT location
    FROM properties
    WHERE location IS NOT NULL
      AND location != ''
    ORDER BY location ASC
");

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $locations[] = $row["location"];

    }

}




$house_types = [];

$result = $conn->query("
    SELECT DISTINCT house_type
    FROM properties
    WHERE house_type IS NOT NULL
      AND house_type != ''
    ORDER BY house_type ASC
");

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $house_types[] = $row["house_type"];

    }

}

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
        content="Find student accommodation near your campus."
    >

    <title>
        Campus-Camp®
    </title>

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


<main class="home-page">

    <section class="search-section">

        <div class="container">


            <h1>
                Find a place to stay.
            </h1>


            <p class="search-subtitle">
                Where do you want your next stay to be?
            </p>


            <!-- SEARCH FORM -->

            <form
                action="search.php"
                method="GET"
                class="search-form"
            >


                <!-- LOCATION -->

                <div class="search-field">

                    <label for="location">
                        Location
                    </label>


                    <div class="select-wrap">
                        <select
                            name="location"
                            id="location"
                        >

                            <option value="">
                                Any location
                            </option>


                        <?php foreach ($locations as $location): ?>

                            <option
                                value="<?= htmlspecialchars($location) ?>"
                            >

                                <?= htmlspecialchars($location) ?>

                            </option>

                        <?php endforeach; ?>


                    </select>

                </div>

                </div>



                <!-- HOUSE TYPE -->

                <div class="search-field">

                    <label for="house_type">
                        Type of house
                    </label>


                    <div class="select-wrap">
                        <select
                            name="house_type"
                            id="house_type"
                            data-all-types="<?= htmlspecialchars(json_encode($house_types, JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") ?>"
                        >

                            <option value="">
                                Any type
                            </option>


                        <?php foreach ($house_types as $type): ?>

                            <option
                                value="<?= htmlspecialchars($type) ?>"
                            >

                                <?= htmlspecialchars($type) ?>

                            </option>

                        <?php endforeach; ?>


                    </select>

                </div>

                </div>



                <!-- SEARCH BUTTON -->

                <button
                    type="submit"
                    class="search-button"
                    aria-label="Search"
                    title="Search"
                >
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <span class="button-label">Search</span>
                </button>


            </form>

            <div class="provider-prompt">
                <p class="provider-question"><em>Are you an Accommodation Provider?</em></p>
                <p class="provider-copy">
                    Make listings with <span class="brand-word">Campus-Camp</span><span class="brand-reg">&reg;</span>
                </p>
                <a href="provider_rules.php" class="search-button-link" aria-label="List a property" title="List a property">
                    <i class="fa fa-home" aria-hidden="true"></i>
                    <span class="button-label">List</span>
                </a>
            </div>


        </div>

    </section>



</main>



<!-- =========================================================
     FOOTER
     ========================================================= -->

<footer class="site-footer">

    <div class="container">
        <div class="site-footer-legal" aria-label="Legal notice">
            <span class="footer-legal-line"><span class="footer-asterisk">*</span> By using this website, you agree to our</span>
            <span class="footer-legal-line"><span class="footer-asterisk">*</span> <a href="terms.php" class="footer-link">Terms &amp; Conditions</a> and <a href="privacy.php" class="footer-link">Privacy Policy</a></span>
        </div>
    </div>

</footer>



<!-- =========================================================
     CASCADING HOUSE TYPE
     ========================================================= -->

<script>

const locationSelect =
    document.getElementById("location");

const houseTypeSelect =
    document.getElementById("house_type");


locationSelect.addEventListener(
    "change",
    function () {

        const location = this.value;


        /*
        |--------------------------------------------------------------------------
        | Clear current house types
        |--------------------------------------------------------------------------
        */

        houseTypeSelect.innerHTML = "";


        /*
        |--------------------------------------------------------------------------
        | "Any type" option
        |--------------------------------------------------------------------------
        */

        const defaultOption =
            document.createElement("option");

        defaultOption.value = "";

        defaultOption.textContent =
            "Any type";

        houseTypeSelect.appendChild(
            defaultOption
        );


        /*
        |--------------------------------------------------------------------------
        | If no location is selected
        |--------------------------------------------------------------------------
        |
        | Restore all house types.
        |
        */

        if (location === "") {

            const allTypes =
                <?= json_encode(
                    $house_types,
                    JSON_UNESCAPED_UNICODE
                ) ?>;


            allTypes.forEach(function (type) {

                const option =
                    document.createElement("option");

                option.value = type;

                option.textContent = type;

                houseTypeSelect.appendChild(
                    option
                );

            });


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Loading
        |--------------------------------------------------------------------------
        */

        const loadingOption =
            document.createElement("option");

        loadingOption.value = "";

        loadingOption.textContent =
            "Loading...";

        loadingOption.disabled = true;

        houseTypeSelect.appendChild(
            loadingOption
        );


        /*
        |--------------------------------------------------------------------------
        | Get house types for selected location
        |--------------------------------------------------------------------------
        */

        fetch(
            "get_house_types.php?location=" +
            encodeURIComponent(location)
        )

        .then(function (response) {

            if (!response.ok) {

                throw new Error(
                    "Network response was not OK"
                );

            }

            return response.json();

        })

        .then(function (types) {


            /*
            | Clear loading option
            */

            houseTypeSelect.innerHTML = "";


            /*
            | Add Any type
            */

            const anyOption =
                document.createElement("option");

            anyOption.value = "";

            anyOption.textContent =
                "Any type";

            houseTypeSelect.appendChild(
                anyOption
            );


            /*
            | No types found
            */

            if (types.length === 0) {

                const noOption =
                    document.createElement("option");

                noOption.value = "";

                noOption.textContent =
                    "No types available";

                noOption.disabled = true;

                houseTypeSelect.appendChild(
                    noOption
                );

                return;
            }


            /*
            | Add available types
            */

            types.forEach(function (type) {

                const option =
                    document.createElement("option");

                option.value = type;

                option.textContent = type;

                houseTypeSelect.appendChild(
                    option
                );

            });

        })


        /*
        |--------------------------------------------------------------------------
        | Error
        |--------------------------------------------------------------------------
        */

        .catch(function (error) {

            console.error(
                "House type loading error:",
                error
            );


            houseTypeSelect.innerHTML = "";


            const errorOption =
                document.createElement("option");

            errorOption.value = "";

            errorOption.textContent =
                "Unable to load types";

            errorOption.disabled = true;

            houseTypeSelect.appendChild(
                errorOption
            );

        });

    }

);

</script>


<script src="js/main.js?v=<?= filemtime(__DIR__ . '/js/main.js') ?>"></script>
</body>

</html>