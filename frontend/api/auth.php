<?php
// ============================================================
//  /api/auth.php — Admin Login Handler
//  Uses PHP sessions + password_verify()
// ============================================================
require_once 'config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON(['success' => false, 'error' => 'Method not allowed.'], 405);
}

$body    = getBody();
$username = trim($body['username'] ?? '');
$password = trim($body['password'] ?? '');

// Validate input
if (!$username || !$password) {
    sendJSON(['success' => false, 'error' => 'Username and password are required.'], 400);
}

// Sanitize
$username = $conn->real_escape_string($username);

// Fetch admin from DB
$r = $conn->query("SELECT * FROM admin WHERE username = '$username' LIMIT 1");

if (!$r || $r->num_rows === 0) {
    sendJSON(['success' => false, 'error' => 'Invalid username or password.'], 401);
}

$admin = $r->fetch_assoc();

// Verify password
if (!password_verify($password, $admin['password'])) {
    sendJSON(['success' => false, 'error' => 'Invalid username or password.'], 401);
}

// Set session
$_SESSION['admin_id']       = $admin['admin_id'];
$_SESSION['admin_username'] = $admin['username'];
$_SESSION['logged_in']      = true;

sendJSON([
    'success'  => true,
    'message'  => 'Login successful.',
    'username' => $admin['username'],
]);

$conn->close();
?>