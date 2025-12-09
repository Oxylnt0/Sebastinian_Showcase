<?php
require_once("../api/config/db.php");
include("header.php"); // session + header

$conn = (new Database())->connect();

/* ============================================================
   FETCH FEATURED PROJECT — Most Liked Approved Project
============================================================ */
$featured_sql = "
    SELECT p.*, u.full_name AS student_name, COUNT(pl.like_id) AS total_likes
    FROM projects p
    LEFT JOIN users u ON p.user_id = u.user_id
    LEFT JOIN project_likes pl ON pl.project_id = p.project_id
    WHERE p.status = 'approved'
    GROUP BY p.project_id
    ORDER BY total_likes DESC, p.date_submitted DESC
    LIMIT 1
";
$featured_result = $conn->query($featured_sql);
$featured = $featured_result ? $featured_result->fetch_assoc() : null;

/* ============================================================
   FETCH LATEST PROJECTS (excluding featured)
============================================================ */
$projects_sql = "
    SELECT p.*, u.full_name AS student_name
    FROM projects p
    LEFT JOIN users u ON p.user_id = u.user_id
    WHERE p.status = 'approved'
    ORDER BY p.date_submitted DESC
    LIMIT 50
";
$projects_result = $conn->query($projects_sql);
$projects = [];

if ($projects_result) {
    while ($row = $projects_result->fetch_assoc()) {
        if ($featured && $row['project_id'] == $featured['project_id']) continue;
        $projects[] = $row;
    }
}
?>

<main id="home">

    <!-- ============================================================
         HERO SECTION — Cinematic Intro
    ============================================================= -->
    <section class="hero-banner">
        <div class="hero-overlay"></div>
        <div class="hero-inner">
            <h1 class="hero-title">Sebastinian Showcase</h1>
            <p class="hero-tagline">
                A curated digital exhibition of exceptional student works aligned with the UN Sustainable Development Goals.
            </p>
            <a href="#projects" class="hero-cta">Explore Projects</a>
        </div>
    </section>

    <!-- ============================================================
         FEATURED PROJECT — Compact Spotlight
    ============================================================= -->
    <?php if ($featured): ?>
    <section class="featured-wrapper">
        <h2 class="section-heading">Featured Project</h2>
        <article class="featured-card">

            <!-- IMAGE — Fixed square ratio -->
            <div class="featured-media">
                <?php if (!empty($featured['image'])): ?>
                    <img
                        src="../uploads/project_images/<?= htmlspecialchars($featured['image']) ?>"
                        alt="<?= htmlspecialchars($featured['title']) ?>"
                        loading="lazy"
                    >
                <?php else: ?>
                    <div class="media-fallback">No Image</div>
                <?php endif; ?>
            </div>

            <!-- DETAILS — Compact, stacked below image -->
            <div class="featured-details">
                <h3 class="featured-title"><?= htmlspecialchars($featured['title']) ?></h3>
                <p class="featured-author">By <?= htmlspecialchars($featured['student_name']) ?></p>
                <p class="featured-summary">
                    <?= htmlspecialchars(substr($featured['description'], 0, 200)) ?>...
                </p>
                <p class="featured-likes">❤️ <?= $featured['total_likes'] ?> Likes</p>
                <a href="project.php?id=<?= $featured['project_id'] ?>" class="featured-link">
                    Read Full Project
                </a>
            </div>

        </article>
    </section>
    <?php endif; ?>

    <!-- ============================================================
         MAIN PROJECT GRID — Compact, Uniform Cards
    ============================================================= -->
    <section id="projects" class="projects-gallery">
        <h2 class="section-heading">Latest Contributions</h2>

        <?php if (!empty($projects)): ?>
        <div class="gallery-grid">

            <?php foreach ($projects as $p): ?>
            <article class="project-card">

                <div class="project-media">
                    <?php if (!empty($p['image'])): ?>
                        <img
                            src="../uploads/project_images/<?= htmlspecialchars($p['image']) ?>"
                            alt="<?= htmlspecialchars($p['title']) ?>"
                            loading="lazy"
                        >
                    <?php else: ?>
                        <div class="media-fallback">No Image</div>
                    <?php endif; ?>
                </div>

                <div class="project-info">
                    <h3 class="project-title"><?= htmlspecialchars($p['title']) ?></h3>
                    <p class="project-author">By <?= htmlspecialchars($p['student_name']) ?></p>
                    <p class="project-excerpt">
                        <?= htmlspecialchars(substr($p['description'], 0, 160)) ?>...
                    </p>
                    <a href="project.php?id=<?= $p['project_id'] ?>" class="project-link">
                        View Project
                    </a>
                </div>

            </article>
            <?php endforeach; ?>

        </div>
        <?php else: ?>
            <p class="no-projects">No approved projects available at the moment.</p>
        <?php endif; ?>

    </section>

</main>

<?php include("footer.php"); ?>
