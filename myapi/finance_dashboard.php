<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration - adjust these to match your config
$host = 'localhost';
$dbname = 'loandb';  // your database name
$username = 'root';   // your db username
$password = '';       // your db password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$response = [];

try {
    // Get statistics (same queries as your web dashboard)
    
    // Total loan requests
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM loan_applications");
    $response['total_loan_requests'] = (int)$stmt->fetch()['count'];

    // Pending approvals (endorsed status for finance)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM loan_applications WHERE status = 'endorsed'");
    $response['pending_approvals'] = (int)$stmt->fetch()['count'];

    // Approved loans
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM loan_applications WHERE status = 'approved'");
    $response['approved_loans'] = (int)$stmt->fetch()['count'];

    // Rejected loans
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM loan_applications WHERE status = 'rejected'");
    $response['rejected_loans'] = (int)$stmt->fetch()['count'];

    // Total loaned (disbursed) - sum of amount_requested for approved or paid
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount_requested),0) as total FROM loan_applications WHERE status IN ('approved','paid')");
    $response['total_loaned'] = (float)$stmt->fetch()['total'];

    // Total paid back - sum for paid status
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount_requested),0) as total FROM loan_applications WHERE status = 'paid'");
    $response['total_paid_back'] = (float)$stmt->fetch()['total'];

    // Get pending applications list (endorsed status)
    $stmt = $pdo->query("
        SELECT 
            la.id,
            la.name,
            la.student_number,
            la.installment_num as period,
            la.amount_requested,
            la.partial_payment,
            la.repayment_date,
            la.or_number,
            la.status,
            la.applied_at as date
        FROM loan_applications la
        WHERE la.status = 'endorsed'
        ORDER BY la.applied_at DESC
    ");
    
    // FIXED: Moved this INSIDE the try block, right after the applications query
    $applications = $stmt->fetchAll();
    foreach ($applications as &$app) {
        $app['id'] = (int)$app['id']; // Cast ID to integer
        $app['period'] = (int)$app['period']; // Cast period too
    }
    unset($app); // Break reference
    $response['applications'] = $applications;

    $response['success'] = true;

} catch (Exception $e) {    
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

// REMOVED: The duplicate code that was here - now inside try block above

echo json_encode($response);
?>  