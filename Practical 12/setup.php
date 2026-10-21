<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$error = '';
$adminExists = false;

try {
    $adminExists = (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0;
} catch (PDOException $exception) {
    http_response_code(500);
    exit('Database is unavailable. Import schema.sql and check your database settings.');
}

if ($adminExists) {
    header('Location: admin/login.php');
    exit;
}

$remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remoteAddress, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Initial administrator setup is available only from this computer.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid()) {
        $error = 'Your session expired. Refresh the page and try again.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['password_confirmation'] ?? '');

        if (!preg_match('/^[A-Za-z0-9_.-]{3,80}$/', $username)) {
            $error = 'Username must be 3-80 characters and contain only letters, numbers, dots, dashes, or underscores.';
        } elseif (strlen($password) < 12) {
            $error = 'Choose a password with at least 12 characters.';
        } elseif ($password !== $confirmation) {
            $error = 'The password confirmation does not match.';
        } else {
            try {
                $statement = db()->prepare('INSERT INTO admins (username, password_hash) VALUES (:username, :password_hash)');
                $statement->execute([
                    'username' => $username,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                header('Location: admin/login.php?setup=complete');
                exit;
            } catch (PDOException $exception) {
                $error = 'Could not create the administrator. The username may already be in use.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set up administrator</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-page">
<main class="auth-card">
    <p class="eyebrow">Event management</p>
    <h1>Create administrator</h1>
    <p>Set up the first account to manage events. This setup page locks automatically after the account is created.</p>
    <?php if ($error !== ''): ?>
        <div class="notice error" role="alert"><?= escape($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
        <label for="username">Username</label>
        <input id="username" name="username" required minlength="3" maxlength="80" autocomplete="username">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required minlength="12" autocomplete="new-password">
        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password">
        <button type="submit">Create administrator</button>
    </form>
</main>
</body>
</html>
