<?php
// ============================================================
//  GYM MANAGEMENT SYSTEM — Database Configuration
//  XAMPP default settings — change password if you set one
// ============================================================

define('DB_HOST',     '127.0.0.1:3307');
define('DB_USER',     'root');
define('DB_PASSWORD', '');
define('DB_NAME',     'gym_management');

// Allow requests from the frontend (CORS)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Create connection
$conn = new mysqli("127.0.0.1", "root", "", "gym_management", 3307);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit();
}

$conn->set_charset('utf8mb4');

// ── Helper: send JSON response ────────────────────────────
function sendJSON($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}

// ── Helper: get request body as associative array ─────────
function getBody() {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}
?>