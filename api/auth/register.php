<?php
require_once("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = (new Database())->connect();

    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $role = 'student';

    // Check if username or email exists
    $check = $conn->query("SELECT * FROM users WHERE username='$username' OR email='$email'");
    if ($check->num_rows > 0) {
        echo json_encode(['status'=>'error','message'=>'Username or email already exists']);
        exit;
    }

    $sql = "INSERT INTO users (username,password,full_name,email,role) 
            VALUES ('$username','$password','$full_name','$email','$role')";
    if ($conn->query($sql)) {
        echo json_encode(['status'=>'success','message'=>'User registered successfully']);
    } else {
        echo json_encode(['status'=>'error','message'=>$conn->error]);
    }
}
?>
