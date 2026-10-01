<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: landlords.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT COUNT(*) AS property_count
    FROM properties
    WHERE landlord_id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ((int)$count["property_count"] > 0) {
    header("Location: landlords.php");
    exit;
}

$stmt = $conn->prepare("DELETE FROM landlords WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: landlords.php");
exit;
