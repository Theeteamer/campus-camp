<?php

session_start();

require_once __DIR__ . "/../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare(
        "SELECT id, username, password FROM users WHERE username = ? LIMIT 1"
    );

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();

    if ($user && password_verify($password, $user["password"])) {

        $_SESSION["admin_id"] = $user["id"];
        $_SESSION["admin_username"] = $user["username"];

        header("Location: dashboard.php");
        exit;

    } else {

        $error = "Invalid username or password.";

    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login - Campus-Camp®</title>

    <link rel="stylesheet" href="../css/styles.css?v=<?= filemtime(__DIR__ . '/../css/styles.css') ?>">

</head>

<body>

<div class="admin-login">

    <div class="admin-login-box">

        <h1><img class="brand-image" src="../images/ofcampus-logo.png" alt="Campus-Camp®"></h1>

        <p>Administrator Login</p>

        <?php if ($error): ?>

            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST" class="admin-login-form">

            <label>Username</label>

            <input
                type="text"
                name="username"
                required
            >

            <label>Password</label>

            <input
                type="password"
                name="password"
                required
            >

            <button type="submit" class="admin-login-button">
                LOGIN
            </button>

        </form>

        <div class="admin-login-link-wrap">
            <a href="../index.php" class="admin-login-link">
                Back to website
            </a>
        </div>

    </div>

</div>

</body>

</html>