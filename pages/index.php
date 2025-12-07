<?php
require_once("../api/config/db.php");
include("header.php"); // session + header

$conn = (new Database())->connect();

/* ---------------------------------------------------------
   FETCH PROJECTS
--------------------------------------------------------- */

// Featured project = most recent approved
$sql_featured = "
    SELECT p.*, u.full_name AS student_name
    FROM projects p
    LEFT JOIN users u ON p.user_id = u.user_id
    WHERE p.status = 'approved'
    ORDER BY p.date_submitted DESC
    LIMIT 1
";

$featured_result = $conn->query($sql_featured);
$featured = $featured_result ? $featured_result->fetch_assoc() : null;

// Fetch all remaining approved projects excluding featured
$sql_projects = "
    SELECT p.*, u.full_name AS student_name
    FROM projects p
    LEFT JOIN users u ON p.user_id = u.user_id
    WHERE p.status = 'approved'
    ORDER BY p.date_submitted DESC
    LIMIT 50
";

$projects_result = $conn->query($sql_projects);
$projects = [];

if ($projects_result) {
    while ($row = $projects_result->fetch_assoc()) {
        // Skip the featured one
        if ($featured && $row['project_id'] == $featured['project_id']) {
            continue;
        }
        $projects[] = $row;
    }
}
?>

<main class="homepage">

    <!-- -----------------------------------------------------
         HERO SECTION — MAGAZINE STYLE
    ------------------------------------------------------ -->
    <section class="hero-banner">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">Sebastinian Showcase</h1>
            <p class="hero-subtitle">
                The official digital exhibit of outstanding student works aligned with the UN Sustainable Development Goals.
            </p>
            <a href="#projects" class="hero-btn">Explore Projects</a>
        </div>
    </section>

    <!-- -----------------------------------------------------
         FEATURED PROJECT (Editorial Layout)
    ------------------------------------------------------ -->
    <?php if ($featured): ?>
    <section class="featured-section">
        <h2 class="section-label">Featured Project</h2>

        <article class="featured-card">

            <div class="featured-image">
                <?php if (!empty($featured['image'])): ?>
                    <img src="../uploads/project_images/<?= htmlspecialchars($featured['image']) ?>"
                         alt="<?= htmlspecialchars($featured['title']) ?>">
                <?php else: ?>
                    <div class="featured-placeholder">No Image Available</div>
                <?php endif; ?>
            </div>

            <div class="featured-info">
                <h3 class="featured-title"><?= htmlspecialchars($featured['title']) ?></h3>

                <p class="featured-author">
                    By <?= htmlspecialchars($featured['student_name']) ?>
                </p>

                <p class="featured-desc">
                    <?= htmlspecialchars(substr($featured['description'], 0, 350)) ?>...
                </p>

                <a href="project.php?id=<?= $featured['project_id'] ?>" class="featured-cta">
                    Read Full Project
                </a>
            </div>

        </article>
    </section>
    <?php endif; ?>

    <!-- -----------------------------------------------------
         PROJECT GRID — Editorial Card Layout
    ------------------------------------------------------ -->
    <section id="projects" class="projects-section">
        <h2 class="section-label">Latest Contributions</h2>

        <?php if (count($projects) > 0): ?>
        <div class="projects-grid">

            <?php foreach ($projects as $project): ?>
            <article class="project-card">

                <div class="project-image">
                    <?php if (!empty($project['image'])): ?>
                        <img src="../uploads/project_images/<?= htmlspecialchars($project['image']) ?>"
                             alt="<?= htmlspecialchars($project['title']) ?>">
                    <?php else: ?>
                        <div class="img-placeholder">No Image</div>
                    <?php endif; ?>
                </div>

                <div class="project-body">
                    <h3 class="project-title">
                        <?= htmlspecialchars($project['title']) ?>
                    </h3>

                    <p class="project-author">
                        By <?= htmlspecialchars($project['student_name']) ?>
                    </p>

                    <p class="project-excerpt">
                        <?= htmlspecialchars(substr($project['description'], 0, 160)) ?>...
                    </p>

                    <a href="project.php?id=<?= $project['project_id'] ?>" class="project-cta">
                        View Project
                    </a>
                </div>

            </article>
            <?php endforeach; ?>

        </div>

        <?php else: ?>
            <p class="no-content">No projects available at the moment.</p>
        <?php endif; ?>
    </section>

</main>

<?php include("footer.php"); ?>
