<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}
require_once 'db.php';
$user_id = $_SESSION['user_id'];

$action = $_POST['action'] ?? '';

// ---------- Get groups for a contact (used in old modal, keep for compatibility) ----------
if ($action === 'get_groups') {
    $contact_id = (int)($_POST['contact_id'] ?? 0);
    if (!$contact_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid contact']);
        exit;
    }
    // Verify contact belongs to user
    $check = $db->prepare("SELECT id FROM contacts WHERE id = ? AND user_id = ?");
    $check->execute([$contact_id, $user_id]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Contact not found']);
        exit;
    }
    $groupsStmt = $db->prepare("SELECT id, group_name FROM groups WHERE user_id = ? ORDER BY group_name");
    $groupsStmt->execute([$user_id]);
    $allGroups = $groupsStmt->fetchAll(PDO::FETCH_ASSOC);
    $selStmt = $db->prepare("SELECT group_id FROM contact_groups WHERE contact_id = ?");
    $selStmt->execute([$contact_id]);
    $selected = $selStmt->fetchAll(PDO::FETCH_COLUMN, 0);
    echo json_encode(['success' => true, 'groups' => $allGroups, 'selected' => $selected]);
    exit;
}

// ---------- Save groups for a contact (old) ----------
if ($action === 'save_groups') {
    $contact_id = (int)($_POST['contact_id'] ?? 0);
    $group_ids = isset($_POST['group_ids']) ? json_decode($_POST['group_ids']) : [];
    if (!$contact_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid contact']);
        exit;
    }
    $check = $db->prepare("SELECT id FROM contacts WHERE id = ? AND user_id = ?");
    $check->execute([$contact_id, $user_id]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Contact not found']);
        exit;
    }
    $db->beginTransaction();
    try {
        $del = $db->prepare("DELETE FROM contact_groups WHERE contact_id = ?");
        $del->execute([$contact_id]);
        if (!empty($group_ids)) {
            $ins = $db->prepare("INSERT INTO contact_groups (contact_id, group_id) VALUES (?, ?)");
            foreach ($group_ids as $gid) {
                $gid = (int)$gid;
                $checkGroup = $db->prepare("SELECT id FROM groups WHERE id = ? AND user_id = ?");
                $checkGroup->execute([$gid, $user_id]);
                if ($checkGroup->fetch()) {
                    $ins->execute([$contact_id, $gid]);
                }
            }
        }
        $db->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

// ---------- Create a new group ----------
if ($action === 'create_group') {
    $group_name = trim($_POST['group_name'] ?? '');
    if (empty($group_name)) {
        echo json_encode(['success' => false, 'message' => 'Group name cannot be empty']);
        exit;
    }
    $stmt = $db->prepare("INSERT INTO groups (user_id, group_name) VALUES (?, ?)");
    $stmt->execute([$user_id, $group_name]);
    $new_id = $db->lastInsertId();
    echo json_encode(['success' => true, 'group_id' => $new_id, 'group_name' => htmlspecialchars($group_name)]);
    exit;
}

// ---------- Get all groups with member counts and contacts ----------
if ($action === 'get_all_groups_contacts') {
    // Fetch all groups for user
    $groupsStmt = $db->prepare("SELECT * FROM groups WHERE user_id = ? ORDER BY group_name");
    $groupsStmt->execute([$user_id]);
    $groups = $groupsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all contacts
    $contactsStmt = $db->prepare("SELECT id, full_name, nickname FROM contacts WHERE user_id = ? ORDER BY nickname");
    $contactsStmt->execute([$user_id]);
    $contacts = $contactsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all contact-group associations
    $assocStmt = $db->prepare("SELECT contact_id, group_id FROM contact_groups WHERE group_id IN (SELECT id FROM groups WHERE user_id = ?)");
    $assocStmt->execute([$user_id]);
    $assocs = $assocStmt->fetchAll(PDO::FETCH_ASSOC);

    // Build group->contacts mapping
    $groupContacts = [];
    foreach ($assocs as $a) {
        $groupContacts[$a['group_id']][] = $a['contact_id'];
    }

    echo json_encode([
        'success' => true,
        'groups' => $groups,
        'contacts' => $contacts,
        'groupContacts' => $groupContacts
    ]);
    exit;
}

// ---------- Update group name ----------
if ($action === 'update_group_name') {
    $group_id = (int)($_POST['group_id'] ?? 0);
    $new_name = trim($_POST['new_name'] ?? '');
    if (!$group_id || empty($new_name)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }
    // Verify ownership
    $check = $db->prepare("SELECT id FROM groups WHERE id = ? AND user_id = ?");
    $check->execute([$group_id, $user_id]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Group not found']);
        exit;
    }
    $update = $db->prepare("UPDATE groups SET group_name = ? WHERE id = ?");
    $update->execute([$new_name, $group_id]);
    echo json_encode(['success' => true]);
    exit;
}

// ---------- Delete group ----------
if ($action === 'delete_group') {
    $group_id = (int)($_POST['group_id'] ?? 0);
    if (!$group_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid group']);
        exit;
    }
    // Verify ownership and delete (cascade will remove contact_groups)
    $del = $db->prepare("DELETE FROM groups WHERE id = ? AND user_id = ?");
    $del->execute([$group_id, $user_id]);
    if ($del->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Group not found']);
    }
    exit;
}

// ---------- Update group members (bulk replace) ----------
if ($action === 'update_group_members') {
    $group_id = (int)($_POST['group_id'] ?? 0);
    $contact_ids = isset($_POST['contact_ids']) ? json_decode($_POST['contact_ids']) : [];
    if (!$group_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid group']);
        exit;
    }
    // Verify group ownership
    $check = $db->prepare("SELECT id FROM groups WHERE id = ? AND user_id = ?");
    $check->execute([$group_id, $user_id]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Group not found']);
        exit;
    }
    $db->beginTransaction();
    try {
        // Delete existing members
        $del = $db->prepare("DELETE FROM contact_groups WHERE group_id = ?");
        $del->execute([$group_id]);
        // Insert new members (verify each contact belongs to user)
        if (!empty($contact_ids)) {
            $ins = $db->prepare("INSERT INTO contact_groups (contact_id, group_id) VALUES (?, ?)");
            foreach ($contact_ids as $cid) {
                $cid = (int)$cid;
                $checkContact = $db->prepare("SELECT id FROM contacts WHERE id = ? AND user_id = ?");
                $checkContact->execute([$cid, $user_id]);
                if ($checkContact->fetch()) {
                    $ins->execute([$cid, $group_id]);
                }
            }
        }
        $db->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);