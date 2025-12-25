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

include_once 'db.php';

$contact = null;
$contact_id = null;

// Handle GET request to load contact data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $contact_id = $_GET['id'];
    $stmt = $db->prepare('SELECT * FROM contacts WHERE id = ? AND user_id = ?');
    $stmt->execute([$contact_id, $_SESSION['user_id']]);
    $contact = $stmt->fetch();
    
    // If contact not found or doesn't belong to user, redirect
    if (!$contact) {
        header('Location: index.php');
        exit;
    }
}

// Handle POST request to update contact
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact_id = $_POST['contact_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $nickname = $_POST['nickname'];
    $phone_number = $_POST['phone_number'];

    // Verify contact belongs to current user
    $checkStmt = $db->prepare('SELECT id FROM contacts WHERE id = ? AND user_id = ?');
    $checkStmt->execute([$contact_id, $_SESSION['user_id']]);
    
    if ($checkStmt->fetch()) {
        $stmt = $db->prepare('UPDATE contacts SET full_name = ?, email = ?, address = ?, nickname = ?, phone_number = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$full_name, $email, $address, $nickname, $phone_number, $contact_id, $_SESSION['user_id']]);
        header('Location: index.php');
        exit;
    } else {
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Contact</title>
    
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

        .contact-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
            color: white;
            margin: 0 auto 20px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
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

        .btn-save {
            background: linear-gradient(135deg, #28a745, #218838);
            color: white;
        }

        .btn-save:hover {
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

        .btn-cancel {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .btn-cancel:hover {
            background: linear-gradient(135deg, #c82333, #bd2130);
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

        .contact-id-badge {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            border-radius: 50px;
            display: inline-block;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 10px;
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
    <?php if ($contact): ?>
        <div class="glass-container">
            <!-- Page Header -->
            <div class="page-header">
                <div class="contact-avatar">
                    <?= strtoupper(substr($contact['nickname'], 0, 1)) ?>
                </div>
                <h1>Edit Contact</h1>
                <p class="subtitle">Update contact information for <?= htmlspecialchars($contact['nickname']) ?></p>
                
            </div>

            <!-- Form Header -->
            <div class="form-header">
                <h3><i class="fas fa-edit mr-2"></i>Edit Contact Information</h3>
            </div>

            <!-- Contact Form -->
            <form action="edit_contact.php" method="POST" onsubmit="return validateForm()" id="contactForm">
                <input type="hidden" name="contact_id" value="<?= $contact['id']; ?>">
                
                <!-- Full Name -->
                <div class="form-group">
                    <label for="full_name">
                        <i class="fas fa-user"></i> Full Name
                    </label>
                    <input type="text" class="form-control" name="full_name" id="full_name" 
                           value="<?= htmlspecialchars($contact['full_name']); ?>" 
                           placeholder="Enter full name" required>
                    <span id="full_name_error" class="error"></span>
                    <!-- <div class="form-hint">Enter the contact's complete legal name</div> -->
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" class="form-control" name="email" id="email" 
                           value="<?= htmlspecialchars($contact['email']); ?>" 
                           placeholder="example@domain.com" required>
                    <span id="email_error" class="error"></span>
                    <!-- <div class="form-hint">A valid email address is required</div> -->
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label for="address">
                        <i class="fas fa-map-marker-alt"></i> Address
                    </label>
                    <input type="text" class="form-control" name="address" id="address" 
                           value="<?= htmlspecialchars($contact['address']); ?>" 
                           placeholder="Street, City, State, ZIP" required>
                    <span id="address_error" class="error"></span>
                    <!-- <div class="form-hint">Complete mailing address</div> -->
                </div>

                <!-- Nickname -->
                <div class="form-group">
                    <label for="nickname">
                        <i class="fas fa-tag"></i> Nickname
                    </label>
                    <input type="text" class="form-control" name="nickname" id="nickname" 
                           value="<?= htmlspecialchars($contact['nickname']); ?>" 
                           placeholder="How you'd like to call them" required>
                    <span id="nickname_error" class="error"></span>
                    <!-- <div class="form-hint">A friendly name for quick reference</div> -->
                </div>

                <!-- Phone Number -->
                <div class="form-group">
                    <label for="phone_number">
                        <i class="fas fa-phone"></i> Phone Number
                    </label>
                    <input type="text" class="form-control" name="phone_number" id="phone_number" 
                           value="<?= htmlspecialchars($contact['phone_number']); ?>" 
                           placeholder="+977 9XXXXXXXXX" required>
                    <span id="phone_number_error" class="error"></span>
                    <!-- <div class="form-hint">Include country code if international</div> -->
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="submit" class="action-btn btn-save">
                        <i class="fas fa-save btn-icon"></i> Save Changes
                    </button>
                    <a href="personal_info.php?id=<?= $contact['id'] ?>" class="action-btn btn-cancel">
                        <i class="fas fa-times btn-icon"></i> Cancel
                    </a>
                    <a href="index.php" class="action-btn btn-back">
                        <i class="fas fa-arrow-left btn-icon"></i> Dashboard
                    </a>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- Contact Not Found -->
        <div class="glass-container">
            <div class="page-header text-center">
                <div class="contact-avatar">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h1>Contact Not Found</h1>
                <p class="subtitle">The contact you're trying to edit doesn't exist or you don't have permission to edit it.</p>
                <div class="action-buttons">
                    <a href="index.php" class="action-btn btn-back">
                        <i class="fas fa-arrow-left btn-icon"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
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
    
    // Add visual feedback for invalid fields
    if (!isValid) {
        $('.form-control').each(function() {
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
    $('.form-control').on('input', function() {
        $(this).css('border-color', 'rgba(255, 255, 255, 0.2)');
        $(this).next('.error').text('');
    });
});
</script>

</body>
</html>