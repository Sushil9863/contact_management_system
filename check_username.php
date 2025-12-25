<?php
require 'db.php';

if (isset($_GET['username'])) {
    $username = $_GET['username'];
    
    // Check if username exists in database
    $stmt = $db->prepare('SELECT username FROM user_detail WHERE username = ?');
    $stmt->execute([$username]);
    $existingUsername = $stmt->fetch();
    
    if ($existingUsername) {
        echo 'taken';
    } else {
        echo 'available';
    }
}
?>