<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=contact_import_template.csv');

// Add BOM for UTF-8
echo chr(0xEF) . chr(0xBB) . chr(0xBF);

// Headers
$headers = ['full_name', 'email', 'phone_number', 'address', 'nickname'];
echo implode(',', $headers) . "\n";

// Sample data
$samples = [
    ['John Doe', 'john@example.com', '1234567890', '123 Main St', 'Johnny'],
    ['Jane Smith', 'jane@example.com', '0987654321', '456 Oak Ave', 'Janey'],
    ['Bob Johnson', 'bob@example.com', '5551234567', '789 Pine Rd', 'Bobby']
];

// Output sample data
foreach ($samples as $sample) {
    // Escape fields that might contain commas
    $escaped_sample = array_map(function($field) {
        return '"' . str_replace('"', '""', $field) . '"';
    }, $sample);
    echo implode(',', $escaped_sample) . "\n";
}
exit;
?>