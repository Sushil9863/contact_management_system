<?php
// friends_debug.php - Simple version to test
session_start();
echo "Session started<br>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";

if (!isset($_SESSION['user_id'])) {
    echo "Redirecting to login...<br>";
    header('Location: login.php');
    exit;
}

include_once 'db.php';
echo "Database included<br>";

$user_id = $_SESSION['user_id'];
echo "Current User ID: $user_id<br>";

// Test the database connection
try {
    // Test user_detail table
    $test_stmt = $db->prepare("SELECT user_id, username, email FROM user_detail WHERE user_id = ?");
    $test_stmt->execute([$user_id]);
    $user = $test_stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "User found: " . ($user ? 'YES' : 'NO') . "<br>";
    if ($user) {
        echo "Username: " . htmlspecialchars($user['username']) . "<br>";
        echo "Email: " . htmlspecialchars($user['email']) . "<br>";
    }
    
    // Test friends table
    $friends_test = $db->query("SHOW COLUMNS FROM friends");
    echo "Friends table columns: <br>";
    foreach ($friends_test->fetchAll() as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")<br>";
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "<br>";
    echo "Error Code: " . $e->getCode() . "<br>";
}

echo "<hr>Debug completed successfully";
?>