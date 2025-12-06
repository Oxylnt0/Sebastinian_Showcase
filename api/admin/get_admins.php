<?php
// ===========================================
// api/admin/get_admins.php
// Returns a JSON list of all admin users
// ===========================================

// Start session and include required files
session_start();
require_once("../utils/auth_check.php");
require_once("../config/db.php");

// Only admins can access
auth_check(['admin']);

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');

// Ensure GET method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method",
        "data" => []
    ]);
    exit;
}

try {
    $conn = (new Database())->connect();

    $sql = "
        SELECT user_id, username, full_name, email, date_created
        FROM users
        WHERE role = 'admin'
        ORDER BY date_created DESC
    ";

    $result = $conn->query($sql);

    if (!$result) {
        echo json_encode([
            "status" => "error",
            "message" => "Database query failed: " . $conn->error,
            "data" => []
        ]);
        exit;
    }

    $admins = [];
    while ($row = $result->fetch_assoc()) {
        $admins[] = [
            'user_id'      => $row['user_id'],
            'username'     => $row['username'],
            'full_name'    => $row['full_name'],
            'email'        => $row['email'],
            'date_created' => date("M d, Y H:i", strtotime($row['date_created']))
        ];
    }

    echo json_encode([
        "status"  => "success",
        "message" => "Admins retrieved successfully",
        "data"    => $admins
    ]);
    exit;

} catch (mysqli_sql_exception $e) {
    echo json_encode([
        "status"  => "error",
        "message" => "Database error: " . $e->getMessage(),
        "data"    => []
    ]);
    exit;
} catch (Exception $e) {
    echo json_encode([
        "status"  => "error",
        "message" => "Server error: " . $e->getMessage(),
        "data"    => []
    ]);
    exit;
}
