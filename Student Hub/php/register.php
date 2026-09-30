<!-- <?php

include "db.php"; 

$name = $_POST["name"] ?? "";
$studentId = $_POST["studentId"] ?? "";
$email = $_POST["email"] ?? "";
$mobile = $_POST["mobile"] ?? "";
$password = $_POST["password"] ?? "";

$sql = "SELECT id FROM users WHERE student_id = ? OR email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $studentId, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {

    echo json_encode([
        "success" => false,
        "message" => "Student ID or email already registered."
    ]);

    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (name, student_id, email, mobile, password)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Prepare failed: " . $conn->error
    ]);
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

if ($stmt->execute()) {

    $newUserId = $stmt->insert_id;

    echo json_encode([
        "success" => true,
        "message" => "Registration successful!",
        "user_id" => $newUserId
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Registration failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();

?>