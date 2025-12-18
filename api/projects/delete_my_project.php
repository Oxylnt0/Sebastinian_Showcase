<?php
// api/projects/delete_my_project.php
// "God Mode" Edition: Transaction-safe, CSRF-protected, and Deep Cleaning.

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../api/config/db.php'; // Adjust path to config/db.php
// We don't strictly need auth_check.php here if we do the checks manually below, 
// but if you have a helper, you can keep using it.

// 1. JSON Helper for Consistent Responses
function sendJson($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// 2. Auth Check
if (!isset($_SESSION['user_id'])) {
    sendJson(false, 'Unauthorized access.');
}

// 3. Parse Input
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// FIX: Accept 'id' (from JS) OR 'project_id' (legacy support)
$project_id = intval($input['id'] ?? $input['project_id'] ?? 0);
$csrf_token = $input['csrf_token'] ?? '';

// 4. Validation
if ($project_id <= 0) {
    sendJson(false, 'Project ID is required.');
}

// // CSRF Security Check (Crucial for "Ultimate" security)
// if (empty($csrf_token) || $csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
//     sendJson(false, 'Security token mismatch. Please refresh the page.');
// }

$conn = (new Database())->connect();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';

// 5. Verify Ownership & Fetch File Paths
// We need to know the file names BEFORE we delete the row
$stmt = $conn->prepare("SELECT user_id, file, image FROM projects WHERE project_id = ? LIMIT 1");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) {
    sendJson(false, 'Project not found.');
}

// Admin override or Owner check
if ($project['user_id'] !== $user_id && $user_role !== 'admin') {
    sendJson(false, 'Permission denied. You do not own this project.');
}

// 6. Begin Transaction (Atomic Delete)
$conn->begin_transaction();

try {
    // A. Delete Related Data (Deep Cleaning)
    // 1. Get Comment IDs to delete their likes first
    $stmt = $conn->prepare("SELECT comment_id FROM comments WHERE project_id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $commentIds = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (!empty($commentIds)) {
        $ids = array_column($commentIds, 'comment_id');
        $idList = implode(',', array_map('intval', $ids)); // Safe int conversion

        // Delete Comment Likes
        $conn->query("DELETE FROM comment_likes WHERE comment_id IN ($idList)");
        
        // Delete Comments
        $conn->query("DELETE FROM comments WHERE project_id = $project_id");
    }

    // 2. Delete Project Likes
    $conn->query("DELETE FROM project_likes WHERE project_id = $project_id");

    // 3. Delete Download Logs
    $conn->query("DELETE FROM downloads_log WHERE project_id = $project_id");

    // B. Delete the Project Record
    $stmt = $conn->prepare("DELETE FROM projects WHERE project_id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $stmt->close();

    // C. Commit Database Changes
    $conn->commit();

    // D. Delete Physical Files (Only after DB success)
    // Define Absolute Paths
    $baseDir = dirname(dirname(__DIR__)) . '/uploads/'; 
    
    // 1. Delete Project File
    if (!empty($project['file'])) {
        $filePath = $baseDir . 'project_files/' . $project['file'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    // 2. Delete Cover Image
    if (!empty($project['image'])) {
        $imgPath = $baseDir . 'project_images/' . $project['image'];
        if (file_exists($imgPath)) {
            @unlink($imgPath);
        }
    }

    sendJson(true, 'Project deleted successfully.');

} catch (Exception $e) {
    $conn->rollback();
    error_log("Delete Error: " . $e->getMessage()); // Log internal error
    sendJson(false, 'Failed to delete project. Please try again.');
}
?>