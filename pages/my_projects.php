<?php
session_start();
require_once("../api/config/db.php");
require_once("../api/utils/auth_check.php");

// 1. Authenticate
Auth::requireLogin();
$user_id = $_SESSION['user_id'];
$user = Auth::currentUser();

// 2. Security
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. Stats Calculation (Keep this for the top counters)
$conn = (new Database())->connect();
$stmt = $conn->prepare("SELECT status FROM projects WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$totalProjects = 0;
$approvedCount = 0;
$pendingCount  = 0;

while ($row = $result->fetch_assoc()) {
    $totalProjects++;
    $status = strtolower($row['status']);
    if ($status === 'approved') $approvedCount++;
    if ($status === 'pending') $pendingCount++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Portfolio | Sebastinian Showcase</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/my_projects.css">
</head>
<body data-csrf="<?php echo $_SESSION['csrf_token']; ?>"> <?php include "header.php"; ?>

    <div class="luxury-layer">
        <div class="gold-orb orb-top"></div>
        <div class="gold-orb orb-bottom"></div>
        <div class="noise-overlay"></div>
    </div>

    <main class="portfolio-wrapper">
        
        <section class="portfolio-hero" data-tilt>
            <div class="hero-content">
                <span class="eyebrow"><i class="fas fa-graduation-cap text-gold"></i> Sebastinian Scholar</span>
                <h1>Research <span class="text-gradient">Portfolio</span></h1>
                <p>Manage, track, and publish your academic contributions.</p>
            </div>
            
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-book"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $totalProjects; ?></h3>
                        <span>Total Studies</span>
                    </div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $approvedCount; ?></h3>
                        <span>Published</span>
                    </div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $pendingCount; ?></h3>
                        <span>Under Review</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="container">
            <div class="search-toolbar glass-panel" style="margin-bottom: 40px; padding: 30px;">
                
                <div class="search-bar-wrapper" style="position: relative; margin-bottom: 20px;">
                    <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #aaa;"></i>
                    <input type="text" id="mySearchInput" placeholder="Search your thesis title, keywords..." 
                           style="width: 100%; padding: 15px 15px 15px 45px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.9);">
                </div>

        <div class="filters-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
            
            <select id="filterStatus" style="padding: 10px; border-radius: 6px;">
                <option value="all">All Statuses</option>
                <option value="approved">Published</option>
                <option value="pending">Pending Review</option>
                <option value="rejected">Returned</option>
            </select>

            <select id="filterType" style="padding: 10px; border-radius: 6px;">
                <option value="all">All Methodologies</option>
                <option value="Quantitative">Quantitative</option>
                <option value="Qualitative">Qualitative</option>
                <option value="Mixed Methods">Mixed Methods</option>
                <option value="Experimental">Experimental</option>
                <option value="Descriptive">Descriptive</option>
                <option value="Case Study">Case Study</option>
            </select>

            <select id="filterYear" style="padding: 10px; border-radius: 6px;">
                <option value="all">All Years</option>
                <?php 
                $curYear = date("Y");
                for($y = $curYear; $y >= 2020; $y--) echo "<option value='$y'>$y</option>";
                ?>
            </select>

            <select id="sortOrder" style="padding: 10px; border-radius: 6px;">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="az">A-Z Title</option>
                <option value="za">Z-A Title</option>
            </select>
        </div>

                <div style="margin-top: 20px; text-align: right;">
                    <a href="upload_projects.php" class="btn-new-project" style="display: inline-block; padding: 10px 20px; background: #D4AF37; color: white; border-radius: 5px; text-decoration: none; font-weight: bold;">
                        <i class="fas fa-plus"></i> Archive New Research
                    </a>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="projects-grid" id="projectsGrid">
                <div class="loading-spinner" style="text-align: center; width: 100%; grid-column: 1 / -1; padding: 50px;">
                    <i class="fas fa-circle-notch fa-spin" style="font-size: 2rem; color: #D4AF37;"></i>
                    <p style="margin-top: 10px; color: #888;">Loading your research...</p>
                </div>
            </div>

            <div id="noResults" style="display:none; text-align:center; padding:50px; color:#888;">
                <i class="fas fa-search" style="font-size:3rem; margin-bottom:15px;"></i>
                <p>No research found matching your filters.</p>
            </div>
        </div>

    </main>

    <?php include "footer.php"; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>
    <script src="../assets/js/my_projects.js"></script>
</body>
</html>