<?php
// 1. ALL PHP LOGIC AT THE VERY TOP
session_start();
require_once("../api/config/db.php");
require_once("../api/utils/auth_check.php");
require_once("../api/utils/response.php");

Auth::requireLogin();
$current_user_id = $_SESSION['user_id'] ?? null;
$current_user_role = $_SESSION['role'] ?? null;

$conn = (new Database())->connect();

$project_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$project_id || $project_id <= 0) {
    header("Location: 404.php");
    exit;
}

// Fetch project
$stmt = $conn->prepare("SELECT * FROM projects WHERE project_id = ? LIMIT 1");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) {
    header("Location: 404.php");
    exit;
}

// Permission check
$is_owner = $current_user_id === (int)$project['user_id'];
$is_admin = $current_user_role === 'admin';
if (!$is_owner && !$is_admin) {
    Response::error("Access Denied", 403);
}

$errors = [];

// HANDLE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($title)) $errors[] = "Title is required.";
    if (empty($description)) $errors[] = "Description is required.";

    // Handle file upload
    $new_file_name = $project['file'];
    if (!empty($_FILES['file']['name'])) {
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $new_file_name = "proj_file_{$project_id}_".time().".{$ext}";
        $target_path = __DIR__ . "/../uploads/project_files/" . $new_file_name;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
            if (!empty($project['file']) && file_exists(__DIR__ . "/../uploads/project_files/".$project['file'])) {
                unlink(__DIR__ . "/../uploads/project_files/".$project['file']);
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE projects SET title=?, description=?, file=? WHERE project_id=?");
        $stmt->bind_param("sssi", $title, $description, $new_file_name, $project_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Thesis updated successfully!";
            // THIS WILL NOW WORK because no HTML has been sent yet
            header("Location: my_projects.php"); 
            exit; 
        } else {
            $errors[] = "Database update failed.";
        }
    }
}

// 2. NOW START THE HTML OUTPUT
include("header.php"); 
?>

<link rel="stylesheet" href="../assets/css/edit_project.css">
<main class="project-container">
    <section class="project-box">
        <a href="my_projects.php" class="back-btn">&larr; Back to My Research</a>

        <h1 class="project-title">Edit Project</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $err): ?>
                    <p class="error"><?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form id="edit-project-form" action="" method="POST" enctype="multipart/form-data">
            <label for="title">Project Title</label>
            <input type="text" name="title" id="title" value="<?= htmlspecialchars($project['title']) ?>" required>

            <label for="description">Project Description</label>
            <textarea name="description" id="description" required><?= htmlspecialchars($project['description']) ?></textarea>

            <label for="file">Project File (PDF/DOCX)</label>
            <input type="file" name="file" id="file" accept=".pdf,.zip,.doc,.docx">

            <button type="submit" class="action-btn edit-btn">Save Changes</button>
        </form>
    </section>
</main>

<script src="../assets/js/edit_project.js"></script>
<?php include("footer.php"); ?>