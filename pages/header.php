<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Project root base path
$base = '/Sebastinian_Showcase/pages';
$assets = '/Sebastinian_Showcase/assets';

// Database connection
require_once(__DIR__ . "/../api/config/db.php");
// Only connect if not already connected (prevents "cannot redeclare" errors)
if (!isset($conn)) {
    $conn = (new Database())->connect();
}

// --- LOGIC: Get Current Page Name ---
// This gets "index.php", "about.php", etc.
$current_page = basename($_SERVER['PHP_SELF']);

// Helper function to check active state
function isActive($target_page, $current_page) {
    return ($current_page === $target_page) ? 'active' : '';
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

<header class="main-header">
    <nav>
        <div class="logo">
            <a href="<?= $base ?>/index.php">
                <img src="<?= $assets ?>/img/sebastinian_showcase_logo.png" alt="Sebastinian Showcase Logo" class="site-logo">
                <span>Sebastinian Showcase</span>
            </a>
        </div>
        <ul class="nav-links">
            <li><a href="<?= $base ?>/index.php" class="<?= isActive('index.php', $current_page) ?>">Home</a></li>
            <li><a href="<?= $base ?>/about.php" class="<?= isActive('about.php', $current_page) ?>">About</a></li>

            <?php if(isset($_SESSION['user_id'])): ?>
                
                <li><a href="<?= $base ?>/profile.php" class="<?= isActive('profile.php', $current_page) ?>">Profile</a></li>

                <?php if($_SESSION['role'] === 'admin'): ?>
                    <li><a href="<?= $base ?>/admin/admin_dashboard.php" class="<?= isActive('admin_dashboard.php', $current_page) ?>">Admin</a></li>
                <?php else: ?>
                    <li><a href="<?= $base ?>/dashboard.php" class="<?= isActive('dashboard.php', $current_page) ?>">Dashboard</a></li>
                    <li><a href="<?= $base ?>/my_projects.php" class="<?= isActive('my_projects.php', $current_page) ?>">My Research</a></li>
                    <li><a href="<?= $base ?>/upload_projects.php" class="<?= isActive('upload_projects.php', $current_page) ?>">Upload Research</a></li>
                <?php endif; ?>

                <li><a href="/Sebastinian_Showcase/api/auth/logout.php" class="btn-logout">Logout</a></li>

            <?php else: ?>
                <li><a href="<?= $base ?>/login.php" class="<?= isActive('login.php', $current_page) ?>">Login</a></li>
                <li><a href="<?= $base ?>/register.php" class="<?= isActive('register.php', $current_page) ?>">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<div class="header-spacer"></div>

<style>
/* 1. STICKY HEADER STYLING */
.main-header {
    background: linear-gradient(90deg, #800000 0%, #A52A2A 100%);
    color: white;
    height: 70px;
    display: flex;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%; 
    z-index: 1000;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    padding: 0; 
}

/* Spacer */
.header-spacer {
    height: 70px; 
    display: block;
}

/* 2. NAVIGATION LAYOUT */
nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    max-width: 100%; 
    padding: 0 40px; 
    box-sizing: border-box; 
}

nav .logo a {
    color: white;
    text-decoration: none;
    font-weight: 700;
    font-size: 1.2em;
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: 'Playfair Display', serif;
    white-space: nowrap; 
}

nav .site-logo {
    height: 40px;
    width: auto;
}

nav .nav-links {
    list-style: none;
    display: flex;
    gap: 25px; 
    margin: 0;
    padding: 0;
    align-items: center;
    flex-wrap: nowrap; 
}

nav .nav-links li {
    position: relative;
    white-space: nowrap; 
}

/* 3. STANDARD LINK STYLING */
/* We exclude the .btn-logout class here using :not() to prevent conflicts */
nav .nav-links a:not(.btn-logout) {
    color: rgba(255, 255, 255, 0.85); 
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500; 
    padding: 22px 5px; 
    transition: all 0.3s ease;
    display: inline-block;
}

/* Hover Effect for Standard Links (Transparent Background) */
nav .nav-links a:not(.btn-logout):hover {
    color: #fff; 
    background: transparent !important; 
}

/* 4. ACTIVE TAB (UNDERLINE ONLY) */
nav .nav-links a:not(.btn-logout).active {
    color: #ffffff !important;
    background: transparent !important; 
    font-weight: 700;
}

nav .nav-links a:not(.btn-logout).active::after {
    content: '';
    position: absolute;
    bottom: 18px; 
    left: 0;
    width: 100%;
    height: 3px;
    background-color: #D4AF37; 
    border-radius: 2px;
    box-shadow: 0 0 8px rgba(212, 175, 55, 0.6); 
}

/* 5. LOGOUT BUTTON (Solid Gold) */
.btn-logout {
    background-color: #D4AF37 !important; 
    color: #000000 !important;           
    border: 1px solid #D4AF37 !important;
    padding: 8px 25px !important;        
    border-radius: 25px;
    font-weight: 600 !important;
    text-decoration: none;
    margin-left: 15px;
    transition: transform 0.2s ease, background-color 0.2s ease;
    display: inline-block;
}

/* Hover State for Logout - FORCE SOLID COLOR */
.btn-logout:hover {
    background-color: #C5A028 !important; /* Slightly Darker Gold */
    color: #000000 !important;
    opacity: 1 !important; /* Ensure it never goes transparent */
    transform: scale(1.05); 
}

/* Ensure no underline on logout button */
.btn-logout::after {
    display: none !important; 
}
</style>