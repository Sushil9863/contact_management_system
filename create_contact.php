<!DOCTYPE html>
<html>
<head>
    <title>Contact Management System</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="code.js"></script>
</head>
<body>
    <?php
    include_once 'header.php';
    ?>
    <div class="container">
        <h1>Contact Management System</h1>
        <form action="add_contact.php" method="POST" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" class="form-control" name="full_name" id="full_name" required>
                <span id="full_name_error" class="text-danger error"></span>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" name="email" id="email" required>
                <span id="email_error" class="text-danger error"></span>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" class="form-control" name="address" id="address" required>
                <span id="address_error" class="text-danger error"></span>
            </div>
            <div class="form-group">
                <label for="nickname">Nickname:</label>
                <input type="text" class="form-control" name="nickname" id="nickname" required>
                <span id="nickname_error" class="text-danger error"></span>
            </div>
            <div class="form-group">
                <label for="phone_number">Phone Number:</label>
                <input type="text" class="form-control" name="phone_number" id="phone_number" required>
                <span id="phone_number_error" class="text-danger error"></span>
            </div>
            <button type="submit" class="btn btn-primary">Add Contact</button>
            <a href="index.php" class="btn btn-secondary ml-2">Back to Dashboard</a>
        </form>
    </div>

    <script>
        
    </script>
</body>
<?php
include_once 'footer.php';
?>
</html>
