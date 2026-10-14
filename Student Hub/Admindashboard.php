
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: Login.html");
    exit;
}

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Access denied.");
}

if (
    !isset($_SESSION["last_activity"]) ||
    time() - $_SESSION["last_activity"] > 900
) {
    $_SESSION = [];
    session_destroy();

    header("Location: Login.html?timeout=1");
    exit;
}

$_SESSION["last_activity"] = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Admin Dashboard</h1>

    <p>
        Welcome,
        <?= htmlspecialchars($_SESSION["name"]) ?>!
    </p>

    <a href="php/logout.php">Logout</a>
</body>
</html>
