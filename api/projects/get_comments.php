<?php
// ===========================================
// api/projects/get_comments.php
// Returns all comments for a given project via AJAX
// ===========================================

session_start();
require_once("../config/db.php");
require_once("../utils/response.php");

// Only GET allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error("Invalid request method", 405);
}

// Get project_id safely from query string
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
if (!$project_id) {
    Response::error("Invalid project ID", 400);
}

try {
    $conn = (new Database())->connect();

    // Fetch comments
    $stmt = $conn->prepare("
        SELECT 
            c.comment_id,
            c.comment,
            c.date_commented,
            c.user_id,
            u.full_name,
            c.parent_id,
            c.likes
        FROM comments c
        LEFT JOIN users u ON c.user_id = u.user_id
        WHERE c.project_id = ?
        ORDER BY c.date_commented ASC
    ");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $comments = [];
    while ($row = $result->fetch_assoc()) {
        // Highlight owner comments if user is logged in
        $row['is_owner'] = isset($_SESSION['user_id']) && $_SESSION['user_id'] === intval($row['user_id']);
        $comments[] = $row;
    }

    Response::success($comments);

} catch (mysqli_sql_exception $e) {
    Response::error("Database error: " . $e->getMessage(), 500);
} catch (Exception $e) {
    Response::error("Server error: " . $e->getMessage(), 500);
}
