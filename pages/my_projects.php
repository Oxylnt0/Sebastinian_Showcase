<?php
session_start();
require_once("../api/config/db.php");
require_once("../api/utils/auth_check.php");

// 1. Authenticate
Auth::requireLogin();
$user_id = $_SESSION['user_id'];
$user = Auth::currentUser();

// 2. Security: Generate Anti-CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. Fetch Projects
$conn = (new Database())->connect();

// Fixed Query: Removed 's.sdg_color' which caused the error
$stmt = $conn->prepare("
    SELECT p.*, s.sdg_name
    FROM projects p
    LEFT JOIN sdgs s ON p.sdg_id = s.sdg_id
    WHERE p.user_id = ?
    ORDER BY p.date_submitted DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$projects = [];
while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}

// 4. Stats Calculation
$totalProjects = count($projects);
// Adjust string comparison if your DB uses different capitalization (e.g., 'Approved')
$approvedCount = count(array_filter($projects, fn($p) => strtolower($p['status']) === 'approved'));
$pendingCount  = count(array_filter($projects, fn($p) => strtolower($p['status']) === 'pending'));
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
<body>

    <?php include "header.php"; ?>

    <div class="luxury-layer">
        <div class="gold-orb orb-top"></div>
        <div class="gold-orb orb-bottom"></div>
        <div class="noise-overlay"></div>
    </div>

    <main class="portfolio-wrapper">
        
        <section class="portfolio-hero" data-tilt>
            <div class="hero-content">
                <span class="eyebrow"><i class="fas fa-crown text-gold"></i> Sebastinian Innovator</span>
                <h1>My <span class="text-gradient">Showcase</span></h1>
                <p>Manage, track, and showcase your academic contributions.</p>
            </div>
            
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $totalProjects; ?></h3>
                        <span>Total Projects</span>
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
                        <span>Pending</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="container">
            <div class="control-bar glass-panel">
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="projectSearch" placeholder="Search your projects...">
                </div>
                <div class="filter-group">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="approved">Published</button>
                    <button class="filter-btn" data-filter="pending">Pending</button>
                    <button class="filter-btn" data-filter="rejected">Drafts</button>
                </div>
                <a href="upload_projects.php" class="btn-new-project">
                    <i class="fas fa-plus"></i> <span>New Project</span>
                </a>
            </div>
        </div>

        <div class="container">
            
            <?php if (empty($projects)): ?>
                
                <div class="empty-state-card glass-panel" data-tilt>
                    <div class="empty-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h2>Your Portfolio is Empty</h2>
                    <p>Great innovation starts with a single step. Share your first project today.</p>
                    <a href="upload_projects.php" class="btn-gold-3d">
                        <span>Upload Now</span>
                        <div class="btn-shine"></div>
                    </a>
                </div>

            <?php else: ?>

                <div class="projects-grid" id="projectsGrid">
                    
                    <?php foreach ($projects as $proj): ?>
                        <?php 
                            // 1. Data Safety & Formatting
                            $title = htmlspecialchars($proj['title']);
                            $status = strtolower($proj['status'] ?? 'draft');
                            $statusLabel = ucfirst($status);
                            
                            // 2. Image Handling
                            $imageName = $proj['image'] ?? '';
                            $imagePath = !empty($imageName) ? '../uploads/project_images/' . htmlspecialchars($imageName) : null;
                            $hasImage = $imagePath && file_exists(__DIR__ . '/../uploads/project_images/' . $imageName);
                            
                            // 3. File Handling
                            $fileName = $proj['file'] ?? '';
                            $filePath = !empty($fileName) ? '../uploads/project_files/' . htmlspecialchars($fileName) : '#';
                            $hasFile = !empty($fileName) && file_exists(__DIR__ . '/../uploads/project_files/' . $fileName);

                            // 4. Date Formatting
                            $dateStr = $proj['date_submitted'] ?? null;
                            $dateDisplay = $dateStr ? date("M d, Y", strtotime($dateStr)) : 'No Date';
                        ?>

                        <article class="project-card glass-card" data-status="<?php echo $status; ?>" data-tilt>
                            
                            <div class="card-media">
                                <?php if ($hasImage): ?>
                                    <img src="<?php echo $imagePath; ?>" alt="Project Cover" loading="lazy">
                                <?php else: ?>
                                    <div class="placeholder-art">
                                        <i class="fas fa-cube"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-overlay">
                                    <a href="project.php?id=<?php echo $proj['project_id']; ?>" class="btn-view">
                                        View Project <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                                
                                <span class="status-badge <?php echo $status; ?>">
                                    <?php echo $statusLabel; ?>
                                </span>
                            </div>

                            <div class="card-content">
                                <div class="card-meta">
                                    <span class="date"><i class="far fa-calendar-alt"></i> <?php echo $dateDisplay; ?></span>
                                    <?php if (!empty($proj['sdg_name'])): ?>
                                        <span class="sdg-pill" title="Aligned with <?php echo htmlspecialchars($proj['sdg_name']); ?>">
                                            <i class="fas fa-globe-americas"></i> SDG
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <h3 class="card-title">
                                    <a href="project.php?id=<?php echo $proj['project_id']; ?>">
                                        <?php echo $title; ?>
                                    </a>
                                </h3>
                                
                                <p class="card-excerpt">
                                    <?php 
                                        $desc = strip_tags($proj['description']);
                                        echo htmlspecialchars(substr($desc, 0, 90)) . (strlen($desc) > 90 ? "..." : ""); 
                                    ?>
                                </p>

                                <div class="card-footer">
                                    <?php if ($hasFile): ?>
                                        <a href="<?php echo $filePath; ?>" class="action-link download" download title="Download Assets">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="action-link disabled" title="No file available"><i class="fas fa-ban"></i></span>
                                    <?php endif; ?>
                                    
                                    <div class="footer-actions">
                                        <button class="action-btn edit" data-id="<?php echo $proj['project_id']; ?>" title="Edit Project">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button class="action-btn delete" data-id="<?php echo $proj['project_id']; ?>" data-token="<?php echo $_SESSION['csrf_token']; ?>" title="Delete Project">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </article>

                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>
    </main>

    <?php include "footer.php"; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>
    <script src="../assets/js/my_projects.js"></script>
</body>
</html>