<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Project root base path
$base = '/Sebastinian_Showcase/pages';
$assets = '/Sebastinian_Showcase/assets';

// Database connection (adjust path relative to this file)
require_once(__DIR__ . "/../api/config/db.php");
$conn = (new Database())->connect();
?>
<header>
    <nav>
        <div class="logo">
            <a href="<?= $base ?>/index.php">
                <img src="<?= $assets ?>/img/sebastinian_showcase_logo.png" alt="Sebastinian Showcase Logo" class="site-logo">
                <span>Sebastinian Showcase</span>
            </a>
        </div>
        <ul class="nav-links">
            <li><a href="<?= $base ?>/index.php">Home</a></li>
            <li><a href="<?= $base ?>/about.php">About</a></li>

            <?php if(isset($_SESSION['user_id'])): ?>

                <li><a href="<?= $base ?>/profile.php">Profile</a></li>

                <?php if($_SESSION['role'] === 'admin'): ?>
                    <li><a href="<?= $base ?>/admin/admin_dashboard.php">Admin</a></li>
                <?php else: ?>
                    <li><a href="<?= $base ?>/dashboard.php">Dashboard</a></li>
                    <li><a href="<?= $base ?>/my_projects.php">My Projects</a></li>
                    <li><a href="<?= $base ?>/upload_projects.php">Upload Project</a></li>
                <?php endif; ?>

                <li><a href="/Sebastinian_Showcase/api/auth/logout.php">Logout</a></li>

            <?php else: ?>
                <li><a href="<?= $base ?>/login.php">Login</a></li>
                <li><a href="<?= $base ?>/register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- CSS -->
    <link rel="stylesheet" href="<?= $assets ?>/css/index.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/login.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/register.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/profile.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/project.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/edit_project.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/my_projects.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/upload_projects.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/admin_dashboard.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/about.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/404.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/footer.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/header.css">

    <!-- JS -->
    <script src="<?= $assets ?>/js/index.js" defer></script>
    <script src="<?= $assets ?>/js/login.js" defer></script>
    <script src="<?= $assets ?>/js/register.js" defer></script>
    <script src="<?= $assets ?>/js/project.js" defer></script>
    <script src="<?= $assets ?>/js/edit_project.js" defer></script>
    <script src="<?= $assets ?>/js/my_projects.js" defer></script>
    <script src="<?= $assets ?>/js/upload_projects.js" defer></script>
    <script src="<?= $assets ?>/js/admin_dashboard.js" defer></script>
    <script src="<?= $assets ?>/js/about.js" defer></script>
    <script src="<?= $assets ?>/js/404.js" defer></script>
    <script src="<?= $assets ?>/js/profile.js" defer></script>
    <script src="<?= $assets ?>/js/footer.js" defer></script>
</header>

<style>
/* Basic header/navigation styling */
header { background: #B22222; color: white; padding: 10px 20px; }
nav { display: flex; align-items: center; justify-content: space-between; }
nav .logo a { color: white; text-decoration: none; font-weight: bold; font-size: 1.3em; }
nav .nav-links { list-style: none; display: flex; gap: 15px; margin: 0; padding: 0; }
nav .nav-links li { position: relative; }
nav .nav-links a { color: white; text-decoration: none; padding: 5px 10px; display: block; }
nav .nav-links a:hover { background: rgba(255,255,255,0.2); border-radius: 4px; }
</style>
