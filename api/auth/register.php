<?php
// api/auth/register.php

require_once("../config/db.php");
require_once("../utils/response.php");
require_once("../utils/validation.php");

header("Content-Type: application/json");

// Only allow POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Invalid request method", 405);
}

// -----------------------------
// Collect input
// -----------------------------
$username     = trim($_POST["username"] ?? "");
$raw_password = $_POST["password"] ?? "";
$full_name    = trim($_POST["full_name"] ?? "");
$email        = trim($_POST["email"] ?? "");
$role         = "student"; // Students register themselves

// -----------------------------
// Validate inputs using Validation class
// -----------------------------
Validation::requireField($username, "Username is required");
Validation::requireField($raw_password, "Password is required");
Validation::requireField($full_name, "Full name is required");
Validation::requireField($email, "Email is required");

if (!Validation::isValidEmail($email)) {
    Response::error("Invalid email address");
}

// Enforce password strength (minimum 6 chars)
if (strlen($raw_password) < 6) {
    Response::error("Password must be at least 6 characters");
}

try {
    $conn = (new Database())->connect();

    // -----------------------------
    // Check if username or email already exists
    // -----------------------------
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
        Response::error("Username or email already exists");
    }

    // -----------------------------
    // Hash password securely
    // -----------------------------
    $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

    // -----------------------------
    // Insert new user
    // -----------------------------
    $stmt = $conn->prepare("
        INSERT INTO users (username, password, full_name, email, role) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssss", $username, $hashed_password, $full_name, $email, $role);

    if (!$stmt->execute()) {
        Response::error("Registration failed. SQL error: " . $stmt->error);
    }

    // -----------------------------
    // Success
    // -----------------------------
    Response::success([], "Account created successfully");

} catch (Exception $e) {
    Response::error("Server error: " . $e->getMessage());
}
