<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$user = current_user();
if ($user === null) {
    header('Location: login.php');
} else {
    header('Location: ' . ($user['role'] === 'admin' ? 'dashboard.php' : 'events.php'));
}
exit;
