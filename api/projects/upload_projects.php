<?php
session_start();
require_once("../config/db.php");
require_once("../utils/auth_check.php");
require_once("../utils/response.php");
require_once("../utils/upload_handler.php");
require_once("../utils/validation.php");

// Only logged-in users can upload
auth_check(['student', 'admin']);

$conn = (new Database())->connect();

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response_json('error', 'Invalid request method', 405);
}

// Gather POST data
$user_id = $_SESSION['user_id'];
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$sdg_id = intval($_POST['sdg_id'] ?? 0);

// Validate input
if ($title === '' || $description === '') {
    response_json('error', 'Title and description are required');
}

// Validate SDG exists
$sdg_check = $conn->prepare("SELECT sdg_id FROM sdgs WHERE sdg_id = ?");
$sdg_check->bind_param("i", $sdg_id);
$sdg_check->execute();
$res = $sdg_check->get_result();
if ($res->num_rows === 0) {
    response_json('error', 'Invalid SDG selection');
}

// Handle file upload
$file_name = upload_file('project_file', '../../uploads/project_files/', ['pdf','docx','pptx','txt','zip']);
$image_name = upload_file('project_image', '../../uploads/project_images/', ['png','jpg','jpeg','webp']);

// Insert project
$stmt = $conn->prepare("
    INSERT INTO projects (user_id, title, description, file, image, sdg_id)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("issssi", $user_id, $title, $description, $file_name, $image_name, $sdg_id);

if ($stmt->execute()) {
    response_json('success', 'Project uploaded successfully');
} else {
    response_json('error', 'Failed to upload project');
}
    