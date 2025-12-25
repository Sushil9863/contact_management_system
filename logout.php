<?php
session_start();

// Destroy the user's session to log them out
session_destroy();

// Redirect to the login page
header('Location: login.php');
exit;
?>
