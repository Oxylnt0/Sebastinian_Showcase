<?php
// ================================
// upload_projects.php - Sebastinian Showcase
// Handles project uploads via AJAX
// ================================

// Enable PHP errors for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require necessary files
require_once("../config/db.php");
require_once("../utils/auth_check.php"); // Correct path
require_once("../utils/response.php");
require_once("../utils/upload_handler.php"); // Correct path

// Always return JSON
header('Content-Type: application/json');

// Catch uncaught errors or exceptions and return as JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (headers_sent() === false) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        'status' => 'error',
        'message' => "PHP Error [$errno]: $errstr in $errfile on line $errline"
    ]);
    exit;
});

set_exception_handler(function($e) {
    if (headers_sent() === false) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        'status' => 'error',
        'message' => "Uncaught Exception: " . $e->getMessage()
    ]);
    exit;
});

try {
    // ----------------------
    // Authentication
    // ----------------------
    Auth::requireLogin();
    Auth::requireRole(['student','admin']);

    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) Response::error("User session invalid or expired");

    // ----------------------
    // Only allow POST requests
    // ----------------------
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        Response::error('Invalid request method', 405);
    }

    // ----------------------
    // Validate POST data
    // ----------------------
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '') Response::error('Project title is required');
    if ($description === '') Response::error('Project description is required');

    // ----------------------
    // Connect to DB
    // ----------------------
    $conn = (new Database())->connect();
    if (!$conn) Response::error('Database connection failed');

    // ----------------------
    // Handle project file upload (required)
    // ----------------------
    if (!isset($_FILES['project_file']) || $_FILES['project_file']['name'] === '') {
        Response::error('Project file is required');
    }

    $file_result = UploadHandler::handle(
        $_FILES['project_file'],
        '../../uploads/project_files/',
        ['pdf','doc','docx','pptx','txt','zip'],
        10 * 1024 * 1024 // 10 MB max
    );

    if (!$file_result['success']) Response::error($file_result['error']);
    $file_upload = $file_result['filename'];

    // ----------------------
    // Handle project image upload (optional)
    // ----------------------
    $image_upload = null;
    if (isset($_FILES['project_image']) && $_FILES['project_image']['name'] !== '') {
        $image_result = UploadHandler::handle(
            $_FILES['project_image'],
            '../../uploads/project_images/',
            ['png','jpg','jpeg','webp'],
            5 * 1024 * 1024 // 5 MB max
        );
        if (!$image_result['success']) Response::error($image_result['error']);
        $image_upload = $image_result['filename'];
    }

    // ----------------------
    // Insert project into DB
    // ----------------------
    $stmt = $conn->prepare("
        INSERT INTO projects (user_id, title, description, file, image)
        VALUES (?, ?, ?, ?, ?)
    ");

    if (!$stmt) Response::error("Failed to prepare statement: " . $conn->error);

    $stmt->bind_param("issss", $user_id, $title, $description, $file_upload, $image_upload);

    if ($stmt->execute()) {
        Response::success([
            'project_id' => $stmt->insert_id,
            'file' => $file_upload,
            'image' => $image_upload
        ], 'Project uploaded successfully');
    } else {
        Response::error("Failed to save project: " . $stmt->error);
    }

} catch (Exception $e) {
    Response::error("Unexpected error: " . $e->getMessage());
}
