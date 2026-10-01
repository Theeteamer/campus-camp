<?php

require_once __DIR__ . "/config/database.php";

header("Content-Type: application/json; charset=UTF-8");

$location = trim($_GET["location"] ?? "");

$house_types = [];


/*
|--------------------------------------------------------------------------
| No location selected
|--------------------------------------------------------------------------
|
| Return every house type available in the database.
|
*/

if ($location === "") {

    $stmt = $conn->prepare("
        SELECT DISTINCT house_type
        FROM properties
        ORDER BY house_type ASC
    ");

} else {

    /*
    |--------------------------------------------------------------------------
    | Location selected
    |--------------------------------------------------------------------------
    |
    | Only return house types that exist at this location.
    |
    */

    $stmt = $conn->prepare("
        SELECT DISTINCT house_type
        FROM properties
        WHERE location = ?
        ORDER BY house_type ASC
    ");

    $stmt->bind_param("s", $location);
}


$stmt->execute();

$result = $stmt->get_result();


while ($row = $result->fetch_assoc()) {

    $house_types[] = $row["house_type"];

}


$stmt->close();

echo json_encode(
    $house_types,
    JSON_UNESCAPED_UNICODE
);