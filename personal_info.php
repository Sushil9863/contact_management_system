<!DOCTYPE html>
<html>
<head>
    <title>Personal Information</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php
include_once 'header.php';
include_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $contact_id = $_GET['id'];
    $stmt = $db->prepare('SELECT * FROM contacts WHERE id = ?');
    $stmt->execute([$contact_id]);
    $contact = $stmt->fetch();
}

if ($contact) {
    echo '<div class="container">';
    echo '<h1>Personal Information</h1>';
    echo '<table class="table">';
    echo '<tr><th>Full Name:</th><td>' . $contact['full_name'] . '</td></tr>';
    echo '<tr><th>Email:</th><td>' . $contact['email'] . '</td></tr>';
    echo '<tr><th>Address:</th><td>' . $contact['address'] . '</td></tr>';
    echo '<tr><th>Nickname:</th><td>' . $contact['nickname'] . '</td></tr>';
    echo '<tr><th>Phone Number:</th><td>' . $contact['phone_number'] . '</td></tr>';
    echo '</table>';

    echo '<div class="btn-group">';
echo '<a href="edit_contact.php?id=' . $contact_id . '" class="btn btn-primary mr-2 rounded">Edit</a>';
echo '<a href="delete_contact.php?id=' . $contact_id . '" class="btn btn-danger mr-2 rounded" onclick="return confirm(\'Are you sure you want to delete this contact?\')">Delete</a>';
echo '<a href="index.php" class="btn btn-secondary rounded">Back to Dashboard</a>';
echo '</div>';

    echo '</div>';
} else {
    echo '<div class="container">';
    echo '<h1>Contact not found</h1>';
    echo '<a href="index.php" class="btn btn-secondary">Back to Dashboard</a>';
    echo '</div>';
}

include_once 'footer.php';
?>

</body>
</html>
