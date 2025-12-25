<?php
// view_profile.php - CORRECTED FRIENDS SECTION
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include_once 'db.php';
$user_id = $_SESSION['user_id'];

// Get the profile ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: friends.php');
    exit;
}

$profile_id = (int)$_GET['id'];

// Check if viewing own profile
$is_own_profile = ($profile_id == $user_id);

// Get profile user's basic info
$profile_stmt = $db->prepare("
    SELECT user_id, username
    FROM user_detail 
    WHERE user_id = ?
");
$profile_stmt->execute([$profile_id]);
$profile = $profile_stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    header('Location: friends.php');
    exit;
}

// Check friendship status
$friendship_stmt = $db->prepare("
    SELECT status 
    FROM friends 
    WHERE (user_id = ? AND friend_id = ?) 
       OR (user_id = ? AND friend_id = ?)
");
$friendship_stmt->execute([$user_id, $profile_id, $profile_id, $user_id]);
$friendship = $friendship_stmt->fetch(PDO::FETCH_ASSOC);

$friendship_status = $friendship['status'] ?? null;
$is_friend = ($friendship_status == 'accepted');
$is_pending = ($friendship_status == 'pending');
$is_blocked = ($friendship_status == 'blocked');

// Handle friend actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    switch ($action) {
        case 'add_friend':
            if (!$friendship_status) {
                $stmt = $db->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')");
                $stmt->execute([$user_id, $profile_id]);
            }
            header("Location: view_profile.php?id=$profile_id");
            exit;
            
        case 'remove_friend':
        case 'cancel_request':
            $stmt = $db->prepare("DELETE FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
            $stmt->execute([$user_id, $profile_id, $profile_id, $user_id]);
            header("Location: view_profile.php?id=$profile_id");
            exit;
            
        case 'accept_request':
            $stmt = $db->prepare("UPDATE friends SET status = 'accepted' WHERE user_id = ? AND friend_id = ?");
            $stmt->execute([$profile_id, $user_id]);
            header("Location: view_profile.php?id=$profile_id");
            exit;
    }
}

// Get profile user's contacts (FILTERED BY VISIBILITY)
$contacts = [];

