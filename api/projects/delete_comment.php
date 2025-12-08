<?php
// delete_comment.php - Delete a comment
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/response.php';

auth_check(); // Ensure user is logged in

// -----------------------
// Parse input
// -----------------------
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$comment_id = intval($input['comment_id'] ?? 0);
if ($comment_id <= 0) Response::error('Comment ID is required', 400);

$user_id = intval($_SESSION['user_id'] ?? 0);
$user_role = $_SESSION['role'] ?? '';
if ($user_id <= 0) Response::error('Authentication error', 401);

$conn = (new Database())->connect();

// -----------------------
// Check if comment exists
// -----------------------
$stmtCheck = $conn->prepare("SELECT user_id FROM comments WHERE comment_id = ? LIMIT 1");
$stmtCheck->bind_param("i", $comment_id);
$stmtCheck->execute();
$comment = $stmtCheck->get_result()->fetch_assoc();
$stmtCheck->close();

if (!$comment) Response::error('Comment not found', 404);

// -----------------------
// Only owner or admin can delete
// -----------------------
if ($comment['user_id'] !== $user_id && $user_role !== 'admin') {
    Response::error('Permission denied', 403);
}

// -----------------------
// Delete comment
// -----------------------
$stmtDel = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
$stmtDel->bind_param("i", $comment_id);
if (!$stmtDel->execute()) {
    $stmtDel->close();
    Response::error('Failed to delete comment', 500);
}
$stmtDel->close();

// -----------------------
// Return success
// -----------------------
Response::success([], 'Comment deleted successfully');
