<?php
session_start();
header('Content-Type: application/json');

require_once("../config/db.php");
require_once("../utils/auth_check.php");

// Check if user is logged in
$current_user_id = $_SESSION['user_id'] ?? null;
$current_user_role = $_SESSION['role'] ?? null;

if (!$current_user_id) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to edit a comment.']);
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents("php://input"), true);
$comment_id = intval($data['comment_id'] ?? 0);
$new_comment = trim($data['comment'] ?? '');

if ($comment_id <= 0 || empty($new_comment)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid comment or empty content.']);
    exit;
}

$conn = (new Database())->connect();

// Check if comment exists and ownership
$stmt = $conn->prepare("SELECT user_id FROM comments WHERE comment_id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Comment not found.']);
    exit;
}

$comment = $result->fetch_assoc();

// Only allow owner or admin to edit
if ($comment['user_id'] != $current_user_id && $current_user_role !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'You do not have permission to edit this comment.']);
    exit;
}

// Update the comment
$update_stmt = $conn->prepare("UPDATE comments SET comment = ?, date_commented = NOW() WHERE comment_id = ?");
$update_stmt->bind_param("si", $new_comment, $comment_id);

if ($update_stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Comment updated successfully.',
        'comment' => $new_comment,
        'date_commented' => date("M d, Y H:i") // return updated timestamp
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update comment.']);
}

$update_stmt->close();
$stmt->close();
$conn->close();
