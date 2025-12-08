<?php
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/response.php';

auth_check(); // Ensure user is logged in

// -----------------------
// Validate project_id
// -----------------------
$project_id = intval($_GET['project_id'] ?? 0);
if (!$project_id) Response::error('Project ID required', 400);

$conn = (new Database())->connect();

// -----------------------
// Fetch project file
// -----------------------
$stmt = $conn->prepare("SELECT file FROM projects WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project || empty($project['file'])) {
    Response::error('File not found', 404);
}

$filepath = __DIR__ . '/../../uploads/project_files/' . $project['file'];
if (!file_exists($filepath)) {
    Response::error('File does not exist on server', 404);
}

// -----------------------
// Log the download (avoid duplicates per day)
// -----------------------
$user_id = $_SESSION['user_id'];

// Check if already logged today
$stmtCheck = $conn->prepare("
    SELECT 1 FROM downloads_log 
    WHERE project_id = ? AND user_id = ? AND DATE(downloaded_at) = CURDATE()
    LIMIT 1
");
$stmtCheck->bind_param("ii", $project_id, $user_id);
$stmtCheck->execute();
$alreadyLogged = $stmtCheck->get_result()->num_rows > 0;
$stmtCheck->close();

if (!$alreadyLogged) {
    $stmtLog = $conn->prepare("
        INSERT INTO downloads_log (project_id, user_id, downloaded_at) 
        VALUES (?, ?, NOW())
    ");
    $stmtLog->bind_param("ii", $project_id, $user_id);
    $stmtLog->execute();
    $stmtLog->close();
}

// -----------------------
// Serve the file for download
// -----------------------
if (ob_get_level()) ob_end_clean();

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));

flush();
readfile($filepath);
exit;
