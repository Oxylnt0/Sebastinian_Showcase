<?php
// ===========================================
// api/admin/get_student_details.php
// Returns student info + all their projects
// ===========================================
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

session_start();
require_once("../utils/auth_check.php");
require_once("../config/db.php");
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
    // Get student_id safely
    // -----------------------------
    $student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
    if (!$student_id) {
        Response::error("Invalid student ID", 400);
    }

    // -----------------------------
    // Fetch student info
    // -----------------------------
    $stmtStudent = $conn->prepare("
        SELECT user_id, full_name, email, date_created
        FROM users
        WHERE user_id = ? AND role = 'student'
    ");
    $stmtStudent->bind_param("i", $student_id);
    $stmtStudent->execute();
    $resultStudent = $stmtStudent->get_result();

    if ($resultStudent->num_rows === 0) {
        Response::error("Student not found", 404);
    }

    $student = $resultStudent->fetch_assoc();
    $student['date_created'] = date("Y-m-d H:i:s", strtotime($student['date_created']));

    // -----------------------------
    // Fetch all projects by student
    // -----------------------------
    $stmtProjects = $conn->prepare("
        SELECT project_id, title, description, status, date_submitted, file, image
        FROM projects
        WHERE user_id = ?
        ORDER BY date_submitted DESC
    ");
    $stmtProjects->bind_param("i", $student_id);
    $stmtProjects->execute();
    $resultProjects = $stmtProjects->get_result();

    $projects = [];
    $summary = ['total' => 0, 'approved' => 0, 'pending' => 0, 'rejected' => 0];

    while ($row = $resultProjects->fetch_assoc()) {
        $projects[] = [
            'project_id' => $row['project_id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'status' => $row['status'],
            'date_submitted' => date("Y-m-d H:i:s", strtotime($row['date_submitted'])),
            'file' => $row['file'],
            'image' => $row['image']
        ];

        $summary['total']++;
        if (isset($summary[$row['status']])) {
            $summary[$row['status']]++;
        }
    }

    // -----------------------------
    // Return JSON
    // -----------------------------
    Response::success([
        'student' => $student,
        'projects' => $projects,
        'summary' => $summary
    ], "Student profile retrieved successfully");

} catch (mysqli_sql_exception $e) {
    Response::error("Database error: " . $e->getMessage(), 500);
} catch (Exception $e) {
    Response::error("Server error: " . $e->getMessage(), 500);
}
