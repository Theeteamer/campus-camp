<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: properties.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Find the property and its landlord
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT landlord_id
    FROM properties
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$property = $result->fetch_assoc();

$stmt->close();


if (!$property) {
    header("Location: properties.php");
    exit;
}

$landlord_id = (int)$property["landlord_id"];


/*
|--------------------------------------------------------------------------
| Start transaction
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {

    /*
    |--------------------------------------------------------------------------
    | Delete facilities
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM facilities
        WHERE property_id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Delete rooms
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM rooms
        WHERE property_id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Delete property
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM properties
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Check whether landlord still owns another property
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS property_count
        FROM properties
        WHERE landlord_id = ?
    ");

    $stmt->bind_param("i", $landlord_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $count = $result->fetch_assoc();

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Delete landlord only if they have no properties left
    |--------------------------------------------------------------------------
    */

    if ((int)$count["property_count"] === 0) {

        $stmt = $conn->prepare("
            DELETE FROM landlords
            WHERE id = ?
        ");

        $stmt->bind_param("i", $landlord_id);
        $stmt->execute();

        $stmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | Complete transaction
    |--------------------------------------------------------------------------
    */

    $conn->commit();

    header("Location: properties.php");
    exit;


} catch (Exception $e) {

    $conn->rollback();

    echo "
        <h2>Unable to delete property</h2>
        <p>Please try again.</p>
        <a href='properties.php'>Back to Properties</a>
    ";

}