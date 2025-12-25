<?php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => '', 'imported' => 0, 'skipped' => 0, 'errors' => 0, 'error_details' => []];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csvFile'])) {
    $response['message'] = 'No file uploaded';
    echo json_encode($response);
    exit;
}

// File validation
$allowed_types = ['text/csv', 'text/plain', 'application/vnd.ms-excel'];
$max_size = 5 * 1024 * 1024; // 5MB
$file = $_FILES['csvFile'];

// Check upload error
if ($file['error'] !== UPLOAD_ERR_OK) {
    $response['message'] = 'File upload failed';
    echo json_encode($response);
    exit;
}

// Check file type and size
$file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($file['type'], $allowed_types) && $file_ext !== 'csv') {
    $response['message'] = 'Only CSV files are allowed';
    echo json_encode($response);
    exit;
}

if ($file['size'] > $max_size) {
    $response['message'] = 'File size must be less than 5MB';
    echo json_encode($response);
    exit;
}

// Read CSV file
$csv_data = [];
if (($handle = fopen($file['tmp_name'], 'r')) !== FALSE) {
    // Get headers
    $headers = fgetcsv($handle);
    
    // Validate required headers
    $required_headers = ['full_name', 'email', 'phone_number'];
    foreach ($required_headers as $required) {
        if (!in_array($required, $headers)) {
            fclose($handle);
            $response['message'] = "Missing required column: $required";
            $response['error_details'][] = "Required columns: " . implode(', ', $required_headers);
            echo json_encode($response);
            exit;
        }
    }
    
    // Map headers to indices
    $header_indices = array_flip($headers);
    
    // Prepare statements
    $insert_sql = "INSERT INTO contacts (full_name, email, address, nickname, phone_number, user_id) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $db->prepare($insert_sql);
    
    $check_sql = "SELECT id FROM contacts WHERE email = ? AND user_id = ?";
    $check_stmt = $db->prepare($check_sql);
    
    // Process rows
    $skip_duplicates = isset($_POST['skip_duplicates']);
    $row_number = 1;
    
    while (($data = fgetcsv($handle)) !== FALSE) {
        $row_number++;
        
        // Skip empty rows
        if (empty(array_filter($data))) {
            continue;
        }
        
        // Extract data
        $full_name = $data[$header_indices['full_name']] ?? '';
        $email = $data[$header_indices['email']] ?? '';
        $phone = $data[$header_indices['phone_number']] ?? '';
        $address = $data[$header_indices['address']] ?? '';
        $nickname = $data[$header_indices['nickname']] ?? '';
        
        // Validate required fields
        if (empty($full_name) || empty($email) || empty($phone)) {
            $response['errors']++;
            $response['error_details'][] = "Row $row_number: Missing required fields";
            continue;
        }
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['errors']++;
            $response['error_details'][] = "Row $row_number: Invalid email format";
            continue;
        }
        
        // Check for duplicates
        if ($skip_duplicates) {
            $check_stmt->execute([$email, $user_id]);
            if ($check_stmt->fetch()) {
                $response['skipped']++;
                continue;
            }
        }
        
        // Insert contact
        try {
            $insert_stmt->execute([$full_name, $email, $address, $nickname, $phone, $user_id]);
            $response['imported']++;
        } catch (PDOException $e) {
            $response['errors']++;
            $response['error_details'][] = "Row $row_number: " . $e->getMessage();
        }
    }
    
    fclose($handle);
    
    // Success response
    $response['success'] = true;
    $response['message'] = 'Import completed successfully';
    
} else {
    $response['message'] = 'Cannot read CSV file';
}

echo json_encode($response);
exit;
?>