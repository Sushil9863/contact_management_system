<?php
// ========================
// MUST BE AT TOP – NO HTML BEFORE THIS
// ========================
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Contact</title>
    
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.3/css/all.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        .glass-container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 40px;
            margin-top: 50px;
            margin-bottom: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
            color: white;
        }

        .page-header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .page-header .subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
        }

        .form-header {
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            color: white;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .form-group label i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: white;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-control:focus::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: white;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s;
            width: 100%;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px 12px;
        }

        .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
        }

        .form-select option {
            background: #764ba2;
            color: white;
        }

        .visibility-info {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 15px;
            margin-top: 15px;
            border-left: 4px solid #667eea;
        }

        .visibility-info h6 {
            color: white;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .visibility-info ul {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0;
            padding-left: 20px;
            font-size: 0.9rem;
        }

        .visibility-info li {
            margin-bottom: 5px;
        }

        .badge-visibility {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 8px;
        }

        .badge-private {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .badge-friends {
            background: rgba(40, 167, 69, 0.2);
            color: #51cf66;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .badge-public {
            background: rgba(0, 123, 255, 0.2);
            color: #4dabf7;
            border: 1px solid rgba(0, 123, 255, 0.3);
        }

        .error {
            color: #ff6b6b;
            font-size: 0.9rem;
            margin-top: 8px;
            display: block;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 160px;
            text-decoration: none;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            text-decoration: none;
        }

        .btn-submit {
            background: linear-gradient(135deg, #28a745, #218838);
            color: white;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #218838, #1e7e34);
            color: white;
        }

        .btn-back {
            background: linear-gradient(135deg, #6c757d, #545b62);
            color: white;
        }

        .btn-back:hover {
            background: linear-gradient(135deg, #545b62, #4a5056);
            color: white;
        }

        .btn-icon {
            margin-right: 8px;
        }

        .form-hint {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            margin-top: 5px;
            font-style: italic;
        }

        /* Floating animation for the header icon */
        .header-icon {
            animation: float 3s ease-in-out infinite;
            margin-bottom: 20px;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .glass-container {
                padding: 25px;
                margin-top: 30px;
            }
            
            .page-header h1 {
                font-size: 1.8rem;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .action-btn {
                min-width: 100%;
                padding: 10px 25px;
            }
        }
    </style>
</head>
<body>

<?php include_once 'header.php'; ?>

<div class="container">
    <div class="glass-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-icon">
                <i class="fas fa-user-plus fa-4x" style="color: white;"></i>
            </div>
            <h1>Create New Contact</h1>
            <p class="subtitle">Add a new contact to your address book</p>
        </div>

        <!-- Form Header -->
        <div class="form-header">
            <h3><i class="fas fa-info-circle mr-2"></i>Contact Information</h3>
        </div>

        <!-- Contact Form -->
        <form action="add_contact.php" method="POST" onsubmit="return validateForm()" id="contactForm">
            <!-- Full Name -->
            <div class="form-group">
                <label for="full_name">
                    <i class="fas fa-user"></i> Full Name
                </label>
                <input type="text" class="form-control" name="full_name" id="full_name" 
                       placeholder="Enter full name" required>
                <span id="full_name_error" class="error"></span>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <input type="email" class="form-control" name="email" id="email" 
                       placeholder="example@domain.com" required>
                <span id="email_error" class="error"></span>
            </div>

            <!-- Address -->
            <div class="form-group">
                <label for="address">
                    <i class="fas fa-map-marker-alt"></i> Address
                </label>
                <input type="text" class="form-control" name="address" id="address" 
                       placeholder="Street, City, State, ZIP" required>
                <span id="address_error" class="error"></span>
            </div>

            <!-- Nickname -->
            <div class="form-group">
                <label for="nickname">
                    <i class="fas fa-tag"></i> Nickname
                </label>
                <input type="text" class="form-control" name="nickname" id="nickname" 
                       placeholder="How you'd like to call them" required>
                <span id="nickname_error" class="error"></span>
            </div>

            <!-- Phone Number -->
            <div class="form-group">
                <label for="phone_number">
                    <i class="fas fa-phone"></i> Phone Number
                </label>
                <input type="text" class="form-control" name="phone_number" id="phone_number" 
                       placeholder="+977 9XXXXXXXXX" required>
                <span id="phone_number_error" class="error"></span>
            </div>

            <!-- Visibility -->
            <div class="form-group">
                <label for="visibility">
                    <i class="fas fa-eye"></i> Visibility
                </label>
                <select class="form-select" name="visibility" id="visibility" required>
                    <option value="">Select visibility...</option>
                    <option value="private">Private <span class="badge-visibility badge-private">Only You</span></option>
                    <option value="friends_only">Friends Only <span class="badge-visibility badge-friends">Friends Can See</span></option>
                    <option value="public">Public <span class="badge-visibility badge-public">Everyone Can See</span></option>
                </select>
                <span id="visibility_error" class="error"></span>
                
                <!-- Visibility Information Box -->
                <div class="visibility-info">
                    <h6><i class="fas fa-info-circle mr-2"></i>Visibility Options:</h6>
                    <ul>
                        <li><strong>Private:</strong> Only you can see this contact</li>
                        <li><strong>Friends Only:</strong> Your friends can see this contact</li>
                        <li><strong>Public:</strong> Anyone can see this contact</li>
                    </ul>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="submit" class="action-btn btn-submit">
                    <i class="fas fa-plus-circle btn-icon"></i> Add Contact
                </button>
                <a href="index.php" class="action-btn btn-back">
                    <i class="fas fa-arrow-left btn-icon"></i> Back to Dashboard
                </a>
            </div>
        </form>
    </div>
</div>

<?php include_once 'footer.php'; ?>

<script>
function validateForm() {
    let isValid = true;
    
    // Clear previous errors
    $('.error').text('');
    
    // Validate Full Name
    const fullName = $('#full_name').val().trim();
    if (fullName.length < 2) {
        $('#full_name_error').text('Full name must be at least 2 characters long');
        isValid = false;
    }
    
    // Validate Email
    const email = $('#email').val().trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        $('#email_error').text('Please enter a valid email address');
        isValid = false;
    }
    
    // Validate Address
    const address = $('#address').val().trim();
    if (address.length < 5) {
        $('#address_error').text('Address must be at least 5 characters long');
        isValid = false;
    }
    
    // Validate Nickname
    const nickname = $('#nickname').val().trim();
    if (nickname.length < 2) {
        $('#nickname_error').text('Nickname must be at least 2 characters long');
        isValid = false;
    }
    
    // Validate Phone Number
    const phone = $('#phone_number').val().trim();
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
    const cleanPhone = phone.replace(/[\s\(\)\-]/g, '');
    
    if (!phoneRegex.test(cleanPhone)) {
        $('#phone_number_error').text('Please enter a valid phone number');
        isValid = false;
    }
    
    // Validate Visibility
    const visibility = $('#visibility').val();
    if (!visibility) {
        $('#visibility_error').text('Please select a visibility option');
        isValid = false;
    }
    
    // Add visual feedback for invalid fields
    if (!isValid) {
        $('.form-control, .form-select').each(function() {
            if ($(this).next('.error').text() !== '') {
                $(this).css('border-color', '#ff6b6b');
            }
        });
        
        // Scroll to first error
        $('html, body').animate({
            scrollTop: $('.error:visible').first().offset().top - 100
        }, 500);
    }
    
    return isValid;
}

// Remove error styling on input
$(document).ready(function() {
    $('.form-control, .form-select').on('input change', function() {
        $(this).css('border-color', 'rgba(255, 255, 255, 0.2)');
        $(this).next('.error').text('');
    });
    
    // Update badge colors in select options
    $('#visibility').on('change', function() {
        const selected = $(this).val();
        $(this).removeClass('selected-private selected-friends selected-public');
        if (selected) {
            $(this).addClass('selected-' + selected);
        }
    });
});
</script>

</body>
</html>