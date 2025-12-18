<?php
// ... (Your PHP session logic remains the same) ...
session_start();
if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }
require_once("../api/config/db.php");
$pageTitle = "Create Account | Sebastinian Showcase";
include("header.php"); 
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* CSS for Password Checklist */
    .password-checklist {
        display: none; /* Hidden by default */
        background: #fdfdfd;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        margin-top: 10px;
        margin-bottom: 20px;
        font-size: 0.85rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .password-checklist h4 {
        margin: 0 0 8px 0;
        font-size: 0.9rem;
        color: #333;
        font-weight: 600;
    }
    .password-checklist ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .password-checklist li {
        margin-bottom: 4px;
        color: #666;
        transition: color 0.3s ease;
    }
    .password-checklist li i {
        margin-right: 8px;
        font-size: 0.8rem;
    }
    .password-checklist li.valid {
        color: #27AE60; /* Green */
    }
    .password-checklist li.valid i::before {
        content: "\f00c"; /* FontAwesome Check */
    }
    .password-checklist li.invalid i::before {
        content: "\f00d"; /* FontAwesome X or Circle */
    }
    /* Simple shake animation */
    @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
</style>

<main class="register-container">
    <div class="register-wrapper">
        <section class="register-intro">
            <img src="../assets/img/sebastinian_showcase_logo.png" alt="Sebastinian Showcase Logo" class="intro-logo">
            <h1>Sebastinian Showcase</h1>
            <p>Become part of the Sebastinian research legacy. Archive your capstone study and share your academic insights with the community.</p>
        </section>

        <section class="register-form-box">
            <div class="form-header">
                <h2>Create Account</h2>
                <p class="subtitle">Enter your details below to get started</p>
            </div>

            <form id="registerForm" autocomplete="off" novalidate>
                
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user input-icon"></i>
                        <input type="text" id="full_name" name="full_name" placeholder="e.g. Juan Dela Cruz" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-at input-icon"></i>
                        <input type="text" id="username" name="username" placeholder="Choose a unique username" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address (@sscr.edu)</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email" placeholder="student@sscr.edu" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" placeholder="8-12 chars" required>
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group half">
                        <label for="confirm_password">Confirm</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat" required>
                            <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="passwordChecklist" class="password-checklist">
                    <h4>Password Requirements:</h4>
                    <ul>
                        <li id="rule-length" class="invalid"><i class="fa-solid fa-circle"></i> 12+ Characters</li>
                        <li id="rule-upper" class="invalid"><i class="fa-solid fa-circle"></i> One Uppercase Letter</li>
                        <li id="rule-special" class="invalid"><i class="fa-solid fa-circle"></i> One Special Character (!@#$...)</li>
                        <li id="rule-number" class="invalid"><i class="fa-solid fa-circle"></i> One Number</li>
                    </ul>
                </div>

                <button type="submit" class="btn-register" id="submitBtn">
                    <span class="btn-text">Sign Up</span>
                    <i class="fa-solid fa-arrow-right btn-icon"></i>
                </button>

                <div id="formFeedback" class="form-feedback" aria-live="polite"></div>

                <div class="form-footer">
                    <p>Already have an account? <a href="login.php" class="link-highlight">Log in here</a></p>
                </div>
            </form>
        </section>
    </div>
</main>

<script src="../assets/js/register.js"></script>
<?php include("footer.php"); ?>