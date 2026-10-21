<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: events.php');
    exit;
}

$error = '';
$flash = isset($_GET['setup']) ? 'Administrator created. Sign in to continue.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid()) {
        $error = 'Your session expired. Refresh the page and try again.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        try {
            $statement = db()->prepare('SELECT id, username, password_hash FROM admins WHERE username = :username');
            $statement->execute(['username' => $username]);
            $admin = $statement->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = (int) $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                header('Location: events.php');
                exit;
            }

            $error = 'Incorrect username or password.';
        } catch (PDOException $exception) {
            http_response_code(500);
            $error = 'Could not connect to the database. Check the setup instructions and database settings.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administrator sign in</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="auth-page">
<main class="auth-card">
    <p class="eyebrow">Event management</p>
    <h1>Administrator sign in</h1>
    <?php if ($flash !== ''): ?>
        <div class="notice success" role="status"><?= escape($flash) ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="notice error" role="alert"><?= escape($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
        <label for="username">Username</label>
        <input id="username" name="username" required autocomplete="username" autofocus>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password">
        <button type="submit">Sign in</button>
    </form>
</main>
</body>
</html>
