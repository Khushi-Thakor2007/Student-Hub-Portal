<?php
declare(strict_types=1);

session_start();

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function requestString(array $source, string $key): string
{
    return isset($source[$key]) && is_string($source[$key]) ? trim($source[$key]) : '';
}

function validDate(string $date): bool
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    return checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4));
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];
$message = '';
$messageType = '';
$databaseError = '';
$db = null;

try {
    require __DIR__ . '/db.php';
} catch (mysqli_sql_exception $exception) {
    error_log('Student management database connection failed: ' . $exception->getMessage());
    $databaseError = 'Could not connect to the database. Check that MySQL is running and that you imported database.sql.';
}

$formData = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'gender' => '',
    'course' => '',
    'date_of_birth' => '',
    'address' => '',
];
$editingId = 0;
$postAction = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = requestString($_POST, 'action');
    $editingId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;

    foreach ($formData as $field => $value) {
        $formData[$field] = requestString($_POST, $field);
    }

    if (!hash_equals($csrfToken, requestString($_POST, 'csrf_token'))) {
        $message = 'Your session token is invalid. Refresh the page and try again.';
        $messageType = 'error';
    } elseif ($db === null) {
        $message = $databaseError;
        $messageType = 'error';
    } elseif ($postAction === 'delete') {
        if ($editingId < 1) {
            $message = 'The selected student record is invalid.';
            $messageType = 'error';
        } else {
            try {
                $statement = $db->prepare('DELETE FROM students WHERE id = ?');
                $statement->bind_param('i', $editingId);
                $statement->execute();

                if ($statement->affected_rows === 1) {
                    $_SESSION['flash'] = ['type' => 'success', 'text' => 'Student record deleted successfully.'];
                } else {
                    $_SESSION['flash'] = ['type' => 'error', 'text' => 'Student record was not found; nothing was deleted.'];
                }
                $statement->close();
                header('Location: index.php');
                exit;
            } catch (mysqli_sql_exception $exception) {
                error_log('Student delete failed: ' . $exception->getMessage());
                $message = 'Unable to delete the student record. Please try again.';
                $messageType = 'error';
            }
        }
    } elseif ($postAction === 'add' || $postAction === 'update') {
        $errors = [];
        if ($formData['full_name'] === '') {
            $errors[] = 'Name is required.';
        }
        if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        }
        if ($formData['course'] === '') {
            $errors[] = 'Course is required.';
        }
        if (!in_array($formData['gender'], ['Female', 'Male', 'Other'], true)) {
            $errors[] = 'Select a valid gender.';
        }
        if ($formData['date_of_birth'] !== '' && !validDate($formData['date_of_birth'])) {
            $errors[] = 'Enter a valid date of birth.';
        }
        if ($postAction === 'update' && $editingId < 1) {
            $errors[] = 'The student record to update is invalid.';
        }

        if ($errors !== []) {
            $message = implode(' ', $errors);
            $messageType = 'error';
        } else {
            try {
                if ($postAction === 'add') {
                    $statement = $db->prepare(
                        'INSERT INTO students (full_name, email, phone, gender, course, date_of_birth, address)
                         VALUES (?, ?, ?, ?, ?, NULLIF(?, \'\'), ?)'
                    );
                    $statement->bind_param(
                        'sssssss',
                        $formData['full_name'],
                        $formData['email'],
                        $formData['phone'],
                        $formData['gender'],
                        $formData['course'],
                        $formData['date_of_birth'],
                        $formData['address']
                    );
                    $statement->execute();
                    $statement->close();
                    $_SESSION['flash'] = ['type' => 'success', 'text' => 'Student added successfully.'];
                } else {
                    $statement = $db->prepare(
                        'UPDATE students
                         SET full_name = ?, email = ?, phone = ?, gender = ?, course = ?,
                             date_of_birth = NULLIF(?, \'\'), address = ?
                         WHERE id = ?'
                    );
                    $statement->bind_param(
                        'sssssssi',
                        $formData['full_name'],
                        $formData['email'],
                        $formData['phone'],
                        $formData['gender'],
                        $formData['course'],
                        $formData['date_of_birth'],
                        $formData['address'],
                        $editingId
                    );
                    $statement->execute();
                    $changedRows = $statement->affected_rows;
                    $statement->close();

                    if ($changedRows === 0) {
                        $statement = $db->prepare('SELECT id FROM students WHERE id = ?');
                        $statement->bind_param('i', $editingId);
                        $statement->execute();
                        $statement->store_result();
                        $recordExists = $statement->num_rows === 1;
                        $statement->close();

                        if (!$recordExists) {
                            throw new RuntimeException('The student record was not found.');
                        }
                    }
                    $_SESSION['flash'] = ['type' => 'success', 'text' => 'Student record updated successfully.'];
                }

                header('Location: index.php');
                exit;
            } catch (mysqli_sql_exception $exception) {
                error_log('Student save failed: ' . $exception->getMessage());
                $message = 'Unable to save the student record. Check that the email address is not already in use.';
                $messageType = 'error';
            } catch (RuntimeException $exception) {
                $message = $exception->getMessage();
                $messageType = 'error';
            }
        }
    } else {
        $message = 'The requested action is not valid.';
        $messageType = 'error';
    }
}

if (isset($_SESSION['flash'])) {
    $message = $_SESSION['flash']['text'];
    $messageType = $_SESSION['flash']['type'];
    unset($_SESSION['flash']);
}

if ($postAction !== 'update' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $requestedId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($requestedId !== false && $requestedId !== null && $requestedId > 0 && $db !== null) {
        try {
            $statement = $db->prepare(
                'SELECT full_name, email, phone, gender, course, COALESCE(date_of_birth, \'\'), address
                 FROM students WHERE id = ?'
            );
            $statement->bind_param('i', $requestedId);
            $statement->execute();
            $statement->bind_result(
                $formData['full_name'],
                $formData['email'],
                $formData['phone'],
                $formData['gender'],
                $formData['course'],
                $formData['date_of_birth'],
                $formData['address']
            );

            if ($statement->fetch()) {
                $editingId = $requestedId;
            } else {
                $message = 'Student record not found.';
                $messageType = 'error';
            }
            $statement->close();
        } catch (mysqli_sql_exception $exception) {
            error_log('Student edit lookup failed: ' . $exception->getMessage());
            $message = 'Unable to load the student record.';
            $messageType = 'error';
        }
    }
}

$search = requestString($_GET, 'search');
$courseFilter = requestString($_GET, 'course');
$genderFilter = requestString($_GET, 'gender');
$students = [];

if ($db !== null) {
    try {
        $statement = $db->prepare(
            'SELECT id, full_name, email, phone, gender, course, date_of_birth, address
             FROM students
             WHERE (? = \'\' OR full_name LIKE ? OR email LIKE ? OR course LIKE ?)
               AND (? = \'\' OR course = ?)
               AND (? = \'\' OR gender = ?)
             ORDER BY full_name ASC'
        );
        $searchPattern = '%' . $search . '%';
        $statement->bind_param(
            'ssssssss',
            $search,
            $searchPattern,
            $searchPattern,
            $searchPattern,
            $courseFilter,
            $courseFilter,
            $genderFilter,
            $genderFilter
        );
        $statement->execute();
        $statement->bind_result($id, $fullName, $email, $phone, $gender, $course, $dateOfBirth, $address);

        while ($statement->fetch()) {
            $students[] = [
                'id' => $id,
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'gender' => $gender,
                'course' => $course,
                'date_of_birth' => $dateOfBirth,
                'address' => $address,
            ];
        }
        $statement->close();
    } catch (mysqli_sql_exception $exception) {
        error_log('Student list lookup failed: ' . $exception->getMessage());
        if ($message === '') {
            $message = 'Unable to retrieve student records.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container">
    <header class="page-header">
        <div>
            <p class="eyebrow">Student records</p>
            <h1>Student Management</h1>
            <p class="subtitle">Add, view, edit, delete, search, and filter student details.</p>
        </div>
    </header>

    <?php if ($databaseError !== ''): ?>
        <div class="notice error" role="alert"><?= escape($databaseError) ?></div>
    <?php endif; ?>
    <?php if ($message !== ''): ?>
        <div class="notice <?= escape($messageType) ?>" role="status"><?= escape($message) ?></div>
    <?php endif; ?>

    <section class="panel">
        <h2><?= $editingId > 0 ? 'Edit student' : 'Add a student' ?></h2>
        <form method="post" action="index.php" class="student-form">
            <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
            <input type="hidden" name="action" value="<?= $editingId > 0 ? 'update' : 'add' ?>">
            <?php if ($editingId > 0): ?>
                <input type="hidden" name="id" value="<?= (int) $editingId ?>">
            <?php endif; ?>

            <label>
                Full name *
                <input type="text" name="full_name" maxlength="120" required value="<?= escape($formData['full_name']) ?>">
            </label>
            <label>
                Email *
                <input type="email" name="email" maxlength="190" required value="<?= escape($formData['email']) ?>">
            </label>
            <label>
                Phone
                <input type="tel" name="phone" maxlength="30" value="<?= escape($formData['phone']) ?>">
            </label>
            <label>
                Gender *
                <select name="gender" required>
                    <option value="">Select gender</option>
                    <?php foreach (['Female', 'Male', 'Other'] as $option): ?>
                        <option value="<?= escape($option) ?>" <?= $formData['gender'] === $option ? 'selected' : '' ?>>
                            <?= escape($option) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Course *
                <input type="text" name="course" maxlength="100" required value="<?= escape($formData['course']) ?>">
            </label>
            <label>
                Date of birth
                <input type="date" name="date_of_birth" value="<?= escape($formData['date_of_birth']) ?>">
            </label>
            <label class="full-width">
                Address
                <textarea name="address" rows="3" maxlength="500"><?= escape($formData['address']) ?></textarea>
            </label>
            <div class="form-actions full-width">
                <button type="submit" class="button"><?= $editingId > 0 ? 'Save changes' : 'Add student' ?></button>
                <?php if ($editingId > 0): ?>
                    <a class="button secondary" href="index.php">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>Student records</h2>
                <p class="record-count"><?= count($students) ?> record<?= count($students) === 1 ? '' : 's' ?> found</p>
            </div>
            <form method="get" action="index.php" class="filters">
                <label>
                    Search
                    <input type="search" name="search" placeholder="Name, email, or course" value="<?= escape($search) ?>">
                </label>
                <label>
                    Course
                    <input type="text" name="course" placeholder="Exact course" value="<?= escape($courseFilter) ?>">
                </label>
                <label>
                    Gender
                    <select name="gender">
                        <option value="">All genders</option>
                        <?php foreach (['Female', 'Male', 'Other'] as $option): ?>
                            <option value="<?= escape($option) ?>" <?= $genderFilter === $option ? 'selected' : '' ?>>
                                <?= escape($option) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button type="submit" class="button">Apply</button>
                <a class="button secondary" href="index.php">Clear</a>
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Gender</th>
                    <th>Course</th>
                    <th>Date of birth</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($students === []): ?>
                    <tr><td colspan="7" class="empty-state">No student records found.</td></tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= escape($student['full_name']) ?></td>
                            <td>
                                <a href="mailto:<?= escape($student['email']) ?>"><?= escape($student['email']) ?></a>
                                <?php if ($student['phone'] !== ''): ?>
                                    <span class="secondary-text"><?= escape($student['phone']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= escape($student['gender']) ?></td>
                            <td><?= escape($student['course']) ?></td>
                            <td><?= escape($student['date_of_birth'] ?? '') ?></td>
                            <td><?= escape($student['address'] ?? '') ?></td>
                            <td>
                                <div class="row-actions">
                                    <a class="button small secondary" href="index.php?edit=<?= (int) $student['id'] ?>">Edit</a>
                                    <form method="post" action="index.php" onsubmit="return confirm('Delete this student record?');">
                                        <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $student['id'] ?>">
                                        <button type="submit" class="button small danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
