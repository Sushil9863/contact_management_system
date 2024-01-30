<!DOCTYPE html>
<html>
<head>
    <title>Edit Contact</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="code.js"></script>
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission to update contact information in the database
    $contact_id = $_POST['contact_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $nickname = $_POST['nickname'];
    $phone_number = $_POST['phone_number'];

    $stmt = $db->prepare('UPDATE contacts SET full_name = ?, email = ?, address = ?, nickname = ?, phone_number = ? WHERE id = ?');
    $stmt->execute([$full_name, $email, $address, $nickname, $phone_number, $contact_id]);
    header('Location: index.php');
    exit;
}
?>

<div class="container">
    <h1>Edit Contact</h1>
    <form action="edit_contact.php" method="POST">
        <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
        <div class="form-group">
            <label for="full_name">Full Name:</label>
            <input type="text" class="form-control" name="full_name" id="full_name" value="<?php echo $contact['full_name']; ?>" required>
            <span id="full_name_error" class="text-danger error"></span>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" name="email" id="email" value="<?php echo $contact['email']; ?>" required>
            <span id="email_error" class="text-danger error"></span>
        </div>
        <div class="form-group">
            <label for="address">Address:</label>
            <input type="text" class="form-control" name="address" id="address" value="<?php echo $contact['address']; ?>" required>
            <span id="address_error" class="text-danger error"></span>
        </div>
        <div class="form-group">
            <label for="nickname">Nickname:</label>
            <input type="text" class="form-control" name="nickname" id="nickname" value="<?php echo $contact['nickname']; ?>" required>
            <span id="nickname_error" class="text-danger error"></span>
        </div>
        <div class="form-group">
            <label for="phone_number">Phone Number:</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo $contact['phone_number']; ?>" required>
            <span id="phone_number_error" class="text-danger error"></span>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="index.php" class="btn btn-secondary ml-2">Back to Dashboard</a>
    </form>
</div>

<?php
include_once 'footer.php';
?>

</body>
</html>
