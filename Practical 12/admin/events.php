<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_admin();

function validate_event_input(array $input): array
{
    $title = trim((string) ($input['title'] ?? ''));
    $description = trim((string) ($input['description'] ?? ''));
    $eventDate = trim((string) ($input['event_date'] ?? ''));
    $location = trim((string) ($input['location'] ?? ''));
    $errors = [];

    if ($title === '' || strlen($title) > 150) {
        $errors[] = 'Title is required and must be 150 characters or fewer.';
    }
    if ($description === '') {
        $errors[] = 'Description is required.';
    }
    if ($location === '' || strlen($location) > 180) {
        $errors[] = 'Location is required and must be 180 characters or fewer.';
    }

    $date = DateTime::createFromFormat('Y-m-d\TH:i', $eventDate);
    if (!$date || $date->format('Y-m-d\TH:i') !== $eventDate) {
        $errors[] = 'Enter a valid event date and time.';
    }

    return [
        'values' => [
            'title' => $title,
            'description' => $description,
            'event_date' => $date ? $date->format('Y-m-d H:i:s') : '',
            'location' => $location,
        ],
        'errors' => $errors,
    ];
}

function save_poster(array $file): ?string
{
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid poster upload.');
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Poster upload failed. Please try again.');
    }
    if (!isset($file['size']) || $file['size'] < 1 || $file['size'] > MAX_POSTER_SIZE) {
        throw new RuntimeException('Poster must be no larger than 5 MB.');
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowedTypes[$mimeType])) {
        throw new RuntimeException('Poster must be a JPEG, PNG, or WebP image.');
    }

    if (!is_dir(POSTER_DIRECTORY) && !mkdir(POSTER_DIRECTORY, 0755, true) && !is_dir(POSTER_DIRECTORY)) {
        throw new RuntimeException('Could not create the poster upload directory.');
    }
    if (!is_writable(POSTER_DIRECTORY)) {
        throw new RuntimeException('The poster upload directory is not writable.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
    if (!move_uploaded_file($file['tmp_name'], POSTER_DIRECTORY . DIRECTORY_SEPARATOR . $filename)) {
        throw new RuntimeException('Could not save the uploaded poster.');
    }

    return $filename;
}

function remove_poster(?string $filename): void
{
    if ($filename === null || !preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $filename)) {
        return;
    }

    $path = POSTER_DIRECTORY . DIRECTORY_SEPARATOR . $filename;
    if (is_file($path) && !unlink($path)) {
        error_log('Could not remove event poster: ' . $path);
    }
}

$error = '';
$formValues = ['title' => '', 'description' => '', 'event_date' => '', 'location' => ''];
$editingEvent = null;
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$flash = get_flash();

