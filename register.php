<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>User Registration</h1>
        <form action="register_process.php" method="POST" id="registrationForm">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" name="username" id="username" required>
                <span id="username_error" class="text-danger error"></span>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" name="password" id="password" required>
                <span id="password_error" class="text-danger error"></span>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#username").on("keyup", function() {
                var username = $(this).val();
                var errorSpan = $("#username_error");
                if (validateUsername(username)) {
                    errorSpan.text("");
                } else {
                    errorSpan.text("Invalid username. Please use only letters, numbers, and underscores.");
                }
            });

            $("#password").on("keyup", function() {
                var password = $(this).val();
                var errorSpan = $("#password_error");
                if (validatePassword(password)) {
                    errorSpan.text("");
                } else {
                    errorSpan.text("Invalid password. Password must be at least 6 characters long and include at least one uppercase letter, one lowercase letter, one digit, and one special character (@, $, !, %, *, ?, or &).");

                }
            });

            function validateUsername(username) {
                var usernameRegex = /^[a-zA-Z0-9_]+$/;
                return usernameRegex.test(username);
            }

            function validatePassword(password) {
    // Requires at least 6 characters, including at least one uppercase letter, one lowercase letter, one digit, and one special character
    var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/;
    return passwordRegex.test(password);
}
        });
    </script>
</body>
</html>
