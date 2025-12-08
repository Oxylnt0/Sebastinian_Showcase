<?php
// get_project_stats.php - Fetch project like & download counts
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/response.php';

// -----------------------
// Validate project_id
// -----------------------
$project_id = intval($_GET['project_id'] ?? 0);
if ($project_id <= 0) Response::error('Project ID required', 400);

$conn = (new Database())->connect();

// -----------------------
// Check project existence
// -----------------------
$stmtProj = $conn->prepare("SELECT project_id FROM projects WHERE project_id = ? LIMIT 1");
$stmtProj->bind_param("i", $project_id);
$stmtProj->execute();
$project = $stmtProj->get_result()->fetch_assoc();
$stmtProj->close();

if (!$project) Response::error('Project not found', 404);

// -----------------------
// Fetch like count
// -----------------------
$stmtLikes = $conn->prepare("SELECT COUNT(*) AS total FROM project_likes WHERE project_id = ?");
$stmtLikes->bind_param("i", $project_id);
$stmtLikes->execute();
$likes = intval($stmtLikes->get_result()->fetch_assoc()['total'] ?? 0);
$stmtLikes->close();

// -----------------------
// Fetch download count
// -----------------------
$stmtDownloads = $conn->prepare("SELECT COUNT(*) AS total FROM downloads_log WHERE project_id = ?");
$stmtDownloads->bind_param("i", $project_id);
$stmtDownloads->execute();
$downloads = intval($stmtDownloads->get_result()->fetch_assoc()['total'] ?? 0);
$stmtDownloads->close();

// -----------------------
// Return JSON response
// -----------------------
Response::success([
    'like_count' => $likes,
    'download_count' => $downloads
]);
