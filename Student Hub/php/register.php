<?php

header("Content-Type: application/json");

include "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);

    exit;
}


$name = trim($_POST["name"] ?? "");
$studentId = trim($_POST["studentId"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirmPassword"] ?? "";
$terms = $_POST["terms"] ?? "0";


$namePattern = "/^[A-Za-z ]{3,40}$/";
$studentIdPattern = "/^[A-Za-z0-9]{4,20}$/";
$emailPattern = "/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/";
$mobilePattern = "/^[6-9][0-9]{9}$/";
$passwordPattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,128}$/";


if (
    $name === "" ||
    $studentId === "" ||
    $email === "" ||
    $mobile === "" ||
    $password === "" ||
    $confirmPassword === ""
) {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required."
    ]);

    exit;
}


if (!preg_match($namePattern, $name)) {
    echo json_encode([
        "success" => false,
        "message" => "Name must contain 3–40 letters and spaces."
    ]);

    exit;
}


if (!preg_match($studentIdPattern, $studentId)) {
    echo json_encode([
        "success" => false,
        "message" => "Student ID must contain 4–20 letters or numbers."
    ]);

    exit;
}


if (
    strlen($email) > 254 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !preg_match($emailPattern, $email)
) {
    echo json_encode([
        "success" => false,
        "message" => "Please enter a valid email address."
    ]);

    exit;
}


if (!preg_match($mobilePattern, $mobile)) {
    echo json_encode([
        "success" => false,
        "message" => "Enter a valid 10-digit mobile number."
    ]);

    exit;
}

if (!preg_match($passwordPattern, $password)) {
    echo json_encode([
        "success" => false,
        "message" => "Password must be 8–128 characters and include uppercase, lowercase, a number and a special character (@$!%*?&)."
    ]);

    exit;
}

if (!hash_equals($password, $confirmPassword)) {
    echo json_encode([
        "success" => false,
        "message" => "Passwords do not match."
    ]);
    exit;
}
if ($terms !== "1") 
{
    echo json_encode([
        "success" => false,
        "message" => "Please agree to the Terms & Conditions."
    ]);
    exit;
}
$sql = "SELECT id FROM users WHERE student_id = ? OR email = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) 
{
    error_log("Registration SELECT prepare failed: " . $conn->error);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to process registration. Please try again."
    ]);
    exit;
}
$stmt->bind_param("ss", $studentId, $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) 
    {
    $stmt->close();
    echo json_encode([
        "success" => false,
        "message" => "Student ID or email already registered."
    ]);
    $conn->close();
    exit;
}
$stmt->close();
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
if ($hashedPassword === false) 
    {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to process your password. Please try again."
    ]);
    $conn->close();
    exit;
}
$sql = "INSERT INTO users
        (name, student_id, email, mobile, password)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) 
{
    error_log("Registration INSERT prepare failed: " . $conn->error);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to complete registration. Please try again."
    ]);
    $conn->close();
    exit;
}
$stmt->bind_param(
    "sssss",
    $name,
    $studentId,
    $email,
    $mobile,
    $hashedPassword
);
if ($stmt->execute()) 
    {
    $newUserId = $stmt->insert_id;
    echo json_encode([
        "success" => true,
        "message" => "Registration successful!",
        "user_id" => $newUserId
    ]);
} 
else 
{
    if ($stmt->errno === 1062) 
        {
        echo json_encode([
            "success" => false,
            "message" => "Student ID or email already registered."
        ]);
    } 
    else 
    {
        error_log("Registration INSERT failed: " . $stmt->error);
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Registration failed. Please try again."
        ]);
    }
}
$stmt->close();
$conn->close();
?>