<?php
// get_comments.php - Final Complete Version
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/response.php';

date_default_timezone_set('Asia/Manila');

// -----------------------
// Validate project_id
// -----------------------
$project_id = intval($_GET['project_id'] ?? 0);
if ($project_id <= 0) {
    Response::error('Project ID is required', 400);
    exit;
}

$conn = (new Database())->connect();

// -----------------------
// Helper: Format time ago
// -----------------------
function formatTimeAgo($datetime) {
    try {
        $time = new DateTime($datetime, new DateTimeZone('Asia/Manila'));
    } catch (Exception $e) {
        return "unknown";
    }

    $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $diff = max(0, $now->getTimestamp() - $time->getTimestamp());

    if ($diff < 60) return $diff . " sec ago";
    if ($diff < 3600) return floor($diff / 60) . " min ago";
    if ($diff < 86400) return floor($diff / 3600) . " hr ago";
    if ($diff < 172800) return "Yesterday";

    if ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    }

    if ($diff < 31536000) {
        $months = floor($diff / 2592000);
        return $months . " month" . ($months > 1 ? "s" : "") . " ago";
    }

    $years = floor($diff / 31536000);
    return $years . " year" . ($years > 1 ? "s" : "") . " ago";
}

// -----------------------
// Fetch all comments with like counts
// -----------------------
$sql = "
    SELECT 
        c.comment_id,
        c.parent_id,
        c.comment,
        c.date_commented,
        u.full_name,
        u.user_id,
        COALESCE(l.like_count, 0) AS likes
    FROM comments c
    LEFT JOIN users u ON c.user_id = u.user_id
    LEFT JOIN (
        SELECT comment_id, COUNT(*) AS like_count
        FROM comment_likes
        GROUP BY comment_id
    ) l ON l.comment_id = c.comment_id
    WHERE c.project_id = ?
    ORDER BY c.date_commented ASC
";

$stmt = $conn->prepare($sql);
if (!$stmt) Response::error("Database error (prepare): " . $conn->error, 500);

$stmt->bind_param("i", $project_id);
$stmt->execute();
$res = $stmt->get_result();
$rows = $res->fetch_all(MYSQLI_ASSOC);

// -----------------------
// Build tree structure
// -----------------------
$commentsById = [];
$rootComments = [];

foreach ($rows as $row) {
    $comment = [
        'comment_id'     => intval($row['comment_id']),
        'parent_id'      => $row['parent_id'] !== null ? intval($row['parent_id']) : null,
        'comment'        => htmlspecialchars($row['comment'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        'date_commented' => $row['date_commented'],
        'time_ago'       => formatTimeAgo($row['date_commented']),
        'likes'          => intval($row['likes']),
        'full_name'      => $row['full_name'],
        'user_id'        => intval($row['user_id']),
        'replies'        => []
    ];

    $commentsById[$comment['comment_id']] = $comment;
}

// Nest comments
foreach ($commentsById as $id => &$comment) {
    if ($comment['parent_id'] === null) {
        $rootComments[] = &$comment;
    } else {
        $parentId = $comment['parent_id'];
        if (isset($commentsById[$parentId])) {
            $commentsById[$parentId]['replies'][] = &$comment;
        } else {
            // If parent missing, treat as root
            $rootComments[] = &$comment;
        }
    }
}
unset($comment); // break reference

// -----------------------
// Return JSON
// -----------------------
Response::success(['comments' => $rootComments]);
exit;
