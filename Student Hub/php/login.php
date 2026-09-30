<?php

include "db.php";

// Get login data
$email = $_POST["email"];
$password = $_POST["password"];

// Find user by email
$sql = "SELECT id, name, student_id, password
        FROM users
        WHERE email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $user = $result->fetch_assoc();

    // Check password
    if (password_verify($password, $user["password"])) {

        echo json_encode([
            "success" => true,
            "message" => "Login successful!",
            "name" => $user["name"],
            "studentId" => $user["student_id"]
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Incorrect password."
        ]);
    }

} else {

    echo json_encode([
        "success" => false,
        "message" => "Email not registered."
    ]);
}

$stmt->close();
$conn->close();

?>