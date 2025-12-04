<?php
require_once("../config/db.php");
require_once("../utils/response.php");
require_once("../utils/validation.php");

header("Content-Type: application/json");

// Only POST allowed
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    json_error("Invalid request method", 405);
}

$username = trim($_POST["username"] ?? "");
$raw_password = $_POST["password"] ?? "";
$full_name = trim($_POST["full_name"] ?? "");
$email = trim($_POST["email"] ?? "");
$role = "student"; // Students register; admins created manually

// Base validation
validate_required($username, "Username required");
validate_required($raw_password, "Password required");
validate_required($full_name, "Full name required");
validate_required($email, "Email required");
validate_email($email);

// Enforce password security
validate_password_strength($raw_password);

try {
    $conn = (new Database())->connect();

    // Check if username or email already exists
    $stmt = $conn->prepare("
        SELECT user_id 
        FROM users 
        WHERE username = ? OR email = ?
        LIMIT 1
    ");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        json_error("Username or email already exists");
    }

    // Secure hashing
    $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO users (username, password, full_name, email, role) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssss", $username, $hashed_password, $full_name, $email, $role);

    if (!$stmt->execute()) {
        json_error("Registration failed. SQL Error.");
    }

    json_success("Account created successfully");

} catch (Exception $e) {
    json_error("Server error: " . $e->getMessage());
}
