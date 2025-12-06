<?php
// ===========================================
// update_project_status.php – Sebastinian Showcase
// Handles project approval/rejection via AJAX
// Production-ready JSON API
// ===========================================

session_start();
require_once("../utils/auth_check.php");
require_once("../config/db.php");
require_once("../utils/response.php");

// Only admins can access
auth_check(['admin']);

// Ensure POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Invalid request method', 405);
}

// -----------------------------
// Get JSON input safely
// -----------------------------
$input = json_decode(file_get_contents('php://input'), true);
$project_id = isset($input['project_id']) ? intval($input['project_id']) : 0;
$status = isset($input['status']) ? strtolower(trim($input['status'])) : '';

// Validate input
$allowed_statuses = ['approved', 'rejected'];
if (!$project_id || !in_array($status, $allowed_statuses, true)) {
    Response::error('Invalid project ID or status', 400);
}

try {
    $conn = (new Database())->connect();

    // -----------------------------
    // Check if project exists
    // -----------------------------
    $stmt_check = $conn->prepare("SELECT project_id, status FROM projects WHERE project_id = ?");
    $stmt_check->bind_param("i", $project_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows === 0) {
        Response::error('Project not found', 404);
    }

    $stmt_check->bind_result($pid, $current_status);
    $stmt_check->fetch();

    // Already in desired status
    if ($current_status === $status) {
        Response::error("Project is already {$status}", 409);
    }

    // -----------------------------
    // Update project status
    // -----------------------------
    $stmt_update = $conn->prepare("
        UPDATE projects 
        SET status = ?, date_submitted = NOW() 
        WHERE project_id = ?
    ");
    $stmt_update->bind_param("si", $status, $project_id);
    if (!$stmt_update->execute()) {
        Response::error('Failed to update project status', 500);
    }

    // -----------------------------
    // Log admin action
    // -----------------------------
    if (!empty($_SESSION['user']['user_id'])) {
        $admin_id = $_SESSION['user']['user_id'];
        $stmt_log = $conn->prepare("
            INSERT INTO activity_log (user_id, action, details, created_at) 
            VALUES (?, 'project_status_update', CONCAT('Project ', ?, ' set to ', ?), NOW())
        ");
        $stmt_log->bind_param("iis", $admin_id, $project_id, $status);
        $stmt_log->execute();
    }

    // -----------------------------
    // Return success JSON
    // -----------------------------
    Response::success([], "Project status updated to {$status}");

} catch (mysqli_sql_exception $e) {
    Response::error('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    Response::error('Server error: ' . $e->getMessage(), 500);
}
