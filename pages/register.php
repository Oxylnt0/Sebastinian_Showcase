<?php
/**
 * pages/register.php
 * Final Version
 * - Secure Session Handling
 * - Structure optimized for "Red & Gold" theme
 * - Clean HTML5 semantic markup
 */

session_start();

// 1. Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once("../api/config/db.php");
$pageTitle = "Create Account | Sebastinian Showcase";
include("header.php"); 
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<main class="register-container">
    
    <div class="register-wrapper">
        
        <section class="register-intro">
            <img src="../assets/img/sebastinian_showcase_logo.png" alt="Sebastinian Showcase Logo" class="intro-logo">
            
            <h1>Sebastinian Showcase</h1>
            <p>Join the hub of innovation. Share your research, projects, and creativity with the world.</p>
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
                    <label for="email">Email Address</label>
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
                            <input type="password" id="password" name="password" placeholder="6+ chars" required>
                            
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

                <div class="strength-meter-container">
                    <div class="strength-bar-bg">
                        <div id="strengthBar" class="strength-bar-fill"></div>
                    </div>
                    <small id="strengthText">Password Strength</small>
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