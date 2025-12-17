<?php
// api/projects/search_my_projects.php
header("Content-Type: application/json");
session_start();
require_once("../config/db.php");
require_once("../utils/auth_check.php");

try {
    Auth::requireLogin();
    $user_id = $_SESSION['user_id'];
    $conn = (new Database())->connect();

    // 1. Get Parameters
    $keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
    $status  = isset($_GET['status']) ? trim($_GET['status']) : 'all'; // New Status Filter
    $type    = isset($_GET['type']) ? trim($_GET['type']) : 'all';
    $dept    = isset($_GET['dept']) ? trim($_GET['dept']) : 'all';
    $year    = isset($_GET['year']) ? trim($_GET['year']) : 'all';
    $sort    = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

    // 2. Base Query (Strictly User's Data)
    $sql = "SELECT * FROM projects WHERE user_id = ?";
    
    $params = [$user_id];
    $types = "i";

    // 3. Apply Filters
    if (!empty($keyword)) {
        $sql .= " AND (title LIKE ? OR description LIKE ? OR authors LIKE ?)";
        $searchTerm = "%$keyword%";
        $params[] = $searchTerm; $params[] = $searchTerm; $params[] = $searchTerm;
        $types .= "sss";
    }

    if ($status !== 'all') {
        $sql .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }

    if ($type !== 'all') {
        $sql .= " AND research_type = ?";
        $params[] = $type;
        $types .= "s";
    }

    if ($dept !== 'all') {
        $sql .= " AND department = ?";
        $params[] = $dept;
        $types .= "s";
    }

    if ($year !== 'all') {
        $sql .= " AND YEAR(publication_date) = ?";
        $params[] = $year;
        $types .= "s";
    }

    // 4. Sorting
    switch ($sort) {
        case 'oldest': $sql .= " ORDER BY date_submitted ASC"; break;
        case 'az':     $sql .= " ORDER BY title ASC"; break;
        case 'za':     $sql .= " ORDER BY title DESC"; break;
        case 'newest': 
        default:       $sql .= " ORDER BY date_submitted DESC"; break;
    }

    // 5. Execute
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $projects]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>