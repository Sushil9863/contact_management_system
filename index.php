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
                                    data-nickname="<?= htmlspecialchars(strtolower($row['nickname'])) ?>">
                                    <td>
                                        <div class="contact-info">
                                            <div class="contact-avatar">
                                                <?= strtoupper(substr($row['nickname'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($row['nickname']) ?></strong><br>
                                                <small><?= htmlspecialchars($row['full_name']) ?></small>
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
                    <small><i class="fas fa-info-circle mr-1"></i> Total Contacts: <span id="totalContacts"><?= count($contacts) ?></span></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once "footer.php"
?>

<script>
    console.log("SCRIPT LOADED - Testing");
alert("Script loaded"); // Remove after test
console.log("jQuery version:", $.fn.jquery);


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
    const totalContacts = $('#totalContacts');
    
    // Get initial contact count
    const initialContactCount = <?= count($contacts) ?>;
    let visibleCount = initialContactCount;
    
    // Debug
    console.log('Contact rows found:', contactRows.length);
    console.log('Initial count:', initialContactCount);
    
    // Test if search input exists and is working
    searchInput.on('input', function() {
        console.log('Search input detected, value:', $(this).val());
        performSearch();
    });
    
    // Search function
    function performSearch() {
        const searchTerm = searchInput.val().trim().toLowerCase();
        console.log('Searching for:', searchTerm);
        
        if (searchTerm.length === 0) {
            // Show all rows
            contactRows.removeClass('hidden');
            if (tableWrapper.length) tableWrapper.show();
            noResults.addClass('hidden');
            searchInfo.hide();
            clearSearchBtn.hide();
            searchText.text('Type to search contacts...');
            searchCount.empty();
            visibleCount = initialContactCount;
            updateTotalCount();
            return;
        }
        
        let foundCount = 0;
        
        // Search through each row
        contactRows.each(function() {
            const $row = $(this);
            const name = $row.data('name') || '';
            const email = $row.data('email') || '';
            const phone = $row.data('phone') || '';
            const nickname = $row.data('nickname') || '';
            
            // Check if any field contains the search term
            const matches = name.includes(searchTerm) || 
                           email.includes(searchTerm) || 
                           String(phone).includes(searchTerm) || 
                           nickname.includes(searchTerm);
            
            if (matches) {
                $row.removeClass('hidden');
                foundCount++;
            } else {
                $row.addClass('hidden');
            }
        });
        
        console.log('Found count:', foundCount);
        
        // Update UI
        visibleCount = foundCount;
        updateTotalCount();
        
        // Show/hide table or no results message
        if (foundCount > 0) {
            if (tableWrapper.length) tableWrapper.show();
            noResults.addClass('hidden');
        } else {
            if (tableWrapper.length) tableWrapper.hide();
            noResults.removeClass('hidden');
        }
        
        // Update search info
        searchInfo.show();
        searchText.html('Showing results for: <strong>"' + searchTerm + '"</strong>');
        searchCount.html('Found: ' + foundCount + ' contact(s)');
        clearSearchBtn.show();
    }
    
    // Update total contacts count
    function updateTotalCount() {
        totalContacts.text(visibleCount);
    }
    
    // Clear search
    function clearSearch() {
        searchInput.val('');
        performSearch();
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
});
</script>

</body>
</html>