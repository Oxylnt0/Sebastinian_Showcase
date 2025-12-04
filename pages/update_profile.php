<?php
session_start();
require_once("../config/db.php");
require_once("validation.php");
require_once("auth_check.php");
require_once("response.php");
require_once("upload_handler.php");

header('Content-Type: application/json');

// Ensure user is logged in
auth_check();

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response_error("Invalid request method", 405);
    exit;
}

$conn = (new Database())->connect();

// Retrieve current user ID
$user_id = $_SESSION['user_id'];

// Sanitize input
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');

// Basic validation
if (!validate_required($full_name) || !validate_required($email)) {
    response_error("Full name and email are required");
    exit;
}

if (!validate_email($email)) {
    response_error("Invalid email format");
    exit;
}

// Check if email already exists for another user
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    response_error("Email is already in use by another account");
    exit;
}

// Handle profile image upload if provided
$profile_image_name = null;
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $upload = handle_upload($_FILES['profile_image'], "../../uploads/profile_images/", ['jpg','jpeg','png','webp']);
    if (!$upload['success']) {
        response_error($upload['message']);
        exit;
    }
    $profile_image_name = $upload['filename'];
}

// Update user in database
if ($profile_image_name) {
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, profile_image = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $full_name, $email, $profile_image_name, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $full_name, $email, $user_id);
}

if ($stmt->execute()) {
    response_success("Profile updated successfully");
} else {
    response_error("Failed to update profile");
}
