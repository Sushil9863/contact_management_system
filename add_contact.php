<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        // Redirect to the login page if not logged in
        header('Location: login.php');
        exit;
    }

    include_once 'db.php';

    // Get the user's user_id from the session
    $user_id = $_SESSION['user_id'];
   
    // Prepare the SQL statement to insert data into the contacts table, including user_id
    $stmt = $db->prepare("INSERT INTO contacts (full_name,email,address,nickname,phone_number,user_id) VALUES(?,?,?,?,?,?)");
    // Bind the form data and user_id to the SQL statement
    $stmt->execute([
        $_POST['full_name'],
        $_POST['email'],
        $_POST['address'],
        $_POST['nickname'],
        $_POST['phone_number'],
        $user_id
    ]);

    // Redirect to a success page or update the contact list
    header('Location: index.php');
    exit;
}
?>
