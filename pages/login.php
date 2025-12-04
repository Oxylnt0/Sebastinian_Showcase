<?php
session_start();

// Redirect already logged-in users to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once("../api/config/db.php");
include("header.php"); // Include header if needed

?>

<main class="login-container">
    <section class="login-box">
        <h1>Login to Sebastinian Showcase</h1>
        <p>Enter your credentials to access your account</p>

        <form id="login-form" method="POST" action="../api/auth/login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="login-btn">Login</button>
            <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>

            <div id="login-message" class="login-message"></div>
        </form>
    </section>
</main>

<?php include("footer.php"); ?>
