<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include("header.php"); ?>
<h1>Admin Dashboard</h1>
<ul>
    <li><a href="manage_projects.php">Manage Projects</a></li>
    <li><a href="manage_users.php">Manage Users</a></li>
    <li><a href="activity_logs.php">Activity Logs</a></li>
</ul>
<?php include("footer.php"); ?>
</body>
</html>
