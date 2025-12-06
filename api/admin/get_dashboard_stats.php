<?php
// ===========================================
// api/admin/get_dashboard_stats.php
// Sebastinian Showcase – Admin Dashboard API
// ===========================================

session_start();

require_once("../utils/auth_check.php");
require_once("../config/db.php");
require_once("../utils/response.php");

// -----------------------------
// Only admins can access
// -----------------------------
auth_check(['admin']);

// -----------------------------
// Optional: number of recent projects
// -----------------------------
$recent_limit = 5;

try {
    $conn = (new Database())->connect();

    if (!$conn) {
        Response::error("Database connection failed", 500);
    }

    // -----------------------------
    // 1️⃣ Total Projects & Status Counts
    // -----------------------------
    $sql_projects = "
        SELECT 
            status,
            COUNT(*) AS count
        FROM projects
        GROUP BY status
    ";
    $result_projects = $conn->query($sql_projects);

    $project_counts = [
        'approved' => 0,
        'pending' => 0,
        'rejected' => 0,
        'total_projects' => 0
    ];

    if ($result_projects) {
        while ($row = $result_projects->fetch_assoc()) {
            $status = strtolower($row['status']);
            if (in_array($status, ['approved', 'pending', 'rejected'])) {
                $project_counts[$status] = (int)$row['count'];
                $project_counts['total_projects'] += (int)$row['count'];
            }
        }
    }

    // -----------------------------
    // 2️⃣ Total Students
    // -----------------------------
    $sql_students = "SELECT COUNT(*) AS total_students FROM users WHERE role='student'";
    $res_students = $conn->query($sql_students);
    $total_students = 0;
    if ($res_students && $row = $res_students->fetch_assoc()) {
        $total_students = (int)$row['total_students'];
    }

    // -----------------------------
    // 3️⃣ Total Admins
    // -----------------------------
    $sql_admins = "SELECT COUNT(*) AS total_admins FROM users WHERE role='admin'";
    $res_admins = $conn->query($sql_admins);
    $total_admins = 0;
    if ($res_admins && $row = $res_admins->fetch_assoc()) {
        $total_admins = (int)$row['total_admins'];
    }

    // -----------------------------
    // 4️⃣ Recent Projects (latest $recent_limit)
    // -----------------------------
    $sql_recent = "
        SELECT 
            p.project_id,
            p.title,
            p.status,
            p.date_submitted,
            u.full_name AS student_name
        FROM projects p
        LEFT JOIN users u ON p.user_id = u.user_id
        ORDER BY p.date_submitted DESC
        LIMIT ?
    ";
    $stmt_recent = $conn->prepare($sql_recent);
    $stmt_recent->bind_param("i", $recent_limit);
    $stmt_recent->execute();
    $res_recent = $stmt_recent->get_result();

    $recent_projects = [];
    while ($row = $res_recent->fetch_assoc()) {
        $recent_projects[] = [
            'project_id' => (int)$row['project_id'],
            'title' => $row['title'],
            'status' => $row['status'],
            'student_name' => $row['student_name'],
            'date_submitted' => date("M d, Y H:i", strtotime($row['date_submitted']))
        ];
    }

    // -----------------------------
    // Build and return response
    // -----------------------------
    $data = [
        'total_projects' => $project_counts['total_projects'],
        'approved' => $project_counts['approved'],
        'pending' => $project_counts['pending'],
        'rejected' => $project_counts['rejected'],
        'total_students' => $total_students,
        'total_admins' => $total_admins,
        'recent_projects' => $recent_projects
    ];

    Response::success($data, "Dashboard stats retrieved successfully");

} catch (mysqli_sql_exception $e) {
    Response::error("Database error: " . $e->getMessage(), 500);
} catch (Exception $e) {
    Response::error("Server error: " . $e->getMessage(), 500);
}
