<?php
require_once __DIR__ . "/../includes/partial_response.php";
start_partial_response();

session_start();

require_once __DIR__ . "/../config/database.php";

$login_failed = false;

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

        $login_failed = true;

    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Verify</title>

    <link rel="stylesheet" href="../css/styles.css?v=<?= filemtime(__DIR__ . '/../css/styles.css') ?>">

    <style>
        .admin-login-form input[aria-invalid="true"] {
            border: 1px solid #b42318;
        }
    </style>

</head>

<body>

<header class="admin-header">
    <div class="container admin-header-inner">
        <strong>
            <img class="brand-image" src="../images/ofcampus-logo.png" alt="Campus-Camp®">
            <span class="admin-context">Admin</span>
        </strong>
    </div>
</header>

<main class="admin-main admin-login">

    <div class="admin-content-actions">
        <div class="container">
            <a href="../index.php" class="cancel-button" aria-label="Back to home" title="Back to home">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                <span class="button-label">Home</span>
            </a>
        </div>
    </div>

    <div class="admin-login-box">
        <div class="admin-login-content">

        <h1>Log In</h1>

        <p>Verify you are an administrator...</p>

        <form method="POST" class="admin-login-form">

            <label>Username</label>

            <input
                type="text"
                name="username"
                required
                <?= $login_failed ? 'aria-invalid="true"' : "" ?>
            >

            <label>Password</label>

            <input
                type="password"
                name="password"
                required
                <?= $login_failed ? 'aria-invalid="true"' : "" ?>
            >

            <button type="submit" class="admin-login-button" aria-label="Log in" title="Log in">
                <i class="fa fa-sign-in" aria-hidden="true"></i>
                <span class="button-label">Log In</span>
            </button>

        </form>

        </div>
    </div>

</main>

<?php include __DIR__ . "/footer.php"; ?>
<script src="../js/main.js?v=<?= filemtime(__DIR__ . '/../js/main.js') ?>"></script>
</body>

</html>