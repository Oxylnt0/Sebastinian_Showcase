<?php
// add_comment.php - Final Polished Version
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/response.php';

auth_check(); // User must be logged in

date_default_timezone_set('Asia/Manila');

// -----------------------
// Parse & validate input
// -----------------------
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$project_id = intval($input['project_id'] ?? 0);
$comment_text_raw = trim((string)($input['comment'] ?? ''));
$parent_id = isset($input['parent_id']) && $input['parent_id'] !== '' ? intval($input['parent_id']) : null;

if ($project_id <= 0) Response::error('Project ID is required', 400);
if ($comment_text_raw === '') Response::error('Comment text is required', 400);

$MAX_COMMENT_LENGTH = 2000;
if (mb_strlen($comment_text_raw) > $MAX_COMMENT_LENGTH) {
    Response::error("Comment too long (max {$MAX_COMMENT_LENGTH} chars)", 400);
}

// Escape HTML to prevent XSS
$comment_text = htmlspecialchars($comment_text_raw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

// -----------------------
// DB connection & user
// -----------------------
$conn = (new Database())->connect();
$user_id = intval($_SESSION['user_id'] ?? 0);
if ($user_id <= 0) Response::error('Authentication error', 401);

// -----------------------
// Validate project exists
// -----------------------
$stmt = $conn->prepare("SELECT project_id FROM projects WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) {
    $stmt->close();
    Response::error('Project not found', 404);
}
$stmt->close();

// -----------------------
// Validate parent_id (if provided)
if ($parent_id !== null) {
    $stmt = $conn->prepare("SELECT comment_id, project_id FROM comments WHERE comment_id = ?");
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $parentRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$parentRow || intval($parentRow['project_id']) !== $project_id) {
        Response::error('Invalid parent comment', 400);
    }
}

// -----------------------
// Prevent duplicate submission (5s window)
$timeWindow = 5; // seconds
if ($parent_id === null) {
    $stmt = $conn->prepare("
        SELECT 1 FROM comments
        WHERE project_id = ? AND user_id = ? AND comment = ? AND parent_id IS NULL
        AND date_commented > DATE_SUB(CONVERT_TZ(NOW(), @@session.time_zone, '+08:00'), INTERVAL ? SECOND)
        LIMIT 1
    ");
    $stmt->bind_param("iisi", $project_id, $user_id, $comment_text, $timeWindow);
} else {
    $stmt = $conn->prepare("
        SELECT 1 FROM comments
        WHERE project_id = ? AND user_id = ? AND comment = ? AND parent_id = ?
        AND date_commented > DATE_SUB(CONVERT_TZ(NOW(), @@session.time_zone, '+08:00'), INTERVAL ? SECOND)
        LIMIT 1
    ");
    $stmt->bind_param("iisii", $project_id, $user_id, $comment_text, $parent_id, $timeWindow);
}
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    Response::error('Duplicate comment detected (too fast)', 409);
}
$stmt->close();

// -----------------------
// Insert comment
$stmt = $conn->prepare("
    INSERT INTO comments (project_id, user_id, parent_id, comment, date_commented)
    VALUES (?, ?, ?, ?, CONVERT_TZ(NOW(), @@session.time_zone, '+08:00'))
");
$stmt->bind_param("iiis", $project_id, $user_id, $parent_id, $comment_text);
if (!$stmt->execute()) {
    $err = $stmt->error ?: $conn->error;
    $stmt->close();
    Response::error('Failed to add comment: ' . $err, 500);
}
$comment_id = $stmt->insert_id;
$stmt->close();

// -----------------------
// Fetch inserted comment with user info and likes
$stmt = $conn->prepare("
    SELECT 
        c.comment_id,
        c.parent_id,
        c.comment,
        c.date_commented,
        COALESCE(l.like_count, 0) AS likes,
        u.full_name,
        u.user_id
    FROM comments c
    LEFT JOIN users u ON c.user_id = u.user_id
    LEFT JOIN (
        SELECT comment_id, COUNT(*) AS like_count
        FROM comment_likes
        WHERE comment_id = ?
        GROUP BY comment_id
    ) l ON l.comment_id = c.comment_id
    WHERE c.comment_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $comment_id, $comment_id);
$stmt->execute();
$newComment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$newComment) Response::error('Failed to retrieve new comment', 500);

// -----------------------
// Format time_ago
function time_ago_php($timestamp) {
    $diff = max(0, time() - $timestamp);
    if ($diff < 60) return $diff . " sec ago";
    if ($diff < 3600) return floor($diff / 60) . " min ago";
    if ($diff < 86400) return floor($diff / 3600) . " hr ago";
    if ($diff < 172800) return "Yesterday";
    if ($diff < 2592000) return floor($diff / 86400) . " day" . (floor($diff / 86400) > 1 ? "s" : "") . " ago";
    if ($diff < 31536000) return floor($diff / 2592000) . " month" . (floor($diff / 2592000) > 1 ? "s" : "") . " ago";
    return floor($diff / 31536000) . " year" . (floor($diff / 31536000) > 1 ? "s" : "") . " ago";
}

$commentTime = strtotime($newComment['date_commented']);
$newComment['time_ago'] = time_ago_php($commentTime);

// -----------------------
// Ensure replies key exists for frontend
$newComment['replies'] = [];

// Cast to proper types
$newComment['comment_id'] = intval($newComment['comment_id']);
$newComment['parent_id'] = $newComment['parent_id'] !== null ? intval($newComment['parent_id']) : null;
$newComment['user_id'] = intval($newComment['user_id']);
$newComment['likes'] = intval($newComment['likes']);

// -----------------------
// Return JSON success
Response::success(['comment' => $newComment], 'Comment added');
exit;
