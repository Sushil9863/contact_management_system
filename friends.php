<?php
// friends.php - CORRECTED FOR YOUR TABLE STRUCTURE
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include_once 'db.php';
$user_id = $_SESSION['user_id'];

// Handle actions
if (isset($_GET['action']) && isset($_GET['friend_id'])) {
    $friend_id = (int)$_GET['friend_id'];
    $action = $_GET['action'];
    
    switch ($action) {
        case 'accept':
            $stmt = $db->prepare("UPDATE friends SET status = 'accepted' WHERE user_id = ? AND friend_id = ?");
            $stmt->execute([$friend_id, $user_id]);
            break;
            
        case 'reject':
        case 'remove':
            $stmt = $db->prepare("DELETE FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
            $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
            break;
            
        case 'block':
            $stmt = $db->prepare("UPDATE friends SET status = 'blocked' WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
            $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
            break;
            
        case 'unblock':
            $stmt = $db->prepare("DELETE FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
            $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
            break;
    }
    
    header('Location: friends.php');
    exit;
}

// Get accepted friends
$friends_stmt = $db->prepare("
    SELECT u.user_id, u.username, f.created_at
    FROM friends f
    JOIN user_detail u ON (
        (f.user_id = u.user_id AND f.friend_id = ?) OR 
        (f.friend_id = u.user_id AND f.user_id = ?)
    )
    WHERE (f.user_id = ? OR f.friend_id = ?) 
    AND u.user_id != ?
    AND f.status = 'accepted'
    ORDER BY u.username
");
$friends_stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
$friends = $friends_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending friend requests (incoming)
$pending_stmt = $db->prepare("
    SELECT u.user_id, u.username, f.created_at
    FROM friends f
    JOIN user_detail u ON f.user_id = u.user_id
    WHERE f.friend_id = ? 
    AND f.status = 'pending'
    ORDER BY f.created_at DESC
");
$pending_stmt->execute([$user_id]);
$pending_requests = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get sent friend requests (outgoing)
$sent_stmt = $db->prepare("
    SELECT u.user_id, u.username, f.created_at
    FROM friends f
    JOIN user_detail u ON f.friend_id = u.user_id
    WHERE f.user_id = ? 
    AND f.status = 'pending'
    ORDER BY f.created_at DESC
");
$sent_stmt->execute([$user_id]);
$sent_requests = $sent_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get blocked users
$blocked_stmt = $db->prepare("
    SELECT u.user_id, u.username, f.created_at
    FROM friends f
    JOIN user_detail u ON (
        (f.user_id = u.user_id AND f.friend_id = ?) OR 
        (f.friend_id = u.user_id AND f.user_id = ?)
    )
    WHERE (f.user_id = ? OR f.friend_id = ?)
    AND u.user_id != ?
    AND f.status = 'blocked'
    ORDER BY u.username
");
$blocked_stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
$blocked_users = $blocked_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Friends - ContactFlow</title>
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
        
        .friends-container {
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
        
        /* Friend Card Styles */
        .friend-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .friend-card:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .friend-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .friend-info {
            flex: 1;
        }
        
        .friend-name {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 1.2rem;
        }
        
        .friend-date {
            font-size: 0.85rem;
            opacity: 0.6;
        }
        
        .friend-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            text-decoration: none;
        }
        
        .btn-accept {
            background: linear-gradient(135deg, #28a745, #218838);
            color: white;
        }
        
        .btn-reject {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        
        .btn-remove {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: white;
        }
        
        .btn-block {
            background: linear-gradient(135deg, #6c757d, #545b62);
            color: white;
        }
        
        .btn-unblock {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
        }
        
        .btn-view {
            background: linear-gradient(135deg, #007bff, #0069d9);
            color: white;
        }
        
        /* Tabs Styles */
        .nav-tabs {
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 30px;
        }
        
        .nav-tabs .nav-link {
            color: rgba(255, 255, 255, 0.7);
            border: none;
            border-radius: 10px 10px 0 0;
            margin-right: 5px;
            padding: 12px 25px;
            font-weight: 500;
            background: transparent;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .nav-tabs .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-bottom: 3px solid #667eea;
        }
        
        .badge-count {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 3px 10px;
            font-size: 0.8rem;
            margin-left: 8px;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h4 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 25px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .btn-find-friends {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 50px;
            padding: 12px 35px;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .btn-find-friends:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            color: white;
            text-decoration: none;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .friend-card {
                flex-direction: column;
                text-align: center;
            }
            
            .friend-avatar {
                margin-right: 0;
                margin-bottom: 15px;
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }
            
            .friend-actions {
                justify-content: center;
                margin-top: 15px;
            }
            
            .nav-tabs .nav-link {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php include_once 'header.php'; ?>
    
    <div class="friends-container">
        <h1 class="page-title"><i class="fas fa-users mr-3"></i>My Friends</h1>
        
        <div class="glass-card">
            <!-- Tabs -->
            <ul class="nav nav-tabs" id="friendsTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="friends-tab" data-toggle="tab" href="#friends" role="tab">
                        <i class="fas fa-user-friends mr-2"></i>Friends
                        <span class="badge-count"><?= count($friends) ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pending-tab" data-toggle="tab" href="#pending" role="tab">
                        <i class="fas fa-clock mr-2"></i>Pending Requests
                        <span class="badge-count"><?= count($pending_requests) ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="sent-tab" data-toggle="tab" href="#sent" role="tab">
                        <i class="fas fa-paper-plane mr-2"></i>Sent Requests
                        <span class="badge-count"><?= count($sent_requests) ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="blocked-tab" data-toggle="tab" href="#blocked" role="tab">
                        <i class="fas fa-ban mr-2"></i>Blocked
                        <span class="badge-count"><?= count($blocked_users) ?></span>
                    </a>
                </li>
            </ul>
            
            <!-- Tab Content -->
            <div class="tab-content mt-4" id="friendsTabContent">
                <!-- Friends Tab -->
                <div class="tab-pane fade show active" id="friends" role="tabpanel">
                    <h3 class="section-title">My Friends</h3>
                    
                    <?php if (count($friends) > 0): ?>
                        <?php foreach ($friends as $friend): ?>
                            <div class="friend-card d-flex align-items-center">
                                <div class="friend-avatar">
                                    <?= strtoupper(substr($friend['username'], 0, 1)) ?>
                                </div>
                                <div class="friend-info">
                                    <div class="friend-name"><?= htmlspecialchars($friend['username']) ?></div>
                                    <div class="friend-date">
                                        Friends since: <?= date('M d, Y', strtotime($friend['created_at'])) ?>
                                    </div>
                                </div>
                                <div class="friend-actions">
                                    <a href="view_profile.php?id=<?= $friend['user_id'] ?>" class="action-btn btn-view">
                                        <i class="fas fa-eye mr-1"></i> View Profile
                                    </a>
                                    <a href="friends.php?action=remove&friend_id=<?= $friend['user_id'] ?>" 
                                       class="action-btn btn-remove"
                                       onclick="return confirm('Remove <?= htmlspecialchars($friend['username']) ?> from friends?')">
                                        <i class="fas fa-user-times mr-1"></i> Remove
                                    </a>
                                    <a href="friends.php?action=block&friend_id=<?= $friend['user_id'] ?>" 
                                       class="action-btn btn-block"
                                       onclick="return confirm('Block <?= htmlspecialchars($friend['username']) ?>?')">
                                        <i class="fas fa-ban mr-1"></i> Block
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-friends"></i>
                            <h4>No friends yet</h4>
                            <p>Start adding friends to see them here. Friends can share contacts with you!</p>
                            <a href="find_friends.php" class="btn-find-friends">
                                <i class="fas fa-search mr-2"></i> Find Friends
                            </a>
                        </div>  
                    <?php endif; ?>
                </div>
                
                <!-- Pending Requests Tab -->
                <div class="tab-pane fade" id="pending" role="tabpanel">
                    <h3 class="section-title">Friend Requests</h3>
                    
                    <?php if (count($pending_requests) > 0): ?>
                        <?php foreach ($pending_requests as $request): ?>
                            <div class="friend-card d-flex align-items-center">
                                <div class="friend-avatar">
                                    <?= strtoupper(substr($request['username'], 0, 1)) ?>
                                </div>
                                <div class="friend-info">
                                    <div class="friend-name"><?= htmlspecialchars($request['username']) ?></div>
                                    <div class="friend-date">
                                        Requested: <?= date('M d, Y', strtotime($request['created_at'])) ?>
                                    </div>
                                </div>
                                <div class="friend-actions">
                                    <a href="friends.php?action=accept&friend_id=<?= $request['user_id'] ?>" 
                                       class="action-btn btn-accept">
                                        <i class="fas fa-check mr-1"></i> Accept
                                    </a>
                                    <a href="friends.php?action=reject&friend_id=<?= $request['user_id'] ?>" 
                                       class="action-btn btn-reject"
                                       onclick="return confirm('Reject friend request from <?= htmlspecialchars($request['username']) ?>?')">
                                        <i class="fas fa-times mr-1"></i> Reject
                                    </a>
                                    <a href="view_profile.php?id=<?= $request['user_id'] ?>" class="action-btn btn-view">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h4>No pending requests</h4>
                            <p>You don't have any pending friend requests at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Sent Requests Tab -->
                <div class="tab-pane fade" id="sent" role="tabpanel">
                    <h3 class="section-title">Sent Requests</h3>
                    
                    <?php if (count($sent_requests) > 0): ?>
                        <?php foreach ($sent_requests as $request): ?>
                            <div class="friend-card d-flex align-items-center">
                                <div class="friend-avatar">
                                    <?= strtoupper(substr($request['username'], 0, 1)) ?>
                                </div>
                                <div class="friend-info">
                                    <div class="friend-name"><?= htmlspecialchars($request['username']) ?></div>
                                    <div class="friend-date">
                                        Sent: <?= date('M d, Y', strtotime($request['created_at'])) ?>
                                    </div>
                                </div>
                                <div class="friend-actions">
                                    <a href="friends.php?action=remove&friend_id=<?= $request['user_id'] ?>" 
                                       class="action-btn btn-reject"
                                       onclick="return confirm('Cancel friend request to <?= htmlspecialchars($request['username']) ?>?')">
                                        <i class="fas fa-times mr-1"></i> Cancel Request
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-paper-plane"></i>
                            <h4>No sent requests</h4>
                            <p>You haven't sent any friend requests yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Blocked Users Tab -->
                <div class="tab-pane fade" id="blocked" role="tabpanel">
                    <h3 class="section-title">Blocked Users</h3>
                    
                    <?php if (count($blocked_users) > 0): ?>
                        <?php foreach ($blocked_users as $user): ?>
                            <div class="friend-card d-flex align-items-center">
                                <div class="friend-avatar">
                                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                </div>
                                <div class="friend-info">
                                    <div class="friend-name"><?= htmlspecialchars($user['username']) ?></div>
                                    <div class="friend-date">
                                        Blocked: <?= date('M d, Y', strtotime($user['created_at'])) ?>
                                    </div>
                                </div>
                                <div class="friend-actions">
                                    <a href="friends.php?action=unblock&friend_id=<?= $user['user_id'] ?>" 
                                       class="action-btn btn-unblock"
                                       onclick="return confirm('Unblock <?= htmlspecialchars($user['username']) ?>?')">
                                        <i class="fas fa-unlock mr-1"></i> Unblock
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-ban"></i>
                            <h4>No blocked users</h4>
                            <p>You haven't blocked anyone.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Find Friends Button -->
            <div class="text-center mt-5">
                <a href="find_friends.php" class="btn-find-friends">
                    <i class="fas fa-user-plus mr-2"></i> Find New Friends
                </a>
            </div>
        </div>
    </div>
    
    <?php include_once 'footer.php'; ?>
    
    <script>
        // Activate tab from URL hash
        $(document).ready(function() {
            var hash = window.location.hash;
            if (hash) {
                $('.nav-tabs a[href="' + hash + '"]').tab('show');
            }
            
            // Handle tab click to update URL
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                var hash = $(e.target).attr('href');
                if (history.pushState) {
                    history.pushState(null, null, hash);
                } else {
                    location.hash = hash;
                }
            });
        });
    </script>
</body>
</html>