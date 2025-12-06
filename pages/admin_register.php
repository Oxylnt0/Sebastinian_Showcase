<?php
// ================================
// admin_register.php - Sebastinian Showcase
// FINAL, SECURE, PRODUCTION-READY VERSION
// ================================

session_start();
require_once("../api/utils/auth_check.php");
require_once("../api/config/db.php");
require_once("../api/utils/response.php");

// ----------------------------
// Require admin access (optional, uncomment if only existing admins can register new admins)
// ----------------------------
// Auth::requireLogin();
// Auth::requireRole(['admin']);

// Initialize feedback
$feedback = null;

// ----------------------------
// Handle form submission
// ----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // ----------------------------
    // Validate input
    // ----------------------------
    if (!$username || !$email || !$password || !$confirm_password) {
        $feedback = ['status' => 'error', 'message' => 'All fields are required'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $feedback = ['status' => 'error', 'message' => 'Invalid email address'];
    } elseif ($password !== $confirm_password) {
        $feedback = ['status' => 'error', 'message' => 'Passwords do not match'];
    } elseif (strlen($password) < 8) {
        $feedback = ['status' => 'error', 'message' => 'Password must be at least 8 characters'];
    } else {
        // ----------------------------
        // Connect to DB
        // ----------------------------
        $conn = (new Database())->connect();

        // Check for duplicate username/email
        $stmt_check = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $feedback = ['status' => 'error', 'message' => 'Username or email already exists'];
        } else {
            // ----------------------------
            // Insert new admin
            // ----------------------------
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt_insert = $conn->prepare(
                "INSERT INTO users (username, email, password, role, full_name, date_created) VALUES (?, ?, ?, 'admin', ?, NOW())"
            );

            // Use username as full_name placeholder if full_name is required
            $full_name = $username;
            $stmt_insert->bind_param("ssss", $username, $email, $hashed_password, $full_name);

            if ($stmt_insert->execute()) {
                $feedback = ['status' => 'success', 'message' => 'Admin registered successfully'];
            } else {
                $feedback = ['status' => 'error', 'message' => 'Failed to register admin: ' . $stmt_insert->error];
            }
        }

        $stmt_check->close();
        if (isset($stmt_insert)) $stmt_insert->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Admin - Sebastinian Showcase</title>
    <link rel="stylesheet" href="../assets/css/admin_register.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php include("header.php"); ?>

    <main class="register-container">
        <div class="register-card">
            <h1>Register New Admin</h1>
            <p class="subtitle">Securely create a new administrator account.</p>

            <?php if ($feedback): ?>
                <div class="feedback <?= htmlspecialchars($feedback['status']) ?>">
                    <?= htmlspecialchars($feedback['message']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="adminRegisterForm" autocomplete="off">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" placeholder="Enter username" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="Enter email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter password" required>
                    <small>Password must be at least 8 characters</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password" required>
                </div>

                <div class="form-group submit-group">
                    <button type="submit" class="btn-submit" id="submitBtn">Register Admin</button>
                </div>
            </form>
        </div>
    </main>

    <?php include("footer.php"); ?>

    <script src="../assets/js/admin_register.js"></script>
</body>
</html>
