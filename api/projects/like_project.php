<?php
// ===========================================
// api/projects/like_project.php
// Toggle like for a project via AJAX
// ===========================================

session_start();
require_once("../config/db.php");
require_once("../utils/response.php");
require_once("../utils/auth_check.php");

// Only logged-in users can like
Auth::requireLogin();
$user_id = $_SESSION['user_id'];

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error("Invalid request method", 405);
}

// Decode JSON input
$input = json_decode(file_get_contents('php://input'), true);
$project_id = isset($input['project_id']) ? intval($input['project_id']) : 0;

if (!$project_id) {
    Response::error("Invalid project ID", 400);
}

try {
    $conn = (new Database())->connect();

    // Check if project exists
    $stmt_check = $conn->prepare("SELECT project_id FROM projects WHERE project_id = ?");
    $stmt_check->bind_param("i", $project_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows === 0) {
        Response::error("Project not found", 404);
    }

    // Check if user already liked
    $stmt_like_check = $conn->prepare("SELECT id FROM project_likes WHERE project_id = ? AND user_id = ?");
    $stmt_like_check->bind_param("ii", $project_id, $user_id);
    $stmt_like_check->execute();
    $stmt_like_check->store_result();

    if ($stmt_like_check->num_rows > 0) {
        // Already liked → remove like
        $stmt_delete = $conn->prepare("DELETE FROM project_likes WHERE project_id = ? AND user_id = ?");
        $stmt_delete->bind_param("ii", $project_id, $user_id);
        $stmt_delete->execute();
        $action = "unliked";
    } else {
        // Not liked → add like
        $stmt_insert = $conn->prepare("INSERT INTO project_likes (project_id, user_id, date_liked) VALUES (?, ?, NOW())");
        $stmt_insert->bind_param("ii", $project_id, $user_id);
        $stmt_insert->execute();
        $action = "liked";
    }

    // Get updated like count
    $stmt_count = $conn->prepare("SELECT COUNT(*) AS total_likes FROM project_likes WHERE project_id = ?");
    $stmt_count->bind_param("i", $project_id);
    $stmt_count->execute();
    $like_count = $stmt_count->get_result()->fetch_assoc()['total_likes'];

    Response::success([
        "project_id" => $project_id,
        "likes" => intval($like_count),
        "action" => $action
    ], "Project successfully $action.");

} catch (mysqli_sql_exception $e) {
    Response::error("Database error: " . $e->getMessage(), 500);
} catch (Exception $e) {
    Response::error("Server error: " . $e->getMessage(), 500);
}
