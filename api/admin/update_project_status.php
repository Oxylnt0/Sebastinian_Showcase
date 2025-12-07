<?php
// ===========================================
// update_project_status.php – Sebastinian Showcase
// Handles project approval/rejection via AJAX
// api/admin/update_project_status.php
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

// Valid statuses
$allowed_statuses = ['approved', 'rejected'];

if (!$project_id || !in_array($status, $allowed_statuses, true)) {
    Response::error('Invalid project ID or status', 400);
}

try {
    $conn = (new Database())->connect();

    // -----------------------------
    // Get Admin ID from Session
    // -----------------------------
    if (!isset($_SESSION['user']['user_id'])) {
        Response::error("Admin session invalid", 401);
    }
    $admin_id = intval($_SESSION['user']['user_id']);

    // -----------------------------
    // Check if project exists
    // -----------------------------
    $stmt_check = $conn->prepare("SELECT status FROM projects WHERE project_id = ?");
    $stmt_check->bind_param("i", $project_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows === 0) {
        Response::error('Project not found', 404);
    }

    $project = $result->fetch_assoc();
    $current_status = $project['status'];

    // Already same status
    if ($current_status === $status) {
        Response::error("Project is already {$status}", 409);
    }

    // -----------------------------
    // Update project status
    // -----------------------------
    $stmt_update = $conn->prepare("
        UPDATE projects 
        SET status = ? 
        WHERE project_id = ?
    ");
    $stmt_update->bind_param("si", $status, $project_id);
    if (!$stmt_update->execute()) {
        Response::error('Failed to update project status', 500);
    }

    // -----------------------------
    // Insert into approvals table
    // -----------------------------
    $stmt_approval = $conn->prepare("
        INSERT INTO approvals (project_id, approved_by, status, `date_approved`)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt_approval->bind_param("iis", $project_id, $admin_id, $status);
    $stmt_approval->execute();

    // -----------------------------
    // Log Admin Action
    // -----------------------------
    $stmt_log = $conn->prepare("
        INSERT INTO activity_log (user_id, action, details, `timestamp`)
        VALUES (?, 'project_status_update', CONCAT('Project ', ?, ' set to ', ?), NOW())
    ");
    $stmt_log->bind_param("iis", $admin_id, $project_id, $status);
    $stmt_log->execute();

    // -----------------------------
    // Success response
    // -----------------------------
    Response::success(["project_id" => $project_id, "new_status" => $status], "Project status updated successfully");

} catch (mysqli_sql_exception $e) {
    Response::error('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    Response::error('Server error: ' . $e->getMessage(), 500);
}
