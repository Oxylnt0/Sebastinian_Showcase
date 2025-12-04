<?php
require_once("../config/db.php");
require_once("../utils/response.php");

header("Content-Type: application/json");

try {
    $conn = (new Database())->connect();

    if (!$conn->connect_error) {
        json_success("Database connection successful");
    } else {
        json_error("Connection error: " . $conn->connect_error);
    }
} catch (Exception $e) {
    json_error("Unexpected error: " . $e->getMessage());
}
