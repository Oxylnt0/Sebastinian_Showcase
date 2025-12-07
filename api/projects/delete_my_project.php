<?php
session_start();
require_once("../config/db.php");
require_once("../utils/auth_check.php");
require_once("../utils/response.php");

// User must be logged in
Auth::requireLogin();
$user = Auth::currentUser();
$user_id = $user['user_id'] ?? null;

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error("Invalid request method", 405);
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$project_id = isset($input['project_id']) ? intval($input['project_id']) : 0;

if (!$project_id) {
    Response::error("Invalid project ID", 400);
}

try {
    $conn = (new Database())->connect();

    // Check ownership
    $stmt_check = $conn->prepare("SELECT file, image, title, user_id FROM projects WHERE project_id = ?");
    $stmt_check->bind_param("i", $project_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $project = $result->fetch_assoc();

    if (!$project) Response::error("Project not found", 404);
    if ($project['user_id'] != $user_id) Response::error("You are not authorized to delete this project", 403);

    // Delete files
    if ($project['file'] && file_exists(__DIR__ . "/../../uploads/project_files/{$project['file']}")) {
        unlink(__DIR__ . "/../../uploads/project_files/{$project['file']}");
    }
    if ($project['image'] && file_exists(__DIR__ . "/../../uploads/project_images/{$project['image']}")) {
        unlink(__DIR__ . "/../../uploads/project_images/{$project['image']}");
    }

    // Delete record
    $stmt_delete = $conn->prepare("DELETE FROM projects WHERE project_id = ?");
    $stmt_delete->bind_param("i", $project_id);
    if ($stmt_delete->execute()) {
        Response::success([], "Project deleted successfully");
    } else {
        Response::error("Failed to delete project: " . $stmt_delete->error);
    }

} catch (mysqli_sql_exception $e) {
    Response::error("Database error: " . $e->getMessage(), 500);
} catch (Exception $e) {
    Response::error("Server error: " . $e->getMessage(), 500);
}
