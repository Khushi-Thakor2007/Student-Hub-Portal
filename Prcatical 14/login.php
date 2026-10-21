<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (current_user() !== null) {
    header('Location: index.php');
    exit;
}

$error = '';
$notice = isset($_GET['registered']) ? 'Account created. You can now sign in.' : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $emailInput = $_POST['email'] ?? '';
    $passwordInput = $_POST['password'] ?? '';
    $email = is_string($emailInput) ? trim($emailInput) : '';
    $password = is_string($passwordInput) ? $passwordInput : '';

    $statement = $pdo->prepare('SELECT id, full_name, email, password_hash, role FROM users WHERE email = :email');
    $statement->execute(['email' => $email]);
    $account = $statement->fetch();

    if ($account !== false && password_verify($password, $account['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $account['id'],
            'name' => $account['full_name'],
            'email' => $account['email'],
            'role' => $account['role'],
        ];
        header('Location: index.php');
        exit;
    }

    $error = 'The email or password is incorrect.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | Campus Portal</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-page">
<main class="auth-card">
    <a class="brand auth-brand" href="index.php">Campus Portal</a>
    <h1>Welcome back</h1>
    <p class="muted">Sign in to continue to your account.</p>
    <?php if ($error !== ''): ?>
        <p class="alert" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($notice !== ''): ?>
        <p class="success" role="status"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="post" action="login.php" class="form-stack">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" autocomplete="email" required value="<?= htmlspecialchars((string) ($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <button class="button" type="submit">Sign in</button>
    </form>
    <p class="auth-alternate">New student? <a href="register.php">Create an account</a></p>
</main>
</body>
</html>
