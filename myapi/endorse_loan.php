<?php
header("Content-Type: application/json");
include "db.php";

if (!isset($_GET['id'])) {
    echo json_encode(["success"=>false,"message"=>"Missing loan ID"]);
    exit();
}

$id = $_GET['id'];

// Update loan status
$sql = "UPDATE loan_applications 
        SET status='endorsed', endorsed_at=NOW() 
        WHERE id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$id);

if ($stmt->execute()) {
    echo json_encode(["success"=>true]);
} else {
    echo json_encode(["success"=>false,"message"=>"Database update failed"]);
}

$stmt->close();
$conn->close();
?>