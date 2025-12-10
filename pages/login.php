<?php
/**
 * pages/login.php
 * The Ultimate Login Experience
 * - Secure Session Handling
 * - Matching Aesthetic with Register Page
 * - Optimized Structure for Stability
 */

session_start();

// 1. Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once("../api/config/db.php");
$pageTitle = "Login | Sebastinian Showcase";
include("header.php"); 
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<main class="login-container">
    
    <div class="login-wrapper">
        
        <section class="login-intro">
            <img src="../assets/img/sebastinian_showcase_logo.png" alt="Sebastinian Showcase Logo" class="intro-logo">
            
            <h1>Welcome Back</h1>
            <p>Access your portfolio, manage your projects, and connect with the Sebastinian community.</p>
        </section>

        <section class="login-form-box">
            
            <div class="form-header">
                <h2>Sign In</h2>
                <p class="subtitle">Please enter your credentials</p>
            </div>

            <form id="loginForm" autocomplete="off" novalidate>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user input-icon"></i>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        
                        <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <span class="btn-text">Log In</span>
                    <i class="fa-solid fa-arrow-right btn-icon"></i>
                </button>

                <div id="formFeedback" class="form-feedback" aria-live="polite"></div>

                <div class="form-footer">
                    <p>New here? <a href="register.php" class="link-highlight">Create an account</a></p>
                </div>

            </form>
        </section>
    </div>
</main>

<script src="../assets/js/login.js"></script>

<?php include("footer.php"); ?>