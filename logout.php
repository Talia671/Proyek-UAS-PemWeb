<?php
require_once 'config.php';

if (isLoggedIn()) {
    logActivity($_SESSION['user_id'], 'User logout');
    
    // Destroy session
    session_destroy();
    
    // Redirect to home with success message
    session_start();
    $_SESSION['success'] = 'Anda telah berhasil logout';
}

redirect('index.php');
?>