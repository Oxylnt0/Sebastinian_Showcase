<?php
session_start();
require_once("../api/config/db.php");
require_once("../api/utils/auth_check.php");
require_once("../api/utils/response.php");

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$conn = (new Database())->connect();
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'] ?? '';

// Fetch project statistics for user
$stats_stmt = $conn->prepare("
    SELECT 
        COUNT(*) AS total_projects,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending
    FROM projects
    WHERE user_id = ?
");
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();

// Fetch 5 most recent projects
$recent_stmt = $conn->prepare("
    SELECT p.*, s.sdg_name 
    FROM projects p
    LEFT JOIN sdgs s ON p.sdg_id = s.sdg_id
    WHERE p.user_id = ?
    ORDER BY p.date_submitted DESC
    LIMIT 5
");
$recent_stmt->bind_param("i", $user_id);
$recent_stmt->execute();
$recent_result = $recent_stmt->get_result();
$recent_projects = [];
while ($row = $recent_result->fetch_assoc()) {
    $recent_projects[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Sebastinian Showcase</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<?php include 'header.php'; ?>

<main class="dashboard-container">
    <section class="welcome-banner">
        <h1>Welcome back, <span class="gold-text"><?= htmlspecialchars($full_name ?: $username) ?></span>!</h1>
        <p>Your projects and contributions are showcased here.</p>
    </section>

    <section class="stats-cards">
        <div class="card total">
            <h3>Total Projects</h3>
            <p><?= $stats['total_projects'] ?></p>
        </div>
        <div class="card approved">
            <h3>Approved</h3>
            <p><?= $stats['approved'] ?></p>
        </div>
        <div class="card rejected">
            <h3>Rejected</h3>
            <p><?= $stats['rejected'] ?></p>
        </div>
        <div class="card pending">
            <h3>Pending</h3>
            <p><?= $stats['pending'] ?></p>
        </div>
    </section>

    <section class="recent-projects">
        <h2>Recently Submitted Projects</h2>
        <?php if (empty($recent_projects)): ?>
            <p>No projects submitted yet. <a href="project.php" class="gold-text">Submit a project now</a>.</p>
        <?php else: ?>
            <div class="project-list">
                <?php foreach ($recent_projects as $project): ?>
                    <div class="project-card">
                        <?php if ($project['image']): ?>
                            <img src="../uploads/project_images/<?= htmlspecialchars($project['image']) ?>" alt="<?= htmlspecialchars($project['title']) ?>">
                        <?php else: ?>
                            <div class="placeholder-img">No Image</div>
                        <?php endif; ?>
                        <div class="project-info">
                            <h3><?= htmlspecialchars($project['title']) ?></h3>
                            <p><?= htmlspecialchars(substr($project['description'], 0, 100)) ?>...</p>
                            <span class="sdg-tag"><?= htmlspecialchars($project['sdg_name'] ?: 'No SDG') ?></span>
                            <span class="status <?= $project['status'] ?>"><?= ucfirst($project['status']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($role === 'admin'): ?>
        <section class="admin-link">
            <a href="admin_dashboard.php" class="gold-btn">Go to Admin Dashboard</a>
        </section>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>
<script src="../assets/js/dashboard.js"></script>
</body>
</html>
