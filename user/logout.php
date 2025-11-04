<?php
session_start();

// Check user type before destroying session
$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'user';
$redirect_url = 'login.php'; // Default redirect

// Determine redirect based on user type
if (isset($_SESSION['admin_id'])) {
    $redirect_url = 'login.php'; // Redirect to unified login
} elseif (isset($_SESSION['user_id'])) {
    $redirect_url = 'login.php'; // Redirect to unified login
} else {
    $redirect_url = 'index.php'; // If no session, go to home
}

// Destroy all session data
session_destroy();

// Redirect with success message
header('Location: ' . $redirect_url . '?logout=success');
exit();
?>