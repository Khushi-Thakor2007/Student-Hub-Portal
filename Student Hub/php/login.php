
<?php

session_start();

header("Content-Type: application/json");

include "db.php";

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if ($email === "" || $password === "") {

    echo json_encode([
        "success" => false,
        "message" => "Email and password are required."
    ]);

    exit;
}

// ADDED: Include role in the user details
$sql = "SELECT id, name, student_id, email, mobile, password, role
        FROM users
        WHERE email = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    echo json_encode([
        "success" => false,
        "message" => "Prepare failed: " . $conn->error
    ]);

    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password."
    ]);

    $stmt->close();
    $conn->close();

    exit;
}

$user = $result->fetch_assoc();

if (password_verify($password, $user["password"]))
{
    // Existing: Regenerate session ID after successful login
    session_regenerate_id(true);

    // Existing: Store logged-in user's information in session
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["name"] = $user["name"];
    $_SESSION["student_id"] = $user["student_id"];
    $_SESSION["email"] = $user["email"];
    $_SESSION["mobile"] = $user["mobile"];

    // ADDED: Store user's role in session
    $_SESSION["role"] = $user["role"];

    // ADDED: Record last activity for session timeout
    $_SESSION["last_activity"] = time();

    // ADDED: Redirect according to user's role
    $redirect = ($user["role"] === "admin")
        ? "Admindashboard.php"
        : "Dashboard.php";

    echo json_encode([
        "success" => true,
        "message" => "Login successful!",

        // ADDED: Send redirect URL to JavaScript
        "redirect" => $redirect,

        "user" => [
            "id" => $user["id"],
            "name" => $user["name"],
            "student_id" => $user["student_id"],
            "email" => $user["email"],
            "mobile" => $user["mobile"],
            "role" => $user["role"]
        ]
    ]);
}
else
{
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password."
    ]);
}

$stmt->close();
$conn->close();

?>
