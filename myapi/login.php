<?php
header("Content-Type: application/json");
include 'db.php';

// Make sure POST parameters exist
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit();
}

$username = $_POST['username'];
$password = $_POST['password'];

// Fetch user + student info
$sql = "SELECT users.id, users.username, users.password, users.role, students.full_name
        FROM users
        LEFT JOIN students ON users.id = students.user_id
        WHERE users.username = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Plain-text password check (back to basic verification)
    if ($password === $row['password']) {

        echo json_encode([
            "status" => "success",
            "id" => $row['id'],                // pass user_id
            "username" => $row['username'],
            "role" => $row['role'],
            "full_name" => $row['full_name']
        ]);

    } else {
        echo json_encode(["status" => "error", "message" => "Incorrect password"]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "User not found"]);
}

$stmt->close();
$conn->close();
?>