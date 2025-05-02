<?php
// Only start session if it hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'You need admin access to view this page.';
    header('Location: ../account.php');
    exit;
}

// Redirect to the new admin dashboard
header('Location: indexadmin.php');
exit;
?>