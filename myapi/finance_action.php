<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';
$dbname = 'loandb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Get parameters from either GET or POST
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '0';
$reason = $_GET['reason'] ?? $_POST['reason'] ?? '';
$appId = (int)$id;

// Debug output
error_log("Action: $action, ID: $id, Parsed ID: $appId, Reason: $reason");

if (empty($action) || $appId <= 0) {
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid parameters',
        'received_action' => $action,
        'received_id' => $id,
        'parsed_id' => $appId
    ]);
    exit;
}

if (!in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

try {
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE loan_applications SET status = 'approved', approved_at = NOW() WHERE id = ? AND status = 'endorsed'");
        $stmt->execute([$appId]);
    } else { // reject
        $stmt = $pdo->prepare("UPDATE loan_applications SET status = 'rejected', rejection_reason = ?, rejected_at = NOW() WHERE id = ? AND status = 'endorsed'");
        $stmt->execute([$reason, $appId]);
    }
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => "Loan $action" . "d successfully"]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Application not found, not endorsed, or already processed']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>