<?php
/**
 * Sebastinian Showcase - Ultimate Dashboard
 * Theme: Deep Red & Metallic Gold
 * Concept: Central Command & Overview
 */

session_start();
require_once "../api/config/db.php";
require_once "../api/utils/auth_check.php";

// 1. Strict Authentication
Auth::requireLogin();
$user = Auth::currentUser();
$conn = (new Database())->connect();

$user_id   = $user['user_id'];
$user_role = $user['role'];
$full_name = htmlspecialchars($_SESSION['full_name'] ?? $user['username']);

// 2. Time-Based Greeting Logic
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good Morning";
    $quote = "The morning is full of potential. Track your goals!";
} elseif ($hour < 18) {
    $greeting = "Good Afternoon";
    $quote = "Midday status check. Excellence requires attention.";
} else {
    $greeting = "Good Evening";
    $quote = "Review your progress. Plan tomorrow's innovation.";
}

// 3. Fetch Statistics (Robust Handling)
$stats_stmt = $conn->prepare("
    SELECT 
        COUNT(*) AS total,
        COALESCE(SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END), 0) AS approved,
        COALESCE(SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END), 0) AS rejected,
        COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) AS pending
    FROM projects
    WHERE user_id = ?
");
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// 4. Fetch Recent Activity (Top 5)
$recent_stmt = $conn->prepare("
    SELECT project_id, title, description, status, image, date_submitted
    FROM projects
    WHERE user_id = ?
    ORDER BY date_submitted DESC
    LIMIT 5
");
$recent_stmt->bind_param("i", $user_id);
$recent_stmt->execute();
$recent_projects = $recent_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 5. Asset Path Constants
define('IMG_PATH', '../uploads/project_images/');
define('PROFILE_IMG_PATH', '../uploads/profile_images/');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sebastinian Showcase</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="luxury-layer">
        <div class="gold-orb orb-1"></div>
        <div class="gold-orb orb-2"></div>
        <div class="noise-overlay"></div>
    </div>

    <main class="dashboard-wrapper">
        <div class="container">
            
            <header class="dashboard-hero-panel glass-panel" data-tilt>
                
                <div class="hero-avatar-area">
                    <?php 
                        $profileImg = PROFILE_IMG_PATH . htmlspecialchars($user['profile_image'] ?? 'default.png');
                        $profileSrc = file_exists($profileImg) ? $profileImg : PROFILE_IMG_PATH . 'default.png';
                    ?>
                    <div class="avatar-frame">
                        <img src="<?php echo $profileSrc; ?>" alt="User Avatar">
                    </div>
                </div>

                <div class="hero-content-area">
                    <span class="greeting-badge"><i class="far fa-hand-peace"></i> <?php echo $greeting; ?></span>
                    <h1 class="welcome-title">Welcome back, <span class="text-gradient-red"><?php echo $full_name; ?></span></h1>
                    <p class="motivational-quote"><i class="fas fa-bullseye"></i> <?php echo $quote; ?></p>
                </div>

                <div class="hero-action-area">
                    <a href="upload_projects.php" class="btn-gold-3d">
                        <i class="fas fa-plus"></i> <span>New Project</span>
                        <div class="btn-shine"></div>
                    </a>
                </div>
            </header>

            <?php if ($user_role === 'admin'): ?>
            <section class="admin-alert glass-panel" data-tilt>
                <div class="alert-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="alert-content">
                    <h3>Admin Access Detected</h3>
                    <p>You have elevated privileges to manage the entire showcase.</p>
                </div>
                <a href="admin_dashboard.php" class="btn-admin">Go to Console <i class="fas fa-arrow-right"></i></a>
            </section>
            <?php endif; ?>

            <section class="stats-grid">
                <div class="stat-card glass-panel total">
                    <div class="stat-icon"><i class="fas fa-folder-open"></i></div>
                    <div class="stat-data">
                        <h2 class="counter"><?php echo $stats['total']; ?></h2>
                        <span>Total Projects</span>
                    </div>
                    <div class="stat-bg-icon"><i class="fas fa-folder"></i></div>
                </div>

                <div class="stat-card glass-panel approved">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-data">
                        <h2 class="counter"><?php echo $stats['approved']; ?></h2>
                        <span>Published</span>
                    </div>
                    <div class="stat-bg-icon"><i class="fas fa-check"></i></div>
                </div>

                <div class="stat-card glass-panel pending">
                    <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="stat-data">
                        <h2 class="counter"><?php echo $stats['pending']; ?></h2>
                        <span>Under Review</span>
                    </div>
                    <div class="stat-bg-icon"><i class="fas fa-hourglass"></i></div>
                </div>

                <div class="stat-card glass-panel rejected">
                    <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="stat-data">
                        <h2 class="counter"><?php echo $stats['rejected']; ?></h2>
                        <span>Returned</span>
                    </div>
                    <div class="stat-bg-icon"><i class="fas fa-ban"></i></div>
                </div>
            </section>

            <section class="recent-section">
                <div class="section-header">
                    <h2 class="section-title">Recent <span class="text-gold">Submissions</span></h2>
                    <a href="my_projects.php" class="view-all-link">View All Portfolio <i class="fas fa-long-arrow-alt-right"></i></a>
                </div>

                <?php if (empty($recent_projects)): ?>
                    
                    <div class="empty-dashboard glass-panel">
                        <div class="empty-art">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h3>Start Your Journey</h3>
                        <p>You haven't uploaded any projects yet. Share your first innovation today.</p>
                        <a href="upload_projects.php" class="btn-text-gold">Upload Now</a>
                    </div>

                <?php else: ?>

                    <div class="recent-list glass-panel">
                        <?php foreach ($recent_projects as $proj): ?>
                            <?php 
                                // Logic: Check if image exists
                                $imgName = $proj['image'];
                                $hasImage = !empty($imgName) && file_exists(IMG_PATH . $imgName);
                                $imgSrc = $hasImage ? IMG_PATH . htmlspecialchars($imgName) : '../assets/images/placeholder.png'; // Use a default or icon
                                
                                $status = strtolower($proj['status']);
                                // FIX: Use date_submitted here as well
                                $date = date("M d, Y", strtotime($proj['date_submitted']));
                            ?>
                            
                            <div class="recent-item">
                                <div class="item-visual">
                                    <?php if ($hasImage): ?>
                                        <img src="<?php echo $imgSrc; ?>" alt="Project">
                                    <?php else: ?>
                                        <div class="visual-placeholder"><i class="fas fa-cube"></i></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="item-info">
                                    <h4><a href="project.php?id=<?php echo $proj['project_id']; ?>"><?php echo htmlspecialchars($proj['title']); ?></a></h4>
                                    <span class="item-date"><i class="far fa-clock"></i> <?php echo $date; ?></span>
                                </div>

                                <div class="item-status">
                                    <span class="status-pill <?php echo $status; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </div>
                                
                                <div class="item-action">
                                    <a href="project.php?id=<?php echo $proj['project_id']; ?>" class="icon-btn" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>
            </section>

        </div>
    </main>

    <?php include 'footer.php'; ?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>