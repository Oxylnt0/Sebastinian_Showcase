<?php
// pages/profile.php
session_start();
require_once("../api/config/db.php");
require_once("../api/utils/response.php");

// Redirect if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = (new Database())->connect();
$user_id = $_SESSION['user_id'];

// Retrieve profile information
$stmt = $conn->prepare("
    SELECT username, full_name, email, profile_image 
    FROM users 
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("User not found.");
}

$user = $result->fetch_assoc();

// --- IMAGE LOGIC FIX ---
// 1. Define the directory path relative to this file
$uploadDir = "../uploads/profile_images/";
$imageFromDb = $user["profile_image"];

// 2. Check if DB has value AND if file physically exists
if (!empty($imageFromDb) && file_exists($uploadDir . $imageFromDb)) {
    $profileImage = $uploadDir . htmlspecialchars($imageFromDb);
} else {
    // 3. Fallback to default if empty or missing
    $profileImage = $uploadDir . "default.png";
}
?>

<?php include 'header.php'; ?>

<div class="profile-wrapper">
    <div class="profile-header">
        <h1 class="profile-title">My Profile</h1>
    </div>

    <div class="profile-card">
        <div class="profile-photo-section">
            <div class="photo-frame">
                <img id="profileImagePreview" 
                     src="<?php echo $profileImage; ?>" 
                     alt="Profile Image"
                     onerror="this.onerror=null;this.src='../uploads/profile_images/default.png';">
            </div>

            <label class="upload-btn" for="profileImage">Change Photo</label>
            <input type="file" id="profileImage" accept="image/*">
            <small class="photo-guidelines">JPG, PNG — Max 3MB</small>
        </div>

        <div class="profile-info-section">
            <form id="profileForm" autocomplete="off">
                <div class="form-group">
                    <label for="fullName">Full Name</label>
                    <input 
                        type="text" 
                        id="fullName" 
                        name="full_name" 
                        value="<?php echo htmlspecialchars($user['full_name']); ?>" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($user['email']); ?>" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        value="<?php echo htmlspecialchars($user['username']); ?>" 
                        disabled
                    >
                </div>

                <button class="save-btn" type="submit">Save Changes</button>
            </form>

            <div id="responseMessage" class="response-message"></div>
        </div>
    </div>
</div>

<script src="../assets/js/profile.js"></script>
<?php include 'footer.php'; ?>