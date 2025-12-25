<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration | Elegant Sign Up</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            --glass-bg: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        }
        
        body {
            background: var(--primary-gradient);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.05)"/></svg>');
            background-size: cover;
        }
        
        .container-glass {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            padding: 40px;
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 1;
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .header-section {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        .header-section::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 2px;
        }
        
        h1 {
            color: white;
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 2.2rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            color: white;
            font-weight: 500;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
        }
        
        .form-label i {
            margin-right: 8px;
            opacity: 0.9;
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            color: white;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
            z-index: 2;
        }
        
        .input-icon input {
            padding-left: 45px;
        }
        
        .error {
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
            padding: 5px 10px;
            border-radius: 6px;
            background: rgba(255, 86, 86, 0.15);
            border-left: 3px solid #ff5656;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(37, 117, 252, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 117, 252, 0.5);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .login-link {
            text-align: center;
            margin-top: 25px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
        }
        
        .login-link a {
            color: white;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 1px solid transparent;
        }
        
        .login-link a:hover {
            border-bottom: 1px solid white;
            color: #fff;
        }
        
        .password-requirements {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin-top: 5px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.85rem;
        }
        
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .password-requirements li {
            margin-bottom: 5px;
        }
        
        .password-requirements li.valid {
            color: #4cd964;
        }
        
        .password-requirements li.valid::before {
            content: '✓ ';
        }
        
        @media (max-width: 576px) {
            .container-glass {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
        }
        
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
            overflow: hidden;
        }
        
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }
        
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 70%;
            right: 10%;
            animation-delay: -5s;
            animation-duration: 25s;
        }
        
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: -10s;
            animation-duration: 15s;
        }
        
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-1000px) rotate(720deg); }
        }
    </style>
</head>
<body>
    <!-- Floating background shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="container-glass">
        <div class="header-section">
            <h1>Create Account</h1>
            <p class="subtitle">Join our community and unlock exclusive features</p>
        </div>
        
        <form action="register_process.php" method="POST" id="registrationForm">
            <div class="form-group">
                <label class="form-label" for="username">
                    <i class="fas fa-user"></i> Username
                </label>
                <div class="input-icon">
                    <i class="fas fa-user-circle"></i>
                    <input type="text" class="form-control" name="username" id="username" placeholder="Enter your username" required>
                </div>
                <span id="username_error" class="text-danger error"></span>
                <small class="text-muted" style="color: rgba(255,255,255,0.6) !important; display: block; margin-top: 5px;">
                    Use only letters, numbers, and underscores
                </small>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="input-icon">
                    <i class="fas fa-key"></i>
                    <input type="password" class="form-control" name="password" id="password" placeholder="Create a strong password" required>
                </div>
                <span id="password_error" class="text-danger error"></span>
                
                <div class="password-requirements">
                    <p style="margin-bottom: 8px; font-weight: 500;">Password must contain:</p>
                    <ul>
                        <li id="req-length">At least 6 characters</li>
                        <li id="req-uppercase">One uppercase letter</li>
                        <li id="req-lowercase">One lowercase letter</li>
                        <li id="req-digit">One digit</li>
                        <li id="req-special">One special character (@$!%*?&)</li>
                    </ul>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Floating shapes animation
            for (let i = 0; i < 5; i++) {
                $('.floating-shapes').append(`
                    <div class="shape" style="
                        width: ${Math.random() * 60 + 20}px;
                        height: ${Math.random() * 60 + 20}px;
                        top: ${Math.random() * 100}%;
                        left: ${Math.random() * 100}%;
                        animation-delay: ${Math.random() * -20}s;
                        animation-duration: ${Math.random() * 30 + 15}s;
                    "></div>
                `);
            }
            
            $("#username").on("keyup", function() {
                var username = $(this).val();
                var errorSpan = $("#username_error");
                if (validateUsername(username)) {
                    errorSpan.text("");
                    $(this).css('border-color', 'rgba(76, 217, 100, 0.5)');
                } else {
                    errorSpan.text("Invalid username. Please use only letters, numbers, and underscores.");
                    $(this).css('border-color', 'rgba(255, 86, 86, 0.5)');
                }
            });

            $("#password").on("keyup", function() {
                var password = $(this).val();
                var errorSpan = $("#password_error");
                
                // Update password requirements
                updatePasswordRequirements(password);
                
                if (validatePassword(password)) {
                    errorSpan.text("");
                    $(this).css('border-color', 'rgba(76, 217, 100, 0.5)');
                } else {
                    errorSpan.text("Password must meet all requirements listed below.");
                    $(this).css('border-color', 'rgba(255, 86, 86, 0.5)');
                }
            });

            function validateUsername(username) {
                var usernameRegex = /^[a-zA-Z0-9_]+$/;
                return usernameRegex.test(username);
            }

            function validatePassword(password) {
                var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/;
                return passwordRegex.test(password);
            }
            
            function updatePasswordRequirements(password) {
                // Check each requirement
                const hasLength = password.length >= 6;
                const hasUppercase = /[A-Z]/.test(password);
                const hasLowercase = /[a-z]/.test(password);
                const hasDigit = /\d/.test(password);
                const hasSpecial = /[@$!%*?&]/.test(password);
                
                // Update UI for each requirement
                updateRequirementUI('req-length', hasLength);
                updateRequirementUI('req-uppercase', hasUppercase);
                updateRequirementUI('req-lowercase', hasLowercase);
                updateRequirementUI('req-digit', hasDigit);
                updateRequirementUI('req-special', hasSpecial);
            }
            
            function updateRequirementUI(elementId, isValid) {
                const element = $(`#${elementId}`);
                if (isValid) {
                    element.addClass('valid');
                } else {
                    element.removeClass('valid');
                }
            }
            
            // Add focus effects
            $('.form-control').on('focus', function() {
                $(this).parent().parent().find('.form-label').css('color', '#fff');
            });
            
            $('.form-control').on('blur', function() {
                $(this).parent().parent().find('.form-label').css('color', 'rgba(255, 255, 255, 0.9)');
            });
        });
    </script>
</body>
</html>