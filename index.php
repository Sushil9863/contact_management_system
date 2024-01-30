<!DOCTYPE html>
<html>
<head>
    <title>Contact List</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.3/css/all.min.css">

</head>
<body>

<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header('Location: login.php');
    exit;
}

include_once 'header.php';
?>

<div class="container">
    <h1>Contact List</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Nickname</th>
                <th>Phone Number</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            include_once 'db.php';

            // Get the user's user_id from the session
            $user_id = $_SESSION['user_id'];

            // Fetch contact data for the current user
            $stmt = $db->prepare('SELECT * FROM contacts WHERE user_id = ?');
            $stmt->execute([$user_id]);

            while ($row = $stmt->fetch()) {
                echo '<tr>';
                echo '<td>' . $row['nickname'] . '</td>';
                echo '<td>' . $row['phone_number'] . '</td>';
                echo '<td>
                        <a href="personal_info.php?id=' . $row['id'] . '" class="btn btn-info btn-sm">View Personal Info</a>
                        <a href="edit_contact.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm">Edit</a>
                        <a href="delete_contact.php?id=' . $row['id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this contact?\')">Delete</a>
                    </td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
    <a href="create_contact.php" class="btn btn-primary ml-2">Create New Contact</a>
</div>
<?php
include_once 'footer.php';
?>

