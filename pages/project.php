<?php
session_start();
require_once("../api/config/db.php");

$project_id = intval($_GET['id'] ?? 0);
if($project_id === 0){
    header("Location: 404.php");
    exit;
}

$conn = (new Database())->connect();
$stmt = $conn->prepare("
    SELECT p.*, u.full_name, s.sdg_name
    FROM projects p
    LEFT JOIN users u ON p.user_id = u.user_id
    LEFT JOIN sdgs s ON p.sdg_id = s.sdg_id
    WHERE p.project_id = ?
");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();

if(!$project){
    header("Location: 404.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($project['title']) ?> - Sebastinian Showcase</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include("header.php"); ?>

<h1><?= htmlspecialchars($project['title']) ?></h1>
<p>By <?= htmlspecialchars($project['full_name']) ?></p>
<p>SDG: <?= htmlspecialchars($project['sdg_name']) ?></p>
<p><?= nl2br(htmlspecialchars($project['description'])) ?></p>

<?php if($project['image']): ?>
    <img src="../uploads/project_images/<?= htmlspecialchars($project['image']) ?>" alt="Project Image">
<?php endif; ?>

<?php if($project['file']): ?>
    <a href="../uploads/project_files/<?= htmlspecialchars($project['file']) ?>" download>Download Project File</a>
<?php endif; ?>

<?php include("footer.php"); ?>
</body>
</html>
