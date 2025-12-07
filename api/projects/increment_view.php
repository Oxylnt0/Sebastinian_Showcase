<?php
// ===========================================
// api/projects/increment_view.php
// Increment project view count via AJAX
// ===========================================

require_once("../config/db.php");
require_once("../utils/response.php");

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

    // Increment view count atomically
    $stmt_update = $conn->prepare("
        UPDATE projects
        SET views = views + 1
        WHERE project_id = ?
    ");
    $stmt_update->bind_param("i", $project_id);
    $stmt_update->execute();

    if ($stmt_update->affected_rows === 0) {
        Response::error("Project not found", 404);
    }

    // Return updated view count
    $stmt_select = $conn->prepare("SELECT views FROM projects WHERE project_id = ?");
    $stmt_select->bind_param("i", $project_id);
    $stmt_select->execute();
    $views = $stmt_select->get_result()->fetch_assoc()['views'];

    Response::success([
        "project_id" => $project_id,
        "views" => intval($views)
    ], "View count incremented.");

} catch (mysqli_sql_exception $e) {
    Response::error("Database error: " . $e->getMessage(), 500);
} catch (Exception $e) {
    Response::error("Server error: " . $e->getMessage(), 500);
}
