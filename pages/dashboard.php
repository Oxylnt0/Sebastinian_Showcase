<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard - Sebastinian Showcase</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include("header.php"); ?>

<h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>

<?php if($_SESSION['role'] === 'admin'): ?>
    <a href="admin_dashboard.php">Go to Admin Dashboard</a>
<?php else: ?>
    <a href="my_projects.php">View My Projects</a>
    <a href="upload_project.php">Upload a Project</a>
<?php endif; ?>

<?php include("footer.php"); ?>
</body>
</html>
