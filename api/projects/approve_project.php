<?php
session_start();
require_once("../config/db.php");
require_once("../utils/auth_check.php");
require_once("../utils/response.php");
require_once("../utils/validation.php");

// Only admin can approve
auth_check(['admin']);

$conn = (new Database())->connect();

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Invalid request method', 405);
}

$project_id = intval($_POST['project_id'] ?? 0);
$status = trim($_POST['status'] ?? '');
$remarks = trim($_POST['remarks'] ?? '');
$approved_by = $_SESSION['user_id'];

$valid_status = ['approved', 'rejected'];
if (!in_array($status, $valid_status, true)) {
    Response::error('Invalid status');
}

// Validate project exists
$project_exists = $conn->prepare("SELECT project_id FROM projects WHERE project_id = ?");
$project_exists->bind_param("i", $project_id);
$project_exists->execute();
$res = $project_exists->get_result();
if ($res->num_rows === 0) {
    Response::error("Project not found", 404); 
}

// Insert into approvals
$insert = $conn->prepare("INSERT INTO approvals (project_id, approved_by, status, remarks) VALUES (?, ?, ?, ?)");
$insert->bind_param("iiss", $project_id, $approved_by, $status, $remarks);

if (!$insert->execute()) {
    Response::error('Approval already exists or database error');
}

// Update project status
$update = $conn->prepare("UPDATE projects SET status = ? WHERE project_id = ?");
$update->bind_param("si", $status, $project_id);
$update->execute();

Response::success([], 'Project status updated successfully');
