<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("../api/config/db.php");

// Fetch SDGs for navigation filter
$conn = (new Database())->connect();
$sdg_result = $conn->query("SELECT sdg_id, sdg_name FROM sdgs ORDER BY sdg_id ASC");
$sdgs = $sdg_result ? $sdg_result->fetch_all(MYSQLI_ASSOC) : [];
?>
<header>
    <nav>
        <div class="logo">
            <a href="index.php">Sebastinian Showcase</a>
        </div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About</a></li>

            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <?php if($_SESSION['role'] === 'admin'): ?>
                    <li><a href="admin_dashboard.php">Admin</a></li>
                <?php else: ?>
                    <li><a href="my_projects.php">My Projects</a></li>
                    <li><a href="upload_projects.php">Upload Project</a></li>
                <?php endif; ?>
                <li><a href="../api/auth/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>

            <li class="sdg-dropdown">
                <a href="#">Filter by SDG</a>
                <ul class="sdg-menu">
                    <?php foreach($sdgs as $sdg): ?>
                        <li><a href="sdg_filter.php?sdg_id=<?= $sdg['sdg_id'] ?>"><?= htmlspecialchars($sdg['sdg_name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="../assets/css/register.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
    <link rel="stylesheet" href="../assets/css/project.css">
    <link rel="stylesheet" href="../assets/css/my_projects.css">
    <link rel="stylesheet" href="../assets/css/upload_projects.css">
    <link rel="stylesheet" href="../assets/css/about.css">
    <link rel="stylesheet" href="../assets/css/404.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link rel="stylesheet" href="../assets/css/header.css">

    <!-- JS -->
    <script src="../assets/js/index.js" defer></script>
    <script src="../assets/js/login.js" defer></script>
    <script src="../assets/js/register.js" defer></script>
    <script src="../assets/js/project.js" defer></script>
    <script src="../assets/js/my_projects.js" defer></script>
    <script src="../assets/js/upload_projects.js" defer></script>
    <script src="../assets/js/about.js" defer></script>
    <script src="../assets/js/404.js" defer></script>
    <script src="../assets/js/profile.js" defer></script>
    <script src="../assets/js/footer.js" defer></script>

</header>

<style>
/* Basic header/navigation styling */
header { background: #B22222; color: white; padding: 10px 20px; }
nav { display: flex; align-items: center; justify-content: space-between; }
nav .logo a { color: white; text-decoration: none; font-weight: bold; font-size: 1.3em; }
nav .nav-links { list-style: none; display: flex; gap: 15px; }
nav .nav-links li { position: relative; }
nav .nav-links a { color: white; text-decoration: none; padding: 5px 10px; display: block; }
nav .nav-links a:hover { background: rgba(255,255,255,0.2); border-radius: 4px; }
.sdg-dropdown:hover .sdg-menu { display: block; }
.sdg-menu { display: none; position: absolute; background: white; color: black; list-style: none; margin: 0; padding: 5px 0; top: 100%; left: 0; min-width: 200px; border-radius: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.2); }
.sdg-menu li a { color: black; padding: 8px 15px; display: block; }
.sdg-menu li a:hover { background: #f0f0f0; }
</style>
