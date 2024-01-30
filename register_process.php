<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if the username is already in use
    $stmt = $db->prepare('SELECT username FROM user_detail WHERE username = ?');
    $stmt->execute([$username]);
    $existingUsername = $stmt->fetch();

    if ($existingUsername) {
        // redirect back to register
        header("Refresh: 2; URL=register.php");
        echo "Username is already in use.";
        echo "<h3> Redirecting in 2 sec </h3>";


    } else {
        // Insert the new user into the database
        $stmt = $db->prepare('INSERT INTO user_detail (username, password) VALUES (?, ?)');
        $stmt->execute([$username, $password]);

        header('Location: login.php');
        exit;
    }
}
?>