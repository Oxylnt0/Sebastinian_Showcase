<?php
// ===========================================
// api/admin/delete_project.php
// Deletes a project via AJAX
// ===========================================

session_start();
require_once("../config/db.php");
require_once("../utils/auth_check.php");
require_once("../utils/response.php");

// Only admins can access
auth_check(['admin']);

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error("Invalid request method", 405);
}

// Get JSON input safely
$input = json_decode(file_get_contents('php://input'), true);
$project_id = isset($input['project_id']) ? intval($input['project_id']) : 0;

if (!$project_id) {
    Response::error("Invalid project ID", 400);
}

try {
    $conn = (new Database())->connect();

    // -------------------------
    // Check if project exists
    // -------------------------
    $stmt_check = $conn->prepare("SELECT file, image, title FROM projects WHERE project_id = ?");
    $stmt_check->bind_param("i", $project_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows === 0) {
        Response::error("Project not found", 404);
    }

    $stmt_check->bind_result($file, $image, $title);
    $stmt_check->fetch();

    // -------------------------
    // Delete project files
    // -------------------------
    if ($file && file_exists(__DIR__ . "/../../uploads/project_files/$file")) {
        unlink(__DIR__ . "/../../uploads/project_files/$file");
    }
    if ($image && file_exists(__DIR__ . "/../../uploads/project_images/$image")) {
        unlink(__DIR__ . "/../../uploads/project_images/$image");
    }

    // -------------------------
    // Delete project record
    // -------------------------
    $stmt_delete = $conn->prepare("DELETE FROM projects WHERE project_id = ?");
    $stmt_delete->bind_param("i", $project_id);

    if ($stmt_delete->execute()) {
        // -------------------------
        // Log admin action
        // -------------------------
        $admin_id = $_SESSION['user']['user_id'] ?? null;
        if ($admin_id) {
            $stmt_log = $conn->prepare("
                INSERT INTO activity_log (user_id, action, details, timestamp)
                VALUES (?, 'delete_project', CONCAT('Deleted project ', ?), NOW())
            ");
            $stmt_log->bind_param("is", $admin_id, $title);
            $stmt_log->execute();
        }

        Response::success([], "Project deleted successfully");
    } else {
        Response::error("Failed to delete project: " . $stmt_delete->error);
    }

} catch (mysqli_sql_exception $e) {
    Response::error("Database error: " . $e->getMessage(), 500);
} catch (Exception $e) {
    Response::error("Server error: " . $e->getMessage(), 500);
}
