<?php
// delete_my_project.php - Delete a project along with related data
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/response.php';

auth_check(); // Ensure user is logged in

// -----------------------
// Parse input
// -----------------------
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$project_id = intval($input['project_id'] ?? 0);
if ($project_id <= 0) Response::error('Project ID is required', 400);

$conn = (new Database())->connect();
$user_id = intval($_SESSION['user_id'] ?? 0);
$user_role = $_SESSION['role'] ?? '';
if ($user_id <= 0) Response::error('Authentication error', 401);

// -----------------------
// Verify ownership or admin
// -----------------------
$stmt = $conn->prepare("SELECT user_id, file FROM projects WHERE project_id = ? LIMIT 1");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) Response::error('Project not found', 404);
if ($project['user_id'] !== $user_id && $user_role !== 'admin') {
    Response::error('Permission denied', 403);
}

// -----------------------
// Begin transaction
// -----------------------
$conn->begin_transaction();
try {
    // -----------------------
    // Delete comment likes & comments
    // -----------------------
    $stmt = $conn->prepare("SELECT comment_id FROM comments WHERE project_id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $commentIds = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (!empty($commentIds)) {
        $ids = array_column($commentIds, 'comment_id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));

        // Delete comment likes
        $stmt = $conn->prepare("DELETE FROM comment_likes WHERE comment_id IN ($placeholders)");
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $stmt->close();

        // Delete comments
        $stmt = $conn->prepare("DELETE FROM comments WHERE comment_id IN ($placeholders)");
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $stmt->close();
    }

    // -----------------------
    // Delete project likes
    // -----------------------
    $stmt = $conn->prepare("DELETE FROM project_likes WHERE project_id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $stmt->close();

    // -----------------------
    // Delete download logs
    // -----------------------
    $stmt = $conn->prepare("DELETE FROM downloads_log WHERE project_id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $stmt->close();

    // -----------------------
    // Delete project file from server
    // -----------------------
    if (!empty($project['file'])) {
        $filepath = __DIR__ . '/../../uploads/project_files/' . $project['file'];
        if (file_exists($filepath) && !@unlink($filepath)) {
            throw new Exception('Failed to delete project file from server.');
        }
    }

    // -----------------------
    // Delete project itself
    // -----------------------
    $stmt = $conn->prepare("DELETE FROM projects WHERE project_id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    $conn->commit();

    Response::success([], 'Project deleted successfully');

} catch (Exception $e) {
    $conn->rollback();
    Response::error('Failed to delete project: ' . $e->getMessage());
}
