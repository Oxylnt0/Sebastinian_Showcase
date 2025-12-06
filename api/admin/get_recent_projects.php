<?php
// ===========================================
// api/admin/get_recent_projects.php
// Returns the latest projects for admin dashboard
// ===========================================

session_start();
require_once("../config/db.php");
require_once("../utils/auth_check.php");
require_once("../utils/response.php");

// Only admins can access
auth_check(['admin']);

try {
    $conn = (new Database())->connect();

    // Optional limit, default 10
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    if ($limit <= 0) $limit = 10;

    $sql = "
        SELECT 
            p.project_id,
            p.title,
            p.status,
            p.date_submitted,
            u.user_id,
            u.full_name AS student_name,
            u.email AS student_email
        FROM projects p
        LEFT JOIN users u ON p.user_id = u.user_id
        ORDER BY p.date_submitted DESC
        LIMIT ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }

    Response::success($projects, "Recent projects retrieved successfully");

} catch (mysqli_sql_exception $e) {
    Response::error("Database error: " . $e->getMessage(), 500);
} catch (Exception $e) {
    Response::error("Server error: " . $e->getMessage(), 500);
}
