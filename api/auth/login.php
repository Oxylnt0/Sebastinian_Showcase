<?php
require_once("../config/db.php");
require_once("../utils/response.php");
require_once("../utils/validation.php");
require_once("../utils/auth_check.php");

// Reject anything except POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Invalid request method", 405);
}

// Grab input
$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

// Input validation using Validation class
if (!Validation::required($username)) {
    Response::error("Username is required");
}
if (!Validation::required($password)) {
    Response::error("Password is required");
}

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
        Response::error("Account not found", 404);
    }

    $user = $result->fetch_assoc();

    if (!password_verify($password, $user["password"])) {
        Response::error("Invalid password", 401);
    }

    // Start session
    session_start();
    $_SESSION["user_id"] = $user["user_id"];
    $_SESSION["username"] = $user["username"];
    $_SESSION["role"] = $user["role"];
    $_SESSION["logged_in_at"] = time();

    // Correct order: data first, then message
    Response::success([
        "user_id" => $user["user_id"],
        "username" => $user["username"],
        "role" => $user["role"]
    ], "Login successful");

} catch (Exception $e) {
    Response::error("Server error: " . $e->getMessage(), 500);
}
