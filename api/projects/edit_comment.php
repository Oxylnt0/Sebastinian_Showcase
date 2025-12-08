<?php
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/response.php';

auth_check(); // User must be logged in
date_default_timezone_set('Asia/Manila');

// -----------------------
// Parse input
// -----------------------
$input = json_decode(file_get_contents('php://input'), true);

$comment_id   = intval($input['comment_id'] ?? 0);
$new_text_raw = trim($input['comment'] ?? '');

if ($comment_id <= 0 || $new_text_raw === '') {
    Response::error('Invalid input', 400);
}

// Escape to prevent XSS
$new_text = htmlspecialchars($new_text_raw, ENT_QUOTES, 'UTF-8');

$conn = (new Database())->connect();
$user_id = $_SESSION['user_id'];

// -----------------------
// Ensure the comment exists and belongs to user
// -----------------------
$stmt = $conn->prepare("SELECT user_id FROM comments WHERE comment_id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$res = $stmt->get_result();
$comment = $res->fetch_assoc();

if (!$comment) {
    Response::error('Comment not found', 404);
}
if ($comment['user_id'] != $user_id) {
    Response::error('Permission denied', 403);
}

// -----------------------
// Update comment text
// -----------------------
$upd = $conn->prepare("
    UPDATE comments 
    SET comment = ?, date_commented = CONVERT_TZ(NOW(), @@session.time_zone, '+08:00')
    WHERE comment_id = ?
");
$upd->bind_param("si", $new_text, $comment_id);
if (!$upd->execute()) {
    Response::error('Failed to update comment', 500);
}

// -----------------------
// Fetch updated comment details
// -----------------------
$stmt2 = $conn->prepare("
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
        GROUP BY comment_id
    ) l ON l.comment_id = c.comment_id
    WHERE c.comment_id = ?
");
$stmt2->bind_param("i", $comment_id);
$stmt2->execute();
$updated = $stmt2->get_result()->fetch_assoc();

if (!$updated) {
    Response::error('Error fetching updated comment', 500);
}

// -----------------------
// Compute time_ago
// -----------------------
$commentTime = new DateTime($updated['date_commented'], new DateTimeZone('Asia/Manila'));
$now         = new DateTime('now', new DateTimeZone('Asia/Manila'));
$diff        = max(0, $now->getTimestamp() - $commentTime->getTimestamp());

if ($diff < 60) {
    $updated['time_ago'] = $diff . " sec ago";
} elseif ($diff < 3600) {
    $updated['time_ago'] = floor($diff / 60) . " min ago";
} elseif ($diff < 86400) {
    $updated['time_ago'] = floor($diff / 3600) . " hr ago";
} elseif ($diff < 2592000) {
    $days = floor($diff / 86400);
    $updated['time_ago'] = $days . " day" . ($days > 1 ? "s" : "") . " ago";
} elseif ($diff < 31536000) {
    $months = floor($diff / 2592000);
    $updated['time_ago'] = $months . " month" . ($months > 1 ? "s" : "") . " ago";
} else {
    $years = floor($diff / 31536000);
    $updated['time_ago'] = $years . " year" . ($years > 1 ? "s" : "") . " ago";
}

// Replies need to exist for frontend structure consistency
$updated['replies'] = [];

// -----------------------
// Success Response
// -----------------------
Response::success(['comment' => $updated], 'Comment updated successfully');
