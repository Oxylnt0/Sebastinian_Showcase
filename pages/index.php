<?php
require_once("../api/config/db.php");
include("header.php"); // includes session start and SDG menu

$conn = (new Database())->connect();

// Fetch approved projects, newest first
$sql = "
    SELECT 
        p.*, 
        u.full_name AS student_name, 
        s.sdg_name
    FROM projects p
    LEFT JOIN users u ON p.user_id = u.user_id
    LEFT JOIN sdgs s ON p.sdg_id = s.sdg_id
    WHERE p.status = 'approved'
    ORDER BY p.date_submitted DESC
";
$result = $conn->query($sql);

$projects = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}
?>

<main class="container">
    <section class="hero">
        <h1>Welcome to Sebastinian Showcase</h1>
        <p>Discover student creativity, innovation, and projects aligned with the UN Sustainable Development Goals.</p>
    </section>

    <section class="projects-grid">
        <?php if(count($projects) > 0): ?>
            <?php foreach($projects as $project): ?>
                <div class="project-card">
                    <?php if($project['image']): ?>
                        <img src="../uploads/project_images/<?= htmlspecialchars($project['image']) ?>" alt="<?= htmlspecialchars($project['title']) ?>">
                    <?php else: ?>
                        <div class="placeholder-img">No Image</div>
                    <?php endif; ?>
                    <div class="project-content">
                        <h2><?= htmlspecialchars($project['title']) ?></h2>
                        <p class="student-name">By: <?= htmlspecialchars($project['student_name']) ?></p>
                        <p class="project-desc"><?= htmlspecialchars(substr($project['description'], 0, 150)) ?>...</p>
                        <span class="sdg-tag"><?= htmlspecialchars($project['sdg_name']) ?></span>
                        <a href="project.php?id=<?= $project['project_id'] ?>" class="view-btn">View Project</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-projects">No projects available at the moment.</p>
        <?php endif; ?>
    </section>
</main>

<?php include("footer.php"); ?>
