<?php
session_start();
header('Content-Type: application/json');

require_once("../config/db.php");
require_once("../utils/auth_check.php");

// Check if user is logged in
$current_user_id = $_SESSION['user_id'] ?? null;
$current_user_role = $_SESSION['role'] ?? null;

if (!$current_user_id) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to delete a comment.']);
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents("php://input"), true);
$comment_id = intval($data['comment_id'] ?? 0);

if ($comment_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid comment ID.']);
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

// Allow deletion if user is owner or admin
if ($comment['user_id'] != $current_user_id && $current_user_role !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'You do not have permission to delete this comment.']);
    exit;
}

// Delete the comment
$delete_stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
$delete_stmt->bind_param("i", $comment_id);

if ($delete_stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Comment deleted successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete comment.']);
}

$delete_stmt->close();
$stmt->close();
$conn->close();
