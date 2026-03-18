<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "loandb");

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed"]));
}

$user_id = $_GET['user_id'];

$sql = "SELECT 
            s.id,
            s.full_name,
            s.student_number,
            u.email
        FROM students s
        JOIN users u ON s.user_id = u.id
        WHERE s.user_id = '$user_id'";

$result = $conn->query($sql);

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        "success" => true,
        "fullName" => $row["full_name"],
        "studentNumber" => $row["student_number"],
        "email" => $row["email"]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Student not found"
    ]);
}

$conn->close();
?>