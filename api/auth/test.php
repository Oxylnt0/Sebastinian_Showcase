<?php
require_once("../config/db.php");
header('Content-Type: application/json');

$conn = (new Database())->connect();

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

echo json_encode(['status' => 'success', 'message' => 'Connected to DB successfully']);
exit;
