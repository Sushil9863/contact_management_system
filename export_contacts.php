<?php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get export parameters
$format = $_GET['format'] ?? 'csv';
$date_from = $_GET['date_from'] ?? null;
$date_to = $_GET['date_to'] ?? null;
$fields = $_GET['fields'] ?? ['full_name', 'email', 'phone_number', 'address', 'nickname'];

// Build query
$query = "SELECT full_name, email, address, nickname, phone_number FROM contacts WHERE user_id = ?";
$params = [$user_id];

// Add date filter if provided
if ($date_from && $date_to) {
    $query .= " AND DATE(created_at) BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
} elseif ($date_from) {
    $query .= " AND DATE(created_at) >= ?";
    $params[] = $date_from;
} elseif ($date_to) {
    $query .= " AND DATE(created_at) <= ?";
    $params[] = $date_to;
}

$stmt = $db->prepare($query);
$stmt->execute($params);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filter fields if specified
if (isset($_GET['fields']) && is_array($_GET['fields'])) {
    $selected_fields = $_GET['fields'];
    $filtered_contacts = [];
    foreach ($contacts as $contact) {
        $filtered_contact = [];
        foreach ($selected_fields as $field) {
            if (isset($contact[$field])) {
                $filtered_contact[$field] = $contact[$field];
            }
        }
        $filtered_contacts[] = $filtered_contact;
    }
    $contacts = $filtered_contacts;
}

// Export based on format
switch ($format) {
    case 'json':
        exportJSON($contacts);
        break;
    case 'excel':
        exportExcel($contacts);
        break;
    case 'csv':
    default:
        exportCSV($contacts);
        break;
}

function exportCSV($contacts) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=contacts_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write headers
    if (!empty($contacts)) {
        fputcsv($output, array_keys($contacts[0]));
    }
    
    // Write data
    foreach ($contacts as $contact) {
        fputcsv($output, $contact);
    }
    
    fclose($output);
    exit;
}

function exportExcel($contacts) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="contacts_' . date('Y-m-d') . '.xlsx"');
    
    // Simple Excel output using CSV with .xlsx extension
    // For more advanced Excel features, consider using a library like PhpSpreadsheet
    $output = fopen('php://output', 'w');
    
    // Write headers
    if (!empty($contacts)) {
        fputcsv($output, array_keys($contacts[0]));
    }
    
    // Write data
    foreach ($contacts as $contact) {
        fputcsv($output, $contact);
    }
    
    fclose($output);
    exit;
}

function exportJSON($contacts) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename=contacts_' . date('Y-m-d') . '.json');
    
    echo json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}
?>