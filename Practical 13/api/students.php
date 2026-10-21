<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

function respond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function readJsonBody(): array
{
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        respond(400, ['error' => 'Request body must be valid JSON.']);
    }

    return $body;
}

function validateStudent(array $input): array
{
    $name = trim((string) ($input['name'] ?? ''));
    $email = trim((string) ($input['email'] ?? ''));
    $course = trim((string) ($input['course'] ?? ''));

    if ($name === '' || $email === '' || $course === '') {
        respond(422, ['error' => 'Name, email, and course are required.']);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(422, ['error' => 'Enter a valid email address.']);
    }

    return ['name' => $name, 'email' => $email, 'course' => $course];
}

try {
    $pdo = getDatabaseConnection();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $statement = $pdo->query(
            'SELECT id, name, email, course, created_at FROM students ORDER BY id DESC'
        );
        respond(200, ['students' => $statement->fetchAll()]);
    }

    if ($method === 'POST') {
        $student = validateStudent(readJsonBody());
        $statement = $pdo->prepare(
            'INSERT INTO students (name, email, course) VALUES (:name, :email, :course)'
        );
        $statement->execute($student);

        $statement = $pdo->prepare(
            'SELECT id, name, email, course, created_at FROM students WHERE id = :id'
        );
        $statement->execute(['id' => $pdo->lastInsertId()]);
        respond(201, ['student' => $statement->fetch()]);
    }

    if ($method === 'PUT') {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            respond(400, ['error' => 'A valid student id is required.']);
        }
        $student = validateStudent(readJsonBody());
        $student['id'] = $id;
        $statement = $pdo->prepare(
            'UPDATE students SET name = :name, email = :email, course = :course WHERE id = :id'
        );
        $statement->execute($student);

        $statement = $pdo->prepare(
            'SELECT id, name, email, course, created_at FROM students WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
        $updatedStudent = $statement->fetch();
        if (!$updatedStudent) {
            respond(404, ['error' => 'Student not found.']);
        }
        respond(200, ['student' => $updatedStudent]);
    }

    if ($method === 'DELETE') {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            respond(400, ['error' => 'A valid student id is required.']);
        }
        $statement = $pdo->prepare('DELETE FROM students WHERE id = :id');
        $statement->execute(['id' => $id]);
        if ($statement->rowCount() === 0) {
            respond(404, ['error' => 'Student not found.']);
        }
        respond(200, ['message' => 'Student deleted.']);
    }

    header('Allow: GET, POST, PUT, DELETE');
    respond(405, ['error' => 'Method not allowed.']);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    respond(500, ['error' => 'A database error occurred. Check the server configuration.']);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    respond(500, ['error' => 'An unexpected server error occurred.']);
}
