<?php
session_start();

// --- Enable error reporting (remove in production) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Output buffering to prevent accidental output breaking JSON ---
ob_start();

// --- Require necessary files ---
require_once(__DIR__ . "/../api/config/db.php");
require_once(__DIR__ . "/../api/utils/validation.php");
require_once(__DIR__ . "/../api/utils/auth_check.php");
require_once(__DIR__ . "/../api/utils/response.php");
require_once(__DIR__ . "/../api/utils/upload_handler.php");

// --- Ensure response is JSON ---
header('Content-Type: application/json');

// --- Catch uncaught errors or exceptions ---
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!headers_sent()) header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => "PHP Error [$errno]: $errstr in $errfile on line $errline"
    ]);
    exit;
});

set_exception_handler(function($e) {
    if (!headers_sent()) header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => "Uncaught Exception: " . $e->getMessage()
    ]);
    exit;
});

// --- Ensure user is logged in ---
auth_check();

// --- Only allow POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    response_error("Invalid request method", 405);
    exit;
}

// --- Connect to database ---
$conn = (new Database())->connect();
if (!$conn) response_error("Database connection failed");

// --- Get current user ID ---
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) response_error("User not logged in");

// --- Sanitize input ---
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');

// --- Validation ---
if (!validate_required($full_name) || !validate_required($email)) response_error("Full name and email are required");
if (!validate_email($email)) response_error("Invalid email format");

// --- Check for duplicate email ---
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
if (!$stmt) response_error("Database prepare failed: " . $conn->error);
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) response_error("Email is already in use");

// --- Handle profile image upload ---
$profile_image_name = null;
if (!empty($_FILES['profile_image']['name'])) {
    $uploadDir = __DIR__ . "/../../uploads/profile_images/";
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) response_error("Failed to create upload directory");

    $upload = UploadHandler::handle($_FILES['profile_image'], $uploadDir, ['jpg','jpeg','png','webp'], 5*1024*1024);
    if (!$upload['success']) response_error("Image upload failed: " . $upload['error']);
    $profile_image_name = $upload['filename'];
}

// --- Update user in database ---
if ($profile_image_name) {
    $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, profile_image=? WHERE user_id=?");
    $stmt->bind_param("sssi", $full_name, $email, $profile_image_name, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET full_name=?, email=? WHERE user_id=?");
    $stmt->bind_param("ssi", $full_name, $email, $user_id);
}

// --- Execute the update ---
if ($stmt->execute()) {
    ob_end_clean();
    response_success("Profile updated successfully");
} else {
    ob_end_clean();
    response_error("Failed to update profile: " . $stmt->error);
}
