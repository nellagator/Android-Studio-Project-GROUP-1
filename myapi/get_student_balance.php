<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "loandb");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "db_connect", "students" => []]);
    exit;
}

// JOIN with users table to get email using user_id
$sql = "SELECT 
            s.id, 
            s.full_name as name, 
            s.student_number, 
            COALESCE(u.email, '-') as email,
            s.installment1 as period1_balance, 
            s.installment2 as period2_balance 
        FROM students s
        LEFT JOIN users u ON s.user_id = u.id";

$result = $conn->query($sql);

$students = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = [
            "id" => intval($row["id"]),
            "name" => $row["name"] ?? "",
            "student_number" => $row["student_number"] ?? "",
            "email" => $row["email"] ?? "-",
            "period1_balance" => floatval($row["period1_balance"] ?? 0),
            "period2_balance" => floatval($row["period2_balance"] ?? 0)
        ];
    }
    echo json_encode(["success" => true, "students" => $students]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error, "students" => []]);
}

$conn->close();
?>