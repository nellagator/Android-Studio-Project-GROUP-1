<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "loandb");

if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Get user_id from query parameter
$user_id = $_GET['user_id'] ?? '';

if (empty($user_id)) {
    echo json_encode(["error" => "User ID is required"]);
    exit();
}

// Filter by student_id (assuming student_id matches the user_id passed from app)
$sql = "SELECT 
    id,
    student_id,
    name,
    student_number,
    installment_num,
    amount_requested,
    partial_payment,
    repayment_date,
    or_number,
    status,
    applied_at,
    endorsed_at,
    approved_at,
    rejected_at
FROM loan_applications 
WHERE student_id = ?
ORDER BY applied_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$loans = [];

while ($row = $result->fetch_assoc()) {
    $loans[] = $row;
}

// Return in format expected by Kotlin: {"loans": [...]}
echo json_encode(["loans" => $loans]);

$stmt->close();
$conn->close();
?>