<?php
// edit_profile.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include_once 'db.php';
$user_id = $_SESSION['user_id'];

// Get current user info
$stmt = $db->prepare("SELECT username FROM user_detail WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = trim($_POST['username'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate username
    if (empty($new_username)) {
        $error_message = "Username cannot be empty!";
    } elseif (strlen($new_username) < 3) {
        $error_message = "Username must be at least 3 characters long!";
    } elseif (strlen($new_username) > 50) {
        $error_message = "Username cannot exceed 50 characters!";
    }
    
    // Check if username is being changed and if it's already taken
    if (empty($error_message) && $new_username !== $user['username']) {
        $check_stmt = $db->prepare("SELECT user_id FROM user_detail WHERE username = ? AND user_id != ?");
        $check_stmt->execute([$new_username, $user_id]);
        
        if ($check_stmt->rowCount() > 0) {
            $error_message = "Username is already taken!";
        }
    }
    
    // Check if password is being changed
    $password_changed = false;
    if (empty($error_message) && (!empty($new_password) || !empty($confirm_password))) {
        // Verify current password
        $verify_stmt = $db->prepare("SELECT password FROM user_detail WHERE user_id = ?");
        $verify_stmt->execute([$user_id]);
        $current_user = $verify_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!password_verify($current_password, $current_user['password'])) {
            $error_message = "Current password is incorrect!";
        } elseif (strlen($new_password) < 6) {
            $error_message = "New password must be at least 6 characters long!";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match!";
        } else {
            $password_changed = true;
        }
    }
    
    // Update database if no errors
    if (empty($error_message)) {
        try {
            if ($password_changed) {
                // Update both username and password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $db->prepare("UPDATE user_detail SET username = ?, password = ? WHERE user_id = ?");
                $update_stmt->execute([$new_username, $hashed_password, $user_id]);
                $success_message = "Username and password updated successfully!";
            } else {
                // Update only username
                $update_stmt = $db->prepare("UPDATE user_detail SET username = ? WHERE user_id = ?");
                $update_stmt->execute([$new_username, $user_id]);
                $success_message = "Username updated successfully!";
            }
            
            // Update session username if changed
            if ($new_username !== $user['username']) {
                $_SESSION['username'] = $new_username;
            }
            
            // Refresh user data
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - ContactFlow</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.3/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            padding-top: 20px;
        }
        
        .edit-profile-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #ffffff, #e0e0e0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-align: center;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: white;
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            color: white;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 0 0.3rem rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #28a745, #218838);
            border: none;
            border-radius: 50px;
            color: white;
            padding: 14px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn-submit:hover {
            background: linear-gradient(135deg, #218838, #1e7e34);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.3);
        }
        
        .btn-submit:active {
            transform: translateY(-1px);
        }
        
        .btn-cancel {
            background: linear-gradient(135deg, #6c757d, #545b62);
            border: none;
            border-radius: 50px;
            color: white;
            padding: 12px 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-top: 15px;
        }
        
        .btn-cancel:hover {
            background: linear-gradient(135deg, #545b62, #4a5056);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .alert-custom {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            color: white;
            backdrop-filter: blur(10px);
            margin-bottom: 25px;
        }
        
        .alert-success {
            border-left: 5px solid #28a745;
        }
        
        .alert-danger {
            border-left: 5px solid #dc3545;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            font-size: 1.2rem;
        }
        
        .password-container {
            position: relative;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .info-box {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        
        .info-box p {
            margin-bottom: 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        @media (max-width: 768px) {
            .edit-profile-container {
                padding: 10px;
            }
            
            .glass-card {
                padding: 20px;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .btn-cancel {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include_once 'header.php'; ?>
    
    <div class="edit-profile-container">
        <h1 class="page-title"><i class="fas fa-user-edit mr-3"></i>Edit Profile</h1>
        
        <div class="glass-card">
            <!-- Alert Messages -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($success_message) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= htmlspecialchars($error_message) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <!-- Current Info Box -->
            <div class="info-box">
                <p><strong>Current Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
                <p><small><i class="fas fa-info-circle mr-1"></i> You can change your username and/or password below.</small></p>
            </div>
            
            <!-- Edit Form -->
            <form method="POST" action="">
                <!-- Username Section -->
                <div class="form-section">
                    <h3 class="section-title">Username</h3>
                    <div class="form-group">
                        <label class="form-label">New Username</label>
                        <input type="text" 
                               name="username" 
                               class="form-control" 
                               value="<?= htmlspecialchars($user['username']) ?>" 
                               required 
                               minlength="3" 
                               maxlength="50">
                        <small class="form-text" style="color: rgba(255, 255, 255, 0.7);">
                            Must be 3-50 characters long. Username must be unique.
                        </small>
                    </div>
                </div>
                
                <!-- Password Section -->
                <div class="form-section">
                    <h3 class="section-title">Password Change (Optional)</h3>
                    <p style="opacity: 0.8; margin-bottom: 20px;">Leave these fields blank if you don't want to change your password.</p>
                    
                    <!-- Current Password -->
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <div class="password-container">
                            <input type="password" 
                                   name="current_password" 
                                   id="current_password"
                                   class="form-control" 
                                   placeholder="Enter current password">
                            <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- New Password -->
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <div class="password-container">
                            <input type="password" 
                                   name="new_password" 
                                   id="new_password"
                                   class="form-control" 
                                   placeholder="Enter new password (min 6 characters)"
                                   minlength="6">
                            <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="form-text" style="color: rgba(255, 255, 255, 0.7);">
                            Minimum 6 characters
                        </small>
                    </div>
                    
                    <!-- Confirm New Password -->
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <div class="password-container">
                            <input type="password" 
                                   name="confirm_password" 
                                   id="confirm_password"
                                   class="form-control" 
                                   placeholder="Confirm new password">
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save mr-2"></i> Save Changes
                </button>
                
                <!-- Cancel Button -->
                <div class="text-center mt-4">
                    <a href="profile.php" class="btn-cancel">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include_once 'footer.php'; ?>
    
    <script>
        // Password visibility toggle
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.parentNode.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Form validation
        $(document).ready(function() {
            $('form').on('submit', function(e) {
                const newPassword = $('input[name="new_password"]').val();
                const confirmPassword = $('input[name="confirm_password"]').val();
                const currentPassword = $('input[name="current_password"]').val();
                
                // If any password field is filled, all must be filled
                if ((newPassword || confirmPassword || currentPassword) && 
                    (!newPassword || !confirmPassword || !currentPassword)) {
                    alert('Please fill all password fields if you want to change your password.');
                    e.preventDefault();
                    return false;
                }
                
                // Check if new password matches confirmation
                if (newPassword && newPassword !== confirmPassword) {
                    alert('New passwords do not match!');
                    e.preventDefault();
                    return false;
                }
            });
            
            // Focus on username field
            $('input[name="username"]').focus();
        });
    </script>
</body>
</html>