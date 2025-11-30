<?php
require_once "config/db.php";

$db = new Database();
$conn = $db->connect();

echo $conn->connect_error ? "FAIL" : "OK";
