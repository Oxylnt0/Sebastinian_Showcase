<?php
session_start();
require_once("../api/config/db.php");
require_once("../api/utils/response.php");

// Fetch projects dynamically
$conn = (new Database())->connect();
$sql = "
    SELECT p.*, u.full_name, s.sdg_name
    FROM projects p
    LEFT JOIN users u ON p.user_id = u.user_id
    LEFT JOIN sdgs s ON p.sdg_id = s.sdg_id
    WHERE p.status = 'approved'
    ORDER BY p.date_submitted DESC
";
$result = $conn->query($sql);
$projects = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sebastinian Showcase</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include("header.php"); ?>
<h1>Project Showcase</h1>

<?php if(count($projects) === 0): ?>
    <p>No projects uploaded yet.</p>
<?php else: ?>
    <div class="projects-grid">
        <?php foreach($projects as $proj): ?>
            <div class="project-card">
                <h3><?= htmlspecialchars($proj['title']) ?></h3>
                <p>By <?= htmlspecialchars($proj['full_name']) ?></p>
                <p>SDG: <?= htmlspecialchars($proj['sdg_name']) ?></p>
                <a href="project.php?id=<?= $proj['project_id'] ?>">View Details</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include("footer.php"); ?>
</body>
</html>
