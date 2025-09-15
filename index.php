<?php
/**
 * ETRM System - Main Entry Point
 * Energy Trading and Risk Management System - Simplified Version
 */

// Load simple session management
require_once 'includes/simple_session.php';

// Check session timeout (1 hour)
checkSessionTimeout(3600);

// Require user to be logged in
requireLogin();

// Get current user information
$currentUser = [
    'id' => getCurrentUserId(),
    'username' => getCurrentUsername(),
    'role' => getCurrentUserRole()
];

// Get user details
$userDetails = null;
if ($currentUser['id']) {
    try {
        require_once 'config/database.php';
        $db = getDB();
        $stmt = $db->query("SELECT * FROM users WHERE id = ?", [$currentUser['id']]);
        $userDetails = $stmt->fetch();
    } catch (Exception $e) {
        error_log("Failed to get user details: " . $e->getMessage());
        $userDetails = [
            'full_name' => $currentUser['username'] ?? 'Unknown User',
            'role' => $currentUser['role'] ?? 'viewer'
        ];
    }
}

if (!$userDetails) {
    $userDetails = [
        'full_name' => $currentUser['username'] ?? 'Unknown User',
        'role' => $currentUser['role'] ?? 'viewer'
    ];
}

// App constants (simplified)
define('APP_NAME', 'ETRM System');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom CSS -->
    <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-lightning-charge"></i>
                <?php echo APP_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" data-tab="dashboard">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-tab="trading">
                            <i class="bi bi-graph-up"></i> Trading
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-tab="operations">
                            <i class="bi bi-gear"></i> Operations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-tab="risk-analytics">
                            <i class="bi bi-shield-check"></i> Risk & Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-tab="master-data">
                            <i class="bi bi-database"></i> Master Data
                        </a>
                    </li>
                    <?php if ($currentUser['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-tab="user-management">
                            <i class="bi bi-people"></i> User Management
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <?php echo htmlspecialchars($userDetails['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" data-action="profile">
                                <i class="bi bi-person"></i> Profile
                            </a></li>
                            <li><a class="dropdown-item" href="#" data-action="change-password">
                                <i class="bi bi-key"></i> Change Password
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-3">
        <!-- Dashboard Tab -->
        <div id="dashboard-tab" class="main-tab active">
            <div class="row">
                <div class="col-12">
                    <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
                    <hr>
                </div>
            </div>
            
            <!-- Dashboard Widgets -->
            <div class="row" id="dashboard-widgets">
                <!-- Widgets will be loaded here -->
            </div>
        </div>

        <!-- Trading Tab -->
        <div id="trading-tab" class="main-tab">
            <div class="row">
                <div class="col-12">
                    <h2><i class="bi bi-graph-up"></i> Trading Operations</h2>
                    <hr>
                </div>
            </div>
            
            <!-- Trading Sub-tabs -->
            <ul class="nav nav-tabs" id="tradingTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="physical-sales-tab" data-bs-toggle="tab" data-bs-target="#physical-sales" type="button" role="tab">
                        <i class="bi bi-box"></i> Physical Sales
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="financial-trades-tab" data-bs-toggle="tab" data-bs-target="#financial-trades" type="button" role="tab">
                        <i class="bi bi-currency-exchange"></i> Financial Trades
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="fx-trades-tab" data-bs-toggle="tab" data-bs-target="#fx-trades" type="button" role="tab">
                        <i class="bi bi-globe"></i> FX Trades
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="tradingTabContent">
                <div class="tab-pane fade show active" id="physical-sales" role="tabpanel">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>Physical Sales</h4>
                            <button class="btn btn-primary" data-action="create-physical-sale">
                                <i class="bi bi-plus"></i> New Physical Sale
                            </button>
                        </div>
                        <div id="physical-sales-table">
                            <!-- Physical sales table will be loaded here -->
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="financial-trades" role="tabpanel">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>Financial Trades</h4>
                            <button class="btn btn-primary" data-action="create-financial-trade">
                                <i class="bi bi-plus"></i> New Financial Trade
                            </button>
                        </div>
                        <div id="financial-trades-table">
                            <!-- Financial trades table will be loaded here -->
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="fx-trades" role="tabpanel">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>FX Trades</h4>
                            <button class="btn btn-primary" data-action="create-fx-trade">
                                <i class="bi bi-plus"></i> New FX Trade
                            </button>
                        </div>
                        <div id="fx-trades-table">
                            <!-- FX trades table will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operations Tab -->
        <div id="operations-tab" class="main-tab">
            <div class="row">
                <div class="col-12">
                    <h2><i class="bi bi-gear"></i> Operations Management</h2>
                    <hr>
                </div>
            </div>
            
            <!-- Operations Sub-tabs -->
            <ul class="nav nav-tabs" id="operationsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab">
                        <i class="bi bi-receipt"></i> Invoices
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="logistics-tab" data-bs-toggle="tab" data-bs-target="#logistics" type="button" role="tab">
                        <i class="bi bi-truck"></i> Logistics
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="settlements-tab" data-bs-toggle="tab" data-bs-target="#settlements" type="button" role="tab">
                        <i class="bi bi-cash-coin"></i> Settlements
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="operationsTabContent">
                <div class="tab-pane fade show active" id="invoices" role="tabpanel">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>Invoices</h4>
                            <button class="btn btn-primary" data-action="create-invoice">
                                <i class="bi bi-plus"></i> New Invoice
                            </button>
                        </div>
                        <div id="invoices-table">
                            <!-- Invoices table will be loaded here -->
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="logistics" role="tabpanel">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>Logistics</h4>
                            <button class="btn btn-primary" data-action="create-logistics">
                                <i class="bi bi-plus"></i> New Logistics
                            </button>
                        </div>
                        <div id="logistics-table">
                            <!-- Logistics table will be loaded here -->
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="settlements" role="tabpanel">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>Settlements</h4>
                            <button class="btn btn-primary" data-action="create-settlement">
                                <i class="bi bi-plus"></i> New Settlement
                            </button>
                        </div>
                        <div id="settlements-table">
                            <!-- Settlements table will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Risk & Analytics Tab -->
        <div id="risk-analytics-tab" class="main-tab">
            <div class="row">
                <div class="col-12">
                    <h2><i class="bi bi-shield-check"></i> Risk & Analytics</h2>
                    <hr>
                </div>
            </div>
            
            <!-- Risk & Analytics Sub-tabs -->
            <ul class="nav nav-tabs" id="riskAnalyticsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="portfolio-tab" data-bs-toggle="tab" data-bs-target="#portfolio" type="button" role="tab">
                        <i class="bi bi-pie-chart"></i> Portfolio Analysis
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="risk-management-tab" data-bs-toggle="tab" data-bs-target="#risk-management" type="button" role="tab">
                        <i class="bi bi-exclamation-triangle"></i> Risk Management
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports" type="button" role="tab">
                        <i class="bi bi-file-earmark-text"></i> Reports
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="riskAnalyticsTabContent">
                <div class="tab-pane fade show active" id="portfolio" role="tabpanel">
                    <div class="mt-3">
                        <h4>Portfolio Analysis</h4>
                        <div id="portfolio-content">
                            <!-- Portfolio content will be loaded here -->
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="risk-management" role="tabpanel">
                    <div class="mt-3">
                        <h4>Risk Management</h4>
                        <div id="risk-alerts">
                            <!-- Risk alerts will be loaded here -->
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="reports" role="tabpanel">
                    <div class="mt-3">
                        <h4>Reports</h4>
                        <div id="reports-section">
                            <!-- Reports section will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Master Data Tab -->
        <div id="master-data-tab" class="main-tab">
            <div class="row">
                <div class="col-12">
                    <h2><i class="bi bi-database"></i> Master Data Management</h2>
                    <hr>
                </div>
            </div>
            
            <!-- Master Data Sub-tabs -->
            <ul class="nav nav-tabs" id="masterDataTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="counterparties-tab" data-bs-toggle="tab" data-bs-target="#counterparties" type="button" role="tab">
                        <i class="bi bi-building"></i> Counterparties
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button" role="tab">
                        <i class="bi bi-box"></i> Products
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="business-units-tab" data-bs-toggle="tab" data-bs-target="#business-units" type="button" role="tab">
                        <i class="bi bi-diagram-3"></i> Business Units
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="brokers-tab" data-bs-toggle="tab" data-bs-target="#brokers" type="button" role="tab">
                        <i class="bi bi-person-badge"></i> Brokers
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ports-tab" data-bs-toggle="tab" data-bs-target="#ports" type="button" role="tab">
                        <i class="bi bi-geo-alt"></i> Ports
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="carriers-tab" data-bs-toggle="tab" data-bs-target="#carriers" type="button" role="tab">
                        <i class="bi bi-truck"></i> Carriers
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="masterDataTabContent">
                <div class="tab-pane fade show active" id="counterparties" role="tabpanel">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>Counterparties</h4>
                            <button class="btn btn-primary" data-action="create-counterparty">
                                <i class="bi bi-plus"></i> New Counterparty
                            </button>
                        </div>
                        <div id="counterparties-table">
                            <!-- Counterparties table will be loaded here -->
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="products" role="tabpanel">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>Products</h4>
                            <button class="btn btn-primary" data-action="create-product">
                                <i class="bi bi-plus"></i> New Product
                            </button>
                        </div>
                        <div id="products-table">
                            <!-- Products table will be loaded here -->
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="business-units" role="tabpanel">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>Business Units</h4>
                            <button class="btn btn-primary" data-action="create-business-unit">
                                <i class="bi bi-plus"></i> New Business Unit
                            </button>
                        </div>
                        <div id="business-units-table">
                            <!-- Business units table will be loaded here -->
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="brokers" role="tabpanel">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>Brokers</h4>
                            <button class="btn btn-primary" data-action="create-broker">
                                <i class="bi bi-plus"></i> New Broker
                            </button>
                        </div>
                        <div id="brokers-table">
                            <!-- Brokers table will be loaded here -->
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="ports" role="tabpanel">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>Ports</h4>
                            <button class="btn btn-primary" data-action="create-port">
                                <i class="bi bi-plus"></i> New Port
                            </button>
                        </div>
                        <div id="ports-table">
                            <!-- Ports table will be loaded here -->
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="carriers" role="tabpanel">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>Carriers</h4>
                            <button class="btn btn-primary" data-action="create-carrier">
                                <i class="bi bi-plus"></i> New Carrier
                            </button>
                        </div>
                        <div id="carriers-table">
                            <!-- Carriers table will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Management Tab (Admin Only) -->
        <?php if ($currentUser['role'] === 'admin'): ?>
        <div id="user-management-tab" class="main-tab">
            <div class="row">
                <div class="col-12">
                    <h2><i class="bi bi-people"></i> User Management</h2>
                    <hr>
                </div>
            </div>
            
            <!-- User Management Sub-tabs -->
            <ul class="nav nav-tabs" id="userManagementTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                        <i class="bi bi-people"></i> Users
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles" type="button" role="tab">
                        <i class="bi bi-shield"></i> Roles
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="permissions-tab" data-bs-toggle="tab" data-bs-target="#permissions" type="button" role="tab">
                        <i class="bi bi-lock"></i> Permissions
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">
                        <i class="bi bi-activity"></i> Activity
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="userManagementTabContent">
                <!-- Users Tab -->
                <div class="tab-pane fade show active" id="users" role="tabpanel">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex gap-2">
                                <input type="text" class="form-control" id="user-search" placeholder="Search users...">
                                <select class="form-select" id="user-role-filter">
                                    <option value="">All Roles</option>
                                </select>
                                <select class="form-select" id="user-status-filter">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-success" id="export-users">
                                    <i class="bi bi-download"></i> Export
                                </button>
                                <button class="btn btn-primary" data-action="create-user">
                                    <i class="bi bi-plus"></i> New User
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="users-table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select-all-users"></th>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Full Name</th>
                                        <th>Status</th>
                                        <th>Last Login</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Users will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="pagination-container">
                            <nav>
                                <ul class="pagination">
                                    <!-- Pagination will be generated here -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                
                <!-- Roles Tab -->
                <div class="tab-pane fade" id="roles" role="tabpanel">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>Roles</h4>
                            <button class="btn btn-primary" data-action="create-role">
                                <i class="bi bi-plus"></i> New Role
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="roles-table">
                                <thead>
                                    <tr>
                                        <th>Role</th>
                                        <th>Users</th>
                                        <th>Permissions</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Roles will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Permissions Tab -->
                <div class="tab-pane fade" id="permissions" role="tabpanel">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>Permissions</h4>
                            <button class="btn btn-primary" data-action="create-permission">
                                <i class="bi bi-plus"></i> New Permission
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="permissions-table">
                                <thead>
                                    <tr>
                                        <th>Permission</th>
                                        <th>Category</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Permissions will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Tab -->
                <div class="tab-pane fade" id="activity" role="tabpanel">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex gap-2">
                                <select class="form-select" id="activity-user-filter">
                                    <option value="">All Users</option>
                                </select>
                                <select class="form-select" id="activity-type-filter">
                                    <option value="">All Activities</option>
                                    <option value="login">Login</option>
                                    <option value="logout">Logout</option>
                                    <option value="create">Create</option>
                                    <option value="update">Update</option>
                                    <option value="delete">Delete</option>
                                </select>
                                <input type="date" class="form-control" id="activity-date-from" placeholder="From Date">
                                <input type="date" class="form-control" id="activity-date-to" placeholder="To Date">
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="activity-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Activity</th>
                                        <th>Details</th>
                                        <th>IP Address</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Activity will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modals -->
    <div id="modal-container">
        <!-- Modals will be dynamically created here -->
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/app.js"></script>
</body>
</html> 