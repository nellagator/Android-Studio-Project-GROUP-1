<?php
header('Content-Type: application/json');

// --- Database connection ---
$conn = new mysqli("localhost", "root", "", "loandb");

if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// --- SQL query ---
// LEFT JOIN so students still return even if user email missing
$sql = "SELECT 
            s.id,
            s.full_name,
            s.student_number,
            u.email
        FROM students s
        LEFT JOIN users u ON s.user_id = u.id";

$result = $conn->query($sql);

$students = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = [
            "id" => $row["id"],
            "fullName" => $row["full_name"],
            "studentNumber" => $row["student_number"],
            "email" => $row["email"] ?? ""
        ];
    }
}

echo json_encode($students);

$conn->close();
?>