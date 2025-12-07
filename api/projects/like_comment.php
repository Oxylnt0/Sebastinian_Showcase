<?php
session_start();
require_once("../config/db.php");
$conn = (new Database())->connect();

$data = json_decode(file_get_contents("php://input"), true);
$comment_id = intval($data['comment_id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? null;

if (!$comment_id || !$user_id) {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

// Check if user already liked
$stmt = $conn->prepare("SELECT 1 FROM comment_likes WHERE comment_id = ? AND user_id = ?");
$stmt->bind_param("ii", $comment_id, $user_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    // Unlike
    $stmt = $conn->prepare("DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $comment_id, $user_id);
    $stmt->execute();
    $liked = false;
} else {
    // Like
    $stmt = $conn->prepare("INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $comment_id, $user_id);
    $stmt->execute();
    $liked = true;
}

// Count updated likes
$stmt = $conn->prepare("SELECT COUNT(*) as like_count FROM comment_likes WHERE comment_id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$like_count = $stmt->get_result()->fetch_assoc()['like_count'] ?? 0;

echo json_encode([
    "status" => "success",
    "likes" => $like_count,
    "liked" => $liked
]);
?>
