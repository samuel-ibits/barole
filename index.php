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
// define('APP_NAME', 'ETRM System');
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
                  <!-- new panes -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="market-prices-tab" data-bs-toggle="tab"
                    data-bs-target="#market-prices" type="button" role="tab">
                    <i class="bi bi-currency-dollar"></i> Market Prices
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="contract-type-tab" data-bs-toggle="tab"
                    data-bs-target="#contract-type" type="button" role="tab">
                    <i class="bi bi-file-earmark"></i> Contract Type
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="commodity-name-tab" data-bs-toggle="tab"
                    data-bs-target="#commodity-name" type="button" role="tab">
                    <i class="bi bi-basket"></i> Commodity Name
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="product-uom-tab" data-bs-toggle="tab"
                    data-bs-target="#product-uom" type="button" role="tab">
                    <i class="bi bi-rulers"></i> Product UOM
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="pricing-unit-tab" data-bs-toggle="tab"
                    data-bs-target="#pricing-unit" type="button" role="tab">
                    <i class="bi bi-diagram-2"></i> Pricing Unit
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="pricing-formula-tab" data-bs-toggle="tab"
                    data-bs-target="#pricing-formula" type="button" role="tab">
                    <i class="bi bi-function"></i> Pricing Formula
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="market-index-tab" data-bs-toggle="tab"
                    data-bs-target="#market-index" type="button" role="tab">
                    <i class="bi bi-bar-chart"></i> Market Index
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="payment-terms-tab" data-bs-toggle="tab"
                    data-bs-target="#payment-terms" type="button" role="tab">
                    <i class="bi bi-wallet2"></i> Payment Terms
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="transfer-method-tab" data-bs-toggle="tab"
                    data-bs-target="#transfer-method" type="button" role="tab">
                    <i class="bi bi-arrow-left-right"></i> Transfer Method
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="governing-body-tab" data-bs-toggle="tab"
                    data-bs-target="#governing-body" type="button" role="tab">
                    <i class="bi bi-bank"></i> Governing Body
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="load-profit-tab" data-bs-toggle="tab"
                    data-bs-target="#load-profit" type="button" role="tab">
                    <i class="bi bi-graph-up-arrow"></i> Load Profit
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="discharging-port-tab" data-bs-toggle="tab"
                    data-bs-target="#discharging-port" type="button" role="tab">
                    <i class="bi bi-geo"></i> Discharging Port
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="pricing-uom-tab" data-bs-toggle="tab"
                    data-bs-target="#pricing-uom" type="button" role="tab">
                    <i class="bi bi-rulers"></i> Pricing UOM
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="internal-bu-tab" data-bs-toggle="tab"
                    data-bs-target="#internal-bu" type="button" role="tab">
                    <i class="bi bi-building"></i> Internal BU
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="api-fix-tab" data-bs-toggle="tab"
                    data-bs-target="#api-fix" type="button" role="tab">
                    <i class="bi bi-plug"></i> API FIX Trade Capture
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="portfolio-master-tab" data-bs-toggle="tab"
                    data-bs-target="#portfolio-master" type="button" role="tab">
                    <i class="bi bi-briefcase"></i> Portfolio
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="exchange-tab" data-bs-toggle="tab"
                    data-bs-target="#exchange" type="button" role="tab">
                    <i class="bi bi-building"></i> Exchange
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" id="currency-tab" data-bs-toggle="tab"
                    data-bs-target="#currency" type="button" role="tab">
                    <i class="bi bi-cash"></i> Currency
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
 <!-- Market Prices -->
        <div class="tab-pane fade" id="market-prices" role="tabpanel">
        <div class="mt-3">
            <h4>Market Prices</h4>
            <button class="btn btn-primary mb-3" data-action="create-market-price">
            <i class="bi bi-plus"></i> New Market Price
            </button>
            <div id="market-prices-table"></div>
        </div>
        </div>

        <!-- Contract Type -->
        <div class="tab-pane fade" id="contract-type" role="tabpanel">
        <div class="mt-3">
            <h4>Contract Type</h4>
            <button class="btn btn-primary mb-3" data-action="create-contract-type">
            <i class="bi bi-plus"></i> New Contract Type
            </button>
            <div id="contract-type-table"></div>
        </div>
        </div>

        <!-- Commodity Name -->
        <div class="tab-pane fade" id="commodity-name" role="tabpanel">
        <div class="mt-3">
            <h4>Commodity Name</h4>
            <button class="btn btn-primary mb-3" data-action="create-commodity">
            <i class="bi bi-plus"></i> New Commodity
            </button>
            <div id="commodity-name-table"></div>
        </div>
        </div>

        <!-- Product UOM -->
        <div class="tab-pane fade" id="product-uom" role="tabpanel">
        <div class="mt-3">
            <h4>Product UOM</h4>
            <button class="btn btn-primary mb-3" data-action="create-product-uom">
            <i class="bi bi-plus"></i> New UOM
            </button>
            <div id="product-uom-table"></div>
        </div>
        </div>

        <!-- Pricing Unit -->
        <div class="tab-pane fade" id="pricing-unit" role="tabpanel">
        <div class="mt-3">
            <h4>Pricing Unit</h4>
            <button class="btn btn-primary mb-3" data-action="create-pricing-unit">
            <i class="bi bi-plus"></i> New Pricing Unit
            </button>
            <div id="pricing-unit-table"></div>
        </div>
        </div>

        <!-- Pricing Formula -->
        <div class="tab-pane fade" id="pricing-formula" role="tabpanel">
        <div class="mt-3">
            <h4>Pricing Formula</h4>
            <button class="btn btn-primary mb-3" data-action="create-pricing-formula">
            <i class="bi bi-plus"></i> New Pricing Formula
            </button>
            <div id="pricing-formula-table"></div>
        </div>
        </div>

        <!-- Market Index -->
        <div class="tab-pane fade" id="market-index" role="tabpanel">
        <div class="mt-3">
            <h4>Market Index</h4>
            <button class="btn btn-primary mb-3" data-action="create-market-index">
            <i class="bi bi-plus"></i> New Index
            </button>
            <div id="market-index-table"></div>
        </div>
        </div>

        <!-- Payment Terms -->
        <div class="tab-pane fade" id="payment-terms" role="tabpanel">
        <div class="mt-3">
            <h4>Payment Terms</h4>
            <button class="btn btn-primary mb-3" data-action="create-payment-term">
            <i class="bi bi-plus"></i> New Payment Term
            </button>
            <div id="payment-terms-table"></div>
        </div>
        </div>

        <!-- Transfer Method -->
        <div class="tab-pane fade" id="transfer-method" role="tabpanel">
        <div class="mt-3">
            <h4>Transfer Method</h4>
            <button class="btn btn-primary mb-3" data-action="create-transfer-method">
            <i class="bi bi-plus"></i> New Transfer Method
            </button>
            <div id="transfer-method-table"></div>
        </div>
        </div>

        <!-- Governing Body -->
        <div class="tab-pane fade" id="governing-body" role="tabpanel">
        <div class="mt-3">
            <h4>Governing Body</h4>
            <button class="btn btn-primary mb-3" data-action="create-governing-body">
            <i class="bi bi-plus"></i> New Governing Body
            </button>
            <div id="governing-body-table"></div>
        </div>
        </div>

        <!-- Load Profit -->
        <div class="tab-pane fade" id="load-profit" role="tabpanel">
        <div class="mt-3">
            <h4>Load Profit</h4>
            <button class="btn btn-primary mb-3" data-action="create-load-profit">
            <i class="bi bi-plus"></i> New Load Profit
            </button>
            <div id="load-profit-table"></div>
        </div>
        </div>

        <!-- Discharging Port -->
        <div class="tab-pane fade" id="discharging-port" role="tabpanel">
        <div class="mt-3">
            <h4>Discharging Port</h4>
            <button class="btn btn-primary mb-3" data-action="create-discharging-port">
            <i class="bi bi-plus"></i> New Port
            </button>
            <div id="discharging-port-table"></div>
        </div>
        </div>

        <!-- Pricing UOM -->
        <div class="tab-pane fade" id="pricing-uom" role="tabpanel">
        <div class="mt-3">
            <h4>Pricing UOM</h4>
            <button class="btn btn-primary mb-3" data-action="create-pricing-uom">
            <i class="bi bi-plus"></i> New Pricing UOM
            </button>
            <div id="pricing-uom-table"></div>
        </div>
        </div>

        <!-- Internal BU -->
        <div class="tab-pane fade" id="internal-bu" role="tabpanel">
        <div class="mt-3">
            <h4>Internal BU</h4>
            <button class="btn btn-primary mb-3" data-action="create-internal-bu">
            <i class="bi bi-plus"></i> New BU
            </button>
            <div id="internal-bu-table"></div>
        </div>
        </div>

        <!-- API FIX Trade Capture -->
        <div class="tab-pane fade" id="api-fix" role="tabpanel">
        <div class="mt-3">
            <h4>API FIX Trade Capture</h4>
            <button class="btn btn-primary mb-3" data-action="create-api-fix">
            <i class="bi bi-plus"></i> New Capture
            </button>
            <div id="api-fix-table"></div>
        </div>
        </div>

        <!-- Portfolio -->
        <div class="tab-pane fade" id="portfolio-master" role="tabpanel">
        <div class="mt-3">
            <h4>Portfolio</h4>
            <button class="btn btn-primary mb-3" data-action="create-portfolio">
            <i class="bi bi-plus"></i> New Portfolio
            </button>
            <div id="portfolio-table"></div>
        </div>
        </div>

        <!-- Exchange -->
        <div class="tab-pane fade" id="exchange" role="tabpanel">
        <div class="mt-3">
            <h4>Exchange</h4>
            <button class="btn btn-primary mb-3" data-action="create-exchange">
            <i class="bi bi-plus"></i> New Exchange
            </button>
            <div id="exchange-table"></div>
        </div>
        </div>

        <!-- Currency -->
        <div class="tab-pane fade" id="currency" role="tabpanel">
        <div class="mt-3">
            <h4>Currency</h4>
            <button class="btn btn-primary mb-3" data-action="create-currency">
            <i class="bi bi-plus"></i> New Currency
            </button>
            <div id="currency-table"></div>
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
                <li class="nav-item">
                <button class="nav-link" id="security-privilege-tab" data-bs-toggle="tab"
                    data-bs-target="#security-privilege" type="button" role="tab">
                    <i class="bi bi-key"></i> Security Privilege
                </button>
                </li>

                <li class="nav-item">
                <button class="nav-link" id="trade-status-tab" data-bs-toggle="tab"
                    data-bs-target="#trade-status" type="button" role="tab">
                    <i class="bi bi-check2-circle"></i> Trade Status
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
                <!-- Security Privilege -->
                <div class="tab-pane fade" id="security-privilege" role="tabpanel">
                <div class="mt-3">
                    <h4>Security Privileges</h4>
                    <button class="btn btn-primary mb-3" data-action="create-privilege">
                    <i class="bi bi-plus"></i> New Privilege
                    </button>
                    <div id="security-privilege-table"></div>
                </div>
                </div>

                <!-- Trade Status -->
                <div class="tab-pane fade" id="trade-status" role="tabpanel">
                <div class="mt-3">
                    <h4>Trade Status</h4>
                    <button class="btn btn-primary mb-3" data-action="create-trade-status">
                    <i class="bi bi-plus"></i> New Trade Status
                    </button>
                    <div id="trade-status-table"></div>
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