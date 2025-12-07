<?php
// ===========================================
// api/admin/search_projects.php
// Allows admin to search projects by title or student name
// ===========================================

ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

session_start();
require_once("../config/db.php");
require_once("../utils/auth_check.php");
require_once("../utils/response.php");

// Only admins can access
auth_check(['admin']);

// Ensure GET method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error("Invalid request method", 405);
}

try {
    $conn = (new Database())->connect();

    // -----------------------------
    // Get query parameters
    // -----------------------------
    $query = trim($_GET['query'] ?? '');
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, intval($_GET['per_page'])) : 50;
    $offset = ($page - 1) * $perPage;

    // -----------------------------
    // Build WHERE clause
    // -----------------------------
    $params = [];
    $whereSQL = '';
    if ($query !== '') {
        $whereSQL = "WHERE p.title LIKE ? OR u.full_name LIKE ?";
        $params[] = "%$query%";
        $params[] = "%$query%";
    }

    // -----------------------------
    // Get total count for pagination
    // -----------------------------
    $countSQL = "SELECT COUNT(*) FROM projects p LEFT JOIN users u ON p.user_id = u.user_id $whereSQL";
    $stmtCount = $conn->prepare($countSQL);
    if (!empty($params)) {
        $stmtCount->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $stmtCount->execute();
    $stmtCount->bind_result($totalProjects);
    $stmtCount->fetch();
    $stmtCount->close();

    // -----------------------------
    // Fetch projects with student info
    // -----------------------------
    $sql = "
        SELECT 
            p.project_id,
            p.title,
            p.description,
            p.status,
            p.date_submitted,
            u.user_id AS user_id,
            u.full_name AS student_name,
            u.email AS student_email
        FROM projects p
        LEFT JOIN users u ON p.user_id = u.user_id
        $whereSQL
        ORDER BY p.date_submitted DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);

    $bindParams = $params;
    $bindParams[] = $perPage;
    $bindParams[] = $offset;

    $types = str_repeat('s', count($params)) . "ii";
    if (!empty($bindParams)) {
        $stmt->bind_param($types, ...$bindParams);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = [
            'project_id' => $row['project_id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'status' => $row['status'],
            'date_submitted' => date("Y-m-d H:i:s", strtotime($row['date_submitted'])),
            'user_id' => $row['user_id'],          
            'student_name' => $row['student_name'],
            'student_email' => $row['student_email']
        ];
    }

    // -----------------------------
    // Return JSON with pagination info
    // -----------------------------
    Response::success([
        'projects' => $projects,
        'query' => $query,
        'pagination' => [
            'total' => $totalProjects,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => ceil($totalProjects / $perPage)
        ]
    ], "Projects retrieved successfully");

} catch (mysqli_sql_exception $e) {
    Response::error("Database error: " . $e->getMessage(), 500);
} catch (Exception $e) {
    Response::error("Server error: " . $e->getMessage(), 500);
}
