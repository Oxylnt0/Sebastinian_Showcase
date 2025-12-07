<?php
// ===========================================
// api/projects/add_comment.php
// Adds a comment to a project via AJAX
// ===========================================

session_start();
require_once("../config/db.php");
require_once("../utils/response.php");
require_once("../utils/auth_check.php");

// Only logged-in users can comment
Auth::requireLogin();
$user_id = $_SESSION['user_id'];

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error("Invalid request method", 405);
}

// Decode JSON input
$input = json_decode(file_get_contents('php://input'), true);
$project_id = isset($input['project_id']) ? intval($input['project_id']) : 0;
$comment    = trim($input['comment'] ?? '');
$parent_id  = isset($input['parent_id']) ? intval($input['parent_id']) : null;

if (!$project_id) {
    Response::error("Invalid project ID", 400);
}

if ($comment === '') {
    Response::error("Comment cannot be empty", 400);
}

try {
    $conn = (new Database())->connect();

    // Optional: Check if project exists
    $stmt_check = $conn->prepare("SELECT project_id FROM projects WHERE project_id = ?");
    $stmt_check->bind_param("i", $project_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows === 0) {
        Response::error("Project not found", 404);
    }

    // Insert comment
    $stmt = $conn->prepare("
        INSERT INTO comments (project_id, user_id, comment, parent_id, date_commented, likes)
        VALUES (?, ?, ?, ?, NOW(), 0)
    ");
    $stmt->bind_param("iisi", $project_id, $user_id, $comment, $parent_id);
    if ($stmt->execute()) {
        $comment_id = $stmt->insert_id;

        // Fetch newly inserted comment to return
        $stmt_fetch = $conn->prepare("
            SELECT c.comment_id, c.comment, c.date_commented, c.user_id, u.full_name, c.parent_id, c.likes
            FROM comments c
            LEFT JOIN users u ON c.user_id = u.user_id
            WHERE c.comment_id = ?
        ");
        $stmt_fetch->bind_param("i", $comment_id);
        $stmt_fetch->execute();
        $new_comment = $stmt_fetch->get_result()->fetch_assoc();
        $new_comment['is_owner'] = true; // current user is the owner

        Response::success($new_comment, "Comment added successfully");
    } else {
        Response::error("Failed to add comment: " . $stmt->error);
    }

} catch (mysqli_sql_exception $e) {
    Response::error("Database error: " . $e->getMessage(), 500);
} catch (Exception $e) {
    Response::error("Server error: " . $e->getMessage(), 500);
}
