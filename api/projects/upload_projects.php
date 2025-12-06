<?php
// =========================================
// api/projects/upload_projects.php
// Handles file upload and project creation
// Always returns JSON
// =========================================

// Show PHP errors only during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required system files
require_once("../config/db.php");
require_once("../utils/auth_check.php");
require_once("../utils/response.php");
require_once("../utils/upload_handler.php");

// Always respond as JSON
header("Content-Type: application/json");

// ---------------------------------------------------------
// GLOBAL ERROR HANDLERS (Return JSON even for PHP fatals)
// ---------------------------------------------------------
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    Response::error("PHP Error [$errno]: $errstr in $errfile on line $errline");
});

set_exception_handler(function($e) {
    Response::error("Uncaught Exception: " . $e->getMessage());
});

// ==========================================
// MAIN EXECUTION BLOCK
// ==========================================
try {

    // -------------------------
    // Verify login + role
    // -------------------------
    Auth::requireLogin();
    Auth::requireRole(['student', 'admin']);

    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        Response::error("Authentication failed. Please log in again.");
    }

    // -------------------------
    // Accept only POST
    // -------------------------
    if ($_SERVER['REQUEST_METHOD'] !== "POST") {
        Response::error("Invalid request method", 405);
    }

    // -------------------------
    // Validate input fields
    // -------------------------
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '') Response::error("Project title is required");
    if ($description === '') Response::error("Project description is required");

    // -------------------------
    // Connect to DB
    // -------------------------
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) Response::error("Database connection failed");

    // ===========================================================
    // REQUIRED FILE UPLOAD: PROJECT FILE
    // ===========================================================
    if (!isset($_FILES['project_file']) || $_FILES['project_file']['error'] === UPLOAD_ERR_NO_FILE) {
        Response::error("Project file is required");
    }

    $file_result = UploadHandler::handle(
        $_FILES['project_file'],
        realpath(__DIR__ . "/../../uploads/project_files/") . "/",
        ['pdf', 'doc', 'docx', 'pptx', 'txt', 'zip'],
        10 * 1024 * 1024 // 10MB
    );

    if (!$file_result['success']) {
        Response::error($file_result['error']);
    }

    $file_upload = $file_result['filename'];

    // ===========================================================
    // OPTIONAL FILE UPLOAD: PROJECT IMAGE
    // ===========================================================
    $image_upload = null;

    if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] !== UPLOAD_ERR_NO_FILE) {

        $image_result = UploadHandler::handle(
            $_FILES['project_image'],
            realpath(__DIR__ . "/../../uploads/project_images/") . "/",
            ['png', 'jpg', 'jpeg', 'webp'],
            5 * 1024 * 1024 // 5MB
        );

        if (!$image_result['success']) {
            Response::error($image_result['error']);
        }

        $image_upload = $image_result['filename'];
    }

    // ===========================================================
    // INSERT PROJECT RECORD INTO DATABASE
    // ===========================================================
    $sql = "
        INSERT INTO projects (user_id, title, description, file, image)
        VALUES (?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        Response::error("Database prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "issss",
        $user_id,
        $title,
        $description,
        $file_upload,
        $image_upload
    );

    if ($stmt->execute()) {
        Response::success([
            "project_id" => $stmt->insert_id,
            "file" => $file_upload,
            "image" => $image_upload
        ], "Project uploaded successfully");
    } else {
        Response::error("Failed to save project: " . $stmt->error);
    }

} catch (Exception $e) {
    Response::error("Unexpected error: " . $e->getMessage());
}
