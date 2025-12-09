<?php
session_start();
require_once("../api/config/db.php");

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = (new Database())->connect();

// Fetch user projects
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
?>

<?php include "header.php"; ?>

<div class="my-projects-container">
    <h1>My Projects</h1>
    <?php if (count($projects) === 0): ?>
        <p class="no-projects">You haven't submitted any projects yet. <a href="upload_projects.php">Submit a new project</a>.</p>
    <?php else: ?>
        <div class="project-list">
            <?php foreach ($projects as $proj): ?>
                <div class="project-card">
                    <?php if (!empty($proj['image']) && file_exists("../uploads/project_images/" . $proj['image'])): ?>
                        <img src="../uploads/project_images/<?php echo htmlspecialchars($proj['image']); ?>" alt="Project Image">
                    <?php else: ?>
                        <div class="placeholder-img">No Image</div>
                    <?php endif; ?>

                    <div class="project-info">
                        <h3><?php echo htmlspecialchars($proj['title']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($proj['description'], 0, 100)) . (strlen($proj['description']) > 100 ? "..." : ""); ?></p>
                        <?php if (!empty($proj['sdg_name'])): ?>
                            <span class="sdg-tag"><?php echo htmlspecialchars($proj['sdg_name']); ?></span>
                        <?php endif; ?>
                        <span class="status <?php echo $proj['status']; ?>"><?php echo ucfirst($proj['status']); ?></span>
                        <div class="project-actions">
                            <?php if (!empty($proj['file']) && file_exists("../uploads/project_files/" . $proj['file'])): ?>
                                <a href="../uploads/project_files/<?php echo htmlspecialchars($proj['file']); ?>" class="gold-btn" download>Download File</a>
                            <?php endif; ?>
                            <a href="project.php?id=<?php echo $proj['project_id']; ?>" class="gold-btn">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include "footer.php"; ?>
