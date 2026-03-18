<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "loandb");

if ($conn->connect_error) {
    echo json_encode(["loans" => [], "error" => "Database connection failed"]);
    exit();
}

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
rejected_at,
rejection_reason
FROM loan_applications
ORDER BY applied_at DESC";

$result = $conn->query($sql);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Debug: show field names
error_log("Fields: " . implode(", ", array_keys($data[0] ?? [])));

echo json_encode(["loans" => $data]);

$conn->close();
?>