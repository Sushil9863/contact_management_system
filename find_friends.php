<?php
// find_friends.php - CLEAN VERSION
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include_once 'db.php';
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle sending friend request
if (isset($_POST['send_request']) && isset($_POST['friend_id']) && isset($_POST['friend_username'])) {
    $friend_id = (int)$_POST['friend_id'];
    $friend_username = trim($_POST['friend_username']);
    
    // Check if request already exists
    $check_stmt = $db->prepare("SELECT * FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
    $check_stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
    
    if ($check_stmt->rowCount() == 0) {
        // Send friend request
        $stmt = $db->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')");
        if ($stmt->execute([$user_id, $friend_id])) {
            $success_message = "Friend request sent to " . htmlspecialchars($friend_username) . "!";
        } else {
            $error_message = "Database error: " . implode(", ", $stmt->errorInfo());
        }
    } else {
        $error_message = "Friend request already exists or you are already friends.";
    }
}

// Search functionality
$search_results = [];
$search_query = "";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = trim($_GET['search']);
    $search_term = "%$search_query%";
    
    $search_stmt = $db->prepare("
        SELECT u.user_id, u.username 
        FROM user_detail u
        WHERE u.username LIKE ?
        AND u.user_id != ?
        AND u.user_id NOT IN (
            SELECT friend_id FROM friends WHERE user_id = ?
            UNION
            SELECT user_id FROM friends WHERE friend_id = ?
        )
        ORDER BY u.username
        LIMIT 50
    ");
    $search_stmt->execute([$search_term, $user_id, $user_id, $user_id]);
    $search_results = $search_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get suggested friends
$suggested_stmt = $db->prepare("
    SELECT u.user_id, u.username
    FROM user_detail u
    WHERE u.user_id != ?
    AND u.user_id NOT IN (
        SELECT friend_id FROM friends WHERE user_id = ?
        UNION
        SELECT user_id FROM friends WHERE friend_id = ?
    )
    ORDER BY RAND()
    LIMIT 10
");
$suggested_stmt->execute([$user_id, $user_id, $user_id]);
$suggested_friends = $suggested_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Find Friends - ContactFlow</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            padding-top: 20px;
        }
        
        .find-friends-container {
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
        
        /* Search Box */
        .search-box-container {
            position: relative;
            margin-bottom: 30px;
        }
        
        .search-input {
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50px;
            color: white;
            padding: 15px 140px 15px 25px;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 0 0.3rem rgba(255, 255, 255, 0.1);
            outline: none;
        }
        
        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 50px;
            color: white;
            padding: 12px 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 120px;
        }
        
        .search-btn:hover {
            background: linear-gradient(135deg, #764ba2, #667eea);
            transform: translateY(-50%) scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        /* User Card */
        .user-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .user-card:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            color: white;
            margin-right: 20px;
            flex-shrink: 0;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-name {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .user-stats {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .add-friend-btn {
            background: linear-gradient(135deg, #28a745, #218838);
            border: none;
            border-radius: 50px;
            color: white;
            padding: 12px 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 1rem;
        }
        
        .add-friend-btn:hover {
            background: linear-gradient(135deg, #218838, #1e7e34);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
            color: white;
            text-decoration: none;
        }
        
        /* Section Titles */
        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 25px;
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
        
        /* Results Count */
        .results-count {
            font-size: 1.1rem;
            margin-bottom: 20px;
            opacity: 0.8;
            padding-left: 10px;
        }
        
        /* Alert Messages */
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .user-card {
                flex-direction: column;
                text-align: center;
                padding: 20px;
            }
            
            .user-avatar {
                margin-right: 0;
                margin-bottom: 15px;
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
            }
            
            .user-stats {
                justify-content: center;
                margin-top: 15px;
            }
            
            .add-friend-btn {
                margin-top: 15px;
                width: 100%;
                justify-content: center;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
            
            .search-input {
                padding: 15px 100px 15px 20px;
            }
            
            .search-btn {
                min-width: 90px;
                padding: 10px 15px;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 480px) {
            .search-input {
                padding: 12px 80px 12px 15px;
                font-size: 1rem;
            }
            
            .search-btn {
                min-width: 70px;
                padding: 8px 12px;
                font-size: 0.85rem;
            }
            
            .search-btn i {
                margin-right: 3px;
            }
        }
    </style>
</head>
<body>
    <?php include_once 'header.php'; ?>
    
    <div class="find-friends-container">
        <h1 class="page-title"><i class="fas fa-search mr-3"></i>Find Friends</h1>
        
        <!-- Alert Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <?= htmlspecialchars($success_message) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?= htmlspecialchars($error_message) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <div class="glass-card">
            <!-- Search Section -->
            <div class="search-section mb-5">
                <h2 class="section-title">Search for Friends</h2>
                <p class="mb-4" style="opacity: 0.9; font-size: 1.1rem; padding-left: 10px;">
                    Search by username to find and connect with other users.
                </p>
                
                <form method="GET" action="" class="search-box-container">
                    <input type="text" 
                           name="search" 
                           class="search-input" 
                           placeholder="Enter username to search..." 
                           value="<?= htmlspecialchars($search_query) ?>"
                           autocomplete="off">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                </form>
                
                <?php if (!empty($search_query)): ?>
                    <div class="results-count">
                        Found <?= count($search_results) ?> result<?= count($search_results) != 1 ? 's' : '' ?> for "<?= htmlspecialchars($search_query) ?>"
                    </div>
                <?php endif; ?>
                
                <!-- Search Results -->
                <?php if (!empty($search_query)): ?>
                    <?php if (count($search_results) > 0): ?>
                        <div class="search-results">
                            <?php foreach ($search_results as $user): ?>
                                <div class="user-card">
                                    <div class="user-avatar">
                                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                    </div>
                                    <div class="user-info">
                                        <div class="user-name"><?= htmlspecialchars($user['username']) ?></div>
                                        <div class="user-stats">
                                            <span class="stat-item">
                                                <i class="fas fa-user"></i> Member
                                            </span>
                                        </div>
                                    </div>
                                    <!-- SIMPLE FORM - NO JAVASCRIPT -->
                                    <form method="POST" action="">
                                        <input type="hidden" name="friend_id" value="<?= $user['user_id'] ?>">
                                        <input type="hidden" name="friend_username" value="<?= htmlspecialchars($user['username']) ?>">
                                        <button type="submit" name="send_request" class="add-friend-btn">
                                            <i class="fas fa-user-plus"></i> Add Friend
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <h4>No users found</h4>
                            <p>No users found matching "<?= htmlspecialchars($search_query) ?>"</p>
                            <p>Try a different search term or check out suggested friends below.</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Suggested Friends -->
            <div class="suggested-section">
                <h2 class="section-title">Suggested Friends</h2>
                <p class="mb-4" style="opacity: 0.9; font-size: 1.1rem; padding-left: 10px;">
                    People you might know based on your network.
                </p>
                
                <?php if (count($suggested_friends) > 0): ?>
                    <div class="suggested-friends">
                        <?php foreach ($suggested_friends as $user): ?>
                            <div class="user-card">
                                <div class="user-avatar">
                                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                </div>
                                <div class="user-info">
                                    <div class="user-name"><?= htmlspecialchars($user['username']) ?></div>
                                    <div class="user-stats">
                                        <span class="stat-item">
                                            <i class="fas fa-user"></i> Member
                                        </span>
                                    </div>
                                </div>
                                <!-- SIMPLE FORM - NO JAVASCRIPT -->
                                <form method="POST" action="">
                                    <input type="hidden" name="friend_id" value="<?= $user['user_id'] ?>">
                                    <input type="hidden" name="friend_username" value="<?= htmlspecialchars($user['username']) ?>">
                                    <button type="submit" name="send_request" class="add-friend-btn">
                                        <i class="fas fa-user-plus"></i> Add Friend
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h4>No suggestions available</h4>
                        <p>We couldn't find any suggested friends at the moment.</p>
                        <p>Try searching for specific users above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="glass-card">
            <h2 class="section-title">Quick Actions</h2>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <a href="friends.php" class="btn btn-primary btn-block" style="
                        background: linear-gradient(135deg, #007bff, #0056b3);
                        border: none;
                        border-radius: 15px;
                        padding: 20px;
                        text-align: left;
                        color: white;
                        text-decoration: none;
                        display: flex;
                        align-items: center;
                        transition: all 0.3s ease;
                    ">
                        <i class="fas fa-users fa-2x mr-3"></i>
                        <div>
                            <h5 class="mb-1">My Friends</h5>
                            <small>View and manage your friends list</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 mb-3">
                    <a href="profile.php" class="btn btn-info btn-block" style="
                        background: linear-gradient(135deg, #17a2b8, #117a8b);
                        border: none;
                        border-radius: 15px;
                        padding: 20px;
                        text-align: left;
                        color: white;
                        text-decoration: none;
                        display: flex;
                        align-items: center;
                        transition: all 0.3s ease;
                    ">
                        <i class="fas fa-user-circle fa-2x mr-3"></i>
                        <div>
                            <h5 class="mb-1">My Profile</h5>
                            <small>View your profile and stats</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include_once 'footer.php'; ?>
    
    <script>
        // Simple focus on search input
        document.addEventListener('DOMContentLoaded', function() {
            var searchInput = document.querySelector('input[name="search"]');
            if (searchInput) searchInput.focus();
        });
    </script>
</body>
</html>