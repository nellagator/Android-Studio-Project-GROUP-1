<?php
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "error" => "POST required"]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "loandb");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "DB failed"]);
    exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$period1 = isset($_POST['period1']) ? floatval($_POST['period1']) : null;
$period2 = isset($_POST['period2']) ? floatval($_POST['period2']) : null;

if ($id <= 0 || $period1 === null || $period2 === null) {
    echo json_encode(["success" => false, "error" => "Invalid input"]);
    exit();
}

// Use correct column names: installment1 and installment2
$stmt = $conn->prepare("UPDATE students SET installment1 = ?, installment2 = ? WHERE id = ?");
$stmt->bind_param("ddi", $period1, $period2, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>