if ($is_own_profile) {
    // User can see all their own contacts
    $contacts_stmt = $db->prepare("
        SELECT id, full_name, email, address, nickname, phone_number, visibility
        FROM contacts
        WHERE user_id = ?
        ORDER BY full_name
    ");
    $contacts_stmt->execute([$profile_id]);
    $contacts = $contacts_stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($is_friend) {
    // Friends can see friends_only and public contacts (NOT private)
    $contacts_stmt = $db->prepare("
        SELECT id, full_name, email, address, nickname, phone_number, visibility
        FROM contacts
        WHERE user_id = ? 
        AND (visibility = 'friends_only' OR visibility = 'public')
        ORDER BY full_name
    ");
    $contacts_stmt->execute([$profile_id]);
    $contacts = $contacts_stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Non-friends can only see public contacts
    $contacts_stmt = $db->prepare("
        SELECT id, full_name, email, address, nickname, phone_number, visibility
        FROM contacts
        WHERE user_id = ? 
        AND visibility = 'public'
        ORDER BY full_name
    ");
    $contacts_stmt->execute([$profile_id]);
    $contacts = $contacts_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get visible contacts count
$visible_contacts_count = count($contacts);

// Get contact count stats for the profile user (only counts user can see)
$contact_stats = [
    'total' => 0,
    'public' => 0,
    'friends_only' => 0,
    'private' => 0
];

if ($is_own_profile) {
    $stats_stmt = $db->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN visibility = 'public' THEN 1 ELSE 0 END) as public,
            SUM(CASE WHEN visibility = 'friends_only' THEN 1 ELSE 0 END) as friends_only,
            SUM(CASE WHEN visibility = 'private' THEN 1 ELSE 0 END) as private
        FROM contacts 
        WHERE user_id = ?
    ");
    $stats_stmt->execute([$profile_id]);
    $contact_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($is_friend) {
    // Friends can see count of friends_only and public
    $stats_stmt = $db->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN visibility = 'public' THEN 1 ELSE 0 END) as public,
            SUM(CASE WHEN visibility = 'friends_only' THEN 1 ELSE 0 END) as friends_only,
            0 as private
        FROM contacts 
        WHERE user_id = ?
        AND (visibility = 'friends_only' OR visibility = 'public')
    ");
    $stats_stmt->execute([$profile_id]);
    $contact_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
} else {
    // Non-friends can only see count of public
    $stats_stmt = $db->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN visibility = 'public' THEN 1 ELSE 0 END) as public,
            0 as friends_only,
            0 as private
        FROM contacts 
        WHERE user_id = ?
        AND visibility = 'public'
    ");
    $stats_stmt->execute([$profile_id]);
    $contact_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
}

// Get mutual friends between current user and profile user
$mutual_friends = [];
$mutual_count = 0;

if ($is_friend || $is_own_profile) {
    // Only show mutual friends if you're friends with the user or viewing your own profile
    $mutual_stmt = $db->prepare("
        SELECT DISTINCT u.user_id, u.username
        FROM friends f1
        JOIN friends f2 ON (
            (f1.friend_id = f2.friend_id AND f1.user_id = ? AND f2.user_id = ?) OR
            (f1.friend_id = f2.user_id AND f1.user_id = ? AND f2.friend_id = ?) OR
            (f1.user_id = f2.friend_id AND f1.friend_id = ? AND f2.user_id = ?) OR
            (f1.user_id = f2.user_id AND f1.friend_id = ? AND f2.friend_id = ?)
        )
        JOIN user_detail u ON (
            (f1.friend_id = u.user_id AND f2.friend_id = u.user_id) OR
            (f1.friend_id = u.user_id AND f2.user_id = u.user_id) OR
            (f1.user_id = u.user_id AND f2.friend_id = u.user_id) OR
            (f1.user_id = u.user_id AND f2.user_id = u.user_id)
        )
        WHERE f1.status = 'accepted'
        AND f2.status = 'accepted'
        AND u.user_id != ?
        AND u.user_id != ?
        GROUP BY u.user_id
        ORDER BY u.username
        LIMIT 20
    ");
    
    if ($is_own_profile) {
        // For own profile, show all friends (since you can see all your own friends)
        $mutual_stmt = $db->prepare("
            SELECT u.user_id, u.username
            FROM friends f
            JOIN user_detail u ON (
                (f.user_id = u.user_id AND f.friend_id = ?) OR 
                (f.friend_id = u.user_id AND f.user_id = ?)
            )
            WHERE (f.user_id = ? OR f.friend_id = ?) 
            AND u.user_id != ?
            AND f.status = 'accepted'
            ORDER BY u.username
            LIMIT 20
        ");
        $mutual_stmt->execute([$profile_id, $profile_id, $profile_id, $profile_id, $profile_id]);
    } else {
        // For friend's profile, show mutual friends only
        $mutual_stmt->execute([
            $user_id, $profile_id,
            $user_id, $profile_id,
            $user_id, $profile_id,
            $user_id, $profile_id,
            $user_id, $profile_id
        ]);
    }
    
    $mutual_friends = $mutual_stmt->fetchAll(PDO::FETCH_ASSOC);
    $mutual_count = count($mutual_friends);
}

// Get total friend count for the profile user (for stats only)
$total_friends_stmt = $db->prepare("
    SELECT COUNT(*) as total_friends
    FROM friends f
    JOIN user_detail u ON (
        (f.user_id = u.user_id AND f.friend_id = ?) OR 
        (f.friend_id = u.user_id AND f.user_id = ?)
    )
    WHERE (f.user_id = ? OR f.friend_id = ?) 
    AND u.user_id != ?
    AND f.status = 'accepted'
");
$total_friends_stmt->execute([$profile_id, $profile_id, $profile_id, $profile_id, $profile_id]);
$total_friends = $total_friends_stmt->fetch(PDO::FETCH_ASSOC);
$total_friends_count = $total_friends['total_friends'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($profile['username']) ?>'s Profile - ContactFlow</title>
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
        
        .profile-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            color: white;
            margin: 0 auto 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }
        
        .profile-name {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            text-align: center;
            min-width: 120px;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            display: block;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.7;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            min-width: 160px;
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            color: white;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0069d9);
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745, #218838);
            color: white;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #545b62);
            color: white;
        }
        
        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .section-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .contact-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .contact-card:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .contact-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-right: 15px;
        }
        
        .visibility-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 12px;
            margin-left: 10px;
        }
        
        .badge-private {
            background: rgba(116, 91, 226, 0.81);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .badge-friends {
            background: rgba(40, 167, 70, 0.68);
            color: #fdfffdff;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .badge-public {
            background: rgba(0, 123, 255, 0.62);
            color: #ffffffff;
            border: 1px solid rgba(0, 123, 255, 0.3);
        }
        
        .friend-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .friend-card:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .friend-avatar {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            font-weight: bold;
            color: white;
            margin: 0 auto 15px;
        }
        
        .friend-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .empty-state h4 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        @media (max-width: 768px) {
            .profile-stats {
                flex-direction: column;
                gap: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
            }
            
            .profile-avatar {
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
            }
            
            .profile-name {
                font-size: 1.8rem;
            }
            
            .page-title {
                font-size: 2rem;
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
        <h1 class="page-title"><i class="fas fa-user-circle mr-3"></i>User Profile</h1>
        
        <div class="glass-card">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <?= strtoupper(substr($profile['username'], 0, 1)) ?>
                </div>
                
                <h1 class="profile-name"><?= htmlspecialchars($profile['username']) ?></h1>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <?php if ($is_own_profile): ?>
                        <a href="profile.php" class="action-btn btn-primary">
                            <i class="fas fa-edit mr-2"></i> Edit My Profile
                        </a>
                    <?php else: ?>
                        <?php if ($is_friend): ?>
                            <a href="friends.php?action=remove&friend_id=<?= $profile_id ?>" 
                               class="action-btn btn-danger"
                               onclick="return confirm('Remove <?= htmlspecialchars($profile['username']) ?> from friends?')">
                                <i class="fas fa-user-times mr-2"></i> Remove Friend
                            </a>
                            <a href="friends.php?action=block&friend_id=<?= $profile_id ?>" 
                               class="action-btn btn-secondary"
                               onclick="return confirm('Block <?= htmlspecialchars($profile['username']) ?>?')">
                                <i class="fas fa-ban mr-2"></i> Block
                            </a>
                        <?php elseif ($is_pending): ?>
                            <?php 
                            // Check who sent the request
                            $sender_check = $db->prepare("SELECT user_id FROM friends WHERE user_id = ? AND friend_id = ? AND status = 'pending'");
                            $sender_check->execute([$user_id, $profile_id]);
                            $is_sender = ($sender_check->rowCount() > 0);
                            ?>
                            
                            <?php if ($is_sender): ?>
                                <a href="view_profile.php?id=<?= $profile_id ?>&action=cancel_request" 
                                   class="action-btn btn-warning">
                                    <i class="fas fa-times mr-2"></i> Cancel Request
                                </a>
                            <?php else: ?>
                                <a href="view_profile.php?id=<?= $profile_id ?>&action=accept_request" 
                                   class="action-btn btn-success">
                                    <i class="fas fa-check mr-2"></i> Accept Request
                                </a>
                                <a href="friends.php?action=reject&friend_id=<?= $profile_id ?>" 
                                   class="action-btn btn-danger"
                                   onclick="return confirm('Reject friend request from <?= htmlspecialchars($profile['username']) ?>?')">
                                    <i class="fas fa-times mr-2"></i> Reject
                                </a>
                            <?php endif; ?>
                        <?php elseif ($is_blocked): ?>
                            <a href="friends.php?action=unblock&friend_id=<?= $profile_id ?>" 
                               class="action-btn btn-success"
                               onclick="return confirm('Unblock <?= htmlspecialchars($profile['username']) ?>?')">
                                <i class="fas fa-unlock mr-2"></i> Unblock
                            </a>
                        <?php else: ?>
                            <a href="view_profile.php?id=<?= $profile_id ?>&action=add_friend" 
                               class="action-btn btn-primary">
                                <i class="fas fa-user-plus mr-2"></i> Add Friend
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <a href="friends.php" class="action-btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Friends
                    </a>
                </div>
                
                <!-- Profile Stats -->
                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-value"><?= $visible_contacts_count ?></span>
                        <span class="stat-label">Visible Contacts</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?= $total_friends_count ?></span>
                        <span class="stat-label">Total Friends</span>
                    </div>
                    <?php if ($mutual_count > 0): ?>
                        <div class="stat-item">
                            <span class="stat-value"><?= $mutual_count ?></span>
                            <span class="stat-label">Mutual Friends</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($contact_stats['public'] > 0): ?>
                        <div class="stat-item">
                            <span class="stat-value"><?= $contact_stats['public'] ?></span>
                            <span class="stat-label">Public Contacts</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Contact Visibility Stats (Only for own profile) -->
            <?php if ($is_own_profile): ?>
                <div class="section-card">
                    <h2 class="section-title">
                        <i class="fas fa-chart-pie mr-2"></i>Contact Visibility Stats
                    </h2>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <div class="stat-value" style="color: #4dabf7;">
                                    <?= $contact_stats['public'] ?? 0 ?>
                                </div>
                                <div class="stat-label">Public Contacts</div>
                                <span class="badge badge-public visibility-badge">Public</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <div class="stat-value" style="color: #51cf66;">
                                    <?= $contact_stats['friends_only'] ?? 0 ?>
                                </div>
                                <div class="stat-label">Friends Only</div>
                                <span class="badge badge-friends visibility-badge">Friends Only</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="text-center">
                                <div class="stat-value" style="color: #ff6b6b;">
                                    <?= $contact_stats['private'] ?? 0 ?>
                                </div>
                                <div class="stat-label">Private Contacts</div>
                                <span class="badge badge-private visibility-badge">Private</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Contacts Section -->
            <div class="section-card">
                <h2 class="section-title">
                    <i class="fas fa-address-book mr-2"></i>Contacts
                    <span class="badge badge-light ml-2"><?= $visible_contacts_count ?></span>
                </h2>
                
                <?php if ($visible_contacts_count > 0): ?>
                    <div class="row">
                        <?php foreach ($contacts as $contact): ?>
                            <div class="col-md-6 mb-3">
                                <div class="contact-card d-flex align-items-center">
                                    <div class="contact-icon">
                                        <?= strtoupper(substr($contact['full_name'], 0, 1)) ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1">
                                            <?= htmlspecialchars($contact['full_name']) ?>
                                            <span class="visibility-badge badge-<?= 
                                                $contact['visibility'] == 'public' ? 'public' : 
                                                ($contact['visibility'] == 'friends_only' ? 'friends' : 'private')
                                            ?>">
                                                <?= ucfirst($contact['visibility']) ?>
                                            </span>
                                        </h5>
                                        
                                        <?php if (!empty($contact['email'])): ?>
                                            <div class="small mb-1">
                                                <i class="fas fa-envelope mr-1"></i>
                                                <?= htmlspecialchars($contact['email']) ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($contact['phone_number'])): ?>
                                            <div class="small mb-1">
                                                <i class="fas fa-phone mr-1"></i>
                                                <?= htmlspecialchars($contact['phone_number']) ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($contact['address'])): ?>
                                            <div class="small">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                <?= htmlspecialchars($contact['address']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <?php if ($is_own_profile): ?>
                            <i class="fas fa-address-book"></i>
                            <h4>No Contacts</h4>
                            <p>You haven't added any contacts yet.</p>
                        <?php elseif ($is_friend): ?>
                            <i class="fas fa-user-friends"></i>
                            <h4>No Visible Contacts</h4>
                            <p><?= htmlspecialchars($profile['username']) ?> hasn't shared any contacts with friends.</p>
                            <p>They might have private contacts that only they can see.</p>
                        <?php else: ?>
                            <i class="fas fa-lock"></i>
                            <h4>Contacts are Private</h4>
                            <p><?= htmlspecialchars($profile['username']) ?> hasn't made any contacts public.</p>
                            <p>Become friends to see their friends-only contacts.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Friends Section -->
            <div class="section-card">
                <h2 class="section-title">
                    <i class="fas fa-users mr-2"></i>
                    <?php if ($is_own_profile): ?>
                        My Friends
                    <?php else: ?>
                        Mutual Friends
                    <?php endif; ?>
                    <span class="badge badge-light ml-2"><?= $mutual_count ?></span>
                </h2>
                
                <?php if ($mutual_count > 0): ?>
                    <div class="row">
                        <?php foreach ($mutual_friends as $friend): ?>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                <a href="view_profile.php?id=<?= $friend['user_id'] ?>" style="text-decoration: none;">
                                    <div class="friend-card">
                                        <div class="friend-avatar">
                                            <?= strtoupper(substr($friend['username'], 0, 1)) ?>
                                        </div>
                                        <div class="friend-name">
                                            <?= htmlspecialchars($friend['username']) ?>
                                        </div>
                                        <?php if (!$is_own_profile && $is_friend): ?>
                                            <small style="color: rgba(255, 255, 255, 0.7);">
                                                <i class="fas fa-handshake mr-1"></i> Mutual Friend
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (!$is_own_profile && $mutual_count == 20): ?>
                        <div class="text-center mt-3">
                            <small style="color: rgba(255, 255, 255, 0.7);">
                                Showing 20 mutual friends
                            </small>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <?php if ($is_own_profile): ?>
                            <i class="fas fa-user-friends"></i>
                            <h4>No Friends Yet</h4>
                            <p>You haven't added any friends yet. Start adding friends to build your network!</p>
                            <a href="find_friends.php" class="btn btn-primary mt-3">
                                <i class="fas fa-search mr-2"></i> Find Friends
                            </a>
                        <?php elseif ($is_friend): ?>
                            <i class="fas fa-user-friends"></i>
                            <h4>No Mutual Friends</h4>
                            <p>You and <?= htmlspecialchars($profile['username']) ?> don't have any mutual friends.</p>
                        <?php else: ?>
                            <i class="fas fa-user-friends"></i>
                            <h4>Friends are Private</h4>
                            <p>You need to be friends with <?= htmlspecialchars($profile['username']) ?> to see mutual friends.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!$is_own_profile && $is_friend && $total_friends_count > $mutual_count): ?>
                    <div class="text-center mt-4">
                        <small style="color: rgba(255, 255, 255, 0.7);">
                            <?= htmlspecialchars($profile['username']) ?> has <?= $total_friends_count ?> friends total.
                            You have <?= $mutual_count ?> mutual friends.
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include_once 'footer.php'; ?>
    
    <script>
        $(document).ready(function() {
            // Smooth scroll for anchor links
            $('a[href^="#"]').on('click', function(event) {
                if (this.hash !== "") {
                    event.preventDefault();
                    var hash = this.hash;
                    $('html, body').animate({
                        scrollTop: $(hash).offset().top - 100
                    }, 800);
                }
            });
        });
    </script>
</body>
</html>