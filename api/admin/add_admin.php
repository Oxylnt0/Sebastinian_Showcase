<?php
// ===========================================
// api/admin/add_admin.php
// Adds a new admin via AJAX (JSON only)
// ===========================================

// ------------------------------
// Config: enable debug logging
// ------------------------------
ini_set('display_errors', 0); // keep JSON safe
ini_set('log_errors', 1);
error_reporting(E_ALL);

// ------------------------------
// Start output buffering
// ------------------------------
ob_start();
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once("../config/db.php");
require_once("../utils/auth_check.php");
require_once("../utils/response.php");
require_once("../utils/validation.php");

// ------------------------------
// Ensure session keys are consistent
// ------------------------------
if (!empty($_SESSION['user'])) {
    $_SESSION['user_id']  = $_SESSION['user']['user_id'] ?? null;
    $_SESSION['username'] = $_SESSION['user']['username'] ?? null;
    $_SESSION['role']     = $_SESSION['user']['role'] ?? null;
}

// ------------------------------
// Require admin access
// ------------------------------
try {
    auth_check(['admin']);
} catch (Exception $e) {
    ob_clean();
    Response::error("Access denied: " . $e->getMessage(), 403);
}

// ------------------------------
// Only allow POST
// ------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    Response::error("Invalid request method", 405);
}

// ------------------------------
// Decode JSON
// ------------------------------
$raw = file_get_contents("php://input");
$input = json_decode($raw, true);

if (!is_array($input)) {
    ob_clean();
    Response::error("Invalid or missing JSON body");
}

// ------------------------------
// Extract fields
// ------------------------------
$username         = trim($input['username'] ?? '');
$email            = trim($input['email'] ?? '');
$password         = $input['password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';

// ------------------------------
// Validate input
// ------------------------------
if ($username === '') Response::error("Username is required");
if ($email === '') Response::error("Email is required");
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Response::error("Invalid email format");
}
if ($password === '') Response::error("Password is required");
if (strlen($password) < 6) Response::error("Password must be at least 6 characters");
if ($password !== $confirm_password) Response::error("Passwords do not match");

// ------------------------------
// Main logic
// ------------------------------
try {
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) throw new Exception("Database connection failed");

    // --- Check duplicates ---
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        ob_clean();
        Response::error("Username or email already exists");
    }
    $stmt->close();

    // --- Hash password ---
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // --- Insert admin ---
    $stmt = $conn->prepare("
        INSERT INTO users (username, full_name, email, password, role, date_created)
        VALUES (?, ?, ?, ?, 'admin', NOW())
    ");
    if (!$stmt) throw new Exception("Prepare insert failed: " . $conn->error);

    $full_name = $username;
    $stmt->bind_param("ssss", $username, $full_name, $email, $password_hash);
    if (!$stmt->execute()) {
        throw new Exception("Insert failed: " . $stmt->error);
    }

    $new_admin_id = $stmt->insert_id;
    $stmt->close();

    // --- Log activity ---
    if (!empty($_SESSION['user_id'])) {
        $current_admin = (int) $_SESSION['user_id'];
        $stmt = $conn->prepare("
            INSERT INTO activity_log (user_id, action, details, timestamp)
            VALUES (?, 'add_admin', CONCAT('Admin ', ?, ' created'), NOW())
        ");
        if ($stmt) {
            $stmt->bind_param("is", $current_admin, $username);
            $stmt->execute();
            $stmt->close();
        }
    }

    // --- Success response ---
    ob_clean();
    Response::success([
        "user_id" => $new_admin_id,
        "username" => $username,
        "email" => $email
    ], "Admin added successfully");

} catch (mysqli_sql_exception $e) {
    error_log("add_admin.php SQL error: " . $e->getMessage());
    ob_clean();
    Response::error("Database error: " . $e->getMessage(), 500);

} catch (Exception $e) {
    error_log("add_admin.php error: " . $e->getMessage());
    ob_clean();
    Response::error("Server error: " . $e->getMessage(), 500);
}
