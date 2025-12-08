<?php
session_start();
require_once("../api/config/db.php");
require_once("../api/utils/auth_check.php");
require_once("../api/utils/response.php");
include("header.php");

// ===============================
// Ensure user is logged in
// ===============================
Auth::requireLogin();
$current_user_id = $_SESSION['user_id'] ?? null;
$current_user_role = $_SESSION['role'] ?? null;

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
// Fetch project info
// ===============================
$stmt = $conn->prepare("SELECT * FROM projects WHERE project_id = ? LIMIT 1");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) {
    header("Location: 404.php");
    exit;
}

// ===============================
// Check ownership / admin
// ===============================
$is_owner = $current_user_id === (int)$project['user_id'];
$is_admin = $current_user_role === 'admin';
if (!$is_owner && !$is_admin) {
    Response::error("You do not have permission to edit this project", 403);
}

// ===============================
// Initialize messages
// ===============================
$errors = [];
$success = "";

// ===============================
// Handle form submission
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($title) || strlen($title) > 150) $errors[] = "Title is required (max 150 chars).";
    if (empty($description)) $errors[] = "Description is required.";

    // --- Handle image upload ---
    $new_image_name = $project['image'];
    if (!empty($_FILES['image']['name'])) {
        $allowed_img_types = ['image/jpeg','image/png','image/gif','image/webp'];
        if (!in_array($_FILES['image']['type'], $allowed_img_types)) $errors[] = "Invalid image type.";
        elseif ($_FILES['image']['size'] > 2*1024*1024) $errors[] = "Image exceeds 2MB.";
        else {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_image_name = "proj_img_{$project_id}_".time().".{$ext}";
            $target_path = __DIR__ . "/../uploads/project_images/" . $new_image_name;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) $errors[] = "Failed to upload image.";
            else {
                if (!empty($project['image']) && file_exists(__DIR__ . "/../uploads/project_images/".$project['image'])) {
                    unlink(__DIR__ . "/../uploads/project_images/".$project['image']);
                }
            }
        }
    }

    // --- Handle project file upload ---
    $new_file_name = $project['file'];
    if (!empty($_FILES['file']['name'])) {
        $allowed_file_types = ['application/pdf','application/zip','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!in_array($_FILES['file']['type'], $allowed_file_types)) $errors[] = "Invalid file type.";
        elseif ($_FILES['file']['size'] > 50*1024*1024) $errors[] = "File exceeds 50MB.";
        else {
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $new_file_name = "proj_file_{$project_id}_".time().".{$ext}";
            $target_path = __DIR__ . "/../uploads/project_files/" . $new_file_name;
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) $errors[] = "Failed to upload file.";
            else {
                if (!empty($project['file']) && file_exists(__DIR__ . "/../uploads/project_files/".$project['file'])) {
                    unlink(__DIR__ . "/../uploads/project_files/".$project['file']);
                }
            }
        }
    }

    // --- Update database if no errors ---
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE projects SET title=?, description=?, image=?, file=? WHERE project_id=?");
        $stmt->bind_param("ssssi", $title, $description, $new_image_name, $new_file_name, $project_id);
        if ($stmt->execute()) {
            $success = "Project updated successfully!";
            $project['title'] = $title;
            $project['description'] = $description;
            $project['image'] = $new_image_name;
            $project['file'] = $new_file_name;
        } else {
            $errors[] = "Database update failed.";
        }
        $stmt->close();
    }
}
?>

<link rel="stylesheet" href="../assets/css/edit_project.css">
<main class="project-container">
    <section class="project-box">
        <!-- Back Button -->
        <a href="javascript:history.back()" class="back-btn">&larr; Back</a>

        <h1 class="project-title">Edit Project</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $err): ?>
                    <p class="error"><?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-message">
                <p class="success"><?= htmlspecialchars($success) ?></p>
            </div>
        <?php endif; ?>

        <form id="edit-project-form" action="" method="POST" enctype="multipart/form-data">
            <label for="title">Project Title</label>
            <input type="text" name="title" id="title" value="<?= htmlspecialchars($project['title']) ?>" required maxlength="150">

            <label for="description">Project Description</label>
            <textarea name="description" id="description" required><?= htmlspecialchars($project['description']) ?></textarea>

            <label for="image">Project Image</label>
            <div class="image-preview">
                <?php if (!empty($project['image'])): ?>
                    <img id="image-preview" src="../uploads/project_images/<?= htmlspecialchars($project['image']) ?>" alt="Current Image">
                <?php else: ?>
                    <img id="image-preview" style="display:none;">
                <?php endif; ?>
            </div>
            <input type="file" name="image" id="image" accept="image/*">

            <label for="file">Project File</label>
            <?php if (!empty($project['file'])): ?>
                <p>Current file: <?= htmlspecialchars($project['file']) ?></p>
            <?php endif; ?>
            <input type="file" name="file" id="file" accept=".pdf,.zip,.doc,.docx">

            <button type="submit" class="action-btn edit-btn">Save Changes</button>
        </form>
    </section>
</main>

<script src="../assets/js/edit_project.js"></script>
<?php include("footer.php"); ?>
