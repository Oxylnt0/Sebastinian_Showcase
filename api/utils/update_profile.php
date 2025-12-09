<?php
session_start();

// --- Enable error reporting for development (disable in production) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Output JSON ---
header('Content-Type: application/json');

// --- Require necessary files ---
require_once(__DIR__ . "/../config/db.php");
require_once(__DIR__ . "/../utils/validation.php");
require_once(__DIR__ . "/../utils/auth_check.php");
require_once(__DIR__ . "/../utils/response.php"); // Provides json_success() and json_error()
require_once(__DIR__ . "/../utils/upload_handler.php");

// --- Global error/exception handlers ---
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    json_error("PHP Error [$errno]: $errstr in $errfile on line $errline");
});
set_exception_handler(function($e) {
    json_error("Uncaught Exception: " . $e->getMessage());
});

// --- Auth check ---
auth_check();

// --- Only allow POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error("Invalid request method", 405);
}

// --- Connect to DB ---
$conn = (new Database())->connect();
if (!$conn) json_error("Database connection failed");

// --- Current user ID ---
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) json_error("User not logged in");

// --- Sanitize input ---
$full_name = Validation::sanitizeText($_POST['full_name'] ?? '');
$email     = Validation::sanitizeText($_POST['email'] ?? '');

// --- Validation ---
Validation::requireField($full_name, "Full name is required");
Validation::requireField($email, "Email is required");

if (!Validation::isValidEmail($email)) {
    json_error("Invalid email format");
}

// --- Check for duplicate email ---
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
if (!$stmt) json_error("Database prepare failed: " . $conn->error);
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    json_error("Email is already in use");
}

// --- Handle profile image upload ---
$profile_image_name = null;
if (!empty($_FILES['profile_image']['name'])) {
    $uploadDir = __DIR__ . "/../../uploads/profile_images/";
    if (!Validation::ensureDirectory($uploadDir)) {
        json_error("Failed to create upload directory");
    }

    $upload = UploadHandler::handle($_FILES['profile_image'], $uploadDir, ['jpg','jpeg','png','webp'], 3*1024*1024);
    if (!$upload['success']) json_error("Image upload failed: " . $upload['error']);
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

// --- Execute update ---
if ($stmt->execute()) {
    json_success([], "Profile updated successfully");
} else {
    json_error("Failed to update profile: " . $stmt->error);
}
