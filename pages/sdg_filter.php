<?php
session_start();
require_once("../api/config/db.php");

// Check if user is logged in
$user_logged_in = isset($_SESSION['user_id']);
$username = $user_logged_in ? $_SESSION['username'] : null;

$conn = (new Database())->connect();

// Fetch all SDGs for filter
$sdg_sql = "SELECT sdg_id, sdg_name FROM sdgs ORDER BY sdg_id ASC";
$sdg_result = $conn->query($sdg_sql);

$sdgs = [];
while ($row = $sdg_result->fetch_assoc()) {
    $sdgs[] = $row;
}

// Optional: get filtered SDG from query string
$selected_sdg_id = intval($_GET['sdg_id'] ?? 0);

// Fetch projects with optional SDG filter
if ($selected_sdg_id > 0) {
    $stmt = $conn->prepare("
        SELECT p.*, u.full_name, s.sdg_name
        FROM projects p
        LEFT JOIN users u ON p.user_id = u.user_id
        LEFT JOIN sdgs s ON p.sdg_id = s.sdg_id
        WHERE p.sdg_id = ?
        ORDER BY p.date_submitted DESC
    ");
    $stmt->bind_param("i", $selected_sdg_id);
    $stmt->execute();
    $projects_result = $stmt->get_result();
} else {
    $projects_result = $conn->query("
        SELECT p.*, u.full_name, s.sdg_name
        FROM projects p
        LEFT JOIN users u ON p.user_id = u.user_id
        LEFT JOIN sdgs s ON p.sdg_id = s.sdg_id
        ORDER BY p.date_submitted DESC
    ");
}

// Collect projects
$projects = [];
while ($row = $projects_result->fetch_assoc()) {
    $projects[] = $row;
}

?>

<?php include 'header.php'; ?>

<div class="sdg-filter-container">
    <h1>Explore Projects by SDG</h1>

    <div class="filter-section">
        <form id="sdgFilterForm" method="GET">
            <label for="sdgSelect">Filter by SDG:</label>
            <select id="sdgSelect" name="sdg_id">
                <option value="0">All SDGs</option>
                <?php foreach ($sdgs as $sdg): ?>
                    <option value="<?= $sdg['sdg_id'] ?>" <?= $sdg['sdg_id'] === $selected_sdg_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sdg['sdg_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Filter</button>
        </form>
    </div>

    <div class="projects-list">
        <?php if (empty($projects)): ?>
            <p class="no-projects">No projects found for this SDG.</p>
        <?php else: ?>
            <?php foreach ($projects as $project): ?>
                <div class="project-card">
                    <div class="project-header">
                        <h3><?= htmlspecialchars($project['title']) ?></h3>
                        <span class="project-sdg"><?= htmlspecialchars($project['sdg_name'] ?? 'N/A') ?></span>
                    </div>
                    <?php if ($project['image']): ?>
                        <div class="project-image">
                            <img src="../uploads/project_images/<?= htmlspecialchars($project['image']) ?>" alt="<?= htmlspecialchars($project['title']) ?>">
                        </div>
                    <?php endif; ?>
                    <p class="project-description"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                    <div class="project-footer">
                        <span>By: <?= htmlspecialchars($project['full_name']) ?></span>
                        <?php if ($project['file']): ?>
                            <a href="../uploads/project_files/<?= htmlspecialchars($project['file']) ?>" download class="download-btn">Download File</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
