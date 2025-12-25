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

$user_id = $_SESSION['user_id'];

// Fetch all contacts
$stmt = $db->prepare("SELECT * FROM contacts WHERE user_id = ? ORDER BY nickname");
$stmt->execute([$user_id]);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact List</title>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.3/css/all.min.css">

    <style>
        .glass-container {
            backdrop-filter: none !important;
        }
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

        .glass-table tbody tr {
            display: table-row;
        }

        .glass-table tbody tr.hidden {
            display: none;
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

        .contact-details {
            flex: 1;
        }

        .contact-name {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 5px;
        }

        .contact-name strong {
            font-size: 1.1rem;
        }

        .contact-fullname {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Visibility Badge Styles */
        .visibility-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-private {
            background: rgba(250, 0, 25, 0.6);
            color: #ffffffff;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .badge-friends {
            background: rgba(19, 214, 65, 0.43);
            color: #ffffffff;
            border: 1px solid rgba(40, 167, 70, 1);
        }

        .badge-public {
            background: rgba(8, 123, 245, 0.54);
            color: #fcfeffff;
            border: 1px solid rgba(0, 123, 255, 0.89);
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

        .empty-state.hidden {
            display: none;
        }

        .import-export-buttons {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .import-export-buttons .btn {
            border-radius: 50px;
            padding: 10px 20px;
            font-weight: 500;
        }

        /* Search bar styles */
        .search-container {
            flex-grow: 1;
            max-width: 400px;
        }

        .search-box {
            position: relative;
        }

        .search-box .form-control {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 50px;
            padding: 12px 45px 12px 20px;
            height: calc(1.5em + 1.25rem);
            transition: all 0.3s;
        }

        .search-box .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-box .form-control:focus {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
            color: white;
        }

        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            padding: 5px;
        }

        .search-btn:hover {
            color: white;
        }

        .clear-search {
            display: inline-flex;
            align-items: center;
            margin-left: 15px;
            color: white;
            text-decoration: none;
            opacity: 0.8;
        }

        .clear-search:hover {
            opacity: 1;
            text-decoration: none;
            color: white;
        }

        /* Search info box */
        #searchInfo {
            display: none;
        }

        /* Visibility filter buttons */
        .visibility-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 0.85rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .filter-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .filter-btn.active {
            background: rgba(255, 255, 255, 0.3);
            font-weight: 600;
        }

        .filter-btn i {
            font-size: 0.8rem;
        }

        /* Contact stats */
        .contact-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                gap: 8px;
            }
            
            .action-btn {
                min-width: 100%;
                padding: 10px;
            }
            
            .import-export-buttons {
                flex-direction: column;
            }
            
            .import-export-buttons .btn,
            .search-container {
                width: 100%;
                max-width: 100%;
            }
            
            .search-box .form-control {
                width: 100%;
            }
            
            .contact-name {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .contact-stats {
                flex-direction: column;
                gap: 10px;
            }
            
            .visibility-filter {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<?php include_once 'header.php'; ?>

<div class="main-container">
    <div class="container">
        <div class="glass-container">
            <!-- Import/Export Buttons and Search -->
            <div class="import-export-buttons">
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#importModal">
                    <i class="fas fa-file-import"></i> Import CSV
                </button>
                <a href="export_contacts.php" class="btn btn-primary">
                    <i class="fas fa-file-export"></i> Export CSV
                </a>
                <a href="export_template.php" class="btn btn-warning" target="_blank">
                    <i class="fas fa-file-download"></i> Download Template
                </a>
                
                <!-- Search Bar -->
                <div class="search-container">
                    <div class="search-box">
                        <input type="text" 
                               class="form-control" 
                               id="searchInput"
                               placeholder="Search contacts..." 
                               autocomplete="off">
                        <button type="button" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Visibility Filter Buttons -->
            <div class="visibility-filter">
                <button type="button" class="filter-btn active" data-visibility="all">
                    <i class="fas fa-globe"></i> All Contacts
                </button>
                <button type="button" class="filter-btn" data-visibility="public">
                    <i class="fas fa-eye"></i> Public
                </button>
                <button type="button" class="filter-btn" data-visibility="friends_only">
                    <i class="fas fa-user-friends"></i> Friends Only
                </button>
                <button type="button" class="filter-btn" data-visibility="private">
                    <i class="fas fa-lock"></i> Private
                </button>
            </div>

            <!-- Contact Stats -->
            <?php 
            // Count contacts by visibility
            $stats = [
                'total' => count($contacts),
                'public' => 0,
                'friends_only' => 0,
                'private' => 0
            ];
            
            foreach ($contacts as $contact) {
                switch ($contact['visibility']) {
                    case 'public':
                        $stats['public']++;
                        break;
                    case 'friends_only':
                        $stats['friends_only']++;
                        break;
                    case 'private':
                        $stats['private']++;
                        break;
                }
            }
            ?>
          

            <!-- Search results info -->
            <div id="searchInfo" class="alert alert-info" style="background: rgba(23, 162, 184, 0.2); color: white; border: none;">
                <i class="fas fa-search mr-2"></i>
                <span id="searchText">Type to search contacts...</span>
                <a href="javascript:void(0)" id="clearSearch" class="clear-search" style="display: none;">
                    <i class="fas fa-times ml-2"></i> Clear search
                </a>
                <span id="searchCount" class="float-right"></span>
            </div>

            <!-- Import Modal -->
            <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="importModalLabel">
                                <i class="fas fa-file-import"></i> Import Contacts from CSV
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="importForm" action="import_contacts.php" method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="csvFile">Select CSV File</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="csvFile" name="csvFile" accept=".csv" required>
                                        <label class="custom-file-label" for="csvFile" id="fileLabel">Choose file</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Download <a href="export_template.php" target="_blank">CSV template</a> for correct format
                                    </small>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="skipDuplicates" name="skip_duplicates" checked>
                                        <label class="custom-control-label" for="skipDuplicates">
                                            Skip duplicate emails
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <small>
                                        <strong>CSV Format Required:</strong><br>
                                        • Columns: full_name, email, phone_number, address, nickname<br>
                                        • First row must be headers<br>
                                        • File size limit: 5MB
                                    </small>
                                </div>
                                
                                <div id="importProgress" style="display: none;">
                                    <div class="progress mb-2">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                                    </div>
                                    <p class="text-center mb-0">
                                        <i class="fas fa-spinner fa-spin"></i> Importing contacts...
                                    </p>
                                </div>
                                
                                <div id="importResult" style="display: none;"></div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" form="importForm" class="btn btn-success" id="importBtn">
                                <i class="fas fa-upload"></i> Import Contacts
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <h1><i class="fas fa-address-book mr-3"></i>Contact List</h1>

            <!-- Contacts table -->
            <?php if ($contacts && count($contacts) > 0): ?>
                <div class="table-responsive" id="tableWrapper">
                    <table class="table glass-table" id="contactsTable">
                        <thead>
                            <tr>
                                <th>Contact</th>
                                <th>Phone Number</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contacts as $row): ?>
                                <tr class="contact-row" 
                                    data-name="<?= htmlspecialchars(strtolower($row['full_name'])) ?>"
                                    data-email="<?= htmlspecialchars(strtolower($row['email'])) ?>"
                                    data-phone="<?= htmlspecialchars(strtolower($row['phone_number'])) ?>"
                                    data-nickname="<?= htmlspecialchars(strtolower($row['nickname'])) ?>"
                                    data-visibility="<?= $row['visibility'] ?>">
                                    <td>
                                        <div class="contact-info">
                                            <div class="contact-avatar">
                                                <?= strtoupper(substr($row['nickname'], 0, 1)) ?>
                                            </div>
                                            <div class="contact-details">
                                                <div class="contact-name">
                                                    <strong><?= htmlspecialchars($row['nickname']) ?></strong>
                                                    <span class="visibility-badge badge-<?= 
                                                        $row['visibility'] == 'public' ? 'public' : 
                                                        ($row['visibility'] == 'friends_only' ? 'friends' : 'private')
                                                    ?>">
                                                        <i class="fas fa-<?= 
                                                            $row['visibility'] == 'public' ? 'eye' : 
                                                            ($row['visibility'] == 'friends_only' ? 'user-friends' : 'lock')
                                                        ?>"></i>
                                                        <?= ucfirst(str_replace('_', ' ', $row['visibility'])) ?>
                                                    </span>
                                                </div>
                                                <div class="contact-fullname">
                                                    <?= htmlspecialchars($row['full_name']) ?>
                                                </div>
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
                
                <!-- Empty search state (hidden by default) -->
                <div id="noResults" class="empty-state hidden">
                    <i class="fas fa-search fa-4x mb-3"></i>
                    <h3>No contacts found</h3>
                    <p>No matching results</p>
                    <button onclick="clearSearch()" class="btn btn-light mt-3">
                        <i class="fas fa-times mr-2"></i> Clear Search
                    </button>
                </div>
            <?php else: ?>
                <!-- No contacts at all -->
                <div class="empty-state">
                    <i class="fas fa-users fa-4x mb-3"></i>
                    <h3>No contacts found</h3>
                    <p>Add your first contact or import from CSV</p>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between mt-4">
                <div>
                    <a href="create_contact.php" class="btn btn-success create-btn">
                        <i class="fas fa-plus-circle mr-2"></i>Create New Contact
                    </a>
                </div>
                <div class="text-white">
                    <small><i class="fas fa-info-circle mr-1"></i> Showing: <span id="visibleContacts"><?= count($contacts) ?></span> of <span id="totalContacts"><?= count($contacts) ?></span> contacts</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once "footer.php"
?>

<script>
$(document).ready(function() {
    console.log('Document ready - Search script loaded');
    
    // Variables
    const searchInput = $('#searchInput');
    const searchInfo = $('#searchInfo');
    const searchText = $('#searchText');
    const searchCount = $('#searchCount');
    const clearSearchBtn = $('#clearSearch');
    const tableWrapper = $('#tableWrapper');
    const contactRows = $('.contact-row');
    const noResults = $('#noResults');
    const visibleContacts = $('#visibleContacts');
    const totalContacts = $('#totalContacts');
    const filterButtons = $('.filter-btn');
    
    // Get initial contact count
    const initialContactCount = <?= count($contacts) ?>;
    let visibleCount = initialContactCount;
    let currentFilter = 'all';
    let currentSearch = '';
    
    // Debug
    console.log('Contact rows found:', contactRows.length);
    console.log('Initial count:', initialContactCount);
    
    // Filter buttons click handler
    filterButtons.on('click', function() {
        const visibility = $(this).data('visibility');
        
        // Update active button
        filterButtons.removeClass('active');
        $(this).addClass('active');
        
        currentFilter = visibility;
        applyFilters();
    });
    
    // Search function
    function performSearch() {
        currentSearch = searchInput.val().trim().toLowerCase();
        console.log('Searching for:', currentSearch);
        applyFilters();
    }
    
    // Apply both search and filter
    function applyFilters() {
        let foundCount = 0;
        
        // Count contacts by visibility for stats
        let visibilityCounts = {
            'all': 0,
            'public': 0,
            'friends_only': 0,
            'private': 0
        };
        
        // Search and filter through each row
        contactRows.each(function() {
            const $row = $(this);
            const name = $row.data('name') || '';
            const email = $row.data('email') || '';
            const phone = $row.data('phone') || '';
            const nickname = $row.data('nickname') || '';
            const visibility = $row.data('visibility') || '';
            
            // Count for stats
            visibilityCounts['all']++;
            visibilityCounts[visibility]++;
            
            // Check search filter
            let searchMatches = true;
            if (currentSearch.length > 0) {
                searchMatches = name.includes(currentSearch) || 
                               email.includes(currentSearch) || 
                               String(phone).includes(currentSearch) || 
                               nickname.includes(currentSearch);
            }
            
            // Check visibility filter
            let visibilityMatches = true;
            if (currentFilter !== 'all') {
                visibilityMatches = (visibility === currentFilter);
            }
            
            if (searchMatches && visibilityMatches) {
                $row.removeClass('hidden');
                foundCount++;
            } else {
                $row.addClass('hidden');
            }
        });
        
        console.log('Found count:', foundCount);
        console.log('Visibility counts:', visibilityCounts);
        
        // Update UI
        visibleCount = foundCount;
        visibleContacts.text(foundCount);
        totalContacts.text(initialContactCount);
        
        // Show/hide table or no results message
        if (foundCount > 0) {
            if (tableWrapper.length) tableWrapper.show();
            noResults.addClass('hidden');
        } else {
            if (tableWrapper.length) tableWrapper.hide();
            noResults.removeClass('hidden');
        }
        
        // Update search info
        if (currentSearch.length > 0 || currentFilter !== 'all') {
            searchInfo.show();
            let searchTextContent = '';
            
            if (currentSearch.length > 0 && currentFilter !== 'all') {
                searchTextContent = `Showing ${currentFilter.replace('_', ' ')} contacts for: <strong>"${currentSearch}"</strong>`;
            } else if (currentSearch.length > 0) {
                searchTextContent = `Showing results for: <strong>"${currentSearch}"</strong>`;
            } else if (currentFilter !== 'all') {
                searchTextContent = `Showing only ${currentFilter.replace('_', ' ')} contacts`;
            }
            
            searchText.html(searchTextContent);
            searchCount.html('Found: ' + foundCount + ' contact(s)');
            clearSearchBtn.show();
        } else {
            searchInfo.hide();
            clearSearchBtn.hide();
            searchText.text('Type to search contacts...');
            searchCount.empty();
        }
        
        // Update filter button counts
        filterButtons.each(function() {
            const btnVisibility = $(this).data('visibility');
            const count = visibilityCounts[btnVisibility] || 0;
            const icon = $(this).find('i').clone();
            $(this).html(icon).append(' ' + btnVisibility.replace('_', ' ').charAt(0).toUpperCase() + btnVisibility.replace('_', ' ').slice(1));
            if (btnVisibility !== 'all') {
                $(this).append(' <small>(' + count + ')</small>');
            }
        });
    }
    
    // Clear search
    function clearSearch() {
        searchInput.val('');
        currentSearch = '';
        applyFilters();
    }
    
    // Clear filters
    function clearFilters() {
        filterButtons.removeClass('active');
        filterButtons.first().addClass('active');
        currentFilter = 'all';
        applyFilters();
    }
    
    // Event handlers
    searchInput.on('keyup', function() {
        performSearch();
    });
    
    $('.search-btn').on('click', function() {
        performSearch();
    });
    
    clearSearchBtn.on('click', function() {
        clearSearch();
    });
    
    // Clear search when pressing Escape
    searchInput.on('keydown', function(e) {
        if (e.key === 'Escape') {
            clearSearch();
            $(this).blur();
        }
    });

    // ========== Existing code for import modal ==========
    // Update file label when file is selected
    $('#csvFile').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $('#fileLabel').text(fileName || 'Choose file');
    });
    
    // Handle form submission with AJAX
    $('#importForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var importBtn = $('#importBtn');
        var originalText = importBtn.html();
        
        // Show progress
        $('#importProgress').show();
        $('#importResult').hide().empty();
        importBtn.prop('disabled', true);
        importBtn.html('<i class="fas fa-spinner fa-spin"></i> Importing...');
        
        // Submit via AJAX
        $.ajax({
            url: 'import_contacts.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#importProgress').hide();
                
                try {
                    var result = JSON.parse(response);
                    
                    if (result.success) {
                        $('#importResult').html(`
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle"></i> Import Successful!</h5>
                                <p><strong>Imported:</strong> ${result.imported} contacts</p>
                                <p><strong>Skipped:</strong> ${result.skipped} duplicates</p>
                                ${result.errors > 0 ? `<p><strong>Errors:</strong> ${result.errors} rows</p>` : ''}
                                ${result.error_details && result.error_details.length > 0 ? 
                                    `<div class="mt-2"><small><strong>Error details:</strong><br>${result.error_details.join('<br>')}</small></div>` : ''}
                            </div>
                        `);
                        
                        // Close modal after 3 seconds and refresh page
                        setTimeout(function() {
                            $('#importModal').modal('hide');
                            location.reload(); // Simple page reload
                        }, 3000);
                    } else {
                        $('#importResult').html(`
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-circle"></i> Import Failed</h5>
                                <p>${result.message}</p>
                                ${result.error_details && result.error_details.length > 0 ? 
                                    `<div class="mt-2"><small><strong>Errors:</strong><br>${result.error_details.join('<br>')}</small></div>` : ''}
                            </div>
                        `);
                    }
                } catch (e) {
                    console.error('Error parsing response:', response);
                    $('#importResult').html(`
                        <div class="alert alert-danger">
                            <h5>Error Processing Response</h5>
                            <p>Server returned invalid JSON. Please check console for details.</p>
                        </div>
                    `);
                }
                
                $('#importResult').show();
                importBtn.prop('disabled', false);
                importBtn.html(originalText);
            },
            error: function(xhr, status, error) {
                $('#importProgress').hide();
                $('#importResult').html(`
                    <div class="alert alert-danger">
                        <h5>Server Error</h5>
                        <p>Unable to process import. Please try again.</p>
                        <small>Error: ${error}</small>
                    </div>
                `).show();
                importBtn.prop('disabled', false);
                importBtn.html(originalText);
            }
        });
    });
    
    // Reset form when modal closes
    $('#importModal').on('hidden.bs.modal', function() {
        $('#importForm')[0].reset();
        $('#fileLabel').text('Choose file');
        $('#importProgress').hide();
        $('#importResult').hide().empty();
        $('#importBtn').prop('disabled', false).html('<i class="fas fa-upload"></i> Import Contacts');
    });
    
    // Initial filter application
    applyFilters();
});
</script>

</body>
</html>