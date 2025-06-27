<?php
require_once '../config/config.php';

// Destroy session
session_destroy();

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to homepage with success message
session_start();
flashMessage('success', 'Te-ai deconectat cu succes!');
redirect('../index.php');
?>
