<?php
require_once("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status'=>'error','message'=>'Not logged in']);
        exit;
    }

    $conn = (new Database())->connect();

    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $sdg_id = intval($_POST['sdg_id']);
    $user_id = $_SESSION['user_id'];

    // Handle file upload
    $file_name = null;
    if (!empty($_FILES['project_file']['name'])) {
        $file_name = time().'_'.basename($_FILES['project_file']['name']);
        move_uploaded_file($_FILES['project_file']['tmp_name'], "../../uploads/project_files/$file_name");
    }

    // Handle image upload
    $image_name = null;
    if (!empty($_FILES['project_image']['name'])) {
        $image_name = time().'_'.basename($_FILES['project_image']['name']);
        move_uploaded_file($_FILES['project_image']['tmp_name'], "../../uploads/project_images/$image_name");
    }

    $sql = "INSERT INTO projects (user_id,title,description,file,image,sdg_id) 
            VALUES ($user_id,'$title','$description','$file_name','$image_name',$sdg_id)";

    if ($conn->query($sql)) {
        echo json_encode(['status'=>'success','message'=>'Project uploaded successfully']);
    } else {
        echo json_encode(['status'=>'error','message'=>$conn->error]);
    }
}
?>
