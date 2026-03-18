<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "loandb");

if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]));
}

$fullName = $_POST['fullName'] ?? '';
$studentNumber = $_POST['studentNumber'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($fullName == "" || $studentNumber == "" || $email == "" || $password == "") {
    echo json_encode([
        "success" => false,
        "message" => "Missing fields"
    ]);
    exit();
}

/* Insert into users table */
$stmtUser = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");

$role = "student";
$username = $studentNumber; // student number becomes the username

$stmtUser->bind_param("ssss", $username, $email, $password, $role);
$stmtUser->execute();

/* Get the generated user id */
$user_id = $stmtUser->insert_id;

/* Insert into students table */
$stmtStudent = $conn->prepare("INSERT INTO students (user_id, full_name, student_number) VALUES (?, ?, ?)");

$stmtStudent->bind_param("iss", $user_id, $fullName, $studentNumber);
$stmtStudent->execute();

echo json_encode([
    "success" => true,
    "message" => "Student added successfully"
]);

$stmtUser->close();
$stmtStudent->close();
$conn->close();
?>