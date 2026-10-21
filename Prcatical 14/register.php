<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (current_user() !== null) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $nameInput = $_POST['full_name'] ?? '';
    $emailInput = $_POST['email'] ?? '';
    $passwordInput = $_POST['password'] ?? '';
    $name = is_string($nameInput) ? trim($nameInput) : '';
    $email = is_string($emailInput) ? trim($emailInput) : '';
    $password = is_string($passwordInput) ? $passwordInput : '';

    if ($name === '' || strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        $error = 'Enter a valid name and email address.';
    } elseif (strlen($password) < 10) {
        $error = 'Choose a password with at least 10 characters.';
    } else {
        try {
            $statement = $pdo->prepare(
                'INSERT INTO users (full_name, email, password_hash, role) VALUES (:name, :email, :password_hash, \'student\')'
            );
            $statement->execute([
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            header('Location: login.php?registered=1');
            exit;
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }
            $error = 'An account with that email address already exists.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create account | Campus Portal</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-page">
<main class="auth-card">
    <a class="brand auth-brand" href="index.php">Campus Portal</a>
    <h1>Create a student account</h1>
    <p class="muted">Register to browse events and manage your registrations.</p>
    <?php if ($error !== ''): ?>
        <p class="alert" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="post" action="register.php" class="form-stack">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <label for="full_name">Full name</label>
        <input id="full_name" name="full_name" type="text" maxlength="120" autocomplete="name" required value="<?= htmlspecialchars((string) ($_POST['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" maxlength="190" autocomplete="email" required value="<?= htmlspecialchars((string) ($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" minlength="10" autocomplete="new-password" required>
        <button class="button" type="submit">Create account</button>
    </form>
    <p class="auth-alternate">Already registered? <a href="login.php">Sign in</a></p>
</main>
</body>
</html>
