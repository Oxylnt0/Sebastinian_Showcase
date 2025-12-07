<?php
session_start();
require_once("../config/db.php");
$conn = (new Database())->connect();

$data = json_decode(file_get_contents("php://input"), true);
$project_id = intval($data['project_id'] ?? 0);
$parent_id = intval($data['parent_id'] ?? 0);
$comment_text = trim($data['comment'] ?? '');
$user_id = $_SESSION['user_id'] ?? null;

if (!$project_id || !$comment_text || !$user_id) {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

// Insert reply
$stmt = $conn->prepare("INSERT INTO comments (project_id, user_id, comment, parent_id, date_commented) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iisi", $project_id, $user_id, $comment_text, $parent_id);
if ($stmt->execute()) {
    $reply_id = $stmt->insert_id;
    $stmt = $conn->prepare("SELECT c.comment_id, c.comment, c.date_commented, u.full_name, u.user_id 
                            FROM comments c 
                            LEFT JOIN users u ON c.user_id = u.user_id 
                            WHERE c.comment_id = ?");
    $stmt->bind_param("i", $reply_id);
    $stmt->execute();
    $reply = $stmt->get_result()->fetch_assoc();

    echo json_encode([
        "status" => "success",
        "comment" => $reply
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to post reply"]);
}
?>
