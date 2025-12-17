<?php  
session_start();
require_once("../api/config/db.php");
require_once("../api/utils/auth_check.php");
include("header.php");

// ===============================
// Set default timezone
// ===============================
date_default_timezone_set('Asia/Manila');

// ===============================
// Connect to database
// ===============================
$conn = (new Database())->connect();

// ===============================
// Get project ID & validate
// ===============================
$project_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$project_id || $project_id <= 0) {
    header("Location: 404.php");
    exit;
}

// ===============================
// Fetch project with owner info, downloads & views
// ===============================
$stmt = $conn->prepare("
    SELECT p.*, u.full_name, u.user_id,
           COALESCE(d.downloads, 0) AS downloads
    FROM projects p
    LEFT JOIN users u ON p.user_id = u.user_id
    LEFT JOIN (
        SELECT project_id, COUNT(*) AS downloads
        FROM downloads_log
        GROUP BY project_id
    ) d ON d.project_id = p.project_id
    WHERE p.project_id = ?
");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) {
    header("Location: 404.php");
    exit;
}

// ===============================
// Increment view counter safely
// ===============================
$conn->query("UPDATE projects SET views = views + 1 WHERE project_id = " . intval($project_id));
$project['views'] += 1;

// ===============================
// Current user info
// ===============================
$current_user_id = $_SESSION['user_id'] ?? null;
$current_user_role = $_SESSION['role'] ?? null;
$is_owner = $current_user_id === (int)$project['user_id'];
$is_admin = $current_user_role === 'admin';

// ===============================
// Project likes info
// ===============================
$like_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM project_likes WHERE project_id = ?");
$like_stmt->bind_param("i", $project_id);
$like_stmt->execute();
$like_count = $like_stmt->get_result()->fetch_assoc()['total'] ?? 0;
$like_stmt->close();

$user_liked = false;
if ($current_user_id) {
    $stmt = $conn->prepare("SELECT 1 FROM project_likes WHERE project_id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param("ii", $project_id, $current_user_id);
    $stmt->execute();
    $user_liked = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}

// ===============================
// Comment count (display only; actual comments loaded via JS)
// ===============================
$comment_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM comments WHERE project_id = ?");
$comment_stmt->bind_param("i", $project_id);
$comment_stmt->execute();
$comment_count = $comment_stmt->get_result()->fetch_assoc()['total'] ?? 0;
$comment_stmt->close();
?>

<!-- Pass essential data to JS -->
<script>
    window.sessionUserId = <?= json_encode($current_user_id ?? 0) ?>;
    window.projectId = <?= json_encode($project_id) ?>;
</script>

<main class="project-container">
    <section class="project-box">
        <h1 class="project-title"><?= htmlspecialchars($project['title']) ?></h1>
        <p class="project-meta">
            Submitted by <strong><?= htmlspecialchars($project['full_name']) ?></strong> |
            Status: <strong class="<?= htmlspecialchars($project['status']) ?>"><?= ucfirst(htmlspecialchars($project['status'])) ?></strong> |
            Views: <strong id="view-count"><?= intval($project['views']) ?></strong> |
            Submitted: <strong><?= date('M d, Y', strtotime($project['date_submitted'])) ?></strong>
        </p>

        <?php if (!empty($project['image'])): ?>
        <div class="project-image">
            <img src="../uploads/project_images/<?= htmlspecialchars($project['image']) ?>" alt="Project Image" loading="lazy">
        </div>
        <?php endif; ?>

        <div class="project-description">
            <p><?= nl2br(htmlspecialchars($project['description'])) ?></p>
        </div>

        <?php if (!empty($project['file'])): ?>
        <div class="project-file">
            <a href="../api/projects/download_file.php?project_id=<?= $project_id ?>" 
               class="download-btn" id="download-file-btn">Download Research</a>
            <span id="download-count">Downloaded: <?= intval($project['downloads']) ?></span>
        </div>
        <?php endif; ?>

        <div class="project-actions">
            <?php if ($is_owner): ?>
                <button id="delete-project-btn" class="delete-btn">Delete Project</button>
                <?php if ($project['status'] !== 'approved'): ?>
                    <a href="edit_project.php?id=<?= $project_id ?>" class="action-btn edit-btn">Edit Project</a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($current_user_id): ?>
                <button id="like-btn" class="like-btn <?= $user_liked ? 'liked' : '' ?>" aria-pressed="<?= $user_liked ? 'true' : 'false' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 
                                 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 
                                 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 
                                 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                    <span id="like-count"><?= intval($like_count) ?></span>
                </button>
            <?php else: ?>
                <p class="login-to-like">Please <a href="login.php">login</a> to like this project.</p>
            <?php endif; ?>
        </div>

        <hr>

        <div class="comments-section">
            <h2>Comments (<?= intval($comment_count) ?>)</h2>
            <div id="comments-list"></div>

            <?php if ($current_user_id): ?>
            <form id="comment-form" data-project-id="<?= $project_id ?>">
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
<script src="../assets/js/project.js"></script>
