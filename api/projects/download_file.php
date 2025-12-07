<?php
session_start();
require_once("../config/db.php");

// Get project ID from GET parameter
$project_id = intval($_GET['project_id'] ?? 0);
if ($project_id <= 0) {
    die("Invalid project ID.");
}

$conn = (new Database())->connect();

// Fetch project file info
$stmt = $conn->prepare("SELECT file, downloads FROM projects WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Project not found.");
}

$project = $result->fetch_assoc();
$file_name = $project['file'];
$downloads = $project['downloads'] ?? 0;

if (!$file_name || !file_exists(__DIR__ . "/../../uploads/project_files/" . $file_name)) {
    die("File not found.");
}

// Increment download count
$update_stmt = $conn->prepare("UPDATE projects SET downloads = downloads + 1 WHERE project_id = ?");
$update_stmt->bind_param("i", $project_id);
$update_stmt->execute();

// Send file for download
$file_path = __DIR__ . "/../../uploads/project_files/" . $file_name;
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
flush();
readfile($file_path);
exit;
