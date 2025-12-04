<?php
session_start();
require_once("../config/db.php");
require_once("response.php");
require_once("auth_check.php");

header('Content-Type: application/json');

// Only logged-in users can comment
if (!isLoggedIn()) {
    echo json_response('error', 'You must be logged in to post a comment.');
    exit;
}

// Only POST requests allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_response('error', 'Invalid request method.');
    exit;
}

$conn = (new Database())->connect();
if ($conn->connect_error) {
    echo json_response('error', 'Database connection failed: ' . $conn->connect_error);
    exit;
}

$project_id = intval($_POST['project_id'] ?? 0);
$comment_text = trim($_POST['comment'] ?? '');

// Validation
if ($project_id <= 0) {
    echo json_response('error', 'Invalid project ID.');
    exit;
}

if ($comment_text === '') {
    echo json_response('error', 'Comment cannot be empty.');
    exit;
}

// Check if project exists
$check = $conn->prepare("SELECT project_id FROM projects WHERE project_id = ?");
$check->bind_param("i", $project_id);
$check->execute();
$res = $check->get_result();
if ($res->num_rows === 0) {
    echo json_response('error', 'Project not found.');
    exit;
}

// Insert comment
$stmt = $conn->prepare("INSERT INTO comments (project_id, user_id, comment) VALUES (?, ?, ?)");
$user_id = $_SESSION['user_id'];
$stmt->bind_param("iis", $project_id, $user_id, $comment_text);

if ($stmt->execute()) {
    // Get user's full name for frontend display
    $user_stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();

    echo json_encode([
        'status' => 'success',
        'message' => 'Comment posted successfully!',
        'user_name' => htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8')
    ]);
} else {
    echo json_response('error', 'Failed to post comment.');
}

exit;
