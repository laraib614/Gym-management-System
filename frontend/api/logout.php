<?php
// ============================================================
//  /api/logout.php — Destroys admin session
// ============================================================
session_start();
session_unset();
session_destroy();

// Redirect to login page
header('Location: ../login.html');
exit();
?>