<?php
session_start();
require_once("../config/db.php");
require_once("../utils/auth_check.php");
require_once("../utils/response.php");
require_once("../utils/upload_handler.php");


// Ensure user is logged in
auth_check(['student', 'admin']); // only students/admins can upload
$user_id = $_SESSION['user_id'];

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response_json('error', 'Invalid request method', 405);
}

// Get POST data
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

// Validate required fields
if ($title === '') response_json('error', 'Project title is required');
if ($description === '') response_json('error', 'Project description is required');

// Connect to DB
$conn = (new Database())->connect();

// Handle project file upload
$project_file = $_FILES['project_file'] ?? null;
if (!$project_file || $project_file['name'] === '') {
    response_json('error', 'Project file is required');
}

$file_upload = upload_file(
    'project_file',
    '../../uploads/project_files/',
    ['pdf', 'doc', 'docx', 'pptx', 'txt', 'zip']
);

if (!$file_upload) {
    response_json('error', 'Failed to upload project file');
}

// Handle project image upload (optional)
$image_name = null;
$project_image = $_FILES['project_image'] ?? null;
if ($project_image && $project_image['name'] !== '') {
    $image_upload = upload_file(
        'project_image',
        '../../uploads/project_images/',
        ['png', 'jpg', 'jpeg', 'webp']
    );

    if (!$image_upload) {
        response_json('error', 'Failed to upload project image');
    }
    $image_name = $image_upload;
}

// Insert into projects table
// If your DB requires sdg_id to be NOT NULL, set it to NULL or a default value
$stmt = $conn->prepare("
    INSERT INTO projects (user_id, title, description, file, image)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("issss", $user_id, $title, $description, $file_upload, $image_name);

if ($stmt->execute()) {
    response_json('success', 'Project uploaded successfully', [
        'project_id' => $stmt->insert_id,
        'file' => $file_upload,
        'image' => $image_name
    ]);
} else {
    response_json('error', 'Failed to save project to database');
}
