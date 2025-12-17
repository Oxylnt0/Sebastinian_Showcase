<?php
// api/auth/verify_otp.php
require_once("../config/db.php");
require_once("../utils/response.php");

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") Response::error("Invalid method", 405);

$email = trim($_POST['email'] ?? '');
$otp   = trim($_POST['otp'] ?? '');

if (empty($email) || empty($otp)) Response::error("Missing parameters");

try {
    $conn = (new Database())->connect();

    // Check if OTP matches and user is unverified
    $stmt = $conn->prepare("SELECT user_id, otp_expires_at FROM users WHERE email = ? AND otp_code = ? AND is_verified = 0");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) Response::error("Invalid Code or Account already verified");

    $user = $result->fetch_assoc();

    // Check Expiration
    if (strtotime($user['otp_expires_at']) < time()) Response::error("Code has expired");

    // Activate Account
    $update = $conn->prepare("UPDATE users SET is_verified = 1, otp_code = NULL, otp_expires_at = NULL WHERE user_id = ?");
    $update->bind_param("i", $user['user_id']);
    
    if ($update->execute()) {
        Response::success([], "Verification successful!");
    } else {
        Response::error("Update failed");
    }

} catch (Exception $e) {
    Response::error("Error: " . $e->getMessage());
}
?>