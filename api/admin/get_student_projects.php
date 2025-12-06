<?php
// ===========================================
// api/admin/get_student_details.php
// Returns a student's profile info and all their projects
// ===========================================

session_start();
require_once("../utils/auth_check.php");
require_once("../config/db.php");
require_once("../utils/response.php");

// Only admins can access
auth_check(['admin']);

// Ensure GET method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Invalid request method', 405);
}

// Get student user_id safely
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if (!$user_id) {
    Response::error('Missing or invalid student ID', 400);
}

try {
    $conn = (new Database())->connect();

    // -----------------------------
    // Fetch student info
    // -----------------------------
    $stmt_student = $conn->prepare("
        SELECT user_id, username, full_name, email, role, date_created, last_login
        FROM users
        WHERE user_id = ? AND role = 'student'
    ");
    $stmt_student->bind_param("i", $user_id);
    $stmt_student->execute();
    $result_student = $stmt_student->get_result();

    if ($result_student->num_rows === 0) {
        Response::error('Student not found', 404);
    }

    $student = $result_student->fetch_assoc();

    // -----------------------------
    // Fetch all projects by this student
    // -----------------------------
    $stmt_projects = $conn->prepare("
        SELECT project_id, title, description, status, date_submitted, file, image
        FROM projects
        WHERE user_id = ?
        ORDER BY date_submitted DESC
    ");
    $stmt_projects->bind_param("i", $user_id);
    $stmt_projects->execute();
    $result_projects = $stmt_projects->get_result();

    $projects = [];
    $summary = [
        'total' => 0,
        'approved' => 0,
        'pending' => 0,
        'rejected' => 0
    ];

    while ($proj = $result_projects->fetch_assoc()) {
        $projects[] = $proj;
        $summary['total']++;
        if (isset($summary[$proj['status']])) {
            $summary[$proj['status']]++;
        }
    }

    // -----------------------------
    // Return JSON response
    // -----------------------------
    Response::success([
        'student' => $student,
        'projects' => $projects,
        'summary' => $summary
    ], 'Student details retrieved successfully');

} catch (mysqli_sql_exception $e) {
    Response::error('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    Response::error('Server error: ' . $e->getMessage(), 500);
}
