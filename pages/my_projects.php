<?php
session_start();
require_once("../api/config/db.php");
if(!isset($_SESSION['user_id'])) header("Location: login.php");

$conn = (new Database())->connect();
$stmt = $conn->prepare("
    SELECT p.*, s.sdg_name
    FROM projects p
    LEFT JOIN sdgs s ON p.sdg_id = s.sdg_id
    WHERE p.user_id = ?
    ORDER BY p.date_submitted DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Projects</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include("header.php"); ?>

<h1>My Projects</h1>
<?php if(count($projects) === 0): ?>
    <p>You haven't submitted any projects yet.</p>
<?php else: ?>
    <ul>
    <?php foreach($projects as $proj): ?>
        <li>
            <?= htmlspecialchars($proj['title']) ?> - Status: <?= htmlspecialchars($proj['status']) ?> - SDG: <?= htmlspecialchars($proj['sdg_name']) ?>
            <a href="project.php?id=<?= $proj['project_id'] ?>">View</a>
        </li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php include("footer.php"); ?>
</body>
</html>
