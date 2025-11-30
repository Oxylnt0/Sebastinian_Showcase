<?php
require_once("../config/db.php");  // fixed path

$db = new Database();
$conn = $db->connect();

if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
} else {
    echo "Connected to DB successfully!";
}
