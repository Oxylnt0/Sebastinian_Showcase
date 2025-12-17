<?php
// api/auth/register.php

require_once("../config/db.php");
require_once("../utils/response.php");
require_once("../utils/validation.php");
require_once("../utils/mailer.php"); 

header("Content-Type: application/json");

// 1. Check Method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    Response::error("Invalid request method", 405);
}

// 2. Input Collection
$username     = trim($_POST["username"] ?? "");
$full_name    = trim($_POST["full_name"] ?? "");
$email        = trim($_POST["email"] ?? "");
$raw_password = $_POST["password"] ?? ""; 
$role         = "student"; 

// 3. Validation
Validation::requireField($username, "Username is required");
Validation::requireField($full_name, "Full name is required");
Validation::requireField($email, "Email is required");
Validation::requireField($raw_password, "Password is required");

// Validations
if (!Validation::isValidEmail($email)) Response::error("Invalid email address.");
if (substr($email, -9) !== '@sscr.edu') Response::error("Registration restricted to @sscr.edu accounts.");

// Password Complexity
if (strlen($raw_password) < 12) Response::error("Password must be at least 12 characters.");
if (!preg_match('/[A-Z]/', $raw_password)) Response::error("Password must contain an uppercase letter.");
if (!preg_match('/[\W_]/', $raw_password)) Response::error("Password must contain a special character.");

try {
    $conn = (new Database())->connect();

    // 4. PREPARE DATA
    $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);
    $otp_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $otp_expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));
    $is_verified = 0;

    // 5. ATTEMPT INSERT (The "Optimistic" Approach)
    // We try to insert directly. If it conflicts, the 'catch' block handles it.
    $insert = $conn->prepare("
        INSERT INTO users (username, password, full_name, email, role, otp_code, otp_expires_at, is_verified) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insert->bind_param("sssssssi", $username, $hashed_password, $full_name, $email, $role, $otp_code, $otp_expires, $is_verified);

    // Try to execute
    try {
        if ($insert->execute()) {
            // Success: New user created
            sendOtpAndResponse($email, $otp_code);
        }
    } catch (mysqli_sql_exception $e) {
        // 6. HANDLE DUPLICATES (Error Code 1062)
        if ($e->getCode() === 1062) {
            
            // Retrieve the user blocking us
            $stmt = $conn->prepare("SELECT user_id, username, is_verified FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();

            if ($existing) {
                // Scenario A: User is already verified -> BLOCK
                if ($existing['is_verified'] == 1) {
                    if ($existing['username'] === $username) {
                        Response::error("The username '$username' is already taken.");
                    } else {
                        Response::error("The email '$email' is already registered.");
                    }
                } 
                // Scenario B: User is NOT verified (Ghost Account) -> OVERWRITE
                else {
                    $update = $conn->prepare("
                        UPDATE users 
                        SET password = ?, full_name = ?, email = ?, otp_code = ?, otp_expires_at = ?, role = ?
                        WHERE user_id = ?
                    ");
                    $update->bind_param("ssssssi", $hashed_password, $full_name, $email, $otp_code, $otp_expires, $role, $existing['user_id']);
                    
                    if ($update->execute()) {
                        sendOtpAndResponse($email, $otp_code);
                    } else {
                        throw new Exception("Account recovery failed.");
                    }
                }
            }
        } else {
            // Some other SQL error
            throw $e; 
        }
    }

} catch (Exception $e) {
    Response::error("Server error: " . $e->getMessage());
}

// Helper
function sendOtpAndResponse($email, $otp_code) {
    if (Mailer::sendOTP($email, $otp_code)) {
        echo json_encode([
            "status" => "success",
            "action" => "verify_otp", 
            "email" => $email,
            "message" => "Verification code sent!"
        ]);
        exit;
    } else {
        Response::error("Account created/updated, but failed to send email.");
    }
}
?>