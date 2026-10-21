<?php
$user = current_user();
$pageTitle = $pageTitle ?? 'Campus Portal';
$navigation = $user['role'] === 'admin'
    ? [
        ['label' => 'Dashboard', 'href' => 'dashboard.php'],
        ['label' => 'Events', 'href' => 'events.php'],
    ]
    : [
        ['label' => 'Events', 'href' => 'events.php'],
        ['label' => 'My registrations', 'href' => 'my-registrations.php'],
    ];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | Campus Portal</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="<?= $user['role'] === 'admin' ? 'dashboard.php' : 'events.php' ?>">Campus Portal</a>
    <nav class="main-nav" aria-label="Main navigation">
        <?php foreach ($navigation as $item): ?>
            <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="account">
        <span><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></span>
        <form action="logout.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <button class="link-button" type="submit">Sign out</button>
        </form>
    </div>
</header>
<main class="container">
