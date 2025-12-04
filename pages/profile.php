<?php
session_start();
require_once("../api/config/db.php");
require_once("../api/utils/response.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = (new Database())->connect();
$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT username, full_name, email, profile_image FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("User not found");
}

$user = $result->fetch_assoc();
?>

<?php include 'header.php'; ?>

<div class="profile-container">
    <h1>My Profile</h1>

    <div class="profile-card">
        <div class="profile-image-section">
            <img id="profileImagePreview" src="../uploads/profile_images/<?php echo htmlspecialchars($user['profile_image'] ?? 'default.png'); ?>" alt="Profile Image">
            <input type="file" id="profileImage" name="profile_image" accept="image/*">
        </div>

        <div class="profile-details">
            <form id="profileForm">
                <div class="form-group">
                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                </div>

                <button type="submit">Update Profile</button>
            </form>
        </div>
    </div>

    <div id="responseMessage" class="response-message"></div>
</div>

<?php include 'footer.php'; ?>
