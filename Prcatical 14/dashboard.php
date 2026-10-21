<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

require_role('admin');

$totalUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$studentCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$eventCount = (int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
$registrationCount = (int) $pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
$latestRegistrations = $pdo->query(
    'SELECT users.full_name, users.email, events.title AS event_title, registrations.registered_at
     FROM registrations
     INNER JOIN users ON users.id = registrations.user_id
     INNER JOIN events ON events.id = registrations.event_id
     ORDER BY registrations.registered_at DESC
     LIMIT 8'
)->fetchAll();

$pageTitle = 'Admin dashboard';
require __DIR__ . '/includes/header.php';
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Administration</p>
        <h1>Dashboard</h1>
        <p class="muted">A snapshot of campus activity and the latest event registrations.</p>
    </div>
    <span class="role-pill">Administrator</span>
</section>

<section class="stat-grid" aria-label="Platform statistics">
    <article class="stat-card">
        <span class="stat-label">Total users</span>
        <strong><?= number_format($totalUsers) ?></strong>
        <span class="stat-note">All registered accounts</span>
    </article>
    <article class="stat-card">
        <span class="stat-label">Students</span>
        <strong><?= number_format($studentCount) ?></strong>
        <span class="stat-note">Student accounts</span>
    </article>
    <article class="stat-card">
        <span class="stat-label">Events</span>
        <strong><?= number_format($eventCount) ?></strong>
        <span class="stat-note">Events in the portal</span>
    </article>
    <article class="stat-card">
        <span class="stat-label">Registrations</span>
        <strong><?= number_format($registrationCount) ?></strong>
        <span class="stat-note">Total event sign-ups</span>
    </article>
</section>

<section class="panel">
    <div class="section-heading">
        <div>
            <h2>Latest registrations</h2>
            <p class="muted">The eight most recent student sign-ups.</p>
        </div>
        <a class="text-link" href="events.php">View events</a>
    </div>
    <?php if ($latestRegistrations === []): ?>
        <div class="empty-state">There are no event registrations yet.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Student</th>
                    <th>Email</th>
                    <th>Event</th>
                    <th>Registered</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($latestRegistrations as $registration): ?>
                    <tr>
                        <td><?= htmlspecialchars($registration['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($registration['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($registration['event_title'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(date('M j, Y g:i A', strtotime($registration['registered_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
