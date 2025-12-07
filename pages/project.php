<?php 
session_start();
require_once("../api/config/db.php");
require_once("../api/utils/auth_check.php");
include("header.php");

$conn = (new Database())->connect();

// Get project ID from query string
$project_id = intval($_GET['id'] ?? 0);
if ($project_id <= 0) {
    header("Location: 404.php");
    exit;
}

// Fetch project details with owner info
$stmt = $conn->prepare("
    SELECT p.*, u.full_name, u.user_id
    FROM projects p
    LEFT JOIN users u ON p.user_id = u.user_id
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

// Increment view counter
$conn->query("UPDATE projects SET views = views + 1 WHERE project_id = $project_id");

// Current user info
$current_user_id = $_SESSION['user_id'] ?? null;
$current_user_role = $_SESSION['role'] ?? null;
$is_owner = $current_user_id === (int)$project['user_id'];
$is_admin = $current_user_role === 'admin';

// Fetch project likes
$likes_stmt = $conn->prepare("SELECT COUNT(*) AS like_count FROM project_likes WHERE project_id = ?");
$likes_stmt->bind_param("i", $project_id);
$likes_stmt->execute();
$like_count = $likes_stmt->get_result()->fetch_assoc()['like_count'] ?? 0;

// Check if current user liked
$user_liked_stmt = $conn->prepare("SELECT 1 FROM project_likes WHERE project_id = ? AND user_id = ?");
$user_liked_stmt->bind_param("ii", $project_id, $current_user_id);
$user_liked_stmt->execute();
$user_liked = $user_liked_stmt->get_result()->num_rows > 0;

// Fetch top-level comments (nested replies optional)
$comments_stmt = $conn->prepare("
    SELECT c.comment_id, c.comment, c.date_commented, u.full_name, u.user_id
    FROM comments c
    LEFT JOIN users u ON c.user_id = u.user_id
    WHERE c.project_id = ? AND c.parent_id IS NULL
    ORDER BY c.date_commented ASC
");
$comments_stmt->bind_param("i", $project_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();
$comments = $comments_result->fetch_all(MYSQLI_ASSOC);

// Function to calculate time ago
function time_ago($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return $diff . " sec ago";
    $diff = round($diff / 60);
    if ($diff < 60) return $diff . " min ago";
    $diff = round($diff / 60);
    if ($diff < 24) return $diff . " hr ago";
    $diff = round($diff / 24);
    if ($diff < 30) return $diff . " days ago";
    return date("M d, Y", $time);
}
?>

<main class="project-container">
    <section class="project-box">
        <h1 class="project-title"><?= htmlspecialchars($project['title']); ?></h1>
        <p class="project-meta">
            Submitted by <strong><?= htmlspecialchars($project['full_name']); ?></strong> |
            Status: <strong class="<?= $project['status']; ?>"><?= ucfirst($project['status']); ?></strong> |
            Views: <strong id="view-count"><?= $project['views'] + 1; ?></strong> |
            Submitted: <strong><?= time_ago($project['date_submitted']); ?></strong>
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
                <a href="../uploads/project_files/<?= htmlspecialchars($project['file']); ?>" target="_blank" class="download-btn" id="download-file-btn">
                    Download Project File
                </a>
                <span id="download-count">Downloaded: <?= $project['downloads'] ?? 0; ?></span>
            </div>
        <?php endif; ?>

        <div class="project-actions">
            <?php if ($is_owner): ?>
                <button id="delete-project-btn" data-project-id="<?= $project_id; ?>" class="delete-btn">Delete Project</button>
                <?php if ($project['status'] !== 'approved'): ?>
                    <a href="edit_project.php?id=<?= $project_id; ?>" class="action-btn edit-btn">Edit Project</a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($current_user_id): ?>
                <button id="like-btn" data-project-id="<?= $project_id; ?>" class="like-btn <?= $user_liked ? 'liked' : ''; ?>">
                    👍 Like (<span id="like-count"><?= $like_count; ?></span>)
                </button>
            <?php endif; ?>
        </div>

        <hr>

        <div class="comments-section">
            <h2>Comments (<?= count($comments); ?>)</h2>

            <div id="comments-list">
                <?php foreach ($comments as $c): ?>
                    <div class="comment <?= $c['user_id'] === $current_user_id ? 'own-comment' : ''; ?>" data-comment-id="<?= $c['comment_id']; ?>">
                        <p><strong><?= htmlspecialchars($c['full_name']); ?>:</strong> <?= nl2br(htmlspecialchars($c['comment'])); ?></p>
                        <span class="comment-date"><?= time_ago($c['date_commented']); ?></span>
                        <?php if ($c['user_id'] === $current_user_id): ?>
                            <button class="edit-comment-btn">Edit</button>
                            <button class="delete-comment-btn">Delete</button>
                        <?php endif; ?>
                        <button class="like-comment-btn">👍 Like</button>
                        <div class="reply-section">
                            <input type="text" class="reply-input" placeholder="Reply..." />
                            <button class="reply-btn">Reply</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($current_user_id): ?>
                <form id="comment-form" data-project-id="<?= $project_id; ?>">
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
