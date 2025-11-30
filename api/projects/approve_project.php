<?php
require_once("../config/db.php");
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['status'=>'error','message'=>'Access denied']);
    exit;
}

$conn = (new Database())->connect();

$project_id = intval($_POST['project_id']);
$status = $_POST['status']; // approved or rejected
$remarks = $conn->real_escape_string($_POST['remarks']);
$approved_by = $_SESSION['user_id'];

$sql = "INSERT INTO approvals (project_id, approved_by, status, remarks) 
        VALUES ($project_id, $approved_by, '$status', '$remarks')";

if ($conn->query($sql)) {
    // Update project status
    $conn->query("UPDATE projects SET status='$status' WHERE project_id=$project_id");
    echo json_encode(['status'=>'success','message'=>'Project status updated']);
} else {
    echo json_encode(['status'=>'error','message'=>$conn->error]);
}
?>
