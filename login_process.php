<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->prepare('SELECT user_id, password FROM user_detail WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        header('Location: index.php');
        exit;
    } else {
        echo 'Invalid username or password. Please try again.';
        header('Location: login.php');
    }
}
?>
