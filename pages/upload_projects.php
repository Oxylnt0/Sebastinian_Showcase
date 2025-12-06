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
    <style>
        /* ============================= */
        /* Upload Project Page - Sebastinian Showcase */
        /* Primary Colors: Red (#B22222) & Gold (#FFD700) */
        /* ============================= */

        /* --- Reset basics --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f9f9f9; color: #333; }

        /* --- Main container --- */
        .upload-container { max-width: 900px; margin: 60px auto 40px auto; background: #fff; padding: 40px 50px; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); border-top: 6px solid #B22222; }

        /* --- Page heading --- */
        .upload-container h1 { color: #B22222; font-size: 2.2rem; margin-bottom: 10px; text-align: center; font-weight: 700; }
        .upload-container .subtitle { color: #555; font-size: 1rem; margin-bottom: 30px; text-align: center; }

        /* --- Form styling --- */
        form { display: flex; flex-direction: column; gap: 20px; }

        /* Form groups */
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 600; margin-bottom: 8px; color: #B22222; }
        .form-group input[type="text"], .form-group textarea, .form-group select, .form-group input[type="file"] { padding: 12px 15px; border: 2px solid #ccc; border-radius: 8px; font-size: 1rem; transition: all 0.3s ease; }
        .form-group input[type="text"]:focus, .form-group textarea:focus, .form-group select:focus, .form-group input[type="file"]:focus { outline: none; border-color: #B22222; box-shadow: 0 0 8px rgba(178, 34, 34, 0.3); }
        textarea { resize: vertical; min-height: 120px; }

        /* File input small text */
        .form-group small { margin-top: 5px; font-size: 0.85rem; color: #777; }

        /* --- Button Styling --- */
        .btn-submit { background: linear-gradient(135deg, #B22222, #FFD700); color: #fff; font-weight: 700; font-size: 1.1rem; padding: 14px 20px; border: none; border-radius: 10px; cursor: pointer; transition: all 0.3s ease; align-self: center; width: 50%; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(178, 34, 34, 0.4); }

        /* --- Feedback message --- */
        .feedback { text-align: center; font-weight: 600; margin-top: 15px; min-height: 24px; }
        .feedback.success { color: #228B22; }
        .feedback.error { color: #B22222; }

        /* --- Upload card --- */
        .upload-card { padding: 30px 25px; background: #fff; border-radius: 12px; box-shadow: 0 6px 18px rgba(0,0,0,0.1); }

        /* --- Drag & Drop Styling --- */
        .dropzone { position: relative; border: 2px dashed #B22222; border-radius: 12px; padding: 20px; background: #fff8f8; cursor: pointer; transition: all 0.3s ease; }
        .dropzone.hover { background: #ffeaea; box-shadow: 0 0 12px rgba(178, 34, 34, 0.3); }
        .dropzone input[type="file"] { position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; }
        .file-preview, .image-preview { margin-top: 10px; font-size: 0.9rem; color: #555; text-align: center; }
        .image-preview img { max-width: 100%; max-height: 150px; margin-top: 10px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); transition: transform 0.3s ease; }
        .image-preview img:hover { transform: scale(1.05); }

        /* --- Responsive --- */
        @media (max-width: 768px) { .upload-container { padding: 30px 20px; margin: 40px 20px; } .btn-submit { width: 70%; } }
        @media (max-width: 480px) { .upload-container { padding: 20px 15px; } .btn-submit { width: 100%; } }
    </style>
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
                <div class="form-group dropzone" id="fileDropzone">
                    <label for="project_file">Project File</label>
                    <input type="file" id="project_file" name="project_file" accept=".pdf,.doc,.docx,.pptx,.txt,.zip" required>
                    <small>Allowed: pdf, doc, docx, pptx, txt, zip (max 10MB)</small>
                    <div class="file-preview" id="filePreview">No file selected</div>
                </div>

                <!-- Project Image Upload -->
                <div class="form-group dropzone" id="imageDropzone">
                    <label for="project_image">Project Image / Thumbnail</label>
                    <input type="file" id="project_image" name="project_image" accept=".png,.jpg,.jpeg,.webp">
                    <small>Optional. Allowed: png, jpg, jpeg, webp (max 5MB)</small>
                    <div class="image-preview" id="imagePreview">No image selected</div>
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

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const fileInput = document.getElementById("project_file");
            const filePreview = document.getElementById("filePreview");
            const fileDropzone = document.getElementById("fileDropzone");

            const imageInput = document.getElementById("project_image");
            const imagePreview = document.getElementById("imagePreview");
            const imageDropzone = document.getElementById("imageDropzone");

            // Drag & drop events
            [fileDropzone, imageDropzone].forEach(zone => {
                zone.addEventListener("dragover", e => {
                    e.preventDefault();
                    zone.classList.add("hover");
                });
                zone.addEventListener("dragleave", e => {
                    e.preventDefault();
                    zone.classList.remove("hover");
                });
                zone.addEventListener("drop", e => {
                    e.preventDefault();
                    zone.classList.remove("hover");
                    const input = zone.querySelector("input[type=file]");
                    if (e.dataTransfer.files.length) {
                        input.files = e.dataTransfer.files;
                        input.dispatchEvent(new Event("change"));
                    }
                });
            });

            // Display selected file name
            fileInput.addEventListener("change", () => {
                const file = fileInput.files[0];
                filePreview.textContent = file ? file.name : "No file selected";
            });

            // Display selected image preview
            imageInput.addEventListener("change", () => {
                const file = imageInput.files[0];
                if (!file) {
                    imagePreview.textContent = "No image selected";
                    return;
                }
                if (!file.type.startsWith("image/")) {
                    imagePreview.textContent = "Invalid image file";
                    return;
                }
                const reader = new FileReader();
                reader.onload = e => {
                    imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
</body>
</html>
