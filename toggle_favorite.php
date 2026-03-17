<?php
session_start();
header('Content-Type: application/json');

// Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

require_once 'db.php';

$user_id = $_SESSION['user_id'];
$contact_id = isset($_POST['contact_id']) ? (int)$_POST['contact_id'] : 0;

if (!$contact_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid contact ID']);
    exit;
}

// Verify the contact belongs to the logged-in user
$stmt = $db->prepare("SELECT id, is_favorite FROM contacts WHERE id = ? AND user_id = ?");
$stmt->execute([$contact_id, $user_id]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contact) {
    echo json_encode(['success' => false, 'message' => 'Contact not found or access denied']);
    exit;
}

// Toggle favorite status
$new_favorite = $contact['is_favorite'] ? 0 : 1;
$update = $db->prepare("UPDATE contacts SET is_favorite = ? WHERE id = ?");
$update->execute([$new_favorite, $contact_id]);

echo json_encode([
    'success' => true,
    'new_favorite' => $new_favorite
]);