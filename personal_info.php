<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Information</title>
    
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.3/css/all.min.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            /* padding-bottom: 50px; */
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 40px;
            margin: 20px auto;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .contact-header {
            text-align: center;
            margin-bottom: 40px;
            color: white;
        }

        .contact-avatar-large {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: bold;
            color: white;
            margin: 0 auto 25px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .contact-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .contact-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.2rem;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            color: white;
            transition: transform 0.3s, background 0.3s;
        }

        .info-card:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-5px);
        }

        .info-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .info-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1.3rem;
            font-weight: 600;
            word-break: break-word;
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
            min-width: 140px;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-edit {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .btn-back {
            background: linear-gradient(135deg, #6c757d, #545b62);
            color: white;
        }

        .btn-icon {
            margin-right: 8px;
        }

        .contact-id {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-block;
            margin-top: 10px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .empty-state {
            text-align: center;
            color: white;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.7;
        }

        /* Animation for the avatar */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .contact-avatar-large {
            animation: float 3s ease-in-out infinite;
        }


        /* Footer Styles */
        .sticky-footer {
            background: rgba(0, 0, 0, 0.2);
            color: white;
            padding: 20px 0;
            width: 100%;
            flex-shrink: 0;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .footer-content {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .footer-links {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>

<?php
include_once 'header.php';
include_once 'db.php';

$contact = null;
$contact_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $contact_id = $_GET['id'];
    $stmt = $db->prepare('SELECT * FROM contacts WHERE id = ?');
    $stmt->execute([$contact_id]);
    $contact = $stmt->fetch();
}
?>

<div class="container">
    <?php if ($contact): ?>
        <div class="glass-card">
            <!-- Header Section -->
            <div class="contact-header">
                <div class="contact-avatar-large">
                    <?= strtoupper(substr($contact['nickname'], 0, 1)) ?>
                </div>
                <h1 class="contact-title"><?= htmlspecialchars($contact['nickname']) ?></h1>
                <p class="contact-subtitle">Contact Information</p>
                
            </div>

            <!-- Information Grid -->
            <div class="row">
                <!-- Full Name -->
                <div class="col-md-6 mb-4">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?= htmlspecialchars($contact['full_name']) ?></div>
                    </div>
                </div>

                <!-- Email -->
                <div class="col-md-6 mb-4">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-label">Email Address</div>
                        <div class="info-value">
                            <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" style="color: white; text-decoration: none;">
                                <?= htmlspecialchars($contact['email']) ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Phone Number -->
                <div class="col-md-6 mb-4">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-label">Phone Number</div>
                        <div class="info-value">
                            <a href="tel:<?= htmlspecialchars($contact['phone_number']) ?>" style="color: white; text-decoration: none;">
                                <?= htmlspecialchars($contact['phone_number']) ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="col-md-6 mb-4">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-label">Address</div>
                        <div class="info-value"><?= htmlspecialchars($contact['address']) ?></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="edit_contact.php?id=<?= $contact_id ?>" class="action-btn btn-edit">
                    <i class="fas fa-edit btn-icon"></i> Edit Contact
                </a>
                <a href="delete_contact.php?id=<?= $contact_id ?>" 
                   class="action-btn btn-delete" 
                   onclick="return confirm('Are you sure you want to delete this contact? This action cannot be undone.')">
                    <i class="fas fa-trash btn-icon"></i> Delete Contact
                </a>
                <a href="index.php" class="action-btn btn-back">
                    <i class="fas fa-arrow-left btn-icon"></i> Back to Dashboard
                </a>
            </div>
        </div>

    <?php else: ?>
        <!-- Empty State -->
        <div class="glass-card">
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-user-slash"></i>
                </div>
                <h2>Contact Not Found</h2>
                <p class="mb-4">The contact you're looking for doesn't exist or has been removed.</p>
                <a href="index.php" class="action-btn btn-back" style="width: auto;">
                    <i class="fas fa-arrow-left btn-icon"></i> Back to Dashboard
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<footer class="sticky-footer">
    <div class="container">
        <div class="footer-content">
            <p>&copy; <?= date('Y') ?> Contact Manager. All rights reserved.</p>
            <!-- <div class="footer-links">
                <a href="#"><i class="fas fa-shield-alt mr-1"></i> Privacy Policy</a>
                <a href="#"><i class="fas fa-file-contract mr-1"></i> Terms of Service</a>
                <a href="#"><i class="fas fa-question-circle mr-1"></i> Help</a>
                <a href="#"><i class="fas fa-envelope mr-1"></i> Contact Us</a>
            </div> -->
        </div>
    </div>
</footer>

</body>
</html>