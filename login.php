<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login | Secure Access</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            --glass-bg: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            --accent-color: #ff9a3c;
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
            max-width: 450px;
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
            margin-bottom: 35px;
            position: relative;
        }
        
        .header-section::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: var(--accent-color);
            border-radius: 2px;
            box-shadow: 0 0 10px rgba(255, 154, 60, 0.5);
        }
        
        h1 {
            color: white;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 2.2rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .welcome-text {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
            text-align: center;
            margin-bottom: 5px;
        }
        
        .security-note {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            text-align: center;
            margin-bottom: 25px;
        }
        
        .security-note i {
            color: var(--accent-color);
            margin-right: 5px;
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
        
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            color: white;
            padding: 12px 15px 12px 45px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(255, 154, 60, 0.2);
            color: white;
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--accent-color) 0%, #ff6b6b 100%);
            border: none;
            border-radius: 10px;
            padding: 13px 30px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(255, 154, 60, 0.4);
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 154, 60, 0.5);
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .form-footer {
            text-align: center;
            margin-top: 25px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
        }
        
        .form-footer a {
            color: white;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 1px solid transparent;
            padding-bottom: 2px;
        }
        
        .form-footer a:hover {
            border-bottom: 1px solid white;
            color: #fff;
        }
        
        .forgot-password {
            text-align: right;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        
        .forgot-password a {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .forgot-password a:hover {
            color: var(--accent-color);
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
            width: 100px;
            height: 100px;
            top: 15%;
            left: 15%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 80px;
            height: 80px;
            top: 75%;
            right: 15%;
            animation-delay: -5s;
            animation-duration: 25s;
        }
        
        .shape:nth-child(3) {
            width: 70px;
            height: 70px;
            bottom: 30%;
            left: 25%;
            animation-delay: -10s;
            animation-duration: 15s;
        }
        
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-1000px) rotate(720deg); }
        }
        
        .login-options {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-login {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .social-login:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }
        
        .social-login.google:hover {
            color: #db4437;
            border-color: #db4437;
        }
        
        .social-login.github:hover {
            color: #333;
            border-color: #333;
        }
        
        .social-login.twitter:hover {
            color: #1da1f2;
            border-color: #1da1f2;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.3);
            margin: 0 10px;
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
            <h1>Welcome Back</h1>
            <p class="welcome-text">Sign in to access your account</p>
            <p class="security-note">
                <i class="fas fa-shield-alt"></i> Secure login with encrypted credentials
            </p>
        </div>
        
        <form action="login_process.php" method="POST" id="loginForm">
            <div class="form-group">
                <label class="form-label" for="username">
                    <i class="fas fa-user-circle"></i> Username
                </label>
                <div class="input-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" class="form-control" name="username" id="username" placeholder="Enter your username" required autofocus>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="input-icon">
                    <i class="fas fa-key"></i>
                    <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password" required>
                </div>
                <div class="forgot-password">
                    <a href="#" onclick="return false;">Forgot password?</a>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
            
            
        </form>
        
        <div class="form-footer">
            Don't have an account? <a href="register.php">Create Account</a>
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
            
            // Form validation and effects
            $('#username, #password').on('focus', function() {
                $(this).css({
                    'border-color': 'rgba(255, 154, 60, 0.6)',
                    'background': 'rgba(255, 255, 255, 0.2)'
                });
                $(this).parent().find('i').css('color', '#ff9a3c');
            });
            
            $('#username, #password').on('blur', function() {
                if (!$(this).val()) {
                    $(this).css({
                        'border-color': 'rgba(255, 255, 255, 0.3)',
                        'background': 'rgba(255, 255, 255, 0.1)'
                    });
                    $(this).parent().find('i').css('color', 'rgba(255, 255, 255, 0.7)');
                }
            });
            
            // Form submission animation
            $('#loginForm').on('submit', function(e) {
                const btn = $(this).find('button[type="submit"]');
                const originalText = btn.html();
                
                // Add loading animation
                btn.html('<i class="fas fa-spinner fa-spin"></i> Signing In...');
                btn.css('opacity', '0.8');
                btn.prop('disabled', true);
                
                // Simulate loading for demo (remove in production)
                setTimeout(() => {
                    btn.html(originalText);
                    btn.css('opacity', '1');
                    btn.prop('disabled', false);
                }, 2000);
            });
            
            // Social login hover effects
            $('.social-login').on('mouseenter', function() {
                $(this).css('transform', 'translateY(-3px)');
            }).on('mouseleave', function() {
                $(this).css('transform', 'translateY(0)');
            });
            
            // Social login click handlers (demo only)
            $('.social-login').on('click', function() {
                const provider = $(this).hasClass('google') ? 'Google' : 
                                $(this).hasClass('github') ? 'GitHub' : 'Twitter';
                
                const btn = $(this);
                const originalHTML = btn.html();
                
                btn.html('<i class="fas fa-spinner fa-spin"></i>');
                btn.css('transform', 'scale(0.9)');
                
                setTimeout(() => {
                    alert(`${provider} login would be implemented here in a real application.`);
                    btn.html(originalHTML);
                    btn.css('transform', 'scale(1)');
                }, 1000);
            });
            
            // Forgot password demo
            $('.forgot-password a').on('click', function() {
                alert('Password reset feature would be implemented here.\nYou would typically enter your email to receive reset instructions.');
                return false;
            });
        });
    </script>
</body>
</html>