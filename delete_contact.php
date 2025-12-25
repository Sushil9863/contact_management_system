<?php
include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $contact_id = $_GET['id'];
    $stmt = $db->prepare('DELETE FROM contacts WHERE id = ?');
    $stmt->execute([$contact_id]);
}

header('Location: index.php');
exit;
?>
