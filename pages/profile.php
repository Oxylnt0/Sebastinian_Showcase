<?php
session_start();
require_once("../api/config/db.php");
if(!isset($_SESSION['user_id'])) header("Location: login.php");

$conn = (new Database())->connect();
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id=?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile - <?= htmlspecialchars($user['username']) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include("header.php"); ?>

<h1>My Profile</h1>
<p>Username: <?= htmlspecialchars($user['username']) ?></p>
<p>Full Name: <?= htmlspecialchars($user['full_name']) ?></p>
<p>Email: <?= htmlspecialchars($user['email']) ?></p>
<img src="../uploads/profile_images/<?= htmlspecialchars($user['profile_image'] ?? 'default.png') ?>" alt="Profile Image">

<form method="POST" action="../api/users/update_profile.php" enctype="multipart/form-data">
    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required><br>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br>
    <input type="file" name="profile_image"><br>
    <button type="submit">Update Profile</button>
</form>

<?php include("footer.php"); ?>
</body>
</html>
