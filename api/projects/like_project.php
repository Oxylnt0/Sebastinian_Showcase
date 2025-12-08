<?php
// like_project.php - Toggle project like
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/response.php';

// -----------------------
// Start session safely
// -----------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -----------------------
// Ensure user is logged in
// -----------------------
Auth::requireLogin();
$user_id = intval($_SESSION['user_id'] ?? 0);
if ($user_id <= 0) Response::error('Authentication required', 401);

// -----------------------
// Parse input
// -----------------------
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$project_id = intval($input['project_id'] ?? 0);
if ($project_id <= 0) Response::error('Project ID is required', 400);

// -----------------------
// Database connection
// -----------------------
$conn = (new Database())->connect();

// -----------------------
// Check if project exists
// -----------------------
$stmt = $conn->prepare("SELECT project_id FROM projects WHERE project_id = ? LIMIT 1");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result->fetch_assoc()) {
    $stmt->close();
    Response::error('Project not found', 404);
}
$stmt->close();

// -----------------------
// Toggle like
// -----------------------
$user_liked = false;

// Check if the user already liked this project
$stmtCheck = $conn->prepare("SELECT 1 FROM project_likes WHERE project_id = ? AND user_id = ? LIMIT 1");
$stmtCheck->bind_param("ii", $project_id, $user_id);
$stmtCheck->execute();
$exists = $stmtCheck->get_result()->num_rows > 0;
$stmtCheck->close();

if ($exists) {
    // Remove like
    $stmtDel = $conn->prepare("DELETE FROM project_likes WHERE project_id = ? AND user_id = ?");
    $stmtDel->bind_param("ii", $project_id, $user_id);
    $stmtDel->execute();
    $stmtDel->close();
    $user_liked = false;
} else {
    // Add like
    $stmtIns = $conn->prepare("INSERT INTO project_likes (project_id, user_id) VALUES (?, ?)");
    $stmtIns->bind_param("ii", $project_id, $user_id);
    $stmtIns->execute();
    $stmtIns->close();
    $user_liked = true;
}

// -----------------------
// Fetch updated like count
// -----------------------
$stmtCount = $conn->prepare("SELECT COUNT(*) AS total FROM project_likes WHERE project_id = ?");
$stmtCount->bind_param("i", $project_id);
$stmtCount->execute();
$like_count = intval($stmtCount->get_result()->fetch_assoc()['total'] ?? 0);
$stmtCount->close();

// -----------------------
// Return JSON response
// -----------------------
Response::success([
    'like_count' => $like_count,
    'user_liked' => $user_liked
], 'Project like toggled successfully');
