<?php
session_start();
// Include DB config explicitly so we can query fresh data
require_once("../api/config/db.php"); 
require_once("../api/utils/auth_check.php");

Auth::requireLogin();
$session_user = Auth::currentUser();
$user_id = $_SESSION['user_id'];

// Fetch Fresh User Data
$conn = (new Database())->connect();
$stmt = $conn->prepare("SELECT full_name, profile_image FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$fresh_user = $stmt->get_result()->fetch_assoc();

$full_name = htmlspecialchars($fresh_user['full_name'] ?? $session_user['full_name']);
$profile_image_name = $fresh_user['profile_image'] ?? 'default.png';

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
    <title>Submit Research | Sebastinian Showcase</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <link rel="stylesheet" href="../assets/css/upload_projects.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>

<style>
        /* 1. Container Relative Position */
        .input-container {
            position: relative;
            width: 100%;
            display: flex; /* Ensures elements align correctly */
            align-items: center;
        }

        /* 2. The Icon (Left Side) */
        .input-container i {
            position: absolute;
            left: 15px;           /* Pinned to the left */
            top: 50%;             /* Vertically centered */
            transform: translateY(-50%); 
            color: #D4AF37;       
            font-size: 1.1rem;
            z-index: 10;          /* Sit ON TOP of the input background */
            pointer-events: none; /* Clicks go through the icon to the input */
            width: 20px;          /* Fixed width for consistency */
            text-align: center;
        }

        /* 3. The Input Field (Push text to the Right) */
        .input-static, 
        .select-static {
            width: 100%;
            /* FORCE padding-left to 55px. 
               15px (left gap) + 20px (icon) + 20px (gap) = 55px 
            */
            padding: 14px 15px 14px 55px !important; 
            
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            color: #333;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box; /* Crucial: includes padding in width */
        }

        /* 4. Textarea Special Handling */
        .textarea-static {
            width: 100%;
            min-height: 150px;
            resize: vertical;
            padding: 15px 15px 15px 55px !important; /* Push text right */
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
        }

        /* Move icon to top-left for big textareas */
        .input-container.textarea-container i {
            top: 20px; 
            transform: none; 
        }

        /* 5. Focus State */
        .input-static:focus, 
        .select-static:focus, 
        .textarea-static:focus {
            border-color: #D4AF37;
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.1);
            outline: none;
        }

        /* 6. Layout Utils */
        .form-group { margin-bottom: 25px; }
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #800000;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        .form-row { display: flex; gap: 20px; flex-wrap: wrap; }
        .half-width { flex: 1; min-width: 250px; }

        /* --- Page Header Styling --- */
        .page-header {
            border-radius: 25px; /* Rounded Corners */
            overflow: hidden;    /* Keeps content inside the rounded corners */
            margin-bottom: 40px;
            /* Ensure the background is dark (Sebastinian Red) so white text pops */
            background: linear-gradient(135deg, #800000 0%, #500000 100%);
            box-shadow: 0 10px 30px rgba(128, 0, 0, 0.2);
            padding: 40px;
            text-align: center;
            color: #fff; /* Default text color white */
        }

        /* Target the text elements specifically */
        .header-content .headline {
            color: #ffffff !important; 
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin: 10px 0;
        }

        .header-content .description {
            color: rgba(255, 255, 255, 0.85) !important; /* Slightly transparent white for hierarchy */
            font-size: 1.1rem;
        }

        .header-content .sub-headline {
            color: #D4AF37 !important; /* Gold for the icon/label */
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.85rem;
            display: block;
            margin-bottom: 5px;
        }

        /* Keep the "Research" span gold */
        .headline .text-gold {
            color: #D4AF37 !important;
        }

    </style>
</head>
<body>
    
    <?php include("header.php"); ?>

    <canvas id="luxury-canvas"></canvas>

    <main class="upload-wrapper">
        <div class="container">
            
            <header class="page-header" data-tilt>
                <div class="header-content">
                    <span class="sub-headline"><i class="fas fa-scroll"></i> Academic Repository</span>
                    <h1 class="headline">Archive Your <span class="text-gold">Research</span></h1>
                    <p class="description">Upload your thesis to the Sebastinian digital library.</p>
                </div>
            </header>

            <div class="upload-grid">
                
                <aside class="upload-sidebar">
                    <div class="user-upload-card" data-tilt>
                        <div class="user-avatar-frame">
                             <?php 
                                $img_path = '../uploads/profile_images/' . $profile_image_name;
                                if (!file_exists($img_path)) {
                                    $img_path = '../uploads/profile_images/default.png';
                                }
                             ?>
                             <img src="<?php echo $img_path; ?>?v=<?php echo time(); ?>" alt="User">
                        </div>
                        <div class="user-details">
                            <span class="label">Uploader</span>
                            <h3><?php echo $full_name; ?></h3>
                            <span class="role-badge">Researcher</span>
                        </div>
                    </div>

                    <div class="thumbnail-preview-card glass-panel" id="thumbCard" style="display:none; text-align:center; margin-top:20px;">
                        <h4><i class="fas fa-magic"></i> Auto-Generated Cover</h4>
                        <div id="pdf-canvas-container" style="width: 100%; height: 200px; overflow: hidden; border-radius: 8px; margin-top: 10px; border: 1px solid rgba(212, 175, 55, 0.3);">
                            </div>
                        <p style="font-size: 0.8rem; color: #aaa; margin-top:5px;">Generated from Page 1</p>
                    </div>

                    <div class="guidelines-card glass-panel">
                        <h4><i class="fas fa-check-circle"></i> Requirements</h4>
                        <ul>
                            <li><strong>PDF Only:</strong> Max 10MB file size.</li>
                            <li><strong>Authors:</strong> List all contributors.</li>
                        </ul>
                    </div>
                </aside>

                <section class="upload-main glass-panel">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" id="generated_thumbnail" name="generated_thumbnail">

                        <div class="form-section">
                            <h3 class="section-title">1. Research Metadata</h3>
                            
                            <div class="form-group">
                                <label for="title" class="form-label">Research Title</label>
                                <div class="input-container">
                                    <i class="fas fa-heading"></i>
                                    <input type="text" id="title" name="title" class="input-static" required placeholder="Enter the complete title of your study" autocomplete="off">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description" class="form-label">Abstract</label>
                                <div class="input-container textarea-container">
                                    <i class="fas fa-align-left"></i>
                                    <textarea id="description" name="description" class="textarea-static" required placeholder="Paste your abstract here..."></textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="authors" class="form-label">Author(s)</label>
                                <div class="input-container">
                                    <i class="fas fa-users"></i>
                                    <input type="text" id="authors" name="authors" class="input-static" required placeholder="e.g. Dela Cruz, J., Santos, M." autocomplete="off">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group half-width">
                                    <label for="publication_date" class="form-label">Publication Date</label>
                                    <div class="input-container">
                                        <i class="fas fa-calendar-alt"></i>
                                        <input type="date" id="publication_date" name="publication_date" class="input-static" required>
                                    </div>
                                </div>

                                <div class="form-group half-width">
                                    <label for="research_type" class="form-label">Methodology</label>
                                    <div class="input-container">
                                        <i class="fas fa-microscope"></i>
                                        <select id="research_type" name="research_type" class="select-static" required>
                                            <option value="" disabled selected>Select Methodology</option>
                                            <option value="Quantitative">Quantitative Research</option>
                                            <option value="Qualitative">Qualitative Research</option>
                                            <option value="Mixed Methods">Mixed Methods</option>
                                            <option value="Experimental">Experimental</option>
                                            <option value="Descriptive">Descriptive</option>
                                            <option value="Case Study">Case Study</option>
                                            <option value="Action Research">Action Research</option>
                                            <option value="Review">Review / Meta-Analysis</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="department" class="form-label">Department / Strand</label>
                                <div class="input-container">
                                    <i class="fas fa-university"></i>
                                    <select id="department" name="department" class="select-static" required>
                                        <option value="" disabled selected>Select Course / Strand</option>
                                        
                                        <optgroup label="College - Business & Management">
                                            <option value="BS Accountancy">BS Accountancy & BSBA Accounting</option>
                                            <option value="BSBA Financial Management">BSBA Financial Management</option>
                                            <option value="BSBA Marketing Management">BSBA Marketing Management</option>
                                            <option value="BSBA HRDM">BSBA Human Resource Dev. Mgmt</option>
                                            <option value="BS Hospitality Management">BS Hospitality Management</option>
                                            <option value="BS Tourism Management">BS Tourism Management</option>
                                        </optgroup>

                                        <optgroup label="College - Engineering & IT">
                                            <option value="BS Information Technology">BS Information Technology</option>
                                            <option value="BS Computer Engineering">BS Computer Engineering</option>
                                            <option value="BS Industrial Engineering">BS Industrial Engineering</option>
                                            <option value="BS Electronics Engineering">BS Electronics Engineering</option>
                                        </optgroup>

                                        <optgroup label="College - Arts & Sciences">
                                            <option value="BS Psychology">BS Psychology</option>
                                            <option value="AB Communication">AB Communication</option>
                                            <option value="BS Criminology">BS Criminology</option>
                                            <option value="BS Nursing">BS Nursing</option>
                                        </optgroup>

                                        <optgroup label="Senior High School">
                                            <option value="SHS - STEM">STEM (Science, Tech, Eng, Math)</option>
                                            <option value="SHS - ABM">ABM (Accountancy, Business, Mgmt)</option>
                                            <option value="SHS - HUMSS">HUMSS (Humanities & Social Sci)</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3 class="section-title">2. Upload Manuscript</h3>
                            
                            <div class="dropzone-grid full-width"> 
                                <div class="dropzone" id="fileZone">
                                    <input type="file" id="project_file" name="project_file" required accept=".pdf">
                                    <div class="dz-content">
                                        <div class="dz-icon"><i class="fas fa-file-pdf"></i></div>
                                        <h5>Drag & Drop PDF</h5>
                                        <p>System will automatically scan Page 1 as cover.</p>
                                    </div>
                                    <div class="dz-preview" id="filePreview"></div>
                                    <div class="dz-success"><i class="fas fa-check"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-footer">
                            <div id="statusMessage" class="status-message"></div>
                            <button type="submit" class="btn-gold-3d" id="submitBtn">
                                <span class="btn-text">Archive Research</span>
                                <span class="btn-icon"><i class="fas fa-upload"></i></span>
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
                    <div class="blade"></div><div class="blade"></div><div class="blade"></div>
                </div>
                <h3>Archiving...</h3>
            </div>
            <div class="modal-content success-state" style="display:none;">
                <h3>Success!</h3>
                <p>Research archived.</p>
                <button class="btn-modal" onclick="window.location.href='profile.php'">View Profile</button>
            </div>
       </div>
    </div>

    <?php include("footer.php"); ?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>
    <script src="../assets/js/upload_projects.js"></script>
</body>
</html>