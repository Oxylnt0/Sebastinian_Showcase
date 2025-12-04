<?php
session_start();
require_once("../api/config/db.php");
include("header.php"); // Include header

$conn = (new Database())->connect();

// Get project ID from query string
$project_id = intval($_GET['id'] ?? 0);

if ($project_id <= 0) {
    header("Location: 404.php");
    exit;
}

// Fetch project details
$stmt = $conn->prepare("
    SELECT p.*, u.full_name, s.sdg_name
    FROM projects p
    LEFT JOIN users u ON p.user_id = u.user_id
    LEFT JOIN sdgs s ON p.sdg_id = s.sdg_id
    WHERE p.project_id = ?
");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: 404.php");
    exit;
}

$project = $result->fetch_assoc();

// Fetch project comments
$comments_stmt = $conn->prepare("
    SELECT c.comment, c.date_commented, u.full_name
    FROM comments c
    LEFT JOIN users u ON c.user_id = u.user_id
    WHERE c.project_id = ?
    ORDER BY c.date_commented ASC
");
$comments_stmt->bind_param("i", $project_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();
$comments = [];
while ($row = $comments_result->fetch_assoc()) {
    $comments[] = $row;
}
?>

<main class="project-container">
    <section class="project-box">
        <h1 class="project-title"><?= htmlspecialchars($project['title']); ?></h1>
        <p class="project-meta">
            Submitted by <strong><?= htmlspecialchars($project['full_name']); ?></strong> |
            SDG: <strong><?= htmlspecialchars($project['sdg_name'] ?? 'N/A'); ?></strong> |
            Status: <strong class="<?= $project['status']; ?>"><?= ucfirst($project['status']); ?></strong> |
            Views: <strong><?= $project['views']; ?></strong>
        </p>

        <?php if ($project['image']): ?>
            <div class="project-image">
                <img src="../uploads/project_images/<?= htmlspecialchars($project['image']); ?>" alt="Project Image">
            </div>
        <?php endif; ?>

        <div class="project-description">
            <p><?= nl2br(htmlspecialchars($project['description'])); ?></p>
        </div>

        <?php if ($project['file']): ?>
            <div class="project-file">
                <a href="../uploads/project_files/<?= htmlspecialchars($project['file']); ?>" target="_blank" class="download-btn">
                    Download Project File
                </a>
            </div>
        <?php endif; ?>

        <hr>

        <!-- COMMENTS SECTION -->
        <div class="comments-section">
            <h2>Comments (<?= count($comments); ?>)</h2>

            <div id="comments-list">
                <?php foreach ($comments as $c): ?>
                    <div class="comment">
                        <p><strong><?= htmlspecialchars($c['full_name']); ?>:</strong> <?= nl2br(htmlspecialchars($c['comment'])); ?></p>
                        <span class="comment-date"><?= date("M d, Y H:i", strtotime($c['date_commented'])); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form id="comment-form" method="POST" data-project-id="<?= $project_id; ?>">
                    <textarea name="comment" id="comment" placeholder="Add your comment..." required></textarea>
                    <button type="submit" class="comment-btn">Post Comment</button>
                    <div id="comment-message" class="comment-message"></div>
                </form>
            <?php else: ?>
                <p class="login-to-comment">Please <a href="login.php">login</a> to comment.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include("footer.php"); ?>
