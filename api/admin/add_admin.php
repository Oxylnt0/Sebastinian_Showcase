<?php
// ===========================================
// api/admin/add_admin.php
// Adds a new admin via AJAX (JSON only)
// ===========================================

ini_set('display_errors', 0); 
ini_set('log_errors', 1);
error_reporting(E_ALL);

ob_start();
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once("../config/db.php");
require_once("../utils/auth_check.php");
require_once("../utils/response.php");
require_once("../utils/validation.php");

// Ensure session keys are consistent
if (!empty($_SESSION['user'])) {
    $_SESSION['user_id']  = $_SESSION['user']['user_id'] ?? null;
    $_SESSION['username'] = $_SESSION['user']['username'] ?? null;
    $_SESSION['role']     = $_SESSION['user']['role'] ?? null;
}

try {
    auth_check(['admin']);
} catch (Exception $e) {
    ob_clean();
    Response::error("Access denied: " . $e->getMessage(), 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    Response::error("Invalid request method", 405);
}

$raw = file_get_contents("php://input");
$input = json_decode($raw, true);

if (!is_array($input)) {
    ob_clean();
    Response::error("Invalid or missing JSON body");
}

$username         = trim($input['username'] ?? '');
$email            = trim($input['email'] ?? '');
$password         = $input['password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';

// ------------------------------
// STRICT VALIDATION
// ------------------------------

if ($username === '') Response::error("Username is required");
if ($email === '') Response::error("Email is required");

// 1. Institutional Domain Check (@sscr.edu)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Response::error("Invalid email format");
}
if (!str_ends_with(strtolower($email), '@sscr.edu')) {
    Response::error("Only @sscr.edu institutional emails are allowed for admins.");
}

// 2. High-Complexity Password Check (12+, Upper, Lower, Num, Special)
if ($password === '') Response::error("Password is required");

$password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{12,}$/';
if (!preg_match($password_pattern, $password)) {
    Response::error("Password must be 12+ chars with an uppercase letter, a number, and a special character.");
}

if ($password !== $confirm_password) Response::error("Passwords do not match");

// ------------------------------
// DATABASE LOGIC
// ------------------------------
try {
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) throw new Exception("Database connection failed");

    // Check duplicates
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        ob_clean();
        Response::error("Username or email already exists");
    }
    $stmt->close();

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO users (username, full_name, email, password, role, is_verified, date_created)
        VALUES (?, ?, ?, ?, 'admin', 1, NOW())
    ");

    $full_name = $username; 
    $stmt->bind_param("ssss", $username, $full_name, $email, $password_hash);
    if (!$stmt->execute()) {
        throw new Exception("Insert failed: " . $stmt->error);
    }

    $new_admin_id = $stmt->insert_id;
    $stmt->close();

    // Log activity
    if (!empty($_SESSION['user_id'])) {
        $current_admin = (int) $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO activity_log (user_id, action, details) VALUES (?, 'add_admin', ?)");
        $log_details = "Created admin account: $username";
        $stmt->bind_param("is", $current_admin, $log_details);
        $stmt->execute();
        $stmt->close();
    }

    ob_clean();
    Response::success(["user_id" => $new_admin_id], "Admin authorized successfully");

} catch (Exception $e) {
    ob_clean();
    Response::error("Server error: " . $e->getMessage(), 500);
}