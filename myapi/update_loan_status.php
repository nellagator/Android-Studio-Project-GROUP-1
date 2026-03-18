<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "loandb");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// Get POST parameters
$loan_id = isset($_POST['loan_id']) ? intval($_POST['loan_id']) : 0;
$status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : '';

if ($loan_id <= 0 || empty($status)) {
    echo json_encode(["success" => false, "message" => "Loan ID and status are required"]);
    exit();
}

// Update the loan status
$sql = "UPDATE loan_applications SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $loan_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Status updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "No record found or status unchanged"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Update failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>