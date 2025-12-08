<?php
// log_download.php - Log a project download
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/response.php';

auth_check(); // Ensure user is logged in

// -----------------------
// Parse input
// -----------------------
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$project_id = intval($input['project_id'] ?? 0);
if ($project_id <= 0) Response::error('Project ID required', 400);

$conn = (new Database())->connect();
$user_id = intval($_SESSION['user_id'] ?? 0);
if ($user_id <= 0) Response::error('Authentication error', 401);

// -----------------------
// Check if project exists
// -----------------------
$stmtProj = $conn->prepare("SELECT project_id FROM projects WHERE project_id = ? LIMIT 1");
$stmtProj->bind_param("i", $project_id);
$stmtProj->execute();
$project = $stmtProj->get_result()->fetch_assoc();
$stmtProj->close();

if (!$project) Response::error('Project not found', 404);

// -----------------------
// Prevent duplicate download log per user per day
// -----------------------
$stmtCheck = $conn->prepare("
    SELECT 1 
    FROM downloads_log 
    WHERE project_id = ? AND user_id = ? AND DATE(date_downloaded) = CURDATE()
");
$stmtCheck->bind_param("ii", $project_id, $user_id);
$stmtCheck->execute();
$alreadyLogged = $stmtCheck->get_result()->num_rows > 0;
$stmtCheck->close();

if (!$alreadyLogged) {
    $stmtLog = $conn->prepare("
        INSERT INTO downloads_log (project_id, user_id, date_downloaded)
        VALUES (?, ?, CONVERT_TZ(NOW(), @@session.time_zone, '+08:00'))
    ");
    $stmtLog->bind_param("ii", $project_id, $user_id);
    if (!$stmtLog->execute()) {
        $stmtLog->close();
        Response::error('Failed to log download', 500);
    }
    $stmtLog->close();
}

// -----------------------
// Fetch updated download count
// -----------------------
$stmtCount = $conn->prepare("SELECT COUNT(*) AS total FROM downloads_log WHERE project_id = ?");
$stmtCount->bind_param("i", $project_id);
$stmtCount->execute();
$result = $stmtCount->get_result();
$download_count = intval($result->fetch_assoc()['total'] ?? 0);
$stmtCount->close();

// -----------------------
// Return updated count
// -----------------------
Response::success([
    'download_count' => $download_count
]);
