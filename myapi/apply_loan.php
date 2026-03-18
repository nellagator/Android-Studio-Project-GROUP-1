<?php
header('Content-Type: application/json');
error_reporting(0);

$conn = new mysqli("localhost", "root", "", "loandb");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// Get POST variables
$user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$student_number = isset($_POST['student_number']) ? trim($_POST['student_number']) : '';
$installment_num = isset($_POST['period']) && is_numeric($_POST['period']) ? intval($_POST['period']) : 0;
$amount_requested = isset($_POST['amount_requested']) && is_numeric($_POST['amount_requested']) ? floatval($_POST['amount_requested']) : 0;
$partial_payment = isset($_POST['partial_payment']) && is_numeric($_POST['partial_payment']) ? floatval($_POST['partial_payment']) : 0;
$repayment_date = isset($_POST['repayment_date']) ? trim($_POST['repayment_date']) : '';

// Validate fields
if (empty($user_id) || empty($name) || empty($student_number) || $installment_num === 0 || $amount_requested <= 0 || $partial_payment <= 0 || empty($repayment_date)) {
    echo json_encode(["success" => false, "message" => "Missing or invalid fields"]);
    exit();
}

// Lookup the actual student_id (primary key) from students table
$lookup = $conn->prepare("SELECT id FROM students WHERE user_id = ?");
$lookup->bind_param("s", $user_id);
$lookup->execute();
$res = $lookup->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Student not found"]);
    exit();
}

$student = $res->fetch_assoc();
$student_id = $student['id']; // This is what you insert into loan_applications

// Check if student already has a loan
$check = $conn->prepare("SELECT id FROM loan_applications WHERE student_id = ?");
$check->bind_param("i", $student_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "You already submitted a loan application."]);
    exit();
}

// Generate sequential OR number based on last entry
$result = $conn->query("SELECT or_number FROM loan_applications ORDER BY id DESC LIMIT 1");
if ($result && $result->num_rows > 0) {
    $lastRow = $result->fetch_assoc();
    $lastOr = $lastRow['or_number'];
    $lastNum = intval(preg_replace('/[^0-9]/', '', $lastOr));
    $or_number = "OR-" . str_pad($lastNum + 1, 3, "0", STR_PAD_LEFT);
} else {
    $or_number = "OR-001"; //start again
}

// Insert new loan with status 'pending' and endorsed_at = NULL
$stmt = $conn->prepare("INSERT INTO loan_applications
(student_id, name, student_number, installment_num, amount_requested, partial_payment, repayment_date, status, endorsed_at, or_number)
VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NULL, ?)");

$stmt->bind_param(
    "issiddss",
    $student_id,
    $name,
    $student_number,
    $installment_num,
    $amount_requested,
    $partial_payment,
    $repayment_date,
    $or_number
);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Loan application submitted successfully",
        "or_number" => $or_number
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to submit loan: " . $stmt->error]);
}

$conn->close();
?>