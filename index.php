<?php
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

    <!-- Font Awesome (local) -->
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">

    <style>
        /* Global & glass container */
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
            backdrop-filter: blur(10px);
        }
        h1 {
            color: white;
            font-weight: 700;
            margin-bottom: 30px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 15px;
        }

        /* Import/Export bar */
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

        /* Search bar */
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

        /* Contact cards grid */
        .contacts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .contact-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 20px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.2s, box-shadow 0.2s;
            color: white;
            display: flex;
            flex-direction: column;
        }
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.15);
        }
        .contact-card.hidden {
            display: none;
        }

        /* Card header: avatar + name + heart */
        .card-header-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        .contact-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.5rem;
            color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            flex-shrink: 0;
        }
        .name-and-fav {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .contact-nickname {
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0;
            line-height: 1.2;
        }
        .favorite-toggle {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: transform 0.2s;
        }
        .favorite-toggle:hover {
            transform: scale(1.15);
        }
        .favorite-toggle i {
            font-size: 1.6rem;
        }

        /* Visibility badge */
        .visibility-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
        }
        .badge-private {
            background: rgba(220, 53, 69, 0.3);
        }
        .badge-friends {
            background: rgba(40, 167, 69, 0.3);
        }
        .badge-public {
            background: rgba(0, 123, 255, 0.3);
        }

        /* Card body */
        .contact-fullname {
            font-size: 0.95rem;
            opacity: 0.9;
            margin-bottom: 8px;
        }
        .contact-phone {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
            font-size: 1rem;
            background: rgba(0,0,0,0.1);
            padding: 8px 12px;
            border-radius: 30px;
            width: fit-content;
        }

        /* Action buttons row */
        .card-actions {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-top: auto;
        }
        .action-btn {
            padding: 8px 15px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            flex: 1;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            color: white;
        }
        .view-btn { background: linear-gradient(135deg, #17a2b8, #138496); }
        .edit-btn { background: linear-gradient(135deg, #007bff, #0069d9); }
        .delete-btn { background: linear-gradient(135deg, #dc3545, #c82333); }
        .btn-icon { margin-right: 5px; font-size: 0.85rem; }

        /* Empty states */
        .empty-state {
            text-align: center;
            color: white;
            padding: 60px;
        }
        .empty-state.hidden {
            display: none;
        }

        /* Create button */
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
        }

        /* Responsive */
        @media (max-width: 768px) {
            .contacts-grid {
                grid-template-columns: 1fr;
            }
            .import-export-buttons {
                flex-direction: column;
            }
            .import-export-buttons .btn,
            .search-container {
                width: 100%;
                max-width: 100%;
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

            <!-- Visibility Filter Buttons (including Favorites) -->
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
                <!-- New Favorites button -->
                <button type="button" class="filter-btn" data-visibility="favorite">
                    <i class="fas fa-heart" style="color: #ff4444;"></i> Favorites
                </button>
            </div>

            <!-- Search results info -->
            <div id="searchInfo" class="alert alert-info" style="background: rgba(23, 162, 184, 0.2); color: white; border: none;">
                <i class="fas fa-search mr-2"></i>
                <span id="searchText">Type to search contacts...</span>
                <!-- <a href="javascript:void(0)" id="clearSearch" class="clear-search" style="display: none;">
                    <i class="fas fa-times ml-2"></i> Clear search
                </a> -->
                <span id="searchCount" class="float-right"></span>
            </div>

            <!-- Import Modal (unchanged) -->
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

            <h1><i class="fas fa-address-book mr-3"></i>My Contacts</h1>

            <!-- Contacts Grid (cards) -->
            <?php if ($contacts && count($contacts) > 0): ?>
                <div class="contacts-grid" id="contactsGrid">
                    <?php foreach ($contacts as $row): ?>
                        <div class="contact-card contact-row" 
                             data-name="<?= htmlspecialchars(strtolower($row['full_name'])) ?>"
                             data-email="<?= htmlspecialchars(strtolower($row['email'])) ?>"
                             data-phone="<?= htmlspecialchars(strtolower($row['phone_number'])) ?>"
                             data-nickname="<?= htmlspecialchars(strtolower($row['nickname'])) ?>"
                             data-visibility="<?= $row['visibility'] ?>"
                             data-favorite="<?= $row['is_favorite'] ?>">   <!-- added data-favorite -->
                            
                            <!-- Header row with avatar, name and heart -->
                            <div class="card-header-row">
                                <div class="contact-avatar">
                                    <?= strtoupper(substr($row['nickname'], 0, 1)) ?>
                                </div>
                                <div class="name-and-fav">
                                    <span class="contact-nickname"><?= htmlspecialchars($row['nickname']) ?></span>
                                    <a href="javascript:void(0)"
                                       class="favorite-toggle"
                                       data-id="<?= $row['id'] ?>"
                                       data-favorite="<?= $row['is_favorite'] ?>"
                                       title="<?= $row['is_favorite'] ? 'Remove from favorites' : 'Add to favorites' ?>">
                                        <i class="<?= $row['is_favorite'] ? 'fas' : 'far' ?> fa-heart"
                                           style="color: <?= $row['is_favorite'] ? '#ff4444' : '#555555' ?>; font-size: 1.5rem;"></i>
                                    </a>
                                </div>
                            </div>

                            <!-- Full name & visibility badge -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="contact-fullname"><?= htmlspecialchars($row['full_name']) ?></span>
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

                            <!-- Phone number -->
                            <div class="contact-phone">
                                <i class="fas fa-phone-alt"></i>
                                <?= htmlspecialchars($row['phone_number']) ?>
                            </div>

                            <!-- Action buttons -->
                            <div class="card-actions">
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
                        </div>
                    <?php endforeach; ?>
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
                    <h3>No contacts yet</h3>
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

<?php include_once "footer.php"; ?>

<script>
$(document).ready(function() {
    // ---------- Search & Filter ----------
    const searchInput = $('#searchInput');
    const searchInfo = $('#searchInfo');
    const searchText = $('#searchText');
    const searchCount = $('#searchCount');
    const clearSearchBtn = $('#clearSearch');
    const contactCards = $('.contact-row');
    const noResults = $('#noResults');
    const visibleContacts = $('#visibleContacts');
    const totalContacts = $('#totalContacts');
    const filterButtons = $('.filter-btn');
    
    const initialContactCount = <?= count($contacts) ?>;
    let currentFilter = 'all';
    let currentSearch = '';
    
    // Filter buttons click
    filterButtons.on('click', function() {
        const visibility = $(this).data('visibility');
        filterButtons.removeClass('active');
        $(this).addClass('active');
        currentFilter = visibility;
        applyFilters();
    });
    
    function performSearch() {
        currentSearch = searchInput.val().trim().toLowerCase();
        applyFilters();
    }
    
    function applyFilters() {
        let foundCount = 0;
        let visibilityCounts = { all: 0, public: 0, friends_only: 0, private: 0, favorite: 0 };
        
        contactCards.each(function() {
            const $card = $(this);
            const name = $card.data('name') || '';
            const email = $card.data('email') || '';
            const phone = $card.data('phone') || '';
            const nickname = $card.data('nickname') || '';
            const visibility = $card.data('visibility') || '';
            const favorite = $card.data('favorite') || 0;
            
            // Count for stats (excluding favorite because it's not a visibility type)
            visibilityCounts.all++;
            if (visibility === 'public') visibilityCounts.public++;
            else if (visibility === 'friends_only') visibilityCounts.friends_only++;
            else if (visibility === 'private') visibilityCounts.private++;
            if (favorite == 1) visibilityCounts.favorite++;
            
            // Check search filter
            let searchMatches = true;
            if (currentSearch.length > 0) {
                searchMatches = name.includes(currentSearch) || 
                               email.includes(currentSearch) || 
                               String(phone).includes(currentSearch) || 
                               nickname.includes(currentSearch);
            }
            
            // Check filter (visibility or favorite)
            let filterMatches = true;
            if (currentFilter === 'favorite') {
                filterMatches = (favorite == 1);
            } else if (currentFilter !== 'all') {
                filterMatches = (visibility === currentFilter);
            }
            
            if (searchMatches && filterMatches) {
                $card.removeClass('hidden');
                foundCount++;
            } else {
                $card.addClass('hidden');
            }
        });
        
        visibleContacts.text(foundCount);
        totalContacts.text(initialContactCount);
        
        if (foundCount > 0) {
            noResults.addClass('hidden');
        } else {
            noResults.removeClass('hidden');
        }
        
        // Update search info bar
        if (currentSearch.length > 0 || currentFilter !== 'all') {
            searchInfo.show();
            let msg = '';
            if (currentSearch.length > 0 && currentFilter !== 'all') {
                let filterName = currentFilter === 'favorite' ? 'favorites' : currentFilter.replace('_', ' ');
                msg = `Showing ${filterName} for: <strong>"${currentSearch}"</strong>`;
            } else if (currentSearch.length > 0) {
                msg = `Showing results for: <strong>"${currentSearch}"</strong>`;
            } else if (currentFilter !== 'all') {
                let filterName = currentFilter === 'favorite' ? 'favorites' : currentFilter.replace('_', ' ');
                msg = `Showing only ${filterName}`;
            }
            searchText.html(msg);
            searchCount.html('Found: ' + foundCount + ' contact(s)');
            clearSearchBtn.show();
        } else {
            searchInfo.hide();
            clearSearchBtn.hide();
        }
        
        // Update filter button counts (for non-favorite filters)
        filterButtons.each(function() {
            const btnVis = $(this).data('visibility');
            const cnt = visibilityCounts[btnVis] || 0;
            const icon = $(this).find('i').clone();
            let text = btnVis === 'all' ? 'All Contacts' : 
                       (btnVis === 'favorite' ? 'Favorites' : 
                       btnVis.replace('_',' ').charAt(0).toUpperCase() + btnVis.replace('_',' ').slice(1));
            $(this).html(icon).append(' ' + text);
            if (btnVis !== 'all') {
                $(this).append(' <small>(' + cnt + ')</small>');
            }
        });
    }
    
    function clearSearch() {
        searchInput.val('');
        currentSearch = '';
        applyFilters();
    }
    
    // Event listeners
    searchInput.on('keyup', performSearch);
    $('.search-btn').on('click', performSearch);
    clearSearchBtn.on('click', clearSearch);
    searchInput.on('keydown', function(e) { if (e.key === 'Escape') clearSearch(); });
    
    // ---------- Import Modal ----------
    $('#csvFile').on('change', function() {
        $('#fileLabel').text($(this).val().split('\\').pop() || 'Choose file');
    });
    
    $('#importForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var importBtn = $('#importBtn');
        var originalText = importBtn.html();
        
        $('#importProgress').show();
        $('#importResult').hide().empty();
        importBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');
        
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
                                ${result.error_details ? `<div><small>${result.error_details.join('<br>')}</small></div>` : ''}
                            </div>
                        `);
                        setTimeout(() => { $('#importModal').modal('hide'); location.reload(); }, 3000);
                    } else {
                        $('#importResult').html(`
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-circle"></i> Import Failed</h5>
                                <p>${result.message}</p>
                                ${result.error_details ? `<small>${result.error_details.join('<br>')}</small>` : ''}
                            </div>
                        `);
                    }
                } catch (e) {
                    $('#importResult').html('<div class="alert alert-danger">Invalid server response.</div>');
                }
                $('#importResult').show();
                importBtn.prop('disabled', false).html(originalText);
            },
            error: function() {
                $('#importProgress').hide();
                $('#importResult').html('<div class="alert alert-danger">Server error. Please try again.</div>').show();
                importBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    $('#importModal').on('hidden.bs.modal', function() {
        $('#importForm')[0].reset();
        $('#fileLabel').text('Choose file');
        $('#importProgress').hide();
        $('#importResult').hide().empty();
        $('#importBtn').prop('disabled', false).html('<i class="fas fa-upload"></i> Import Contacts');
    });
    
    // ---------- Favorite Toggle ----------
    $(document).on('click', '.favorite-toggle', function(e) {
        e.preventDefault();
        const $link = $(this);
        const contactId = $link.data('id');
        const $icon = $link.find('i');
        
        $link.css('pointer-events', 'none');
        
        $.ajax({
            url: 'toggle_favorite.php',
            type: 'POST',
            data: { contact_id: contactId },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    const newFav = res.new_favorite;
                    $link.data('favorite', newFav);
                    $link.attr('title', newFav ? 'Remove from favorites' : 'Add to favorites');
                    // Also update the data-favorite attribute on the parent card for filter
                    $link.closest('.contact-card').attr('data-favorite', newFav);
                    if (newFav == 1) {
                        $icon.removeClass('far').addClass('fas').css('color', '#ff4444');
                    } else {
                        $icon.removeClass('fas').addClass('far').css('color', '#555555');
                    }
                    // Reapply filters to reflect the change (if favorite filter is active)
                    applyFilters();
                } else {
                    alert('Error: ' + res.message);
                }
            },
            error: function() {
                alert('AJAX error.');
            },
            complete: function() {
                $link.css('pointer-events', 'auto');
            }
        });
    });
    
    // Initial filter application
    applyFilters();
});
</script>

</body>
</html>