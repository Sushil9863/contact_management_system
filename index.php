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
    <title>Contact List</title>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.3/css/all.min.css">

    <style>
        html, body {
            height: 100%;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }

        .main-container {
            flex: 1 0 auto;
        }

        .glass-container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 40px;
            margin-top: 50px;
            margin-bottom: 30px;
        }

        h1 {
            color: white;
            font-weight: 700;
            margin-bottom: 30px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 15px;
        }

        .glass-table {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            overflow: hidden;
        }

        .glass-table th,
        .glass-table td {
            color: white;
            border: none;
            padding: 15px;
            vertical-align: middle;
        }

        .glass-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .contact-info {
            display: flex;
            align-items: center;
        }

        .contact-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .action-btn {
            padding: 8px 15px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            min-width: 90px;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            color: white;
        }

        .view-btn { 
            background: linear-gradient(135deg, #17a2b8, #138496);
        }
        .view-btn:hover {
            background: linear-gradient(135deg, #138496, #117a8b);
        }

        .edit-btn { 
            background: linear-gradient(135deg, #007bff, #0069d9);
        }
        .edit-btn:hover {
            background: linear-gradient(135deg, #0069d9, #0056b3);
        }

        .delete-btn { 
            background: linear-gradient(135deg, #dc3545, #c82333);
        }
        .delete-btn:hover {
            background: linear-gradient(135deg, #c82333, #bd2130);
        }

        .btn-icon {
            margin-right: 5px;
            font-size: 0.85rem;
        }

        .create-btn {
            margin-top: 25px;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            background: linear-gradient(135deg, #28a745, #218838);
            border: none;
            transition: all 0.3s;
        }

        .create-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            background: linear-gradient(135deg, #218838, #1e7e34);
        }

        .empty-state {
            text-align: center;
            color: white;
            padding: 60px;
        }

        /* Responsive adjustments for action buttons */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                gap: 8px;
            }
            
            .action-btn {
                min-width: 100%;
                padding: 10px;
            }
            
            .glass-table th:nth-child(3),
            .glass-table td:nth-child(3) {
                min-width: 200px;
            }
        }
    </style>
</head>
<body>

<?php include_once 'header.php'; ?>

<div class="main-container">
    <div class="container">
        <div class="glass-container">
            <h1><i class="fas fa-address-book mr-3"></i>Contact List</h1>

            <?php
            include_once 'db.php';

            $user_id = $_SESSION['user_id'];

            $stmt = $db->prepare("SELECT * FROM contacts WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <?php if ($contacts && count($contacts) > 0): ?>
                <div class="table-responsive">
                    <table class="table glass-table">
                        <thead>
                            <tr>
                                <th>Contact</th>
                                <th>Phone Number</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contacts as $row): ?>
                                <tr>
                                    <td>
                                        <div class="contact-info">
                                            <div class="contact-avatar">
                                                <?= strtoupper(substr($row['nickname'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($row['nickname']) ?></strong><br>
                                                
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <i class="fas fa-phone mr-2"></i>
                                        <?= htmlspecialchars($row['phone_number']) ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="personal_info.php?id=<?= $row['id'] ?>" class="action-btn view-btn">
                                                <i class="fas fa-eye btn-icon"></i>View
                                            </a>
                                            <a href="edit_contact.php?id=<?= $row['id'] ?>" class="action-btn edit-btn">
                                                <i class="fas fa-edit btn-icon"></i>Edit
                                            </a>
                                            <a href="delete_contact.php?id=<?= $row['id'] ?>"
                                               class="action-btn delete-btn"
                                               onclick="return confirm('Delete this contact?')">
                                                <i class="fas fa-trash btn-icon"></i>Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users fa-4x mb-3"></i>
                    <h3>No contacts found</h3>
                    <p>Add your first contact</p>
                </div>
            <?php endif; ?>

            <a href="create_contact.php" class="btn btn-success create-btn">
                <i class="fas fa-plus-circle mr-2"></i>Create New Contact
            </a>
        </div>
    </div>
</div>

<?php
include_once "footer.php"
?>

</body>
</html>