<?php
require "db.php";
header("Content-Type: application/json");
error_reporting(0);

if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing user_id"
    ]);
    exit();
}

$user_id = $_GET['user_id'];

$stmt = $conn->prepare("SELECT full_name, installment1, installment2, installment3 FROM students WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "full_name" => $row["full_name"],
        "installment1" => $row["installment1"],
        "installment2" => $row["installment2"],
        "installment3" => $row["installment3"]
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No record found"
    ]);
}

$stmt->close();
$conn->close();
?>