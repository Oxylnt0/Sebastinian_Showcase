<?php
require_once("../config/db.php");
require_once("../utils/response.php");
require_once("../utils/validation.php");
require_once("../utils/auth_check.php");

header("Content-Type: application/json");

// Reject anything except POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    json_error("Invalid request method", 405);
}

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

// Input validation
validate_required($username, "Username is required");
validate_required($password, "Password is required");

try {
    $conn = (new Database())->connect();

    $stmt = $conn->prepare("
        SELECT user_id, username, password, role 
        FROM users 
        WHERE username = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        json_error("Account not found");
    }

    $user = $result->fetch_assoc();

    if (!password_verify($password, $user["password"])) {
        json_error("Invalid password");
    }

    session_start();
    $_SESSION["user_id"] = $user["user_id"];
    $_SESSION["username"] = $user["username"];
    $_SESSION["role"] = $user["role"];
    $_SESSION["logged_in_at"] = time();

    json_success("Login successful", [
        "user_id" => $user["user_id"],
        "username" => $user["username"],
        "role" => $user["role"]
    ]);

} catch (Exception $e) {
    json_error("Server error: " . $e->getMessage());
}
