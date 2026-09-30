<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "student_hub";

$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("MySQL connection failed: " . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS student_hub";

if (!$conn->query($sql)) {
    die("Database creation failed: " . $conn->error);
}

$conn->select_db($database);

$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    mobile VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die("Table creation failed: " . $conn->error);
}

?>