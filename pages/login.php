<?php
session_start();
if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - Sebastinian Showcase</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include("header.php"); ?>

<h1>Login</h1>
<form id="loginForm" method="POST" action="../api/auth/login.php">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Login</button>
</form>

<?php include("footer.php"); ?>
</body>
</html>
