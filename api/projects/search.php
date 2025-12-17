<?php
// api/projects/search.php
header("Content-Type: application/json");
require_once("../config/db.php");

try {
    $conn = (new Database())->connect();

    // 1. Get Filter Parameters
    $keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
    $type    = isset($_GET['type']) ? trim($_GET['type']) : '';
    $dept    = isset($_GET['dept']) ? trim($_GET['dept']) : '';
    $year    = isset($_GET['year']) ? trim($_GET['year']) : '';
    $sort    = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

    // 2. Base Query
    $sql = "SELECT p.*, u.full_name AS author_name 
            FROM projects p 
            LEFT JOIN users u ON p.user_id = u.user_id 
            WHERE p.status = 'approved'";
    
    $params = [];
    $types = "";

    // 3. Apply Filters Dynamically
    if (!empty($keyword)) {
        $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.authors LIKE ?)";
        $searchTerm = "%$keyword%";
        $params[] = $searchTerm; $params[] = $searchTerm; $params[] = $searchTerm;
        $types .= "sss";
    }

    if (!empty($type) && $type !== 'all') {
        $sql .= " AND p.research_type = ?";
        $params[] = $type;
        $types .= "s";
    }

    if (!empty($dept) && $dept !== 'all') {
        $sql .= " AND p.department = ?";
        $params[] = $dept;
        $types .= "s";
    }

    if (!empty($year) && $year !== 'all') {
        $sql .= " AND YEAR(p.publication_date) = ?";
        $params[] = $year;
        $types .= "s";
    }

    // 4. Apply Sorting
    switch ($sort) {
        case 'oldest': $sql .= " ORDER BY p.date_submitted ASC"; break;
        case 'az':     $sql .= " ORDER BY p.title ASC"; break;
        case 'za':     $sql .= " ORDER BY p.title DESC"; break;
        case 'newest': 
        default:       $sql .= " ORDER BY p.date_submitted DESC"; break;
    }

    // 5. Execute
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
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