try {
    if ($editId) {
        $statement = db()->prepare('SELECT * FROM events WHERE id = :id');
        $statement->execute(['id' => $editId]);
        $editingEvent = $statement->fetch() ?: null;
        if (!$editingEvent) {
            set_flash('error', 'The selected event was not found.');
            header('Location: events.php');
            exit;
        }
        $formValues = [
            'title' => $editingEvent['title'],
            'description' => $editingEvent['description'],
            'event_date' => date('Y-m-d\TH:i', strtotime($editingEvent['event_date'])),
            'location' => $editingEvent['location'],
        ];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_is_valid()) {
            throw new RuntimeException('Your session expired. Refresh the page and try again.');
        }

        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'delete') {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                throw new RuntimeException('Invalid event selected.');
            }
            $statement = db()->prepare('SELECT poster_filename FROM events WHERE id = :id');
            $statement->execute(['id' => $id]);
            $poster = $statement->fetchColumn();
            if ($poster === false) {
                throw new RuntimeException('The event was not found.');
            }
            $statement = db()->prepare('DELETE FROM events WHERE id = :id');
            $statement->execute(['id' => $id]);
            remove_poster($poster ?: null);
            set_flash('success', 'Event deleted.');
            header('Location: events.php');
            exit;
        }

        if ($action !== 'create' && $action !== 'update') {
            throw new RuntimeException('Invalid event action.');
        }

        $validation = validate_event_input($_POST);
        $formValues = [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'event_date' => trim((string) ($_POST['event_date'] ?? '')),
            'location' => trim((string) ($_POST['location'] ?? '')),
        ];
        if ($validation['errors']) {
            throw new RuntimeException(implode(' ', $validation['errors']));
        }
        $values = $validation['values'];

        if ($action === 'create') {
            $posterFilename = save_poster($_FILES['poster'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
            try {
                $statement = db()->prepare(
                    'INSERT INTO events (title, description, event_date, location, poster_filename)
                     VALUES (:title, :description, :event_date, :location, :poster_filename)'
                );
                $statement->execute($values + ['poster_filename' => $posterFilename]);
            } catch (Throwable $exception) {
                remove_poster($posterFilename);
                throw $exception;
            }
            set_flash('success', 'Event created.');
            header('Location: events.php');
            exit;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            throw new RuntimeException('Invalid event selected.');
        }
        $statement = db()->prepare('SELECT poster_filename FROM events WHERE id = :id');
        $statement->execute(['id' => $id]);
        $oldPoster = $statement->fetchColumn();
        if ($oldPoster === false) {
            throw new RuntimeException('The event was not found.');
        }

        $newPoster = save_poster($_FILES['poster'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
        $posterFilename = $newPoster ?: ($oldPoster ?: null);
        try {
            $statement = db()->prepare(
                'UPDATE events
                 SET title = :title, description = :description, event_date = :event_date,
                     location = :location, poster_filename = :poster_filename
                 WHERE id = :id'
            );
            $statement->execute($values + ['poster_filename' => $posterFilename, 'id' => $id]);
        } catch (Throwable $exception) {
            remove_poster($newPoster);
            throw $exception;
        }
        if ($newPoster) {
            remove_poster($oldPoster ?: null);
        }
        set_flash('success', 'Event updated.');
        header('Location: events.php');
        exit;
    }
} catch (RuntimeException $exception) {
    $error = $exception->getMessage();
} catch (PDOException $exception) {
    http_response_code(500);
    $error = 'The event could not be saved because of a database error.';
    error_log($exception->getMessage());
}

try {
    $events = db()->query('SELECT * FROM events ORDER BY event_date ASC, id DESC')->fetchAll();
} catch (PDOException $exception) {
    http_response_code(500);
    exit('Could not load events. Check the database setup and settings.');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage events</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="events.php">Event management</a>
    <div class="account">
        <span><?= escape((string) ($_SESSION['admin_username'] ?? 'Administrator')) ?></span>
        <form action="logout.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
            <button class="button-secondary" type="submit">Sign out</button>
        </form>
    </div>
</header>
<main class="page">
    <div class="page-heading">
        <div>
            <p class="eyebrow">Administration</p>
            <h1>Events</h1>
            <p>Create and maintain your event listings and posters.</p>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="notice <?= escape($flash['type']) ?>" role="status"><?= escape($flash['message']) ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="notice error" role="alert"><?= escape($error) ?></div>
    <?php endif; ?>

    <section class="panel" id="event-form">
        <div class="panel-heading">
            <div>
                <h2><?= $editingEvent ? 'Update event' : 'Add an event' ?></h2>
                <p>Poster formats: JPEG, PNG, or WebP. Maximum file size: 5 MB.</p>
            </div>
            <?php if ($editingEvent): ?>
                <a class="text-link" href="events.php">Cancel editing</a>
            <?php endif; ?>
        </div>
        <form method="post" enctype="multipart/form-data" class="event-form">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
            <input type="hidden" name="action" value="<?= $editingEvent ? 'update' : 'create' ?>">
            <?php if ($editingEvent): ?>
                <input type="hidden" name="id" value="<?= (int) $editingEvent['id'] ?>">
            <?php endif; ?>
            <div class="form-grid">
                <div class="field">
                    <label for="title">Event title</label>
                    <input id="title" name="title" maxlength="150" required value="<?= escape($formValues['title']) ?>">
                </div>
                <div class="field">
                    <label for="location">Location</label>
                    <input id="location" name="location" maxlength="180" required value="<?= escape($formValues['location']) ?>">
                </div>
                <div class="field">
                    <label for="event_date">Date and time</label>
                    <input id="event_date" name="event_date" type="datetime-local" required value="<?= escape($formValues['event_date']) ?>">
                </div>
                <div class="field">
                    <label for="poster">Event poster<?= $editingEvent ? ' (optional; upload a new poster to replace)' : ' (optional)' ?></label>
                    <input id="poster" name="poster" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <?php if ($editingEvent && $editingEvent['poster_filename']): ?>
                        <img class="current-poster" src="<?= escape('../' . POSTER_URL . rawurlencode($editingEvent['poster_filename'])) ?>" alt="Current event poster">
                    <?php endif; ?>
                </div>
                <div class="field field-full">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" required><?= escape($formValues['description']) ?></textarea>
                </div>
            </div>
            <button type="submit"><?= $editingEvent ? 'Save changes' : 'Add event' ?></button>
        </form>
    </section>

    <section class="events-section">
        <div class="section-heading">
            <div>
                <h2>All events</h2>
                <p><?= count($events) ?> <?= count($events) === 1 ? 'event' : 'events' ?></p>
            </div>
        </div>
        <?php if (!$events): ?>
            <div class="empty-state">No events yet. Use the form above to add your first event.</div>
        <?php else: ?>
            <div class="event-grid">
                <?php foreach ($events as $event): ?>
                    <article class="event-card">
                        <?php if ($event['poster_filename']): ?>
                            <img class="event-poster" src="<?= escape('../' . POSTER_URL . rawurlencode($event['poster_filename'])) ?>" alt="Poster for <?= escape($event['title']) ?>">
                        <?php else: ?>
                            <div class="poster-placeholder" aria-label="No poster uploaded">Event poster</div>
                        <?php endif; ?>
                        <div class="event-content">
                            <p class="event-date"><?= escape(date('M j, Y · g:i A', strtotime($event['event_date']))) ?></p>
                            <h3><?= escape($event['title']) ?></h3>
                            <p class="event-location"><?= escape($event['location']) ?></p>
                            <p class="event-description"><?= nl2br(escape($event['description'])) ?></p>
                            <div class="card-actions">
                                <a class="button-secondary" href="events.php?edit=<?= (int) $event['id'] ?>#event-form">Edit</a>
                                <form method="post" onsubmit="return confirm('Delete this event? This action cannot be undone.');">
                                    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $event['id'] ?>">
                                    <button class="button-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
