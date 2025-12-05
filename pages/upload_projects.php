<?php
session_start();
require_once("../api/utils/auth_check.php");
Auth::requireLogin();

// Get logged-in user info
$user = Auth::currentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Project - Sebastinian Showcase</title>
    <link rel="stylesheet" href="../assets/css/upload_projects.css">
    <script src="../assets/js/upload_projects.js" defer></script>
</head>
<body>
    <?php include("header.php"); ?>

    <main class="upload-container">
        <div class="upload-card">
            <h1>Upload Your Project</h1>
            <p class="subtitle">Showcase your creativity and share your project!</p>

            <form id="uploadForm" enctype="multipart/form-data" method="POST" action="../api/projects/upload_projects.php">
                <!-- Project Title -->
                <div class="form-group">
                    <label for="title">Project Title</label>
                    <input type="text" id="title" name="title" placeholder="Enter your project title" required>
                </div>

                <!-- Project Description -->
                <div class="form-group">
                    <label for="description">Project Description</label>
                    <textarea id="description" name="description" placeholder="Describe your project..." required></textarea>
                </div>

                <!-- Project File Upload -->
                <div class="form-group">
                    <label for="project_file">Project File</label>
                    <input type="file" id="project_file" name="project_file" accept=".pdf,.doc,.docx,.pptx,.txt,.zip" required>
                    <small>Allowed: pdf, doc, docx, pptx, txt, zip (max 10MB)</small>
                </div>

                <!-- Project Image Upload -->
                <div class="form-group">
                    <label for="project_image">Project Image / Thumbnail</label>
                    <input type="file" id="project_image" name="project_image" accept=".png,.jpg,.jpeg,.webp">
                    <small>Optional. Allowed: png, jpg, jpeg, webp (max 5MB)</small>
                </div>

                <!-- Submit Button -->
                <div class="form-group submit-group">
                    <button type="submit" class="btn-submit" id="submitBtn">Upload Project</button>
                </div>

                <!-- Feedback Message -->
                <div id="feedback" class="feedback"></div>
            </form>
        </div>
    </main>

    <?php include("footer.php"); ?>
</body>
</html>
