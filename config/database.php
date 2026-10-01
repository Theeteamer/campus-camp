<?php

$host = "127.0.0.1";
$dbname = "offcampus";
$username = "root";
$password = "1998";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>