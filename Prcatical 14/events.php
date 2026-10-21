<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$user = require_login();
$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    if ($user['role'] !== 'student') {
        http_response_code(403);
        exit('Only student accounts can register for events.');
    }

    $eventIdInput = $_POST['event_id'] ?? null;
    $eventId = is_string($eventIdInput) ? filter_var($eventIdInput, FILTER_VALIDATE_INT) : false;
    if ($eventId === false || $eventId === null || $eventId < 1) {
        $error = 'Select a valid event.';
    } else {
        $eventCheck = $pdo->prepare('SELECT id FROM events WHERE id = :id AND event_date > NOW()');
        $eventCheck->execute(['id' => $eventId]);
        if ($eventCheck->fetchColumn() === false) {
            $error = 'That event is no longer available for registration.';
        } else {
            try {
                $registration = $pdo->prepare('INSERT INTO registrations (user_id, event_id) VALUES (:user_id, :event_id)');
                $registration->execute(['user_id' => $user['id'], 'event_id' => $eventId]);
                $notice = 'You are registered for the event.';
            } catch (PDOException $exception) {
                if ($exception->getCode() !== '23000') {
                    throw $exception;
                }
                $error = 'You are already registered for that event.';
            }
        }
    }
}

$eventStatement = $pdo->prepare(
    'SELECT events.id, events.title, events.description, events.event_date, events.location,
            registrations.id AS registration_id
     FROM events
     LEFT JOIN registrations ON registrations.event_id = events.id AND registrations.user_id = :user_id
     ORDER BY events.event_date ASC'
);
$eventStatement->execute(['user_id' => $user['id']]);
$events = $eventStatement->fetchAll();

$pageTitle = 'Events';
require __DIR__ . '/includes/header.php';
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Campus calendar</p>
        <h1>Events</h1>
        <p class="muted">Explore upcoming events and keep track of what's happening on campus.</p>
    </div>
</section>

<?php if ($notice !== ''): ?>
    <p class="success" role="status"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>
<?php if ($error !== ''): ?>
    <p class="alert" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php if ($events === []): ?>
    <section class="panel empty-state">No events have been added yet.</section>
<?php else: ?>
    <section class="event-grid">
        <?php foreach ($events as $event): ?>
            <?php $isUpcoming = strtotime($event['event_date']) > time(); ?>
            <article class="event-card">
                <p class="eyebrow"><?= htmlspecialchars(date('D, M j, Y · g:i A', strtotime($event['event_date'])), ENT_QUOTES, 'UTF-8') ?></p>
                <h2><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="event-location"><?= htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><?= nl2br(htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                <?php if ($user['role'] === 'student'): ?>
                    <?php if ($event['registration_id'] !== null): ?>
                        <span class="status-pill">Registered</span>
                    <?php elseif ($isUpcoming): ?>
                        <form method="post" action="events.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="event_id" value="<?= (int) $event['id'] ?>">
                            <button class="button button-small" type="submit">Register</button>
                        </form>
                    <?php else: ?>
                        <span class="muted">Registration closed</span>
                    <?php endif; ?>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
