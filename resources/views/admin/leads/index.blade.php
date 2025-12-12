@extends('layouts.admin')

@section('title', 'Lead Management')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.2/dist/css/bootstrap-multiselect.css">
<style>
    .lead-status-badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
    }
    .priority-badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
    }
    .priority-low { background-color: #28a745; color: white; }
    .priority-medium { background-color: #ffc107; color: #000; }
    .priority-high { background-color: #dc3545; color: white; }
    .slide-over {
        position: fixed;
        top: 0;
        right: -600px;
        width: 600px;
        height: 100vh;
        background: white;
        box-shadow: -2px 0 10px rgba(0,0,0,0.1);
        transition: right 0.3s ease;
        z-index: 1050;
        overflow-y: auto;
    }
    .slide-over.show {
        right: 0;
    }
    .slide-over-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1040;
        display: none;
    }
    .slide-over-backdrop.show {
        display: block;
    }
    .quick-action-btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .activity-item {
        border-left: 3px solid #007bff;
        padding-left: 1rem;
        margin-bottom: 1rem;
    }
    .activity-item.note { border-color: #17a2b8; }
    .activity-item.call { border-color: #28a745; }
    .activity-item.email { border-color: #ffc107; }
    .activity-item.meeting { border-color: #6f42c1; }
    .activity-item.file { border-color: #fd7e14; }
    .activity-item.reminder { border-color: #dc3545; }
    .skeleton-loader {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    /* Fix dropdown menu positioning in table */
    .table-responsive {
        position: relative;
        overflow-x: auto;
        overflow-y: visible !important;
    }
    #leadsTable {
        position: relative;
    }
    #leadsTable .btn-group {
        position: static;
    }
    /* Prevent Popper.js from positioning dropdown inside table */
    #leadsTable .dropdown-menu {
        min-width: 180px;
        position: absolute !important;
    }
    /* When dropdown is appended to body, use fixed positioning with high z-index */
    body > .dropdown-menu {
        z-index: 1055 !important;
        position: fixed !important;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        pointer-events: auto !important;
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    /* Popover styling for actions menu */
    .popover {
        max-width: 200px;
        z-index: 1060 !important;
    }
    .popover .popover-body {
        padding: 0.5rem 0;
    }
    .popover .list-unstyled {
        margin: 0;
    }
    .popover .dropdown-item {
        padding: 0.5rem 1rem;
        display: block;
        color: #212529;
        text-decoration: none;
        border: none;
        background: transparent;
        width: 100%;
        text-align: left;
    }
    .popover .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #212529;
    }
    .popover .dropdown-item.text-danger:hover {
        background-color: #f8d7da;
        color: #721c24;
    }
    .popover .dropdown-divider {
        margin: 0.5rem 0;
        border-top: 1px solid #dee2e6;
    }
    /* Ensure card and card-body don't clip dropdowns */
    .card {
        overflow: visible !important;
    }
    .card-body {
        overflow: visible !important;
    }
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    /* Bootstrap Multiselect styling */
    .multiselect-container {
        max-height: 300px;
        overflow-y: auto;
    }
    .multiselect-container .form-check {
        padding: 0.5rem 1rem;
    }
    .multiselect-container .form-check:hover {
        background-color: #f8f9fa;
    }
    .multiselect-native-select {
        position: relative;
    }
    /* Activity file section styling */
    .activity-item .bg-light {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
    }
    .activity-item .text-truncate {
        display: inline-block;
        vertical-align: middle;
    }
</style>
@endpush

@section('content')

<div class="container-fluid">
    <div class="py-5">
        <!-- Header -->
        <div class="row g-4 align-items-center mb-4">
            <div class="col">
                <nav class="mb-2" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-sa-simple">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Leads</li>
                    </ol>
                </nav>
                <h1 class="h3 m-0">Lead Management</h1>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" id="addLeadBtn">
                    <i class='bx bx-plus'></i> Add Lead
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">All Status</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Source</label>
                        <select class="form-select" id="filterSource">
                            <option value="">All Sources</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Priority</label>
                        <select class="form-select" id="filterPriority">
                            <option value="">All Priorities</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Assigned To</label>
                        <select class="form-select" id="filterAssignedTo">
                            <option value="">All Users</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" id="filterDateFrom">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" id="filterDateTo">
                    </div>
                    <div class="col-md-12">
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-search'></i></span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search by name, email, phone, or company...">
                            <button class="btn btn-outline-secondary" type="button" id="clearFilters">
                                <i class='bx bx-x'></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leads Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <input type="checkbox" id="selectAllLeads" class="form-check-input me-2">
                    <span id="selectedCount">0 selected</span>
                </div>
                <div class="btn-group" id="bulkActions" class="mt-4" style="display: none;">
                    <button class="btn btn-sm btn-outline-primary" id="bulkStatusUpdate">
                        <i class='bx bx-check-circle'></i> Update Status
                    </button>
                    <button class="btn btn-sm btn-outline-primary" id="bulkAssign">
                        <i class='bx bx-user'></i> Assign User
                    </button>
                    <button class="btn btn-sm btn-outline-primary" id="bulkPriority">
                        <i class='bx bx-star'></i> Change Priority
                    </button>
                    <button class="btn btn-sm btn-outline-danger" id="bulkDelete">
                        <i class='bx bx-trash'></i> Delete
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="leadsTable">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                            </th>
                            <th>Lead Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Company</th>
                            <th>Value</th>
                            <th>Status</th>
                            <th>Source</th>
                            <th>Priority</th>
                            <th>Assigned To</th>
                            <th>Tags</th>
                            <th>Created</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="leadsTableBody">
                        <tr>
                            <td colspan="13" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="card-footer">
                <div class="d-flex justify-content-center" id="paginationContainer">
                    <!-- Pagination will be loaded here via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Lead Modal -->
<div class="modal fade" id="leadModal" tabindex="-1" aria-labelledby="leadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leadModalLabel">Add New Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="leadForm">
                <div class="modal-body">
                    <input type="hidden" id="leadId" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Lead Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="leadName" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="leadEmail" name="email" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" id="leadPhone" name="phone">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="leadCompany" name="company">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lead Value</label>
                            <input type="number" step="0.01" class="form-control" id="leadValue" name="value" placeholder="0.00">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="leadStatus" name="status_id" required>
                                <option value="">Select Status</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Source <span class="text-danger">*</span></label>
                            <select class="form-select" id="leadSource" name="source_id" required>
                                <option value="">Select Source</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <select class="form-select" id="leadPriority" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assigned To</label>
                            <select class="form-select" id="leadAssignedTo" name="assigned_to">
                                <option value="">Unassigned</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tags</label>
                            <select class="form-select" id="leadTags" name="tags[]" multiple>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description/Notes</label>
                            <textarea class="form-control" id="leadDescription" name="description" rows="4"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveLeadBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Save Lead
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lead Detail Slide-over -->
<div class="slide-over-backdrop" id="leadDetailBackdrop"></div>
<div class="slide-over" id="leadDetailSlideOver">
    <div class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="m-0">Lead Details</h4>
            <button class="btn btn-sm btn-outline-secondary" id="closeSlideOver">
                <i class='bx bx-x'></i>
            </button>
        </div>
        <div id="leadDetailContent">
            <!-- Content will be loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Activity Modal -->
<div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityModalLabel">Add Activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="activityForm">
                <input type="hidden" id="activityLeadId" name="lead_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Activity Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="activityType" name="type" required>
                            <option value="">Select Type</option>
                            <option value="note">Note</option>
                            <option value="call">Call Log</option>
                            <option value="email">Email Summary</option>
                            <option value="meeting">Meeting Log</option>
                            <option value="file">File Upload</option>
                            <option value="reminder">Reminder Follow-up</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="activityDescription" name="description" rows="4" required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Follow-up Date</label>
                            <input type="text" class="form-control" id="activityFollowUpDate" name="follow_up_date" autocomplete="off" placeholder="Select date and time">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Next Action Owner</label>
                            <select class="form-select" id="activityNextActionOwner" name="next_action_owner">
                                <option value="">Select User</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mb-3" id="fileUploadSection" style="display: none;">
                        <label class="form-label">Upload File</label>
                        <input type="file" class="form-control" id="activityFile" name="file">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Save Activity
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Action Modals -->
<div class="modal fade" id="quickStatusModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <select class="form-select" id="quickStatusSelect">
                    <option value="">Select Status</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmQuickStatus">Update</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="quickAssignModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <select class="form-select" id="quickAssignSelect">
                    <option value="">Unassigned</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmQuickAssign">Assign</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="quickPriorityModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Priority</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <select class="form-select" id="quickPrioritySelect">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmQuickPriority">Update</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the selected lead(s)? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class='bx bx-check-circle text-success me-2'></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-multiselect@1.1.2/dist/js/bootstrap-multiselect.js"></script>
<script>
$(document).ready(function() {
    // Global variables
    let currentPage = 1;
    let selectedLeads = [];
    let currentLeadId = null;
    let quickActionLeadId = null;
    let masterData = {
        statuses: [],
        sources: [],
        users: [],
        tags: []
    };

    // Fix dropdown menu positioning in table - intercept click before Bootstrap
    $(document).on('click', '#leadsTable .dropdown-toggle[data-bs-toggle="dropdown"]', function(e) {
        const button = $(this);
        const dropdown = button.closest('.dropdown');
        
        // Only handle dropdowns inside the leads table
        if (!dropdown.length || !dropdown.closest('#leadsTable').length) {
            return;
        }
        
        const menu = dropdown.find('.dropdown-menu');
        if (!menu.length) return;
        
        // Check if dropdown is already shown
        const isShown = menu.hasClass('show');
        
        if (!isShown) {
            // Prevent default Bootstrap behavior temporarily
            e.stopImmediatePropagation();
            
            // Store reference
            menu.data('parent-dropdown', dropdown);
            menu.data('parent-button', button);
            
            // Move menu to body immediately
            if (menu.parent()[0] !== document.body) {
                menu.detach().appendTo('body');
            }
            
            // Disable Popper completely
            const dropdownInstance = bootstrap.Dropdown.getInstance(button[0]);
            if (dropdownInstance && dropdownInstance._popper) {
                dropdownInstance._popper.destroy();
                dropdownInstance._popper = null;
            }
            
            // Manually show the dropdown
            menu.addClass('show');
            button.attr('aria-expanded', 'true');
            
            // Position the menu
            positionDropdownMenu(menu, button);
            
            // Re-enable click handling after a short delay
            setTimeout(function() {
                // Allow Bootstrap to handle the state, but we control positioning
                if (!dropdownInstance) {
                    new bootstrap.Dropdown(button[0], {
                        boundary: 'viewport',
                        popperConfig: null
                    });
                }
            }, 0);
        } else {
            // Hide dropdown
            menu.removeClass('show');
            button.attr('aria-expanded', 'false');
            
            // Return menu to dropdown
            if (menu.parent()[0] === document.body) {
                menu.appendTo(dropdown);
                menu[0].style.cssText = '';
            }
        }
    });
    
    // Function to position dropdown menu
    function positionDropdownMenu(menu, button) {
        if (!menu.length || !button.length) return;
        
        function positionMenu() {
            if (!menu.length || !button.length) return;
            
            // Use getBoundingClientRect for viewport-relative positioning
            const buttonRect = button[0].getBoundingClientRect();
            const buttonWidth = button.outerWidth();
            const buttonHeight = button.outerHeight();
            const menuWidth = menu.outerWidth() || 180;
            const menuHeight = menu.outerHeight() || 200;
            const windowWidth = $(window).width();
            const windowHeight = $(window).height();
            const scrollTop = $(window).scrollTop();
            const scrollLeft = $(window).scrollLeft();
            
            // Calculate position - align to right edge of button (dropdown-menu-end)
            let top = buttonRect.top + buttonHeight + scrollTop;
            let left = buttonRect.left + buttonWidth - menuWidth + scrollLeft;
            
            // Adjust if menu would go off screen
            if (left < scrollLeft + 10) {
                left = buttonRect.left + scrollLeft;
            }
            if (left + menuWidth > windowWidth + scrollLeft - 10) {
                left = windowWidth + scrollLeft - menuWidth - 10;
            }
            if (top + menuHeight > windowHeight + scrollTop - 10) {
                top = buttonRect.top - menuHeight + scrollTop;
            }
            if (top < scrollTop + 10) {
                top = scrollTop + 10;
            }
            
            // Apply fixed positioning
            menu[0].style.cssText = `
                position: fixed !important;
                top: ${top}px !important;
                left: ${left}px !important;
                z-index: 1055 !important;
                display: block !important;
                transform: none !important;
                margin: 0 !important;
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
            `;
        }
        
        positionMenu();
        requestAnimationFrame(positionMenu);
        setTimeout(positionMenu, 0);
        setTimeout(positionMenu, 10);
        setTimeout(positionMenu, 50);
        
        // Watch for changes
        const observer = new MutationObserver(function() {
            positionMenu();
        });
        
        observer.observe(menu[0], {
            attributes: true,
            attributeFilter: ['style']
        });
        
        menu.data('position-observer', observer);
    }
    
    // Also handle show.bs.dropdown as backup
    $(document).on('show.bs.dropdown', '#leadsTable .dropdown', function(e) {
        const dropdown = $(this);
        const menu = dropdown.find('.dropdown-menu');
        const button = dropdown.find('.dropdown-toggle');
        
        if (!menu.length || !button.length) return;
        
        // Move to body if not already there
        if (menu.parent()[0] !== document.body) {
            menu.detach().appendTo('body');
            menu.data('parent-dropdown', dropdown);
            menu.data('parent-button', button);
        }
        
        // Disable Popper
        const dropdownInstance = bootstrap.Dropdown.getInstance(button[0]);
        if (dropdownInstance && dropdownInstance._popper) {
            dropdownInstance._popper.destroy();
            dropdownInstance._popper = null;
        }
        
        // Position the menu
        positionDropdownMenu(menu, button);
    });
    
    // Update position on scroll and resize
    function updateDropdownPositions() {
        $('body > .dropdown-menu.show').each(function() {
            const menu = $(this);
            const button = menu.data('parent-button');
            if (button && button.length) {
                const buttonRect = button[0].getBoundingClientRect();
                const buttonHeight = button.outerHeight();
                const menuHeight = menu.outerHeight() || 200;
                const scrollTop = $(window).scrollTop();
                const scrollLeft = $(window).scrollLeft();
                const windowHeight = $(window).height();
                const menuWidth = menu.outerWidth() || 180;
                
                let top = buttonRect.top + buttonHeight + scrollTop;
                let left = buttonRect.left + button.outerWidth() - menuWidth + scrollLeft;
                
                // Adjust if menu would go off screen
                if (left < scrollLeft + 10) {
                    left = buttonRect.left + scrollLeft;
                }
                if (left + menuWidth > $(window).width() + scrollLeft - 10) {
                    left = $(window).width() + scrollLeft - menuWidth - 10;
                }
                if (top + menuHeight > windowHeight + scrollTop - 10) {
                    top = buttonRect.top - menuHeight + scrollTop;
                }
                if (top < scrollTop + 10) {
                    top = scrollTop + 10;
                }
                
                menu[0].style.setProperty('top', top + 'px', 'important');
                menu[0].style.setProperty('left', left + 'px', 'important');
            }
        });
    }
    
    $(window).on('scroll resize', updateDropdownPositions);
    
    // Clean up when dropdown is hidden
    $(document).on('hidden.bs.dropdown', '#leadsTable .dropdown', function(e) {
        const dropdown = $(this);
        const menu = dropdown.find('.dropdown-menu');
        
        // Return menu to original position if it was moved to body
        if (menu.length && menu.parent()[0] === document.body) {
            // Stop observer
            const observer = menu.data('position-observer');
            if (observer) {
                observer.disconnect();
                menu.removeData('position-observer');
            }
            
            // Return menu to dropdown
            menu.appendTo(dropdown);
            menu.removeClass('show');
            menu.removeData('parent-dropdown');
            menu.removeData('parent-button');
            
            // Clear all inline styles
            menu[0].style.cssText = '';
        }
    });
    
    // Handle clicks outside to close dropdown
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#leadsTable .dropdown, body > .dropdown-menu').length) {
            $('#leadsTable .dropdown-toggle[aria-expanded="true"]').each(function() {
                const button = $(this);
                const dropdown = button.closest('.dropdown');
                const menu = dropdown.find('.dropdown-menu');
                
                if (menu.hasClass('show')) {
                    menu.removeClass('show');
                    button.attr('aria-expanded', 'false');
                    
                    if (menu.parent()[0] === document.body) {
                        // Stop observer
                        const observer = menu.data('position-observer');
                        if (observer) {
                            observer.disconnect();
                            menu.removeData('position-observer');
                        }
                        
                        menu.appendTo(dropdown);
                        menu[0].style.cssText = '';
                    }
                }
            });
        }
    });

    // Initialize
    loadMasterData();
    loadLeads();

    // Load master data (statuses, sources, users)
    function loadMasterData() {
        return $.ajax({
            url: '{{ route("leads.master-data") }}',
            type: 'GET',
            success: function(response) {
                console.log('=== MASTER DATA RESPONSE ===');
                console.log('Full response:', response);
                if(response.success) {
                    masterData = response.data;
                    console.log('Master Data Object:', masterData);
                    console.log('Statuses:', masterData.statuses);
                    console.log('Sources:', masterData.sources);
                    console.log('Users:', masterData.users);
                    console.log('Users count:', masterData.users ? masterData.users.length : 0);
                    populateMasterData();
                    setTimeout(function() {
                        initializeMultiselect();
                    }, 100);
                }
            },
            error: function(xhr) {
                console.error('Error loading master data:', xhr);
            }
        });
    }

    // Initialize Bootstrap Multiselect for all select inputs
    function initializeMultiselect() {
        // Destroy existing multiselect instances
        try {
            $('#leadStatus, #leadSource, #leadAssignedTo, #leadTags, #filterStatus, #filterSource, #filterAssignedTo, #quickStatusSelect, #quickAssignSelect').multiselect('destroy');
        } catch(e) {
            console.log('Error destroying multiselect:', e);
        }
        
        // Initialize form dropdowns (not filters)
        $('#leadStatus, #leadSource, #leadAssignedTo, #quickStatusSelect, #quickAssignSelect').multiselect({
            enableFiltering: false,
            enableClickableOptGroups: false,
            enableCollapsibleOptGroups: false,
            buttonWidth: '100%',
            includeSelectAllOption: false,
            selectAllText: 'Select All',
            allSelectedText: 'All selected',
            nonSelectedText: 'Select an option',
            nSelectedText: ' selected',
            numberDisplayed: 0,
            maxHeight: 300,
            buttonClass: 'btn btn-outline-secondary',
            templates: {
                button: '<button type="button" class="multiselect dropdown-toggle" data-bs-toggle="dropdown"><span class="multiselect-selected-text"></span> <b class="caret"></b></button>',
                ul: '<ul class="multiselect-container dropdown-menu"></ul>',
                filter: '<li class="multiselect-item multiselect-filter"><div class="input-group"><span class="input-group-text"><i class="fa fa-search"></i></span><input class="form-control multiselect-search" type="text"></div></li>',
                li: '<li><a class="dropdown-item" href="javascript:void(0);"><label></label></a></li>',
                divider: '<li class="multiselect-item divider"></li>',
                liGroup: '<li class="multiselect-item multiselect-group"><label></label></li>'
            }
        });
        
        // Initialize filter dropdowns with appropriate placeholders
        $('#filterStatus').multiselect({
            enableFiltering: false,
            buttonWidth: '100%',
            includeSelectAllOption: false,
            nonSelectedText: 'All Status',
            maxHeight: 300,
            buttonClass: 'btn btn-outline-secondary',
            templates: {
                button: '<button type="button" class="multiselect dropdown-toggle" data-bs-toggle="dropdown"><span class="multiselect-selected-text"></span> <b class="caret"></b></button>',
                ul: '<ul class="multiselect-container dropdown-menu"></ul>',
                li: '<li><a class="dropdown-item" href="javascript:void(0);"><label></label></a></li>'
            }
        });
        
        $('#filterSource').multiselect({
            enableFiltering: false,
            buttonWidth: '100%',
            includeSelectAllOption: false,
            nonSelectedText: 'All Sources',
            maxHeight: 300,
            buttonClass: 'btn btn-outline-secondary',
            templates: {
                button: '<button type="button" class="multiselect dropdown-toggle" data-bs-toggle="dropdown"><span class="multiselect-selected-text"></span> <b class="caret"></b></button>',
                ul: '<ul class="multiselect-container dropdown-menu"></ul>',
                li: '<li><a class="dropdown-item" href="javascript:void(0);"><label></label></a></li>'
            }
        });
        
        $('#filterAssignedTo').multiselect({
            enableFiltering: false,
            buttonWidth: '100%',
            includeSelectAllOption: false,
            nonSelectedText: 'All Users',
            maxHeight: 300,
            buttonClass: 'btn btn-outline-secondary',
            templates: {
                button: '<button type="button" class="multiselect dropdown-toggle" data-bs-toggle="dropdown"><span class="multiselect-selected-text"></span> <b class="caret"></b></button>',
                ul: '<ul class="multiselect-container dropdown-menu"></ul>',
                li: '<li><a class="dropdown-item" href="javascript:void(0);"><label></label></a></li>'
            }
        });
        
        // Initialize tags with checkboxes and "Select all" option
        $('#leadTags').multiselect({
            enableFiltering: false,
            enableClickableOptGroups: false,
            enableCollapsibleOptGroups: false,
            buttonWidth: '100%',
            includeSelectAllOption: true,
            selectAllText: 'Select All',
            allSelectedText: 'All tags selected',
            nonSelectedText: 'Select tags',
            nSelectedText: ' tags selected',
            numberDisplayed: 3,
            maxHeight: 300,
            buttonClass: 'btn btn-outline-secondary',
            templates: {
                button: '<button type="button" class="multiselect dropdown-toggle" data-bs-toggle="dropdown"><span class="multiselect-selected-text"></span> <b class="caret"></b></button>',
                ul: '<ul class="multiselect-container dropdown-menu"></ul>',
                filter: '<li class="multiselect-item multiselect-filter"><div class="input-group"><span class="input-group-text"><i class="fa fa-search"></i></span><input class="form-control multiselect-search" type="text"></div></li>',
                li: '<li><a class="dropdown-item" href="javascript:void(0);"><label></label></a></li>',
                divider: '<li class="multiselect-item divider"></li>',
                liGroup: '<li class="multiselect-item multiselect-group"><label></label></li>'
            }
        });
    }

    // Populate dropdowns with master data
    function populateMasterData() {
        console.log('=== POPULATING DROPDOWNS ===');
        console.log('Master Data:', masterData);
        
        // Status dropdowns - filter needs "All Status" option
        $('#filterStatus').empty().append('<option value="">All Status</option>');
        $('#leadStatus, #quickStatusSelect').empty().append('<option value="">Select Status</option>');
        if (masterData.statuses && masterData.statuses.length > 0) {
            console.log('Populating Status dropdown with', masterData.statuses.length, 'statuses');
            masterData.statuses.forEach(function(status) {
                console.log('Adding status option:', status.id, status.name);
                $('#filterStatus, #leadStatus, #quickStatusSelect').append(`<option value="${status.id}">${status.name}</option>`);
            });
        } else {
            console.warn('No statuses found in masterData');
        }

        // Source dropdowns - filter needs "All Sources" option
        $('#filterSource').empty().append('<option value="">All Sources</option>');
        $('#leadSource').empty().append('<option value="">Select Source</option>');
        if (masterData.sources && masterData.sources.length > 0) {
            console.log('Populating Source dropdown with', masterData.sources.length, 'sources');
            masterData.sources.forEach(function(source) {
                console.log('Adding source option:', source.id, source.name);
                $('#filterSource, #leadSource').append(`<option value="${source.id}">${source.name}</option>`);
            });
        } else {
            console.warn('No sources found in masterData');
        }

        // User dropdowns - filter needs "All Users" option
        $('#filterAssignedTo').empty().append('<option value="">All Users</option>');
        $('#leadAssignedTo, #activityNextActionOwner, #quickAssignSelect').empty().append('<option value="">Unassigned</option>');
        if (masterData.users && masterData.users.length > 0) {
            console.log('Populating User dropdown with', masterData.users.length, 'users');
            masterData.users.forEach(function(user) {
                console.log('Adding user option:', user.id, user.name, user.email);
                const option = `<option value="${user.id}">${user.name}</option>`;
                $('#filterAssignedTo, #leadAssignedTo, #activityNextActionOwner, #quickAssignSelect').append(option);
            });
            console.log('User dropdown options after population:', $('#leadAssignedTo option').map(function() { return $(this).val() + ':' + $(this).text(); }).get());
        } else {
            console.warn('No users found in masterData');
        }

        // Tags dropdown
        $('#leadTags').empty();
        if (masterData.tags && masterData.tags.length > 0) {
            console.log('Populating Tags dropdown with', masterData.tags.length, 'tags');
            masterData.tags.forEach(function(tag) {
                console.log('Adding tag option:', tag.id, tag.name);
                const option = `<option value="${tag.id}">${tag.name}</option>`;
                $('#leadTags').append(option);
            });
            console.log('Tags dropdown options after population:', $('#leadTags option').map(function() { return $(this).val() + ':' + $(this).text(); }).get());
        } else {
            console.warn('No tags found in masterData');
        }
        
        // Refresh multiselect if initialized
        setTimeout(function() {
            if ($('#leadTags').data('multiselect')) {
                $('#leadTags').multiselect('refresh');
            }
        }, 50);
    }

    // Load leads with filters
    function loadLeads() {
        const params = new URLSearchParams();
        params.append('page', currentPage);
        
        const search = $('#searchInput').val();
        if(search) params.append('search', search);
        
        const status = $('#filterStatus').val();
        if(status) params.append('status_id', status);
        
        const source = $('#filterSource').val();
        if(source) params.append('source_id', source);
        
        const priority = $('#filterPriority').val();
        if(priority) params.append('priority', priority);
        
        const assignedTo = $('#filterAssignedTo').val();
        if(assignedTo) params.append('assigned_to', assignedTo);
        
        const dateFrom = $('#filterDateFrom').val();
        if(dateFrom) params.append('date_from', dateFrom);
        
        const dateTo = $('#filterDateTo').val();
        if(dateTo) params.append('date_to', dateTo);

        $.ajax({
            url: '{{ route("leads.index") }}?' + params.toString(),
            type: 'GET',
            beforeSend: function() {
                $('#leadsTableBody').html(`
                    <tr>
                        <td colspan="13" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </td>
                    </tr>
                `);
            },
            success: function(response) {
                if(response.success) {
                    renderLeadsTable(response.data);
                    updatePagination(response.pagination);
                }
            },
            error: function(xhr) {
                console.error('Error loading leads:', xhr);
                $('#leadsTableBody').html('<tr><td colspan="13" class="text-center py-4 text-danger">Error loading leads</td></tr>');
            }
        });
    }

    // Render leads table
    function renderLeadsTable(leads) {
        const tbody = $('#leadsTableBody');
        tbody.empty();

        if(leads.length === 0) {
            tbody.html('<tr><td colspan="13" class="text-center py-4">No leads found</td></tr>');
            return;
        }

        leads.forEach(function(lead) {
            const statusBadge = getStatusBadge(lead.status);
            const priorityBadge = getPriorityBadge(lead.priority);
            const assignedUser = lead.assigned_to ? lead.assigned_to.name : '<span class="text-muted">Unassigned</span>';
            const value = lead.value ? formatCurrency(lead.value) : '<span class="text-muted">-</span>';
            
            // Render tags as chips
            let tagsHtml = '<span class="text-muted">-</span>';
            if (lead.tags && lead.tags.length > 0) {
                tagsHtml = lead.tags.map(tag => 
                    `<span class="badge bg-secondary me-1 mb-1">${tag.name}</span>`
                ).join('');
            }

            const row = `
                <tr data-id="${lead.id}">
                    <td>
                        <input type="checkbox" class="form-check-input lead-checkbox" value="${lead.id}">
                    </td>
                    <td>
                        <strong>${lead.name}</strong>
                        ${lead.company ? `<br><small class="text-muted">${lead.company}</small>` : ''}
                    </td>
                    <td>${lead.email || '-'}</td>
                    <td>${lead.phone || '-'}</td>
                    <td>${lead.company || '-'}</td>
                    <td>${value}</td>
                    <td>${statusBadge}</td>
                    <td>${lead.source ? lead.source.name : '-'}</td>
                    <td>${priorityBadge}</td>
                    <td>${assignedUser}</td>
                    <td>${tagsHtml}</td>
                    <td>${formatDate(lead.created_at)}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary view-lead" data-id="${lead.id}" title="View">
                                <i class='bx bx-show'></i>
                            </button>
                            <button class="btn btn-outline-secondary edit-lead" data-id="${lead.id}" title="Edit">
                                <i class='bx bx-edit'></i>
                            </button>
                            <button type="button" class="btn btn-outline-info actions-popover" 
                                    data-bs-toggle="popover" 
                                    data-bs-placement="left"
                                    data-bs-html="true"
                                    data-bs-trigger="click"
                                    data-bs-container="body"
                                    data-lead-id="${lead.id}"
                                    data-status-id="${lead.status ? lead.status.id : ''}"
                                    data-user-id="${lead.assigned_to ? lead.assigned_to.id : ''}"
                                    data-priority="${lead.priority || ''}"
                                    data-bs-content='<ul class="list-unstyled mb-0" style="min-width: 180px;"><li><a class="dropdown-item quick-status" href="#" data-id="${lead.id}" data-status-id="${lead.status ? lead.status.id : ''}">Update Status</a></li><li><a class="dropdown-item quick-assign" href="#" data-id="${lead.id}" data-user-id="${lead.assigned_to ? lead.assigned_to.id : ''}">Assign User</a></li><li><a class="dropdown-item quick-priority" href="#" data-id="${lead.id}" data-priority="${lead.priority || ''}">Change Priority</a></li><li><hr class="dropdown-divider"></li><li><a class="dropdown-item text-danger delete-lead" href="#" data-id="${lead.id}">Delete</a></li></ul>'>
                                <i class='bx bx-dots-vertical-rounded'></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
        
        // Initialize popovers for all action buttons after rendering
        setTimeout(function() {
            $('#leadsTable .actions-popover').each(function() {
                const btn = $(this);
                // Destroy existing popover if any
                const existingPopover = bootstrap.Popover.getInstance(this);
                if (existingPopover) {
                    existingPopover.dispose();
                }
                // Initialize new popover
                new bootstrap.Popover(this, {
                    container: 'body',
                    placement: 'left',
                    html: true,
                    trigger: 'click',
                    sanitize: false
                });
            });
        }, 100);
    }

    // Helper functions
    function getStatusBadge(status) {
        if(!status) return '<span class="badge bg-secondary">-</span>';
        const colors = {
            'new': 'bg-info',
            'in_progress': 'bg-primary',
            'qualified': 'bg-success',
            'lost': 'bg-danger',
            'won': 'bg-success'
        };
        const color = colors[status.slug] || 'bg-secondary';
        return `<span class="badge ${color} lead-status-badge">${status.name}</span>`;
    }

    function getPriorityBadge(priority) {
        if(!priority) return '<span class="badge bg-secondary">-</span>';
        return `<span class="badge priority-badge priority-${priority}">${priority.charAt(0).toUpperCase() + priority.slice(1)}</span>`;
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 2
        }).format(amount);
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    // Update pagination
    function updatePagination(pagination) {
        let html = '';
        if(pagination.last_page > 1) {
            html = '<nav><ul class="pagination">';
            if(pagination.current_page > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a></li>`;
            }
            for(let i = 1; i <= pagination.last_page; i++) {
                if(i === 1 || i === pagination.last_page || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
                    const active = i === pagination.current_page ? 'active' : '';
                    html += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                } else if(i === pagination.current_page - 3 || i === pagination.current_page + 3) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            if(pagination.current_page < pagination.last_page) {
                html += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a></li>`;
            }
            html += '</ul></nav>';
        }
        $('#paginationContainer').html(html);
    }

    // Filter events
    $('#searchInput').on('keyup', debounce(function() {
        currentPage = 1;
        loadLeads();
    }, 500));

    // Filter change events - handle multiselect and regular inputs
    $('#filterPriority, #filterDateFrom, #filterDateTo').on('change', function() {
        currentPage = 1;
        loadLeads();
    });
    
    // For multiselect filters, use delegated event handler
    $(document).on('change', '#filterStatus, #filterSource, #filterAssignedTo', function() {
        currentPage = 1;
        loadLeads();
    });

    $('#clearFilters').on('click', function() {
        // Clear search input
        $('#searchInput').val('');
        
        // Clear multiselect filters - need to reset the underlying select
        $('#filterStatus, #filterSource, #filterAssignedTo').each(function() {
            $(this).val('').trigger('change');
        });
        
        // Clear regular inputs
        $('#filterPriority, #filterDateFrom, #filterDateTo').val('');
        
        // Re-initialize multiselect to show placeholder
        setTimeout(function() {
            initializeMultiselect();
        }, 100);
        
        currentPage = 1;
        loadLeads();
    });

    // Pagination click
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        currentPage = $(this).data('page');
        loadLeads();
    });

    // Add Lead Button
    $('#addLeadBtn').on('click', function() {
        resetLeadForm();
        populateMasterData(); // Ensure dropdowns are populated
        setTimeout(function() {
            initializeMultiselect();
        }, 150);
        $('#leadModalLabel').text('Add New Lead');
        $('#leadModal').modal('show');
    });

    // Store lead data for editing (to set after modal is shown)
    let editingLeadData = null;

    // Handle modal shown event to set values after modal is fully displayed
    $('#leadModal').on('shown.bs.modal', function() {
        console.log('=== MODAL SHOWN EVENT ===');
        console.log('editingLeadData:', editingLeadData);
        if (editingLeadData) {
            // Set values after modal is fully shown
            const lead = editingLeadData;
            console.log('=== SETTING LEAD VALUES IN MODAL ===');
            console.log('Lead data:', lead);
            
            // Extract IDs from objects
            const statusId = lead.status_id || (lead.status ? lead.status.id : null);
            const sourceId = lead.source_id || (lead.source ? lead.source.id : null);
            const assignedToId = (lead.assigned_to && typeof lead.assigned_to === 'object') ? lead.assigned_to.id : (lead.assigned_to || null);
            
            console.log('Extracted status_id:', statusId);
            console.log('Extracted source_id:', sourceId);
            console.log('Extracted assigned_to:', assignedToId);
            console.log('Lead priority:', lead.priority);
            
            // Populate text fields
            $('#leadId').val(lead.id);
            $('#leadName').val(lead.name);
            $('#leadEmail').val(lead.email);
            $('#leadPhone').val(lead.phone || '');
            $('#leadCompany').val(lead.company || '');
            $('#leadValue').val(lead.value || '');
            $('#leadDescription').val(lead.description || '');
            
            // Check dropdown options before setting values
            console.log('Status dropdown options:', $('#leadStatus option').map(function() { return $(this).val() + ':' + $(this).text(); }).get());
            console.log('Source dropdown options:', $('#leadSource option').map(function() { return $(this).val() + ':' + $(this).text(); }).get());
            console.log('AssignedTo dropdown options:', $('#leadAssignedTo option').map(function() { return $(this).val() + ':' + $(this).text(); }).get());
            
            // Set dropdown values
            if (statusId) {
                console.log('Setting status_id to:', statusId);
                $('#leadStatus').val(statusId);
                if ($('#leadStatus').data('multiselect')) {
                    $('#leadStatus').multiselect('refresh');
                }
                console.log('Status value after setting:', $('#leadStatus').val());
            }
            if (sourceId) {
                console.log('Setting source_id to:', sourceId);
                $('#leadSource').val(sourceId);
                if ($('#leadSource').data('multiselect')) {
                    $('#leadSource').multiselect('refresh');
                }
                console.log('Source value after setting:', $('#leadSource').val());
            }
            if (lead.priority) {
                console.log('Setting priority to:', lead.priority);
                $('#leadPriority').val(lead.priority);
                console.log('Priority value after setting:', $('#leadPriority').val());
            }
            if (assignedToId) {
                console.log('Setting assigned_to to:', assignedToId);
                $('#leadAssignedTo').val(assignedToId);
                if ($('#leadAssignedTo').data('multiselect')) {
                    $('#leadAssignedTo').multiselect('refresh');
                }
                console.log('AssignedTo value after setting:', $('#leadAssignedTo').val());
            } else {
                console.log('No assigned_to, setting to empty');
                $('#leadAssignedTo').val('');
                if ($('#leadAssignedTo').data('multiselect')) {
                    $('#leadAssignedTo').multiselect('refresh');
                }
            }
            
            // Handle tags
            if (lead.tags && lead.tags.length > 0) {
                const tagIds = lead.tags.map(tag => tag.id);
                $('#leadTags').val(tagIds);
                if ($('#leadTags').data('multiselect')) {
                    $('#leadTags').multiselect('refresh');
                }
            } else {
                $('#leadTags').val([]);
                if ($('#leadTags').data('multiselect')) {
                    $('#leadTags').multiselect('refresh');
                }
            }
            
            // Re-initialize multiselect after setting values
            setTimeout(function() {
                initializeMultiselect();
            }, 200);
            
            editingLeadData = null; // Clear after setting
        } else {
            console.log('No editingLeadData found - this is a new lead');
        }
    });

    // Reset editing data when modal is hidden
    $('#leadModal').on('hidden.bs.modal', function() {
        editingLeadData = null;
        // Destroy multiselect instances when modal is closed
        try {
            $('#leadStatus, #leadSource, #leadAssignedTo, #leadTags').each(function() {
                if ($(this).data('multiselect')) {
                    $(this).multiselect('destroy');
                }
            });
        } catch(e) {
            console.log('Error destroying multiselect on modal close:', e);
        }
    });

    // Edit Lead
    $(document).on('click', '.edit-lead', function() {
        const leadId = $(this).data('id');
        loadLeadForEdit(leadId);
    });

    function loadLeadForEdit(leadId) {
        // Ensure master data is loaded and dropdowns are populated
        if (!masterData.statuses || masterData.statuses.length === 0) {
            // If master data not loaded, load it first
            loadMasterData().done(function() {
                loadLeadData(leadId);
            });
        } else {
            // Master data already loaded, proceed to load lead data
            loadLeadData(leadId);
        }
    }

    function loadLeadData(leadId) {
        console.log('=== LOADING LEAD DATA ===');
        console.log('Lead ID:', leadId);
        $.ajax({
            url: `{{ route("leads.index") }}/${leadId}`,
            type: 'GET',
            success: function(response) {
                console.log('=== LEAD DATA RESPONSE ===');
                console.log('Full response:', response);
                if(response.success) {
                    const lead = response.data;
                    console.log('Lead object:', lead);
                    console.log('Lead status:', lead.status);
                    console.log('Lead source:', lead.source);
                    console.log('Lead status_id:', lead.status_id, typeof lead.status_id);
                    console.log('Lead source_id:', lead.source_id, typeof lead.source_id);
                    console.log('Lead priority:', lead.priority);
                    console.log('Lead assigned_to:', lead.assigned_to, typeof lead.assigned_to);
                    
                    // Extract IDs if they're in object format
                    if (lead.status && !lead.status_id) {
                        lead.status_id = lead.status.id;
                    }
                    if (lead.source && !lead.source_id) {
                        lead.source_id = lead.source.id;
                    }
                    if (lead.assigned_to && typeof lead.assigned_to === 'object' && lead.assigned_to.id) {
                        lead.assigned_to = lead.assigned_to.id;
                    }
                    
                    console.log('After extraction - status_id:', lead.status_id, 'source_id:', lead.source_id, 'assigned_to:', lead.assigned_to);
                    
                    // Store lead data for setting after modal is shown
                    editingLeadData = lead;
                    console.log('Stored editingLeadData:', editingLeadData);
                    
                    // First, populate dropdowns with master data
                    console.log('Populating dropdowns before showing modal...');
                    populateMasterData();
                    
                    // Initialize multiselect after populating
                    setTimeout(function() {
                        initializeMultiselect();
                    }, 150);
                    
                    // Update modal title
                    $('#leadModalLabel').text('Edit Lead');
                    
                    // Show modal - values will be set in the 'shown.bs.modal' event
                    console.log('Showing modal...');
                    $('#leadModal').modal('show');
                }
            },
            error: function(xhr) {
                console.error('Error loading lead data:', xhr);
                showToast('error', 'Failed to load lead data');
            }
        });
    }

    // Save Lead Form
    $('#leadForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const leadId = $('#leadId').val();
        const url = leadId ? `{{ route("leads.index") }}/${leadId}` : '{{ route("leads.store") }}';
        const method = leadId ? 'PUT' : 'POST';

        $('#saveLeadBtn').prop('disabled', true).find('.spinner-border').removeClass('d-none');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', response.message || 'Lead saved successfully');
                    $('#leadModal').modal('hide');
                    loadLeads();
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    displayFormErrors(errors);
                } else {
                    showToast('error', xhr.responseJSON.message || 'Failed to save lead');
                }
            },
            complete: function() {
                $('#saveLeadBtn').prop('disabled', false).find('.spinner-border').addClass('d-none');
            }
        });
    });

    // View Lead Details
    $(document).on('click', '.view-lead', function() {
        const leadId = $(this).data('id');
        loadLeadDetails(leadId);
    });

    function loadLeadDetails(leadId) {
        currentLeadId = leadId;
        $.ajax({
            url: `{{ route("leads.index") }}/${leadId}`,
            type: 'GET',
            success: function(response) {
                if(response.success) {
                    renderLeadDetails(response.data);
                    $('#leadDetailSlideOver').addClass('show');
                    $('#leadDetailBackdrop').addClass('show');
                }
            },
            error: function(xhr) {
                showToast('error', 'Failed to load lead details');
            }
        });
    }

    function renderLeadDetails(lead) {
        const statusBadge = getStatusBadge(lead.status);
        const priorityBadge = getPriorityBadge(lead.priority);
        const assignedUser = lead.assigned_to ? (typeof lead.assigned_to === 'object' ? lead.assigned_to.name : lead.assigned_to) : 'Unassigned';
        const value = lead.value ? formatCurrency(lead.value) : 'Not specified';
        
        // Render tags as chips
        let tagsHtml = '<span class="text-muted">No tags</span>';
        if (lead.tags && lead.tags.length > 0) {
            tagsHtml = lead.tags.map(tag => 
                `<span class="badge bg-secondary me-1 mb-1">${tag.name}</span>`
            ).join('');
        }

        const html = `
            <div class="mb-4">
                <h5>${lead.name}</h5>
                <div class="d-flex gap-2 mb-3">
                    ${statusBadge}
                    ${priorityBadge}
                </div>
            </div>
            <div class="mb-4">
                <h6>Contact Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Email:</strong></td><td>${lead.email || '-'}</td></tr>
                    <tr><td><strong>Phone:</strong></td><td>${lead.phone || '-'}</td></tr>
                    <tr><td><strong>Company:</strong></td><td>${lead.company || '-'}</td></tr>
                </table>
            </div>
            <div class="mb-4">
                <h6>Lead Details</h6>
                <table class="table table-sm">
                    <tr><td><strong>Value:</strong></td><td>${value}</td></tr>
                    <tr><td><strong>Source:</strong></td><td>${lead.source ? lead.source.name : '-'}</td></tr>
                    <tr><td><strong>Assigned To:</strong></td><td>${assignedUser}</td></tr>
                    <tr><td><strong>Tags:</strong></td><td>${tagsHtml}</td></tr>
                    <tr><td><strong>Created:</strong></td><td>${formatDate(lead.created_at)}</td></tr>
                </table>
            </div>
            ${lead.description ? `<div class="mb-4"><h6>Description</h6><p>${lead.description}</p></div>` : ''}
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="m-0">Activities</h6>
                    <button class="btn btn-sm btn-primary" id="addActivityBtn">
                        <i class='bx bx-plus'></i> Add Activity
                    </button>
                </div>
                <div id="activitiesList">
                    <div class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                    </div>
                </div>
            </div>
        `;
        $('#leadDetailContent').html(html);
        loadActivities(leadId);
    }

    // Close slide-over
    $('#closeSlideOver, #leadDetailBackdrop').on('click', function() {
        $('#leadDetailSlideOver').removeClass('show');
        $('#leadDetailBackdrop').removeClass('show');
    });

    // Load Activities
    function loadActivities(leadId) {
        // Ensure leadId is a valid value, not an element
        if (!leadId || typeof leadId === 'object') {
            leadId = currentLeadId;
        }
        
        if (!leadId) {
            $('#activitiesList').html('<p class="text-muted">No lead selected</p>');
            return;
        }
        
        $.ajax({
            url: `{{ route("leads.index") }}/${leadId}/activities`,
            type: 'GET',
            success: function(response) {
                if(response.success) {
                    renderActivities(response.data);
                }
            },
            error: function(xhr) {
                $('#activitiesList').html('<p class="text-danger">Failed to load activities</p>');
            }
        });
    }

    function renderActivities(activities) {
        const container = $('#activitiesList');
        if(activities.length === 0) {
            container.html('<p class="text-muted">No activities yet</p>');
            return;
        }

        let html = '';
        activities.forEach(function(activity) {
            const typeClass = activity.type;
            const typeIcon = {
                'note': 'bx-note',
                'call': 'bx-phone',
                'email': 'bx-envelope',
                'meeting': 'bx-calendar',
                'file': 'bx-file',
                'reminder': 'bx-bell'
            }[activity.type] || 'bx-info-circle';

            // Get file name from path
            let fileHtml = '';
            if (activity.file_path) {
                const fileName = activity.file_path.split('/').pop();
                const fileUrl = `/storage/${activity.file_path}`;
                const fileExtension = fileName.split('.').pop().toLowerCase();
                const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtension);
                
                fileHtml = `
                    <div class="mt-2 p-2 bg-light rounded">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class='bx ${isImage ? 'bx-image' : 'bx-file'} me-2'></i>
                                <span class="text-truncate" style="max-width: 200px;" title="${fileName}">${fileName}</span>
                            </div>
                            <div class="btn-group btn-group-sm ms-2">
                                ${isImage ? `<a href="${fileUrl}" target="_blank" class="btn btn-outline-primary btn-sm" title="View">
                                    <i class='bx bx-show'></i> View
                                </a>` : ''}
                                <a href="${fileUrl}" download="${fileName}" class="btn btn-outline-success btn-sm" title="Download">
                                    <i class='bx bx-download'></i> Download
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            }

            html += `
                <div class="activity-item ${typeClass} mb-3 p-3 border rounded">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6><i class='bx ${typeIcon}'></i> ${activity.type.charAt(0).toUpperCase() + activity.type.slice(1)}</h6>
                            <p class="mb-1">${activity.description}</p>
                            <small class="text-muted">By ${activity.created_by ? activity.created_by.name : 'System'} on ${formatDate(activity.created_at)}</small>
                            ${activity.follow_up_date ? `<div class="mt-1"><small class="text-info"><i class='bx bx-calendar'></i> Follow-up: ${formatDate(activity.follow_up_date)}</small></div>` : ''}
                            ${fileHtml}
                        </div>
                    </div>
                </div>
            `;
        });
        container.html(html);
    }

    // Add Activity
    // Initialize date-time picker for follow-up date
    let followUpDatePicker = null;
    
    $(document).on('click', '#addActivityBtn', function() {
        $('#activityLeadId').val(currentLeadId);
        $('#activityForm')[0].reset();
        
        // Initialize or reinitialize Flatpickr date-time picker
        if (followUpDatePicker) {
            followUpDatePicker.destroy();
        }
        
        followUpDatePicker = flatpickr('#activityFollowUpDate', {
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            time_24hr: true,
            minDate: 'today',
            defaultHour: new Date().getHours(),
            defaultMinute: new Date().getMinutes(),
            minuteIncrement: 1,
            allowInput: true,
            clickOpens: true,
            onChange: function(selectedDates, dateStr, instance) {
                // Format date for backend (YYYY-MM-DD HH:mm:ss)
                if (dateStr) {
                    const formatted = dateStr + ':00';
                    $('#activityFollowUpDate').data('formatted-date', formatted);
                }
            }
        });
        
        $('#activityModal').modal('show');
    });

    $('#activityType').on('change', function() {
        if($(this).val() === 'file') {
            $('#fileUploadSection').show();
        } else {
            $('#fileUploadSection').hide();
        }
    });

    $('#activityForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        let leadId = $('#activityLeadId').val();
        
        // Fallback to currentLeadId if activityLeadId is empty
        if (!leadId || leadId === '') {
            leadId = currentLeadId;
        }
        
        // Ensure leadId is a valid number/string
        if (!leadId) {
            showToast('error', 'Lead ID is missing');
            return;
        }
        
        // Get formatted date from datepicker if available
        const formattedDate = $('#activityFollowUpDate').data('formatted-date');
        if (formattedDate) {
            formData.set('follow_up_date', formattedDate);
        }

        $.ajax({
            url: `{{ route("leads.index") }}/${leadId}/activities`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', 'Activity added successfully');
                    $('#activityModal').modal('hide');
                    if (currentLeadId) {
                        loadActivities(currentLeadId);
                    }
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    displayFormErrors(errors, '#activityForm');
                } else {
                    showToast('error', 'Failed to add activity');
                }
            }
        });
    });

    // Quick Actions - handle clicks in popover content
    $(document).on('click', '.quick-status', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Close the popover - find the button by lead ID
        const leadId = $(this).data('id');
        const popoverBtn = $('#leadsTable .actions-popover[data-lead-id="' + leadId + '"]')[0];
        if (popoverBtn) {
            const popover = bootstrap.Popover.getInstance(popoverBtn);
            if (popover) {
                popover.hide();
            }
        }
        
        quickActionLeadId = leadId;
        const currentStatusId = $(this).data('status-id');
        
        // Ensure dropdown is populated
        if ($('#quickStatusSelect option').length <= 1) {
            populateMasterData();
        }
        
        // Set current value and initialize multiselect
        setTimeout(function() {
            if (currentStatusId) {
                $('#quickStatusSelect').val(currentStatusId);
            }
            if (!$('#quickStatusSelect').data('multiselect')) {
                $('#quickStatusSelect').multiselect({
                    enableFiltering: false,
                    buttonWidth: '100%',
                    includeSelectAllOption: false,
                    nonSelectedText: 'Select Status',
                    maxHeight: 300,
                    buttonClass: 'btn btn-outline-secondary'
                });
            } else {
                $('#quickStatusSelect').multiselect('refresh');
            }
        }, 100);
        
        $('#quickStatusModal').modal('show');
    });

    $(document).on('click', '.quick-assign', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Close the popover - find the button by lead ID
        const leadId = $(this).data('id');
        const popoverBtn = $('#leadsTable .actions-popover[data-lead-id="' + leadId + '"]')[0];
        if (popoverBtn) {
            const popover = bootstrap.Popover.getInstance(popoverBtn);
            if (popover) {
                popover.hide();
            }
        }
        
        quickActionLeadId = leadId;
        const currentUserId = $(this).data('user-id');
        
        // Ensure dropdown is populated
        if ($('#quickAssignSelect option').length <= 1) {
            populateMasterData();
        }
        
        // Set current value and initialize multiselect
        setTimeout(function() {
            if (currentUserId) {
                $('#quickAssignSelect').val(currentUserId);
            } else {
                $('#quickAssignSelect').val('');
            }
            if (!$('#quickAssignSelect').data('multiselect')) {
                $('#quickAssignSelect').multiselect({
                    enableFiltering: false,
                    buttonWidth: '100%',
                    includeSelectAllOption: false,
                    nonSelectedText: 'Unassigned',
                    maxHeight: 300,
                    buttonClass: 'btn btn-outline-secondary'
                });
            } else {
                $('#quickAssignSelect').multiselect('refresh');
            }
        }, 100);
        
        $('#quickAssignModal').modal('show');
    });

    $(document).on('click', '.quick-priority', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Close the popover - find the button by lead ID
        const leadId = $(this).data('id');
        const popoverBtn = $('#leadsTable .actions-popover[data-lead-id="' + leadId + '"]')[0];
        if (popoverBtn) {
            const popover = bootstrap.Popover.getInstance(popoverBtn);
            if (popover) {
                popover.hide();
            }
        }
        
        quickActionLeadId = leadId;
        const currentPriority = $(this).data('priority');
        
        // Set current value
        if (currentPriority) {
            $('#quickPrioritySelect').val(currentPriority);
        } else {
            $('#quickPrioritySelect').val('medium');
        }
        
        $('#quickPriorityModal').modal('show');
    });

    $('#confirmQuickStatus').on('click', function() {
        const statusId = $('#quickStatusSelect').val();
        if(!statusId) {
            showToast('error', 'Please select a status');
            return;
        }
        updateLeadStatus(quickActionLeadId, statusId);
    });

    $('#confirmQuickAssign').on('click', function() {
        const userId = $('#quickAssignSelect').val();
        assignLeadToUser(quickActionLeadId, userId);
    });

    $('#confirmQuickPriority').on('click', function() {
        const priority = $('#quickPrioritySelect').val();
        updateLeadPriority(quickActionLeadId, priority);
    });

    function updateLeadStatus(leadId, statusId) {
        $.ajax({
            url: `{{ route("leads.index") }}/${leadId}/status`,
            type: 'POST',
            data: { status_id: statusId },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', 'Status updated successfully');
                    $('#quickStatusModal').modal('hide');
                    loadLeads();
                }
            },
            error: function(xhr) {
                showToast('error', 'Failed to update status');
            }
        });
    }

    function assignLeadToUser(leadId, userId) {
        $.ajax({
            url: `{{ route("leads.index") }}/${leadId}/assign`,
            type: 'POST',
            data: { user_id: userId },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', 'Lead assigned successfully');
                    $('#quickAssignModal').modal('hide');
                    loadLeads();
                }
            },
            error: function(xhr) {
                showToast('error', 'Failed to assign lead');
            }
        });
    }

    function updateLeadPriority(leadId, priority) {
        $.ajax({
            url: `{{ route("leads.index") }}/${leadId}/priority`,
            type: 'POST',
            data: { priority: priority },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', 'Priority updated successfully');
                    $('#quickPriorityModal').modal('hide');
                    loadLeads();
                }
            },
            error: function(xhr) {
                showToast('error', 'Failed to update priority');
            }
        });
    }

    // Delete Lead
    $(document).on('click', '.delete-lead', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Close the popover - find the button by lead ID
        const leadId = $(this).data('id');
        const popoverBtn = $('#leadsTable .actions-popover[data-lead-id="' + leadId + '"]')[0];
        if (popoverBtn) {
            const popover = bootstrap.Popover.getInstance(popoverBtn);
            if (popover) {
                popover.hide();
            }
        }
        
        selectedLeads = [leadId];
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').on('click', function() {
        if(selectedLeads.length === 0) return;

        $.ajax({
            url: '{{ route("leads.bulk-delete") }}',
            type: 'POST',
            data: { lead_ids: selectedLeads },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    showToast('success', 'Lead(s) deleted successfully');
                    $('#deleteModal').modal('hide');
                    selectedLeads = [];
                    loadLeads();
                }
            },
            error: function(xhr) {
                showToast('error', 'Failed to delete lead(s)');
            }
        });
    });

    // Bulk Actions
    $('#selectAllCheckbox, #selectAllLeads').on('change', function() {
        const checked = $(this).is(':checked');
        $('.lead-checkbox').prop('checked', checked);
        updateSelectedLeads();
    });

    $(document).on('change', '.lead-checkbox', function() {
        updateSelectedLeads();
    });

    function updateSelectedLeads() {
        selectedLeads = [];
        $('.lead-checkbox:checked').each(function() {
            selectedLeads.push($(this).val());
        });
        $('#selectedCount').text(`${selectedLeads.length} selected`);
        if(selectedLeads.length > 0) {
            $('#bulkActions').show();
        } else {
            $('#bulkActions').hide();
        }
    }

    // Utility functions
    function resetLeadForm() {
        $('#leadForm')[0].reset();
        $('#leadId').val('');
        $('.invalid-feedback').text('');
        $('.form-control, .form-select').removeClass('is-invalid');
    }

    function displayFormErrors(errors, formSelector = '#leadForm') {
        $(formSelector + ' .form-control, ' + formSelector + ' .form-select').removeClass('is-invalid');
        $(formSelector + ' .invalid-feedback').text('');

        $.each(errors, function(field, messages) {
            const input = $(formSelector + ' [name="' + field + '"]');
            input.addClass('is-invalid');
            input.siblings('.invalid-feedback').text(messages[0]);
        });
    }

    function showToast(type, message) {
        const toast = $('#toast');
        
        // Check if toast element exists
        if (!toast.length || !toast[0]) {
            console.error('Toast element not found');
            alert(message); // Fallback to alert
            return;
        }
        
        const toastBody = toast.find('.toast-body');
        const toastHeader = toast.find('.toast-header');

        if (toastBody.length) {
            toastBody.text(message);
        }

        if(type === 'success') {
            if (toastHeader.length) {
                toastHeader.html('<i class=\'bx bx-check-circle text-success me-2\'></i><strong class="me-auto">Success</strong><button type="button" class="btn-close" data-bs-dismiss="toast"></button>');
            }
        } else {
            if (toastHeader.length) {
                toastHeader.html('<i class=\'bx bx-x-circle text-danger me-2\'></i><strong class="me-auto">Error</strong><button type="button" class="btn-close" data-bs-dismiss="toast"></button>');
            }
        }

        try {
            const bsToast = new bootstrap.Toast(toast[0], {
                autohide: true,
                delay: 5000
            });
            bsToast.show();
        } catch (e) {
            console.error('Error showing toast:', e);
            alert(message); // Fallback to alert
        }
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});
</script>
@endpush

@endsection
