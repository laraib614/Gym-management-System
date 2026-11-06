<?php
// ============================================================
//  /api/check_session.php — Validates admin session
//  Called by every protected page via fetch()
// ============================================================
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (
    isset($_SESSION['logged_in']) &&
    $_SESSION['logged_in'] === true &&
    isset($_SESSION['admin_id'])
) {
    echo json_encode([
        'success'  => true,
        'username' => $_SESSION['admin_username'] ?? 'Admin',
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error'   => 'Not authenticated.',
    ]);
}
?>