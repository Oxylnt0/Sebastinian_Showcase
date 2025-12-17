<?php
require_once("api/config/db.php");

try {
    $conn = (new Database())->connect();
    
    // --- SETTINGS ---
    $username = 'Admin';
    $password = 'Admin123'; // PUT YOUR PASSWORD HERE
    $full_name = 'Sebastinian Admin';
    $email = 'admin_account@sscr.edu';
    $role = 'admin';
    $is_verified = 1;

    // Create the secure hash
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password, full_name, email, role, is_verified) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $username, $hashed_password, $full_name, $email, $role, $is_verified);

    if ($stmt->execute()) {
        echo "Admin account created successfully! Please delete this file immediately.";
    } else {
        echo "Error: " . $stmt->error;
    }
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>