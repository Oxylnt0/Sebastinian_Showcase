<?php
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/response.php';

auth_check();

$input = json_decode(file_get_contents('php://input'), true);
$project_id = intval($input['project_id'] ?? 0);
$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');

if (!$project_id || !$title || !$description) Response::error('Invalid input', 400);

$conn = (new Database())->connect();

// Only owner or admin
$stmt = $conn->prepare("SELECT user_id FROM projects WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
if (!$project) Response::error('Project not found', 404);
if ($project['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') Response::error('Permission denied', 403);

$upd = $conn->prepare("UPDATE projects SET title = ?, description = ? WHERE project_id = ?");
$upd->bind_param("ssi", $title, $description, $project_id);
if ($upd->execute()) Response::success([], 'Project updated');
else Response::error('Failed to update project');
