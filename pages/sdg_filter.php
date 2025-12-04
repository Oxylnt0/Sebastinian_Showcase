<?php
require_once("../api/config/db.php");

$sdg_id = intval($_GET['sdg_id'] ?? 0);
$conn = (new Database())->connect();
$stmt = $conn->prepare("
    SELECT p.*, u.full_name, s.sdg_name
    FROM projects p
    LEFT JOIN users u ON p.user_id=u.user_id
    LEFT JOIN sdgs s ON p.sdg_id=s.sdg_id
    WHERE p.status='approved' AND s.sdg_id=?
    ORDER BY p.date_submitted DESC
");
$stmt->bind_param("i", $sdg_id);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Filter by SDG</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include("header.php"); ?>
<h1>Projects by SDG</h1>
<?php if(!$projects) echo "<p>No projects found.</p>"; ?>
<ul>
<?php foreach($projects as $proj): ?>
    <li><?= htmlspecialchars($proj['title']) ?> by <?= htmlspecialchars($proj['full_name']) ?></li>
<?php endforeach; ?>
</ul>
<?php include("footer.php"); ?>
</body>
</html>
