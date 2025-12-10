<?php
session_start();
require_once("../api/utils/auth_check.php");
Auth::requireLogin();

$user = Auth::currentUser();

// Security: Generate Anti-CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Project | Sebastinian Showcase</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <link rel="stylesheet" href="../assets/css/upload_projects.css">
</head>
<body>
    
    <?php include("header.php"); ?>

    <canvas id="luxury-canvas"></canvas>

    <main class="upload-wrapper">
        <div class="container">
            
            <header class="page-header" data-tilt>
                <div class="header-content">
                    <span class="sub-headline"><i class="fas fa-star"></i> Sebastinian Excellence</span>
                    <h1 class="headline">Upload Your <span class="text-gold">Masterpiece</span></h1>
                    <p class="description">Contribute to the legacy. Share your innovation with the global community.</p>
                </div>
            </header>

            <div class="upload-grid">
                
                <aside class="upload-sidebar">
                    <div class="user-upload-card" data-tilt data-tilt-scale="1.05">
                        <div class="user-avatar-frame">
                             <img src="<?php echo !empty($user['profile_image']) ? '../uploads/profile_images/'.htmlspecialchars($user['profile_image']) : '../uploads/profile_images/default.png'; ?>" alt="User">
                        </div>
                        <div class="user-details">
                            <span class="label">Posting as</span>
                            <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                            <span class="role-badge">Student Innovator</span>
                        </div>
                    </div>

                    <div class="guidelines-card glass-panel">
                        <h4><i class="fas fa-check-double"></i> Submission Rules</h4>
                        <ul>
                            <li><strong>Originality:</strong> Ensure work is yours.</li>
                            <li><strong>Format:</strong> PDF for docs, JPG for covers.</li>
                            <li><strong>Size:</strong> Max 10MB per file.</li>
                        </ul>
                    </div>
                </aside>

                <section class="upload-main glass-panel">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <div class="form-section">
                            <h3 class="section-title">Project Details</h3>
                            
                            <div class="input-wrapper">
                                <input type="text" id="title" name="title" required placeholder=" " autocomplete="off">
                                <label for="title">Project Title</label>
                                <i class="fas fa-pen-nib input-icon"></i>
                                <div class="line-ripple"></div>
                            </div>

                            <div class="input-wrapper">
                                <textarea id="description" name="description" required placeholder=" "></textarea>
                                <label for="description">Abstract / Description</label>
                                <i class="fas fa-align-left input-icon top-align"></i>
                                <div class="line-ripple"></div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3 class="section-title">Project Assets</h3>
                            
                            <div class="dropzone-grid">
                                <div class="dropzone" id="fileZone">
                                    <input type="file" id="project_file" name="project_file" required accept=".pdf,.doc,.docx,.pptx,.zip">
                                    <div class="dz-content">
                                        <div class="dz-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                        <h5>Project File</h5>
                                        <p>Drag & Drop or <span>Browse</span></p>
                                    </div>
                                    <div class="dz-preview" id="filePreview"></div>
                                    <div class="dz-success"><i class="fas fa-check"></i></div>
                                </div>

                                <div class="dropzone" id="imageZone">
                                    <input type="file" id="project_image" name="project_image" accept=".png,.jpg,.jpeg,.webp">
                                    <div class="dz-content">
                                        <div class="dz-icon"><i class="fas fa-image"></i></div>
                                        <h5>Cover Image</h5>
                                        <p>Drag & Drop or <span>Browse</span></p>
                                    </div>
                                    <div class="dz-preview image-type" id="imagePreview"></div>
                                    <div class="dz-success"><i class="fas fa-check"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-footer">
                            <div id="statusMessage" class="status-message"></div>
                            <button type="submit" class="btn-gold-3d" id="submitBtn">
                                <span class="btn-text">Publish Project</span>
                                <span class="btn-icon"><i class="fas fa-rocket"></i></span>
                                <div class="btn-shine"></div>
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </main>

    <div id="uploadModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-content loading-state">
                <div class="sebastinian-loader">
                    <div class="blade"></div>
                    <div class="blade"></div>
                    <div class="blade"></div>
                </div>
                <h3>Uploading to Cloud...</h3>
                <p>Please wait while we secure your files.</p>
            </div>
            
            <div class="modal-content success-state" style="display:none;">
                <div class="success-icon-anim">
                    <svg viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg>
                </div>
                <h3>Upload Complete!</h3>
                <p>Your project is now live on the showcase.</p>
                <button class="btn-modal" onclick="window.location.href='profile.php'">View Profile</button>
            </div>
        </div>
    </div>

    <?php include("footer.php"); ?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>
    <script src="../assets/js/upload_projects.js"></script>
</body>
</html>