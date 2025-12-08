<?php
// toggle_comment_like.php - Toggle comment like
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
if ($user_id <= 0) Response::error('Authentication error', 401);

$conn = (new Database())->connect();

// -----------------------
// Ensure comment exists
// -----------------------
$stmtCheck = $conn->prepare("SELECT comment_id FROM comments WHERE comment_id = ? LIMIT 1");
$stmtCheck->bind_param("i", $comment_id);
$stmtCheck->execute();
if (!$stmtCheck->get_result()->fetch_assoc()) {
    $stmtCheck->close();
    Response::error('Comment not found', 404);
}
$stmtCheck->close();

// -----------------------
// Check if user already liked this comment
// -----------------------
$user_liked = false;
$stmt = $conn->prepare("SELECT 1 FROM comment_likes WHERE comment_id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param("ii", $comment_id, $user_id);
$stmt->execute();
$exists = $stmt->get_result()->num_rows > 0;
$stmt->close();

// -----------------------
// Toggle like
// -----------------------
if ($exists) {
    $stmtDel = $conn->prepare("DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?");
    $stmtDel->bind_param("ii", $comment_id, $user_id);
    $stmtDel->execute();
    $stmtDel->close();
    $user_liked = false;
} else {
    $stmtIns = $conn->prepare("INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)");
    $stmtIns->bind_param("ii", $comment_id, $user_id);
    if (!$stmtIns->execute()) {
        $stmtIns->close();
        Response::error('Failed to like comment', 500);
    }
    $stmtIns->close();
    $user_liked = true;
}

// -----------------------
// Fetch updated like count
// -----------------------
$stmtCount = $conn->prepare("SELECT COUNT(*) AS total FROM comment_likes WHERE comment_id = ?");
$stmtCount->bind_param("i", $comment_id);
$stmtCount->execute();
$like_count = intval($stmtCount->get_result()->fetch_assoc()['total'] ?? 0);
$stmtCount->close();

// -----------------------
// Return JSON response
// -----------------------
Response::success([
    'like_count' => $like_count,
    'liked' => $user_liked
], 'Comment like toggled successfully');
