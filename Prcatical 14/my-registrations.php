<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$student = require_role('student');
$statement = $pdo->prepare(
    'SELECT events.title, events.event_date, events.location, registrations.registered_at
     FROM registrations
     INNER JOIN events ON events.id = registrations.event_id
     WHERE registrations.user_id = :user_id
     ORDER BY events.event_date DESC'
);
$statement->execute(['user_id' => $student['id']]);
$registrations = $statement->fetchAll();

$pageTitle = 'My registrations';
require __DIR__ . '/includes/header.php';
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Your activity</p>
        <h1>My registrations</h1>
        <p class="muted">Events you have signed up for.</p>
    </div>
</section>
<section class="panel">
    <?php if ($registrations === []): ?>
        <div class="empty-state">You have not registered for any events yet. <a href="events.php">Browse events</a>.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Registered on</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($registrations as $registration): ?>
                    <tr>
                        <td><?= htmlspecialchars($registration['title'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(date('M j, Y g:i A', strtotime($registration['event_date'])), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($registration['location'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(date('M j, Y', strtotime($registration['registered_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
