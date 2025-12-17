<?php
// =========================================
// api/projects/upload_projects.php
// Updated for Research Repository (PDF + Auto-Thumb)
// =========================================

// Show PHP errors only during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../config/db.php");
require_once("../utils/auth_check.php");
require_once("../utils/response.php");
require_once("../utils/upload_handler.php");

header("Content-Type: application/json");

// GLOBAL ERROR HANDLERS
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    Response::error("PHP Error [$errno]: $errstr in $errfile on line $errline");
});
set_exception_handler(function($e) {
    Response::error("Uncaught Exception: " . $e->getMessage());
});

try {
    // 1. AUTHENTICATION
    Auth::requireLogin();
    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) Response::error("Authentication failed. Please log in again.");

    if ($_SERVER['REQUEST_METHOD'] !== "POST") {
        Response::error("Invalid request method", 405);
    }

    // 2. VALIDATE INPUTS (New Research Fields)
    $title = trim($_POST['title'] ?? '');
    $authors = trim($_POST['authors'] ?? '');
    $pub_date = trim($_POST['publication_date'] ?? '');
    $res_type = trim($_POST['research_type'] ?? '');
    $dept = trim($_POST['department'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Strict validation for required fields
    if ($title === '') Response::error("Research title is required");
    if ($authors === '') Response::error("Authors are required");
    if ($pub_date === '') Response::error("Publication date is required");
    if ($res_type === '') Response::error("Research methodology is required");
    if ($dept === '') Response::error("Department is required");
    if ($description === '') Response::error("Abstract is required");

    // 3. DATABASE CONNECTION
    $db = new Database();
    $conn = $db->connect();
    if (!$conn) Response::error("Database connection failed");

    // 4. HANDLE MAIN PDF UPLOAD
    if (!isset($_FILES['project_file']) || $_FILES['project_file']['error'] === UPLOAD_ERR_NO_FILE) {
        Response::error("Research PDF is required");
    }

    // Define upload path for Files
    // ENSURE THIS FOLDER EXISTS: /uploads/project_files/
    $uploadDirFiles = __DIR__ . "/../../uploads/project_files/"; 
    if (!is_dir($uploadDirFiles)) mkdir($uploadDirFiles, 0777, true);

    $file_result = UploadHandler::handle(
        $_FILES['project_file'],
        $uploadDirFiles,
        ['pdf'], // Strict PDF only
        15 * 1024 * 1024 // 15MB Limit
    );

    if (!$file_result['success']) {
        Response::error($file_result['error']);
    }

    $file_upload = $file_result['filename'];
    $file_size = $_FILES['project_file']['size']; // Capture size for DB
    $file_type = "pdf"; // Hardcoded since we only allow PDF

    // 5. HANDLE AUTO-GENERATED THUMBNAIL (Base64)
    $image_upload = null;
    $base64_string = $_POST['generated_thumbnail'] ?? '';

    if (!empty($base64_string)) {
        // Define upload path for Images
        // ENSURE THIS FOLDER EXISTS: /uploads/project_images/
        $uploadDirImages = __DIR__ . "/../../uploads/project_images/";
        if (!is_dir($uploadDirImages)) mkdir($uploadDirImages, 0777, true);

        // Process Base64 String
        // Format is usually: "data:image/jpeg;base64,/9j/4AAQSkZJRg..."
        if (preg_match('/^data:image\/(\w+);base64,/', $base64_string, $type)) {
            $base64_string = substr($base64_string, strpos($base64_string, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, etc.

            if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                // Fallback if type is weird, assume jpg
                $type = 'jpg'; 
            }

            $decoded_image = base64_decode($base64_string);

            if ($decoded_image === false) {
                Response::error("Failed to decode thumbnail image.");
            }

            // Generate unique name: thumb_TIMESTAMP_RANDOM.jpg
            $image_name = 'thumb_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $type;
            $image_path = $uploadDirImages . $image_name;

            // Save the file
            if (file_put_contents($image_path, $decoded_image)) {
                $image_upload = $image_name;
            } else {
                Response::error("Failed to save thumbnail to server.");
            }
        }
    }

    // 6. INSERT INTO DATABASE (New Columns)
    // Note: 'status' defaults to 'pending' in your DB structure, so we omit it here or force it.
    $sql = "INSERT INTO projects 
            (user_id, title, authors, publication_date, research_type, department, description, file, image, file_type, file_size, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        Response::error("Database prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "isssssssssi", // 11 parameters: i = int, s = string
        $user_id,
        $title,
        $authors,
        $pub_date,
        $res_type,
        $dept,
        $description,
        $file_upload,
        $image_upload, // Can be null, but DB column should allow NULL
        $file_type,
        $file_size
    );

    if ($stmt->execute()) {
        Response::success([
            "project_id" => $stmt->insert_id,
            "title" => $title
        ], "Research archived successfully");
    } else {
        Response::error("Failed to save to database: " . $stmt->error);
    }

} catch (Exception $e) {
    Response::error("Server Error: " . $e->getMessage());
}
?>