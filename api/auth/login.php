<?php
require_once("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = (new Database())->connect();

    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            echo json_encode(['status'=>'success','message'=>'Login successful']);
        } else {
            echo json_encode(['status'=>'error','message'=>'Invalid password']);
        }
    } else {
        echo json_encode(['status'=>'error','message'=>'User not found']);
    }
}
?>
