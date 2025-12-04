<?php
require_once("../config/db.php");
require_once("../utils/response.php");

$conn = (new Database())->connect();

$sql = "
    SELECT 
        p.*, 
        u.full_name, 
        s.sdg_name
    FROM projects p
    LEFT JOIN users u ON p.user_id = u.user_id
    LEFT JOIN sdgs s ON p.sdg_id = s.sdg_id
    ORDER BY p.date_submitted DESC
";

$result = $conn->query($sql);

if (!$result) {
    response_json('error', 'Database query failed');
}

$projects = [];
while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}

response_json('success', 'Projects retrieved successfully', $projects);
