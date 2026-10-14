
<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "student_hub";

mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Check whether the role column exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");

if ($result && $result->num_rows == 0) {
    $conn->query("
        ALTER TABLE users
        ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'student'
    ");
}

?>
