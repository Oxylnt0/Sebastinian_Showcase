<?php
// ===========================================
// api/admin/delete_admin.php
// Deletes an admin via AJAX (JSON)
// ===========================================

header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once("../config/db.php");
require_once("../utils/response.php");
require_once("../utils/auth_check.php");

try {
    auth_check(['admin']);
} catch (Exception $e) {
    Response::error("Access denied", 403);
}

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error("Invalid request method", 405);
}

// Get JSON body
$raw = file_get_contents("php://input");
$input = json_decode($raw, true);

if (!isset($input['user_id'])) {
    Response::error("Missing user_id");
}

$user_id = intval($input['user_id']);

try {
    $db = new Database();
    $conn = $db->connect();

    // Prevent deleting yourself
    if ($user_id === $_SESSION['user_id']) {
        Response::error("You cannot delete your own admin account.");
    }

    // Check if exists & is admin
    $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows === 0) {
        Response::error("Admin not found");
    }

    $row = $res->fetch_assoc();
    if ($row['role'] !== 'admin') {
        Response::error("Only admin accounts can be deleted");
    }

    // Delete admin
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);

    if (!$stmt->execute()) {
        Response::error("Deletion failed: " . $stmt->error);
    }

    // Log activity
    if (!empty($_SESSION['user_id'])) {
        $admin = $_SESSION['user_id'];
        $log = $conn->prepare("
            INSERT INTO activity_log (user_id, action, details, timestamp)
            VALUES (?, 'delete_admin', CONCAT('Deleted admin ID ', ?), NOW())
        ");
        $log->bind_param("ii", $admin, $user_id);
        $log->execute();
    }

    Response::success(null, "Admin deleted successfully");

} catch (Exception $e) {
    Response::error("Server error: " . $e->getMessage(), 500);
}
?>
