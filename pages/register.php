<?php
session_start();

// Redirect already logged-in users to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once("../api/config/db.php");
include("header.php"); // Include your header
?>

<main class="register-container">
    <section class="register-box">
        <h1>Create Your Account</h1>
        <p>Join Sebastinian Showcase and share your amazing projects!</p>

        <form id="register-form" method="POST" action="../api/auth/register.php">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
            </div>

            <button type="submit" class="register-btn">Register</button>

            <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>

            <div id="register-message" class="register-message"></div>
        </form>
    </section>
</main>

<?php include("footer.php"); ?>
