<?php
// profile.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include_once 'db.php';
$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $db->prepare("SELECT * FROM user_detail WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get friends count
$friends_stmt = $db->prepare("
    SELECT COUNT(*) as friend_count 
    FROM friends 
    WHERE (user_id = ? OR friend_id = ?) 
    AND status = 'accepted'
");
$friends_stmt->execute([$user_id, $user_id]);
$friends_count = $friends_stmt->fetch(PDO::FETCH_ASSOC)['friend_count'];

// Get pending friend requests
$pending_stmt = $db->prepare("
    SELECT COUNT(*) as pending_count 
    FROM friends 
    WHERE friend_id = ? 
    AND status = 'pending'
");
$pending_stmt->execute([$user_id]);
$pending_count = $pending_stmt->fetch(PDO::FETCH_ASSOC)['pending_count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - ContactFlow</title>
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
        }
        
        .profile-container {
            max-width: 1200px;
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
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            font-weight: bold;
            color: white;
            margin: 0 auto 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border: 5px solid rgba(255, 255, 255, 0.3);
        }
        
        .profile-name {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
            text-align: center;
            background: linear-gradient(135deg, #ffffff, #e0e0e0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .profile-email {
            font-size: 1.1rem;
            opacity: 0.8;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .profile-stats {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .stat-item:last-child {
            border-bottom: none;
        }
        
        .stat-label {
            font-size: 1rem;
            font-weight: 500;
            opacity: 0.9;
        }
        
        .stat-value {
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .badge-custom {
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .badge-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
        }
        
        .badge-info {
            background: linear-gradient(135deg, #17a2b8, #117a8b);
        }
        
        .badge-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
        }
        
        .action-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 25px;
            text-decoration: none;
            display: block;
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .action-card:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-5px);
            text-decoration: none;
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .action-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .action-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: white;
        }
        
        .action-desc {
            font-size: 0.95rem;
            opacity: 0.7;
            color: white;
        }
        
        .action-primary .action-icon {
            background: linear-gradient(135deg, #007bff, #0056b3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .action-success .action-icon {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .action-info .action-icon {
            background: linear-gradient(135deg, #17a2b8, #117a8b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .action-warning .action-icon {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .activity-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 15px;
            border-left: 4px solid #28a745;
            display: flex;
            align-items: center;
        }
        
        .activity-icon {
            font-size: 1.2rem;
            margin-right: 15px;
            width: 40px;
            height: 40px;
            background: rgba(40, 167, 69, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .activity-text {
            flex: 1;
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .view-all-link {
            display: inline-block;
            padding: 10px 25px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .view-all-link:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .profile-avatar {
                width: 120px;
                height: 120px;
                font-size: 3rem;
            }
            
            .profile-name {
                font-size: 1.8rem;
            }
            
            .glass-card {
                padding: 20px;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include_once 'header.php'; ?>
    
    <div class="profile-container">
        <div class="row">
            <!-- Left Column: Profile Info -->
            <div class="col-lg-4 col-md-5 mb-4">
                <div class="glass-card">
                    <div class="text-center">
                        <div class="profile-avatar">
                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                        </div>
                        <h1 class="profile-name"><?= htmlspecialchars($user['username']) ?></h1>
                        <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-label"><i class="fas fa-users mr-2"></i>Friends</span>
                            <span class="stat-value badge badge-primary badge-custom"><?= $friends_count ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label"><i class="fas fa-address-book mr-2"></i>Contacts</span>
                            <span class="stat-value badge badge-info badge-custom">
                                <?php 
                                $contact_stmt = $db->prepare("SELECT COUNT(*) FROM contacts WHERE user_id = ?");
                                $contact_stmt->execute([$user_id]);
                                echo $contact_stmt->fetchColumn();
                                ?>
                            </span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label"><i class="fas fa-clock mr-2"></i>Pending Requests</span>
                            <span class="stat-value badge badge-warning badge-custom"><?= $pending_count ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Quick Actions -->
            <div class="col-lg-8 col-md-7">
                <h2 class="section-title">Quick Actions</h2>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <a href="friends.php" class="action-card action-primary">
                            <div class="text-center">
                                <div class="action-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h3 class="action-title">My Friends</h3>
                                <p class="action-desc">Manage your friends list and requests</p>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <a href="find_friends.php" class="action-card action-success">
                            <div class="text-center">
                                <div class="action-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h3 class="action-title">Find Friends</h3>
                                <p class="action-desc">Search and add new friends</p>
                            </div>
                        </a>
                    </div>
                    
                    <!-- <div class="col-md-6 mb-4">
                        <a href="shared_contacts.php" class="action-card action-info">
                            <div class="text-center">
                                <div class="action-icon">
                                    <i class="fas fa-share-alt"></i>
                                </div>
                                <h3 class="action-title">Shared Contacts</h3>
                                <p class="action-desc">View contacts shared by friends</p>
                            </div>
                        </a>
                    </div> -->
                    
                    <div class="col-md-6 mb-4">
                        <a href="edit_profile.php" class="action-card action-warning">
                            <div class="text-center">
                                <div class="action-icon">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <h3 class="action-title">Edit Profile</h3>
                                <p class="action-desc">Update your account settings</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include_once 'footer.php'; ?>
</body>
</html>