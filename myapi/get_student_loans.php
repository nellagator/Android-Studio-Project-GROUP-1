<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "loandb");
if ($conn->connect_error) {
    echo json_encode(["loans" => [], "error" => "Database connection failed"]);
    exit();
}

// Get user_id from query parameter
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

error_log("DEBUG: Requested user_id = $user_id");

if ($user_id <= 0) {
    echo json_encode(["loans" => [], "error" => "User ID is required"]);
    exit();
}

// Map user_id to student_id
$lookup = $conn->prepare("SELECT id FROM students WHERE user_id = ?");
$lookup->bind_param("i", $user_id);
$lookup->execute();
$res = $lookup->get_result();

error_log("DEBUG: Student lookup result rows = " . $res->num_rows);

if ($res->num_rows === 0) {
    echo json_encode(["loans" => [], "error" => "Student not found for user_id=$user_id"]);
    exit();
}

$student = $res->fetch_assoc();
$student_id = $student['id'];

error_log("DEBUG: user_id=$user_id mapped to student_id=$student_id");

// Get loans for this student
$sql = "SELECT applied_at, amount_requested, status, rejection_reason FROM loan_applications WHERE student_id = ? ORDER BY applied_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

error_log("DEBUG: Loans found = " . $result->num_rows);

$loans = [];
while ($row = $result->fetch_assoc()) {
    $loans[] = $row;
    error_log("DEBUG: Loan data = " . json_encode($row));
}

// Return JSON expected by Kotlin
echo json_encode(["loans" => $loans]);

$stmt->close();
$conn->close();
?>