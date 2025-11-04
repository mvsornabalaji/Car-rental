<?php
session_start();

// Check user type before destroying session
$is_admin = isset($_SESSION['admin_id']);
$is_user = isset($_SESSION['user_id']);

// Destroy all session data
session_destroy();

// Determine redirect based on where the logout came from
$redirect_url = 'user/login.php'; // Default to user login

if ($is_admin) {
    $redirect_url = 'user/login.php'; // Admin also goes to unified login
} elseif ($is_user) {
    $redirect_url = 'user/login.php'; // User goes to unified login
}

// Redirect with success message
header('Location: ' . $redirect_url . '?logout=success');
exit();
?>