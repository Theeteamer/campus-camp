<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP is working.<br>";

require_once __DIR__ . "/config/database.php";

echo "Database connection successful!";

?>