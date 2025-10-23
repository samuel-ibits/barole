/**
 * ETRM System - Main Application
 * Clean, simple implementation focused on functionality
 */

// Global Application Object
window.ETRM = {
    // Application state
    state: {
        currentTab: 'dashboard',
        isLoading: false,
        user: null
    },

    // Configuration
    config: {
        apiBaseUrl: '/api',
        refreshInterval: 30000
    },

    // Initialize the application
    init() {
        console.log('🚀 Initializing ETRM System...');
        
        this.setupEventListeners();
        this.restoreLastTab();
        
        console.log('✅ ETRM System initialized successfully');
    },

    // Setup all event listeners
    setupEventListeners() {
        // Tab navigation
        document.querySelectorAll('[data-tab]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const tabName = e.currentTarget.getAttribute('data-tab');
                this.switchTab(tabName);
            });
        });

        // Sub-tab navigation (Bootstrap tabs)
        document.querySelectorAll('.nav-tabs .nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                const tabId = e.currentTarget.getAttribute('data-bs-target');
                if (tabId) {
                    this.handleSubTab(tabId);
                }
            });
        });

        // Create/New button actions
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action^="create-"]') || e.target.closest('[data-action^="create-"]')) {
                const button = e.target.matches('[data-action^="create-"]') ? e.target : e.target.closest('[data-action^="create-"]');
                const action = button.getAttribute('data-action');
                this.handleCreateAction(action);
            }
        });
    },

    // Switch main tabs
    switchTab(tabName) {
        console.log(`🔄 Switching to tab: ${tabName}`);

        // Update navigation
        document.querySelectorAll('[data-tab]').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

        // Hide all tabs
        document.querySelectorAll('.main-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        // Show target tab
        const targetTab = document.getElementById(`${tabName}-tab`);
        if (targetTab) {
            targetTab.classList.add('active');
        }

        // Update state and save to localStorage
        this.state.currentTab = tabName;
        localStorage.setItem('etrm_current_tab', tabName);

        // Load tab content
        this.loadTabContent(tabName);
    },

    // Restore the last viewed tab on page load
    restoreLastTab() {
        const savedTab = localStorage.getItem('etrm_current_tab');
        const savedSubTab = localStorage.getItem('etrm_current_subtab');
        const validTabs = ['dashboard', 'trading', 'operations', 'master-data', 'reports', 'admin'];
        
        // Use saved tab if valid, otherwise default to dashboard
        const tabToLoad = (savedTab && validTabs.includes(savedTab)) ? savedTab : 'dashboard';
        
        console.log(`🔄 Restoring last tab: ${tabToLoad}`);
        this.switchTab(tabToLoad);
        
        // Restore sub-tab if we have one and it's relevant to the current tab
        if (savedSubTab && tabToLoad !== 'dashboard') {
            setTimeout(() => {
                this.restoreSubTab(savedSubTab, tabToLoad);
            }, 100); // Small delay to ensure main tab loads first
        }
    },

    // Restore the last viewed sub-tab
    restoreSubTab(subTabId, currentTab) {
        // Map sub-tabs to their parent tabs
        const subTabMap = {
            'physical-sales': 'trading',
            'financial-trades': 'trading', 
            'fx-trades': 'trading',
            'invoices': 'operations',
            'logistics': 'operations',
            'settlements': 'operations',
            'counterparties': 'master-data',
            'products': 'master-data',
            'business-units': 'master-data',
            'brokers': 'master-data',
            'ports': 'master-data',
            'carriers': 'master-data',
            'portfolio': 'reports',
            'risk-management': 'reports',
            'reports': 'reports',
            'users': 'admin',
            'roles': 'admin',
            'permissions': 'admin',
            'activity': 'admin'
        };
        
        // Only restore sub-tab if it belongs to the current main tab
        if (subTabMap[subTabId] === currentTab) {
            console.log(`🔄 Restoring sub-tab: ${subTabId}`);
            
            // Activate the Bootstrap tab
            const tabElement = document.querySelector(`[data-bs-target="#${subTabId}"]`);
            if (tabElement) {
                const tab = new bootstrap.Tab(tabElement);
                tab.show();
            }
            
            // Load the sub-tab content
            this.handleSubTab(`#${subTabId}`);
        }
    },

    // Load content for specific tab
    loadTabContent(tabName) {
        // Clear dashboard content when switching away from dashboard
        if (tabName !== 'dashboard') {
            this.clearDashboardContent();
        }

        switch (tabName) {
            case 'dashboard':
                this.loadDashboard();
                break;
            case 'trading':
                this.loadTrading();
                break;
            case 'operations':
                this.loadOperations();
                break;
            case 'risk-analytics':
                this.loadRiskAnalytics();
                break;
            case 'master-data':
                this.loadMasterData();
                break;
            case 'user-management':
                this.loadUserManagement();
                break;
        }
    },

    // Clear dashboard content (utility method)
    clearDashboardContent() {
        const container = document.getElementById('dashboard-widgets');
        if (container) {
            container.innerHTML = '';
        }
    },

    // Handle sub-tabs
    handleSubTab(tabId) {
        const cleanId = tabId.replace('#', '');
        console.log(`📋 Loading sub-tab: ${cleanId}`);
        
        // Save current sub-tab to localStorage
        localStorage.setItem('etrm_current_subtab', cleanId);
        
        // Load data based on sub-tab
        switch (cleanId) {
            case 'physical-sales':
                this.loadPhysicalSales();
                break;
            case 'financial-trades':
                this.loadFinancialTrades();
                break;
            case 'fx-trades':
                this.loadFXTrades();
                break;
            case 'invoices':
                this.loadInvoices();
                break;
            case 'logistics':
                this.loadLogistics();
                break;
            case 'settlements':
                this.loadSettlements();
                break;
            case 'counterparties':
                this.loadCounterparties();
                break;
            case 'products':
                this.loadProducts();
                break;
            case 'business-units':
                this.loadBusinessUnits();
                break;
            case 'brokers':
                this.loadBrokers();
                break;
            case 'ports':
                this.loadPorts();
                break;
            case 'carriers':
                this.loadCarriers();
                break;
            case 'fx-trade-capture':
                this.loadTable('fx-trade-capture-table', '/trading/fx-trades.php', this.getFXTradesColumns, 'Failed to load FX trade capture');
                break;
            case 'portfolio':
                this.loadPortfolio();
                break;
            case 'risk-management':
                this.loadRiskAlerts();
                break;
            case 'reports':
                this.loadReports();
                break;
            case 'users':
                this.loadUsers();
                break;
            case 'roles':
                this.loadRoles();
                break;
            case 'permissions':
                this.loadPermissions();
                break;
            case 'activity':
                this.loadActivity();
                break;
            // load new sub-tabs here
            case 'fx-trades':
                this.loadTable('fx-trades-table', '/trading/fx-trades.php', this.getFXTradesColumns, 'Failed to load FX trades');
                break;
            case 'physical-sales':
                this.loadTable('physical-sales-table', '/trading/physical-sales.php', this.getPhysicalSalesColumns, 'Failed to load physical sales');
                break;
            case 'financial-trades':
                this.loadTable('financial-trades-table', '/trading/financial-trades.php', this.getFinancialTradesColumns, 'Failed to load financial trades');
                break;

            // Operations
            case 'invoices':
                this.loadTable('invoices-table', '/operations/invoices.php', this.getInvoicesColumns, 'Failed to load invoices');
                break;
            case 'settlements':
                this.loadTable('settlements-table', '/operations/settlements.php', this.getSettlementsColumns, 'Failed to load settlements');
                break;
            case 'logistics':
                this.loadTable('logistics-table', '/operations/logistics.php', this.getLogisticsColumns, 'Failed to load logistics');
                break;

            // Master Data
            case 'counterparties':
                this.loadTable('counterparties-table', '/master-data/counterparties.php', this.getCounterpartiesColumns, 'Failed to load counterparties');
                break;
            case 'products':
                this.loadTable('products-table', '/master-data/products.php', this.getProductsColumns, 'Failed to load products');
                break;
            case 'business-units':
                this.loadTable('business-units-table', '/master-data/business-units.php', this.getBusinessUnitsColumns, 'Failed to load business units');
                break;
            case 'brokers':
                this.loadTable('brokers-table', '/master-data/brokers.php', this.getBrokersColumns, 'Failed to load brokers');
                break;
            case 'ports':
                this.loadTable('ports-table', '/master-data/ports.php', this.getPortsColumns, 'Failed to load ports');
                break;
            case 'carriers':
                this.loadTable('carriers-table', '/master-data/carriers.php', this.getCarriersColumns, 'Failed to load carriers');
                break;
            case 'contract-type':
                this.loadTable('contract-types-table', '/master-data/contract-types.php', this.getContractTypesColumns, 'Failed to load contract types');
                break;
            case 'commodity-name':
                this.loadTable('commodity-names-table', '/master-data/commodities.php', this.getCommoditiesColumns, 'Failed to load commodity names');
                break;
            case 'product-uom':
                this.loadTable('product-uom-table', '/master-data/product-uom.php', this.getProductUOMColumns, 'Failed to load product UOM');
                break;
            case 'pricing-units':
                this.loadTable('pricing-units-table', '/master-data/pricing-units.php', this.getPricingUnitsColumns, 'Failed to load pricing units');
                break;
            case 'pricing-formulas':
                this.loadTable('pricing-formulas-table', '/master-data/pricing-formulas.php', this.getPricingFormulasColumns, 'Failed to load pricing formulas');
                break;
            case 'market-index':
                this.loadTable('market-index-table', '/master-data/market-index.php', this.getMarketIndexColumns, 'Failed to load market index');
                break;
            case 'payment-terms':
                this.loadTable('payment-term-table', '/master-data/payment-terms.php', this.getPaymentTermsColumns, 'Failed to load payment terms');
                break;
            case 'transfer-method':
                this.loadTable('transfer-method-table', '/master-data/transfer-methods.php', this.getTransferMethodsColumns, 'Failed to load transfer methods');
                break;
            case 'governing-bodies':
                this.loadTable('governing-bodies-table', '/master-data/governing-bodies.php', this.getGoverningBodiesColumns, 'Failed to load governing bodies');
                break;
            case 'load-profits':
                this.loadTable('load-profits-table', '/master-data/load-profits.php', this.getLoadProfitsColumns, 'Failed to load load profits');
                break;
            case 'discharging-ports':
                this.loadTable('discharging-ports-table', '/master-data/discharging-ports.php', this.getDischargingPortsColumns, 'Failed to load discharging ports');
                break;
            case 'pricing-uom':
                this.loadTable('pricing-uom-table', '/master-data/pricing-uom.php', this.getPricingUOMColumns, 'Failed to load pricing UOM');
                break;
            case 'internal-bu':
                this.loadTable('internal-bu-table', '/master-data/internal-bu.php', this.getInternalBUColumns, 'Failed to load internal business units');
                break;
            case 'portfolio-master':
                this.loadTable('portfolio-master-table', '/master-data/portfolio.php', this.getPortfolioColumns, 'Failed to load portfolio master');
                break;
            case 'exchange':
                this.loadTable('exchange-table', '/master-data/exchange.php', this.getExchangeColumns, 'Failed to load exchange');
                break;
            case 'currency':
                this.loadTable('currency-table', '/master-data/currency.php', this.getCurrencyColumns, 'Failed to load currency');
                break;

            // End of Day (EOB) Checklist under Master Data
            case 'end-of-day':
                this.renderEndOfDayChecklist();
                break;
        
            // Invoice Capture (Master Data)
            case 'invoice-capture':
                this.loadTable('invoice-capture-table', '/operations/invoices.php', this.getInvoicesColumns, 'Failed to load invoice capture');
                break;
            // Logistics & Scheduling (Master Data)
            case 'logistics-scheduling':
                this.loadTable('logistics-scheduling-table', '/operations/logistics.php', this.getLogisticsColumns, 'Failed to load logistics & scheduling');
                break;

            // Users & Security
            case 'users':
                this.loadTable('users-table', '/users/list.php', this.getUsersColumns, 'Failed to load users');
                break;
            case 'roles':
                this.loadTable('roles-table', '/users/roles.php', this.getRolesColumns, 'Failed to load roles');
                break;
            case 'permissions':
                this.loadTable('permissions-table', '/users/permissions.php', this.getPermissionsColumns, 'Failed to load permissions');
                break;
            case 'activity':
                this.loadTable('activity-table', '/users/activity.php', this.getActivityColumns, 'Failed to load activity');
                break;
            case 'market-prices': 
                this.loadTable('market-prices-table', '/master-data/market-prices.php', this.getMarketPricesColumns, 'Failed to load market prices'); 
                 break; 
            case 'commodity_name':     
                this.loadTable('commodity-names-table', '/master-data/commodity-names.php', this.getCommoditiesColumns, 'Failed to load commodity names');
                break;  
            case 'pricing-unit': 
                this.loadTable('pricing-unit-table', '/master-data/pricing-units.php', this.getPricingUnitsColumns, 'Failed to load pricing units'); 
                break; 
            case 'pricing-formula': 
                this.loadTable('pricing-formula-table', '/master-data/pricing-formulas.php', this.getPricingFormulasColumns, 'Failed to load pricing formulas'); 
                break; 
            case 'transfer-methods': 
                this.loadTable('transfer-method-table', '/master-data/transfer-methods.php', this.getTransferMethodsColumns, 'Failed to load transfer methods'); 
                break; 
            case 'governing-body': 
                this.loadTable('governing-bodies-table', '/master-data/governing-bodies.php', this.getGoverningBodiesColumns, 'Failed to load governing bodies'); 
                break; 
            case 'load_profits': 
                this.loadTable('load-profits-table', '/master-data/load-profits.php', this.getLoadProfitsColumns, 'Failed to load load profits'); 
                break; 
            case 'discharging-port': 
                this.loadTable('discharging-port-table', '/master-data/discharging-ports.php', this.getDischargingPortsColumns, 'Failed to load discharging ports'); 
                break; 
          
            // Integrations
            case 'api-fix':
                this.loadTable('api-fix-table', '/integrations/api-fix.php', this.getApiFixColumns, 'Failed to load API FIX');
                break;

            default:
                console.warn(`Unknown table type: ${cleanId}`);
        

            }
        },

    // Render EOB Checklist into its container if present
    renderEndOfDayChecklist() {
        const container = document.getElementById('end-of-day-content');
        if (!container) {
            console.warn('EOB container not found: #end-of-day-content');
            // As a fallback, open in a modal
            this.showCreateModal('End of Day Checklist', this.getEndOfDayProcessForm(), 'master-data/end-of-day.php');
            return;
        }
        container.innerHTML = this.getEndOfDayProcessForm();
    },

    // Render COB Checklist into its container if present
    renderCloseOfBusinessChecklist() {
        const container = document.getElementById('close-of-business-content');
        if (!container) {
            console.warn('COB container not found: #close-of-business-content');
            // Fallback to modal using the same form for now; can be customized later
            this.showCreateModal('Close of Business Checklist', this.getEndOfDayProcessForm(), 'master-data/close-of-business.php');
            return;
        }
        container.innerHTML = this.getEndOfDayProcessForm();
    },

    // Clear saved tab state (utility method)
    clearTabState() {
        localStorage.removeItem('etrm_current_tab');
        localStorage.removeItem('etrm_current_subtab');
        console.log('🧹 Tab state cleared');
    },

    // ===== DASHBOARD METHODS =====
    loadDashboard() {
        console.log('📊 Loading dashboard...');
        this.loadDashboardWidgets();
    },

    loadDashboardWidgets() {
        const container = document.getElementById('dashboard-widgets');
        if (!container) {
            console.warn('Dashboard widgets container not found');
            return;
        }

        this.showLoading(container);

        this.apiCall('/dashboard/widgets.php')
            .then(data => {
                if (data.success) {
                    this.renderWidgets(container, data.data);
                } else {
                    this.showError(container, 'Failed to load dashboard widgets');
                }
            })
            .catch(error => {
                console.error('Dashboard widgets error:', error);
                this.showError(container, 'Error loading dashboard widgets');
            });
    },

    renderWidgets(container, widgets) {
        let html = '';
        
        widgets.forEach(widget => {
            const changeClass = widget.change > 0 ? 'positive' : widget.change < 0 ? 'negative' : '';
            const changeIcon = widget.change > 0 ? '↗️' : widget.change < 0 ? '↘️' : '➡️';
            
            html += `
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="dashboard-widget fade-in">
                        <div class="widget-icon">
                            <i class="${widget.icon}"></i>
                        </div>
                        <div class="widget-value">${widget.value}</div>
                        <div class="widget-title">${widget.title}</div>
                        ${widget.change !== 0 ? `
                            <div class="widget-change ${changeClass}">
                                ${changeIcon} ${Math.abs(widget.change)}%
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        console.log(`✅ Rendered ${widgets.length} dashboard widgets`);
    },

    // ===== TRADING METHODS =====
    loadTrading() {
        console.log('💹 Loading trading data...');
        // Default to physical sales
        this.loadPhysicalSales();
    },

    loadPhysicalSales() {
        const container = document.getElementById('physical-sales-table');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/trading/physical-sales.php')
            .then(data => {
                if (data.success) {
                    this.renderTable(container, data.data, this.getPhysicalSalesColumns());
                } else {
                    this.showError(container, 'Failed to load physical sales');
                }
            })
            .catch(error => {
                console.error('Physical sales error:', error);
                this.showError(container, 'Error loading physical sales');
            });
    },

    loadFinancialTrades() {
        const container = document.getElementById('financial-trades-table');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/trading/financial-trades.php')
            .then(data => {
                if (data.success) {
                    this.renderTable(container, data.data, this.getFinancialTradesColumns());
                } else {
                    this.showError(container, 'Failed to load financial trades');
                }
            })
            .catch(error => {
                console.error('Financial trades error:', error);
                this.showError(container, 'Error loading financial trades');
            });
    },

    loadFXTrades() {
        const container = document.getElementById('fx-trades-table');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/trading/fx-trades.php')
            .then(data => {
                if (data.success) {
                    this.renderTable(container, data.data, this.getFXTradesColumns());
                } else {
                    this.showError(container, 'Failed to load FX trades');
                }
            })
            .catch(error => {
                console.error('FX trades error:', error);
                this.showError(container, 'Error loading FX trades');
            });
    },

    // ===== OPERATIONS METHODS =====
    loadOperations() {
        console.log('⚙️ Loading operations data...');
        this.loadInvoices();
    },

    loadInvoices() {
        const container = document.getElementById('invoices-table');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/operations/invoices.php')
            .then(data => {
                if (data.success) {
                    this.renderTable(container, data.data, this.getInvoicesColumns());
                } else {
                    this.showError(container, 'Failed to load invoices');
                }
            })
            .catch(error => {
                console.error('Invoices error:', error);
                this.showError(container, 'Error loading invoices');
            });
    },

    loadLogistics() {
        const container = document.getElementById('logistics-table');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/operations/logistics.php')
            .then(data => {
                if (data.success) {
                    this.renderTable(container, data.data, this.getLogisticsColumns());
                } else {
                    this.showError(container, 'Failed to load logistics');
                }
            })
            .catch(error => {
                console.error('Logistics error:', error);
                this.showError(container, 'Error loading logistics');
            });
    },

    loadSettlements() {
        const container = document.getElementById('settlements-table');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/operations/settlements.php')
            .then(data => {
                if (data.success) {
                    this.renderTable(container, data.data, this.getSettlementsColumns());
                } else {
                    this.showError(container, 'Failed to load settlements');
                }
            })
            .catch(error => {
                console.error('Settlements error:', error);
                this.showError(container, 'Error loading settlements');
            });
    },

    // ===== RISK & ANALYTICS METHODS =====
    loadRiskAnalytics() {
        console.log('📈 Loading risk analytics...');
        this.loadPortfolio();
        this.loadRiskAlerts();
    },

    loadReports() {
        const container = document.getElementById('reports-section');
        if (!container) return;

        this.showLoading(container);

        // Load reports interface
        this.renderReportsInterface(container);
        
        console.log('📊 Reports system loaded');
    },

    renderReportsInterface(container) {
        container.innerHTML = `
            <div class="row">
                <!-- Report Categories -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-folder2-open"></i> Report Categories</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <button type="button" class="list-group-item list-group-item-action active" 
                                        data-report-category="trading">
                                    <i class="bi bi-graph-up"></i> Trading Reports
                    </button>
                                <button type="button" class="list-group-item list-group-item-action" 
                                        data-report-category="operations">
                                    <i class="bi bi-gear"></i> Operations Reports
                    </button>
                                <button type="button" class="list-group-item list-group-item-action" 
                                        data-report-category="risk">
                                    <i class="bi bi-shield-exclamation"></i> Risk Reports
                                </button>
                                <button type="button" class="list-group-item list-group-item-action" 
                                        data-report-category="financial">
                                    <i class="bi bi-currency-dollar"></i> Financial Reports
                                </button>
                                <button type="button" class="list-group-item list-group-item-action" 
                                        data-report-category="regulatory">
                                    <i class="bi bi-file-text"></i> Regulatory Reports
                    </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Generation Panel -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-file-earmark-bar-graph"></i> Generate Reports</h5>
                        </div>
                        <div class="card-body">
                            <div id="report-generator">
                                <!-- Report generation form will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Reports -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-clock-history"></i> Recent Reports</h5>
                        </div>
                        <div class="card-body">
                            <div id="recent-reports">
                                <!-- Recent reports will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Set up event listeners for report categories
        container.querySelectorAll('[data-report-category]').forEach(button => {
            button.addEventListener('click', (e) => {
                // Update active state
                container.querySelectorAll('[data-report-category]').forEach(btn => 
                    btn.classList.remove('active'));
                e.target.classList.add('active');
                
                // Load report generator for category
                const category = e.target.getAttribute('data-report-category');
                this.loadReportGenerator(category);
            });
        });

        // Load default category (trading)
        this.loadReportGenerator('trading');
        this.loadRecentReports();
    },

    loadReportGenerator(category) {
        const container = document.getElementById('report-generator');
        if (!container) return;

        const reportTypes = this.getReportTypes(category);
        
        container.innerHTML = `
            <form id="report-generation-form" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-control" id="reportType" name="reportType" required>
                                <option value="">Select Report Type</option>
                                ${reportTypes.map(type => 
                                    `<option value="${type.value}">${type.label}</option>`
                                ).join('')}
                            </select>
                            <label for="reportType">Report Type</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-control" id="reportFormat" name="reportFormat" required>
                                <option value="">Select Format</option>
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                            <label for="reportFormat">Format</label>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="startDate" name="startDate" required>
                            <label for="startDate">Start Date</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="endDate" name="endDate" required>
                            <label for="endDate">End Date</label>
                        </div>
                    </div>
                </div>

                ${this.getAdditionalFilters(category)}

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-download"></i> Generate Report
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2" onclick="ETRM.scheduleReport()">
                            <i class="bi bi-calendar-plus"></i> Schedule Report
                        </button>
                    </div>
                </div>
            </form>
        `;

        // Set default date range (last 30 days)
        const today = new Date();
        const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
        
        document.getElementById('startDate').value = thirtyDaysAgo.toISOString().split('T')[0];
        document.getElementById('endDate').value = today.toISOString().split('T')[0];

        // Set up form submission
        document.getElementById('report-generation-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.generateReport(e.target, category);
        });
    },

    getReportTypes(category) {
        const reportTypes = {
            trading: [
                { value: 'trade_summary', label: 'Trade Summary Report' },
                { value: 'pnl_report', label: 'P&L Report' },
                { value: 'volume_analysis', label: 'Volume Analysis' },
                { value: 'performance_metrics', label: 'Trading Performance Metrics' },
                { value: 'position_report', label: 'Position Report' }
            ],
            operations: [
                { value: 'invoice_summary', label: 'Invoice Summary' },
                { value: 'settlement_report', label: 'Settlement Report' },
                { value: 'logistics_tracking', label: 'Logistics Tracking Report' },
                { value: 'operational_metrics', label: 'Operational Metrics' }
            ],
            risk: [
                { value: 'var_report', label: 'Value at Risk (VaR) Report' },
                { value: 'exposure_analysis', label: 'Exposure Analysis' },
                { value: 'credit_risk', label: 'Credit Risk Report' },
                { value: 'market_risk', label: 'Market Risk Assessment' },
                { value: 'risk_limits', label: 'Risk Limits Monitoring' }
            ],
            financial: [
                { value: 'cashflow_report', label: 'Cash Flow Report' },
                { value: 'balance_sheet', label: 'Balance Sheet' },
                { value: 'income_statement', label: 'Income Statement' },
                { value: 'financial_summary', label: 'Financial Summary' }
            ],
            regulatory: [
                { value: 'compliance_report', label: 'Compliance Report' },
                { value: 'audit_trail', label: 'Audit Trail' },
                { value: 'regulatory_filing', label: 'Regulatory Filing' },
                { value: 'transaction_report', label: 'Transaction Report' }
            ]
        };

        return reportTypes[category] || [];
    },

    getAdditionalFilters(category) {
        const filters = {
            trading: `
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-control" id="commodity" name="commodity">
                                <option value="">All Commodities</option>
                                <option value="crude_oil">Crude Oil</option>
                                <option value="natural_gas">Natural Gas</option>
                                <option value="refined_products">Refined Products</option>
                            </select>
                            <label for="commodity">Commodity</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-control" id="trader" name="trader">
                                <option value="">All Traders</option>
                                <option value="1">John Smith</option>
                                <option value="2">Sarah Johnson</option>
                                <option value="3">Michael Chen</option>
                            </select>
                            <label for="trader">Trader</label>
                        </div>
                    </div>
                </div>
            `,
            operations: `
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-control" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <label for="status">Status</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-control" id="counterparty" name="counterparty">
                                <option value="">All Counterparties</option>
                                <option value="1">ABC Energy Corp</option>
                                <option value="2">XYZ Trading Ltd</option>
                                <option value="3">Global Petro Inc</option>
                            </select>
                            <label for="counterparty">Counterparty</label>
                        </div>
                    </div>
                </div>
            `,
            risk: `
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-control" id="riskType" name="riskType">
                                <option value="">All Risk Types</option>
                                <option value="market">Market Risk</option>
                                <option value="credit">Credit Risk</option>
                                <option value="operational">Operational Risk</option>
                            </select>
                            <label for="riskType">Risk Type</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-control" id="confidenceLevel" name="confidenceLevel">
                                <option value="95">95% Confidence</option>
                                <option value="99">99% Confidence</option>
                            </select>
                            <label for="confidenceLevel">Confidence Level</label>
                        </div>
                    </div>
                </div>
            `
        };

        return filters[category] || '';
    },

    generateReport(form, category) {
        const formData = new FormData(form);
        const reportParams = Object.fromEntries(formData.entries());
        
        console.log('🔄 Generating report:', { category, ...reportParams });
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Generating...';
        submitBtn.disabled = true;

        // Simulate report generation
        this.apiCall('/reports/generate.php', 'POST', { category, ...reportParams })
            .then(data => {
                if (data.success) {
                    this.showSuccess('Report generated successfully!');
                    this.loadRecentReports(); // Refresh recent reports
                    
                    // Trigger download
                    if (data.downloadUrl) {
                        window.open(data.downloadUrl, '_blank');
                    }
                } else {
                    this.showError(form, 'Failed to generate report: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Report generation error:', error);
                this.showError(form, 'Error generating report');
            })
            .finally(() => {
                // Restore button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    },

    loadRecentReports() {
        const container = document.getElementById('recent-reports');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/reports/recent.php')
            .then(data => {
                if (data.success) {
                    this.renderRecentReports(container, data.data);
                } else {
                    this.showError(container, 'Failed to load recent reports');
                }
            })
            .catch(error => {
                console.error('Recent reports error:', error);
                this.showError(container, 'Error loading recent reports');
            });
    },

    renderRecentReports(container, reports) {
        if (!reports || reports.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="bi bi-file-earmark-text" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="mt-2">No reports generated yet</p>
                </div>
            `;
            return;
        }

        const tableHTML = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Report Name</th>
                            <th>Category</th>
                            <th>Format</th>
                            <th>Generated</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${reports.map(report => `
                            <tr>
                                <td>
                                    <i class="bi bi-file-earmark-${this.getFileIcon(report.format)}"></i>
                                    ${report.name}
                                </td>
                                <td>
                                    <span class="badge bg-secondary">${report.category}</span>
                                </td>
                                <td>${report.format.toUpperCase()}</td>
                                <td>${this.formatDate(report.generated_at)}</td>
                                <td>
                                    <span class="badge bg-${this.getStatusColor(report.status)}">${report.status}</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="ETRM.downloadReport('${report.id}')"
                                                ${report.status !== 'completed' ? 'disabled' : ''}>
                                            <i class="bi bi-download"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="ETRM.deleteReport('${report.id}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;

        container.innerHTML = tableHTML;
    },

    getFileIcon(format) {
        const icons = {
            pdf: 'pdf',
            excel: 'excel',
            csv: 'text'
        };
        return icons[format] || 'text';
    },

    getStatusColor(status) {
        const colors = {
            'pending': 'warning',
            'generating': 'info',
            'completed': 'success',
            'failed': 'danger'
        };
        return colors[status] || 'secondary';
    },

    downloadReport(reportId) {
        console.log('🔄 Downloading report:', reportId);
        // Implement download logic
        window.open(`/api/reports/download.php?id=${reportId}`, '_blank');
    },

    deleteReport(reportId) {
        if (confirm('Are you sure you want to delete this report?')) {
            this.apiCall(`/reports/delete.php`, 'DELETE', { id: reportId })
                .then(data => {
                    if (data.success) {
                        this.showSuccess('Report deleted successfully');
                        this.loadRecentReports();
                    } else {
                        this.showError(null, 'Failed to delete report');
                    }
                })
                .catch(error => {
                    console.error('Delete report error:', error);
                    this.showError(null, 'Error deleting report');
                });
        }
    },

    scheduleReport() {
        this.showCreateModal('Schedule Report', this.getScheduleReportForm(), 'reports/schedule.php');
    },

    getScheduleReportForm() {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Schedule Name" required>
                        <label for="name">Schedule Name</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="frequency" name="frequency" required>
                            <option value="">Select Frequency</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                        </select>
                        <label for="frequency">Frequency</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="time" class="form-control" id="time" name="time" required>
                        <label for="time">Execution Time</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email Recipients" required>
                        <label for="email">Email Recipients</label>
                    </div>
                </div>
            </div>
        `;
    },

    loadPortfolio() {
        const container = document.getElementById('portfolio-content');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/risk-analytics/portfolio.php')
            .then(data => {
                if (data.success) {
                    this.renderPortfolio(container, data.data);
                } else {
                    this.showError(container, 'Failed to load portfolio data');
                }
            })
            .catch(error => {
                console.error('Portfolio error:', error);
                this.showError(container, 'Error loading portfolio data');
            });
    },

    loadRiskAlerts() {
        const container = document.getElementById('risk-alerts');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/risk-analytics/alerts.php')
            .then(data => {
                if (data.success) {
                    this.renderTable(container, data.data, this.getRiskAlertsColumns());
                } else {
                    this.showError(container, 'Failed to load risk alerts');
                }
            })
            .catch(error => {
                console.error('Risk alerts error:', error);
                this.showError(container, 'Error loading risk alerts');
            });
    },

    renderPortfolio(container, data) {
        if (!data || !data.positions || data.positions.length === 0) {
            container.innerHTML = `
                <div class="success-state">
                    <i class="bi bi-pie-chart"></i>
                    <h5>No Portfolio Data</h5>
                    <p>No portfolio positions found.</p>
                </div>
            `;
            return;
        }

        let html = `
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total Value</h5>
                            <h3 class="text-primary">$${data.metrics.total_value || '0'}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total P&L</h5>
                            <h3 class="${(data.metrics.total_pnl || 0) >= 0 ? 'text-success' : 'text-danger'}">
                                $${data.metrics.total_pnl || '0'}
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Positions</h5>
                            <h3 class="text-info">${data.metrics.position_count || 0}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Avg Price</th>
                            <th>Current Price</th>
                            <th>Market Value</th>
                            <th>P&L</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        data.positions.forEach(position => {
            // Parse the formatted strings back to numbers
            const quantity = parseFloat((position.quantity || '0').replace(/,/g, ''));
            const avgPrice = parseFloat((position.average_price || '0').replace(/,/g, ''));
            const currentPrice = parseFloat((position.current_price || '0').replace(/,/g, ''));
            const pnl = parseFloat((position.pnl || '0').replace(/,/g, ''));
            const pnlClass = pnl >= 0 ? 'text-success' : 'text-danger';
            
            html += `
                <tr>
                    <td>${position.product_name || '-'}</td>
                    <td>${new Intl.NumberFormat('en-US').format(quantity)}</td>
                    <td>$${new Intl.NumberFormat('en-US', {minimumFractionDigits: 2}).format(avgPrice)}</td>
                    <td>$${new Intl.NumberFormat('en-US', {minimumFractionDigits: 2}).format(currentPrice)}</td>
                    <td>$${position.market_value || '-'}</td>
                    <td class="${pnlClass}">$${new Intl.NumberFormat('en-US', {minimumFractionDigits: 2}).format(Math.abs(pnl))}</td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        container.innerHTML = html;
        console.log(`✅ Rendered portfolio with ${data.positions.length} positions`);
    },

    // ===== MASTER DATA METHODS =====
    loadMasterData() {
        console.log('🗃️ Loading master data...');
        this.loadCounterparties();
    },

        loadTable(containerId, url, columnFn, errorMessage = 'Failed to load data') {
            const container = document.getElementById(containerId);
            if (!container) return;

            this.showLoading(container);

            this.apiCall(url)
                .then(data => {
                    // console.error('load data');
                    if (data.success) {
                        this.renderTable(container, data.data, columnFn());
                    } else {
                        this.showError(container, errorMessage);
                    }
                })
                .catch(error => {
                    console.error(`${containerId} error:`, error);
                    this.showError(container, `Error loading ${containerId}`);
                });
            },
        loadData(url) {
            this.apiCall(url)
                        .then(data => {
                            return data;
                        })
                        .catch(error => {
                            console.error(`${containerId} error:`, error);
                            this.showError(container, `Error loading ${containerId}`);
                        });
                   
        },
     
    loadCounterparties() {
        const container = document.getElementById('counterparties-table');
        if (!container) {
            // Create the content area if it doesn't exist
            const tabContent = document.getElementById('masterDataTabContent');
            if (tabContent) {
                tabContent.innerHTML = `
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
                `;
            }
            return;
        }

        this.showLoading(container);

        this.apiCall('/master-data/counterparties.php')
            .then(data => {
                if (data.success) {
                    this.renderTable(container, data.data, this.getCounterpartiesColumns());
                } else {
                    this.showError(container, 'Failed to load counterparties');
                }
            })
            .catch(error => {
                console.error('Counterparties error:', error);
                this.showError(container, 'Error loading counterparties');
            });
    },

    loadProducts() {
        const container = document.getElementById('products-table');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/master-data/products.php')
            .then(data => {
                if (data.success) {
                    this.renderTable(container, data.data, this.getProductsColumns());
                } else {
                    this.showError(container, 'Failed to load products');
                }
            })
            .catch(error => {
                console.error('Products error:', error);
                this.showError(container, 'Error loading products');
            });
    },

    loadBusinessUnits() {
        const container = document.getElementById('business-units-table');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/master-data/business-units.php')
            .then(data => {
                if (data.success) {
                    this.renderTable(container, data.data, this.getBusinessUnitsColumns());
                } else {
                    this.showError(container, 'Failed to load business units');
                }
            })
            .catch(error => {
                console.error('Business units error:', error);
                this.showError(container, 'Error loading business units');
            });
    },

    loadBrokers() {
        const container = document.getElementById('brokers-table');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/master-data/brokers.php')
            .then(data => {
                if (data.success) {
                    this.renderTable(container, data.data, this.getBrokersColumns());
                } else {
                    this.showError(container, 'Failed to load brokers');
                }
            })
            .catch(error => {
                console.error('Brokers error:', error);
                this.showError(container, 'Error loading brokers');
            });
    },

    loadPorts() {
        const container = document.getElementById('ports-table');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/master-data/ports.php')
            .then(data => {
                if (data.success) {
                    this.renderTable(container, data.data, this.getPortsColumns());
                } else {
                    this.showError(container, 'Failed to load ports');
                }
            })
            .catch(error => {
                console.error('Ports error:', error);
                this.showError(container, 'Error loading ports');
            });
    },

    loadCarriers() {
        const container = document.getElementById('carriers-table');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/master-data/carriers.php')
            .then(data => {
                if (data.success) {
                    this.renderTable(container, data.data, this.getCarriersColumns());
                } else {
                    this.showError(container, 'Failed to load carriers');
                }
            })
            .catch(error => {
                console.error('Carriers error:', error);
                this.showError(container, 'Error loading carriers');
            });
    },

    // ===== USER MANAGEMENT METHODS =====
    loadUserManagement() {
        console.log('👥 Loading user management...');
        this.loadUsers();
    },

    loadUsers() {
        const container = document.getElementById('users-table');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/users/list_simple.php')
            .then(data => {
                if (data.success) {
                    this.renderTable(container, data.data.users, this.getUsersColumns());
                } else {
                    this.showError(container, 'Failed to load users');
                }
            })
            .catch(error => {
                console.error('Users error:', error);
                this.showError(container, 'Error loading users');
            });
    },

    loadRoles() {
        const container = document.getElementById('roles-table');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/users/roles.php')
            .then(data => {
                if (data.success) {
                    this.renderTable(container, data.data, this.getRolesColumns());
                } else {
                    this.showError(container, 'Failed to load roles');
                }
            })
            .catch(error => {
                console.error('Roles error:', error);
                this.showError(container, 'Error loading roles');
            });
    },

    loadPermissions() {
        const container = document.getElementById('permissions-table');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/users/permissions.php')
            .then(data => {
                if (data.success) {
                    this.renderTable(container, data.data, this.getPermissionsColumns());
                } else {
                    this.showError(container, 'Failed to load permissions');
                }
            })
            .catch(error => {
                console.error('Permissions error:', error);
                this.showError(container, 'Error loading permissions');
            });
    },

    loadActivity() {
        const container = document.getElementById('activity-table');
        if (!container) return;

        this.showLoading(container);

        this.apiCall('/users/activity.php')
            .then(data => {
                if (data.success) {
                    this.renderTable(container, data.data, this.getActivityColumns());
                } else {
                    this.showError(container, 'Failed to load activity');
                }
            })
            .catch(error => {
                console.error('Activity error:', error);
                this.showError(container, 'Error loading activity');
            });
    },

    // ===== TABLE RENDERING =====
    renderTable(container, data, columns) {
        if (!data || data.length === 0) {
            container.innerHTML = `
                <div class="success-state">
                    <i class="bi bi-inbox"></i>
                    <h5>No Data Available</h5>
                    <p>No records found for this section.</p>
                </div>
            `;
            return;
        }

        let html = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            ${columns.map(col => `<th>${col.title}</th>`).join('')}
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        data.forEach(row => {
            html += '<tr>';
            columns.forEach(col => {
                let value = row[col.field] || '-';
                
                // Format specific field types
                if (col.type === 'status') {
                    value = `<span class="status-badge status-${value.toLowerCase()}">${value}</span>`;
                } else if (col.type === 'currency') {
                    value = new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD'
                    }).format(parseFloat(value) || 0);
                } else if (col.type === 'number') {
                    value = new Intl.NumberFormat('en-US').format(parseFloat(value) || 0);
                } else if (col.type === 'date') {
                    value = new Date(value).toLocaleDateString();
                }
                
                html += `<td data-label="${col.title}">${value}</td>`;
            });

            const rowId = this.getPrimaryKey(row);
            const containerId = container.id;
            html += `
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary" ${rowId ? '' : 'disabled'} onclick="ETRM.editRecord('${containerId}', '${rowId || ''}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-danger" ${rowId ? '' : 'disabled'} onclick="ETRM.deleteRecord('${containerId}', '${rowId || ''}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            html += '</tr>';
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        container.innerHTML = html;
        console.log(`✅ Rendered table with ${data.length} rows`);
    },

    // Determine a primary key for generic actions
    getPrimaryKey(row) {
        const candidateKeys = [
            'id',
            'trade_id',
            'invoice_number',
            'logistics_id',
            'settlement_id',
            'market_index_id',
            'code'
        ];
        for (const key of candidateKeys) {
            if (row[key]) return String(row[key]);
        }
        // Fallback: first truthy field
        const firstKey = Object.keys(row).find(k => row[k]);
        return firstKey ? String(row[firstKey]) : '';
    },

    // Map container id to forms and API endpoints
    getEntityConfigFromContainerId(containerId) {
        const map = {
            // Trading
            'physical-sales-table': { title: 'Physical Sale', form: 'getPhysicalSaleForm', endpoint: 'trading/physical-sales.php', idParam: 'sale_id' },
            'financial-trades-table': { title: 'Financial Trade', form: 'getFinancialTradeForm', endpoint: 'trading/financial-trades.php', idParam: 'trade_id' },
            'fx-trades-table': { title: 'FX Trade', form: 'getFXTradeForm', endpoint: 'trading/fx-trades.php', idParam: 'trade_id' },

            // Operations
            'invoices-table': { title: 'Invoice', form: 'getInvoiceForm', endpoint: 'operations/invoices.php', idParam: 'invoice_number' },
            'logistics-table': { title: 'Logistics', form: 'getLogisticsForm', endpoint: 'operations/logistics.php', idParam: 'logistics_id' },
            'settlements-table': { title: 'Settlement', form: 'getSettlementForm', endpoint: 'operations/settlements.php', idParam: 'settlement_id' },

            // Master Data
            'counterparties-table': { title: 'Counterparty', form: 'getCounterpartyForm', endpoint: 'master-data/counterparties.php', idParam: 'id' },
            'products-table': { title: 'Product', form: 'getProductForm', endpoint: 'master-data/products.php', idParam: 'id' },
            'business-units-table': { title: 'Business Unit', form: 'getBusinessUnitForm', endpoint: 'master-data/business-units.php', idParam: 'id' },
            'brokers-table': { title: 'Broker', form: 'getBrokerForm', endpoint: 'master-data/brokers.php', idParam: 'id' },
            'ports-table': { title: 'Port', form: 'getPortForm', endpoint: 'master-data/ports.php', idParam: 'id' },
            'carriers-table': { title: 'Carrier', form: 'getCarrierForm', endpoint: 'master-data/carriers.php', idParam: 'id' },
            'market-prices-table': { title: 'Market Price', form: 'getMarketPriceForm', endpoint: 'master-data/market-prices.php', idParam: 'id' },
            'contract-types-table': { title: 'Contract Type', form: 'getContractTypeForm', endpoint: 'master-data/contract-types.php', idParam: 'id' },
            'commodity-names-table': { title: 'Commodity Name', form: 'getCommodityNameForm', endpoint: 'master-data/commodities.php', idParam: 'id' },
            'product-uom-table': { title: 'Product UOM', form: 'getProductUomForm', endpoint: 'master-data/product-uom.php', idParam: 'id' },
            'pricing-units-table': { title: 'Pricing Unit', form: 'getPricingUnitForm', endpoint: 'master-data/pricing-units.php', idParam: 'id' },
            'pricing-formulas-table': { title: 'Pricing Formula', form: 'getPricingFormulaForm', endpoint: 'master-data/pricing-formulas.php', idParam: 'id' },
            'market-index-table': { title: 'Market Index', form: 'getMarketIndexForm', endpoint: 'master-data/market-index.php', idParam: 'id' },
            'payment-term-table': { title: 'Payment Term', form: 'getPaymentTermsForm', endpoint: 'master-data/payment-terms.php', idParam: 'id' },
            'transfer-method-table': { title: 'Transfer Method', form: 'getTransferMethodForm', endpoint: 'master-data/transfer-methods.php', idParam: 'id' },
            'governing-bodies-table': { title: 'Governing Body', form: 'getGoverningBodyForm', endpoint: 'master-data/governing-bodies.php', idParam: 'id' },
            'load-profits-table': { title: 'Load Profit', form: 'getLoadProfitForm', endpoint: 'master-data/load-profits.php', idParam: 'id' },
            'discharging-ports-table': { title: 'Discharging Port', form: 'getDischargingPortForm', endpoint: 'master-data/discharging-ports.php', idParam: 'id' },
            'pricing-uom-table': { title: 'Pricing UOM', form: 'getPricingUomForm', endpoint: 'master-data/pricing-uom.php', idParam: 'id' },
            'internal-bu-table': { title: 'Internal BU', form: 'getInternalBUForm', endpoint: 'master-data/internal-bu.php', idParam: 'id' },
            'portfolio-master-table': { title: 'Portfolio', form: 'getPortfolioForm', endpoint: 'master-data/portfolio.php', idParam: 'id' },
            'exchange-table': { title: 'Exchange', form: 'getExchangeForm', endpoint: 'master-data/exchange.php', idParam: 'id' },
            'currency-table': { title: 'Currency', form: 'getCurrencyForm', endpoint: 'master-data/currency.php', idParam: 'id' },

            // Users & Security
            'users-table': { title: 'User', form: 'getUserForm', endpoint: 'users/create_simple.php', idParam: 'id' },
            'roles-table': { title: 'Role', form: 'getRoleForm', endpoint: 'users/roles.php', idParam: 'id' },
            'permissions-table': { title: 'Permission', form: 'getPermissionForm', endpoint: 'users/permissions.php', idParam: 'id' },
            'activity-table': { title: 'Activity', form: null, endpoint: 'users/activity.php', idParam: 'id' }
        };
        return map[containerId] || null;
    },

    async editRecord(containerId, recordId) {
        try {
            const config = this.getEntityConfigFromContainerId(containerId);
            if (!config || !config.form) {
                this.showNotification('Info', 'Editing is not available for this table yet.', 'info');
                return;
            }

            // Open modal with form
            const formHtml = this[config.form]();
            this.showCreateModal(`Edit ${config.title}`, formHtml, config.endpoint);

            // Try to load existing data
            let data;
            try {
                data = await this.apiCall(`/${config.endpoint}?${encodeURIComponent(config.idParam)}=${encodeURIComponent(recordId)}`);
            } catch (e) {
                // If single fetch not supported, ignore and allow manual edit
                data = null;
            }

            // Prefill when possible
            setTimeout(() => {
                try {
                    const form = document.getElementById('createForm');
                    const payload = data && data.data ? (Array.isArray(data.data) ? data.data[0] : data.data) : null;
                    if (form && payload) {
                        Array.from(form.elements).forEach(el => {
                            if (!el.name) return;
                            if (payload.hasOwnProperty(el.name)) {
                                el.value = payload[el.name] ?? '';
                            }
                        });
                    }
                    // Ensure id is present for update
                    if (form && config.idParam) {
                        const hiddenId = document.createElement('input');
                        hiddenId.type = 'hidden';
                        hiddenId.name = config.idParam;
                        hiddenId.value = recordId;
                        form.appendChild(hiddenId);
                    }
                } catch (prefillErr) {
                    console.warn('Prefill error:', prefillErr);
                }
            }, 150);
        } catch (error) {
            console.error('Edit error:', error);
            this.showNotification('Error', 'Failed to open edit form', 'danger');
        }
    },

    async deleteRecord(containerId, recordId) {
        try {
            const config = this.getEntityConfigFromContainerId(containerId);
            if (!config) {
                this.showNotification('Info', 'Delete is not available for this table yet.', 'info');
                return;
            }

            if (!confirm('Are you sure you want to delete this record?')) return;

            const formData = new FormData();
            formData.append(config.idParam || 'id', recordId);

            const response = await fetch(`api/${config.endpoint}?${encodeURIComponent(config.idParam)}=${encodeURIComponent(recordId)}`, {
                method: 'DELETE',
                body: formData
            });
            const text = await response.text();
            let json;
            try {
                json = JSON.parse(text);
            } catch (e) {
                throw new Error('Invalid server response during delete');
            }

            if (json && json.success) {
                this.showNotification('Success', `${config.title} deleted successfully`, 'success');
                this.refreshCurrentTable();
            } else {
                throw new Error(json && json.message ? json.message : 'Delete failed');
            }
        } catch (error) {
            console.error('Delete error:', error);
            this.showNotification('Error', error.message || 'Failed to delete record', 'danger');
        }
    },

    // ===== COLUMN DEFINITIONS =====
    getPhysicalSalesColumns() {
        return [
            { field: 'sale_id', title: 'Sale ID' },
            { field: 'product_name', title: 'Product' },
            { field: 'quantity', title: 'Quantity', type: 'number' },
            { field: 'price', title: 'Price', type: 'currency' },
            { field: 'currency', title: 'Currency' },
            { field: 'counterparty_name', title: 'Counterparty' },
            { field: 'business_unit_name', title: 'Business Unit' },
            { field: 'trader_name', title: 'Trader' },
            { field: 'status', title: 'Status', type: 'status' },
            { field: 'delivery_date', title: 'Delivery Date', type: 'date' }
        ];
    },

    getFinancialTradesColumns() {
        return [
            { field: 'trade_id', title: 'Trade ID' },
            { field: 'commodity_name', title: 'Commodity' },
            { field: 'trade_type', title: 'Trade Type' },
            { field: 'contract_type', title: 'Contract Type' },
            { field: 'quantity', title: 'Quantity', type: 'number' },
            { field: 'price', title: 'Price', type: 'currency' },
            { field: 'currency', title: 'Currency' },
            { field: 'counterparty_name', title: 'Counterparty' },
            { field: 'business_unit_name', title: 'Business Unit' },
            { field: 'trader_name', title: 'Trader' },
            { field: 'status', title: 'Status', type: 'status' },
            { field: 'settlement_date', title: 'Settlement', type: 'date' }
        ];
    },

    getFXTradesColumns() {
        return [
            { field: 'trade_id', title: 'Trade ID' },
            { field: 'counterparty_name', title: 'Counterparty' },
            { field: 'base_currency', title: 'Base' },
            { field: 'quote_currency', title: 'Quote' },
            { field: 'trade_type', title: 'Type' },
            { field: 'amount', title: 'Amount', type: 'currency' },
            { field: 'exchange_rate', title: 'Rate', type: 'number' },
            { field: 'total_value', title: 'Total Value', type: 'currency' },
            { field: 'status', title: 'Status', type: 'status' },
            { field: 'trade_date', title: 'Trade Date', type: 'date' },
            { field: 'value_date', title: 'Value Date', type: 'date' }
        ];
    },

    getInvoicesColumns() {
        return [
            { field: 'invoice_number', title: 'Invoice #' },
            { field: 'counterparty_name', title: 'Counterparty' },
            { field: 'amount', title: 'Amount', type: 'currency' },
            { field: 'status', title: 'Status', type: 'status' },
            { field: 'due_date', title: 'Due Date', type: 'date' }
        ];
    },

    getLogisticsColumns() {
        return [
            { field: 'logistics_id', title: 'Logistics ID' },
            { field: 'origin', title: 'Origin' },
            { field: 'destination', title: 'Destination' },
            { field: 'shipping_method', title: 'Method' },
            { field: 'status', title: 'Status', type: 'status' },
            { field: 'departure_date', title: 'Departure', type: 'date' }
        ];
    },

    getSettlementsColumns() {
        return [
            { field: 'settlement_id', title: 'Settlement ID' },
            { field: 'amount', title: 'Amount', type: 'currency' },
            { field: 'payment_method', title: 'Payment Method' },
            { field: 'status', title: 'Status', type: 'status' },
            { field: 'settlement_date', title: 'Date', type: 'date' }
        ];
    },

    getRiskAlertsColumns() {
        return [
            { field: 'alert_type', title: 'Type' },
            { field: 'severity', title: 'Severity', type: 'status' },
            { field: 'message', title: 'Message' },
            { field: 'status', title: 'Status', type: 'status' },
            { field: 'created_at', title: 'Created', type: 'date' }
        ];
    },

    getCounterpartiesColumns() {
        return [
            { field: 'code', title: 'Code' },
            { field: 'name', title: 'Name' },
            { field: 'type', title: 'Type' },
            { field: 'country', title: 'Country' },
            { field: 'status', title: 'Status', type: 'status' }
        ];
    },

    getProductsColumns() {
        return [
            { field: 'code', title: 'Code' },
            { field: 'product_name', title: 'Product Name' },
            { field: 'category', title: 'Category' },
            { field: 'unit', title: 'Unit' },
            { field: 'status', title: 'Status', type: 'status' }
        ];
    },

    getBusinessUnitsColumns() {
        return [
            { field: 'code', title: 'Code' },
            { field: 'business_unit_name', title: 'Business Unit' },
            { field: 'manager_name', title: 'Manager' },
            { field: 'location', title: 'Location' },
            { field: 'status', title: 'Status', type: 'status' }
        ];
    },

    getBrokersColumns() {
        return [
            { field: 'code', title: 'Code' },
            { field: 'name', title: 'Broker Name' },
            { field: 'exchange', title: 'Exchange' },
            { field: 'commission_rate', title: 'Commission Rate' },
            { field: 'contact_person', title: 'Contact' },
            { field: 'status', title: 'Status', type: 'status' }
        ];
    },

    getPortsColumns() {
        return [
            { field: 'code', title: 'Code' },
            { field: 'port_name', title: 'Port Name' },
            { field: 'city', title: 'City' },
            { field: 'country', title: 'Country' },
            { field: 'facilities', title: 'Facilities' },
            { field: 'status', title: 'Status', type: 'status' }
        ];
    },

                getCarriersColumns() {
                return [
                    { field: 'code', title: 'Code' },
                    { field: 'name', title: 'Carrier Name' },
                    { field: 'contact_person', title: 'Contact Person' },
                    { field: 'email', title: 'Email' },
                    { field: 'phone', title: 'Phone' },
                    { field: 'status', title: 'Status', type: 'status' }
                ];
            },

            getUsersColumns() {
                return [
                    { field: 'username', title: 'Username' },
                    { field: 'email', title: 'Email' },
                    { field: 'full_name', title: 'Full Name' },
                    { field: 'role', title: 'Role', type: 'status' },
                    { field: 'status', title: 'Status', type: 'status' },
                    { field: 'department', title: 'Department' },
                    { field: 'created_at', title: 'Created', type: 'date' },
                    { field: 'last_login', title: 'Last Login', type: 'date' }
                ];
            },

            getRolesColumns() {
                return [
                    { field: 'name', title: 'Role' },
                    { field: 'description', title: 'Description' },
                    { field: 'user_count', title: 'Users' },
                    { field: 'permission_count', title: 'Permissions' },
                    { field: 'created_at', title: 'Created', type: 'date' }
                ];
            },

            getPermissionsColumns() {
                return [
                    { field: 'name', title: 'Permission' },
                    { field: 'description', title: 'Description' },
                    { field: 'category', title: 'Category' },
                    { field: 'created_at', title: 'Created', type: 'date' }
                ];
            },

            getActivityColumns() {
                return [
                    { field: 'username', title: 'User' },
                    { field: 'action', title: 'Activity' },
                    { field: 'details', title: 'Details' },
                    { field: 'ip_address', title: 'IP Address' },
                    { field: 'created_at', title: 'Date', type: 'date' }
                ];
            },  
            //new columns
                        // Market Prices
            getMarketPricesColumns() {
                return [
                    { field: 'market_index_id', title: 'Market Index ID' },
                    { field: 'closing_date', title: 'Closing Date', type: 'date' },
                    { field: 'expiry_date', title: 'Expiry Date', type: 'date' },
                    { field: 'closing_price', title: 'Closing Price', type: 'currency' }
                ];
            },

            // Contract Types
            getContractTypesColumns() {
                return [
                    { field: 'id', title: 'ID' },
                    { field: 'name', title: 'Contract Type' },
                ];
            },

            // Commodities
            getCommoditiesColumns() {
                return [
                    { field: 'id', title: 'ID' },
                    { field: 'name', title: 'Commodity Name' },
                    // { field: 'category', title: 'Category' },
                    // { field: 'uom', title: 'Unit of Measure' },
                    // { field: 'status', title: 'Status', type: 'status' }
                ];
            },

            // Product UOM
            getProductUOMColumns() {
                return [
                    { field: 'id', title: 'ID' },
                    { field: 'name', title: 'Unit Name' },
                    { field: 'symbol', title: 'Symbol' },
                    { field: 'description', title: 'Description' }
                ];
            },

            // Pricing Units
            getPricingUnitsColumns() {
                return [
                    { field: 'id', title: 'ID' },
                    { field: 'name', title: 'Pricing Unit' },
                    // { field: 'description', title: 'Description' }
                ];
            },

            // Pricing Formulas
            getPricingFormulasColumns() {
                return [
                    { field: 'id', title: 'ID' },
                    { field: 'name', title: 'Formula Name' },
                    { field: 'details', title: 'Description' }
                ];
            },

            // Market Indices
           getMarketIndexColumns() {
                return [
                    { field: 'id', title: 'ID' },
                    { field: 'index_name', title: 'Index Name' },
                    { field: 'index_uom', title: 'Value' },
                    { field: 'exchange', title: 'Exchange' },
                    { field: 'expiry_date', title: ' Expiry Date', type: 'date' }
                ];
            },

            // Payment Terms
            getPaymentTermsColumns() {
                return [
                    { field: 'id', title: 'ID' },
                    { field: 'name', title: 'Payment Term' },
                    { field: 'details', title: 'Description' }
                ];
            },

            // Transfer Methods
            getTransferMethodsColumns() {
                return [
                    { field: 'id', title: 'ID' },
                    { field: 'name', title: 'Method' },
                ];
            },

            // Governing Bodies
            getGoverningBodiesColumns() {
                return [
                    { field: 'id', title: 'ID' },
                    { field: 'name', title: 'Governing Body' },
                ];
            },

            // Load Profits
            getLoadProfitsColumns() {
                return [
                    { field: 'id', title: 'ID' },
                    { field: 'load_id', title: 'Load ID' },
                    { field: 'profit', title: 'Profit', type: 'currency' },
                    { field: 'currency', title: 'Currency' },
                    { field: 'date', title: 'Date', type: 'date' }
                ];
            },

            // Discharging Ports
            getDischargingPortsColumns() {
                return [
                    { field: 'id', title: 'ID' },
                    { field: 'name', title: 'Port Name' },
                ];
            },

            // Pricing UOM
            getPricingUOMColumns() {
                return [
                    { field: 'id', title: 'ID' },
                    { field: 'name', title: 'Pricing UOM' },
                    // { field: 'symbol', title: 'Symbol' },
                    // { field: 'description', title: 'Description' }
                ];
            },

            // Internal BUs
            getInternalBUColumns() {
                return [
                    { field: 'id', title: 'ID' },
                    { field: 'name', title: 'Business Unit' },
                  
                ];
            },

            // API FIX Trade Capture
            getAPIFIXColumns() {
                return [
                    { field: 'id', title: 'ID' },
                    { field: 'trade_ref', title: 'Trade Ref' },
                    { field: 'status', title: 'Status', type: 'status' },
                    { field: 'captured_at', title: 'Captured At', type: 'date' }
                ];
            },

            // Portfolio
            getPortfolioColumns() {
                return [
                    { field: 'id', title: 'ID' },
                    { field: 'name', title: 'Portfolio Name' },
                 
                ];
            },

            // Exchange
            getExchangeColumns() {
                return [
                    { field: 'id', title: 'ID' },
                    { field: 'name', title: 'Exchange' },
                
                ];
            },

            // Currency
            getCurrencyColumns() {
                return [
                    { field: 'id', title: 'ID' },
                    { field: 'name', title: 'Currency Name' },
                ];
            },


    // ===== CREATE/NEW RECORD METHODS =====
    handleCreateAction(action) {
        console.log(`➕ Handling create action: ${action}`);
        
            switch (action) {
            case 'create-physical-sale':
                this.showCreateModal('Physical Sale', this.getPhysicalSaleForm(), 'trading/physical-sales.php');
                break;
            case 'create-financial-trade':
                this.showCreateModal('Financial Trade', this.getFinancialTradeForm(), 'trading/financial-trades.php');
                break;
            case 'create-fx-trade':
                this.showCreateModal('FX Trade', this.getFXTradeForm(), 'trading/fx-trades.php');
                break;
            case 'create-invoice':
                this.showCreateModal('Invoice', this.getInvoiceForm(), 'operations/invoices.php');
                break;
            case 'create-logistics':
                this.showCreateModal('Logistics', this.getLogisticsForm(), 'operations/logistics.php');
                break;
            case 'create-settlement':
                this.showCreateModal('Settlement', this.getSettlementForm(), 'operations/settlements.php');
                break;
            case 'create-counterparty':
                this.showCreateModal('Counterparty', this.getCounterpartyForm(), 'master-data/counterparties.php');
                break;
            case 'create-product':
                this.showCreateModal('Product', this.getProductForm(), 'master-data/products.php');
                break;
            case 'create-business-unit':
                this.showCreateModal('Business Unit', this.getBusinessUnitForm(), 'master-data/business-units.php');
                break;
            case 'create-broker':
                this.showCreateModal('Broker', this.getBrokerForm(), 'master-data/brokers.php');
                break;
            case 'create-port':
                this.showCreateModal('Port', this.getPortForm(), 'master-data/ports.php');
                break;
            case 'create-carrier':
                this.showCreateModal('Carrier', this.getCarrierForm(), 'master-data/carriers.php');
                break;
            case 'create-user':
                this.showCreateModal('User', this.getUserForm(), 'users/create_simple.php');
                break;
            case 'create-role':
                this.showCreateModal('Role', this.getRoleForm(), 'users/roles.php');
                break;
            case 'create-permission':
                this.showCreateModal('Permission', this.getPermissionForm(), 'users/permissions.php');
                break;

            // 🔽 New cases for your nav tabs
            case 'create-market-price':
                this.showCreateModal('Market Price', this.getMarketPriceForm(), 'master-data/market-prices.php');
                this.populateMarketIndexSelect();
                break;
            case 'create-contract-type':
                this.showCreateModal('Contract Type', this.getContractTypeForm(), 'master-data/contract-types.php');
                break;
            case 'create-commodity':
                this.showCreateModal('Commodity Name', this.getCommodityNameForm(), 'master-data/commodities.php');
                break;
            case 'create-product-uom':
                this.showCreateModal('Product UOM', this.getProductUomForm(), 'master-data/product-uom.php');
                break;
            case 'create-pricing-unit':
                this.showCreateModal('Pricing Unit', this.getPricingUnitForm(), 'master-data/pricing-units.php');
                break;
            case 'create-pricing-formula':
                this.showCreateModal('Pricing Formula', this.getPricingFormulaForm(), 'master-data/pricing-formulas.php');
                break;
            case 'create-market-index':
                this.showCreateModal('Market Index', this.getMarketIndexForm(), 'master-data/market-index.php');
                break;
            case 'create-payment-term':
                this.showCreateModal('Payment Term', this.getPaymentTermsForm(), 'master-data/payment-terms.php');
                break;
           
            case 'create-governing-body':
                this.showCreateModal('Governing Body', this.getGoverningBodyForm(), 'master-data/governing-bodies.php');
                break;
            case 'create-load-profit':
                this.showCreateModal('Load Profit', this.getLoadProfitForm(), 'master-data/load-profits.php');
                break;
            case 'create-discharging-port':
                this.showCreateModal('Discharging Port', this.getDischargingPortForm(), 'master-data/discharging-ports.php');
                break;
            case 'create-pricing-uom':
                this.showCreateModal('Pricing UOM', this.getPricingUomForm(), 'master-data/pricing-uom.php');
                break;
            case 'create-internal-bu':
                this.showCreateModal('Internal BU', this.getInternalBUForm(), 'master-data/internal-bu.php');
                break;
            case 'create-api-fix':
                this.showCreateModal('API FIX Trade Capture', this.getAPIFIXForm(), 'master-data/api-fix.php');
                break;
            case 'create-portfolio':
                this.showCreateModal('Portfolio', this.getPortfolioForm(), 'master-data/portfolio.php');
                break;
            case 'create-exchange':
                this.showCreateModal('Exchange', this.getExchangeForm(), 'master-data/exchanges.php');
                break;
            case 'create-currency':
                this.showCreateModal('Currency', this.getCurrencyForm(), 'master-data/currencies.php');
                break;

            default:
                console.warn(`Unknown create action: ${action}`);
        }

    },

    showCreateModal(title, formHtml, apiEndpoint) {
        const modalId = 'createModal';
        
        // Remove existing modal if any
        const existingModal = document.getElementById(modalId);
        if (existingModal) {
            existingModal.remove();
        }

        // Create modal HTML
        const modalHtml = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}Label" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="${modalId}Label">
                                <i class="bi bi-plus-circle"></i> New ${title}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="createForm" data-api-endpoint="${apiEndpoint}">
                                ${formHtml}
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="saveBtn">
                                <i class="bi bi-check-circle"></i> Save ${title}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add modal to page
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();

        // Handle save button
        document.getElementById('saveBtn').addEventListener('click', () => {
            this.handleCreateSubmit(apiEndpoint, modalId);
        });

        // Handle form enter key
        document.getElementById('createForm').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.handleCreateSubmit(apiEndpoint, modalId);
            }
        });
    },

    handleCreateSubmit(apiEndpoint, modalId) {
        const form = document.getElementById('createForm');
        const saveBtn = document.getElementById('saveBtn');
        const formData = new FormData(form);
    
        // Auto-detect edit mode if the form includes an "id" field
        const recordId = formData.get('id');
        const isEdit = !!recordId;
    
        // Special validation for user creation (only for new users)
        if (apiEndpoint.includes('users/create') && !isEdit) {
            const password = formData.get('password');
            const confirmPassword = formData.get('confirm_password');
    
            if (password !== confirmPassword) {
                this.showNotification('Error', 'Passwords do not match', 'danger');
                return;
            }
    
            if (password.length < 8) {
                this.showNotification('Error', 'Password must be at least 8 characters long', 'danger');
                return;
            }
        }
    
        // Handle edit: append ?id=123 and use PUT method
        let method = 'POST';
        let url = `api/${apiEndpoint}`;
        if (isEdit) {
            const separator = apiEndpoint.includes('?') ? '&' : '?';
            url = `api/${apiEndpoint}${separator}id=${recordId}`;
            method = 'PUT';
        }
    
        // Show loading state
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
        saveBtn.disabled = true;
    
        // Submit to API
        fetch(url, {
            method,
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Response text:', text);
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                if (text.includes('<html>')) {
                    throw new Error('Server returned HTML instead of JSON. Check authentication or API endpoint.');
                } else {
                    throw new Error(`Invalid JSON response: ${text.substring(0, 100)}...`);
                }
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
                modal.hide();
    
                // Show success message
                const recordType = apiEndpoint.split('/').pop().replace('.php', '').replace('-', ' ');
                const action = isEdit ? 'updated' : 'created';
                this.showNotification('Success', `${recordType} ${action} successfully!`, 'success');
    
                // Refresh table
                setTimeout(() => {
                    try {
                        this.refreshCurrentTable();
                    } catch (refreshError) {
                        console.error('Table refresh failed:', refreshError);
                        this.showNotification('Info', `Record ${action} successfully. Please refresh the page to see the latest data.`, 'info');
                    }
                }, 200);
            } else {
                throw new Error(data.message || `Failed to ${isEdit ? 'update' : 'create'} record`);
            }
        })
        .catch(error => {
            console.error(`${isEdit ? 'Update' : 'Create'} error:`, error);
            this.showNotification('Error', error.message || `Failed to ${isEdit ? 'update' : 'create'} record`, 'danger');
        })
        .finally(() => {
            // Restore button
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
    }
    
,    

    refreshCurrentTable() {
        try {
            console.log('🔄 Attempting to refresh current table...');
            
            // Determine which table to refresh based on current active tab
            const activeMainTab = document.querySelector('.main-tab.active');
            if (!activeMainTab) {
                console.warn('No active main tab found');
                return;
            }

            const mainTabId = activeMainTab.id.replace('-tab', '');
            console.log('Active main tab:', mainTabId);
            
            // Get active sub-tab if any
            const activeSubTab = activeMainTab.querySelector('.tab-pane.active');
            if (activeSubTab) {
                const subTabId = activeSubTab.id;
                console.log('Active sub-tab:', subTabId);
                this.handleSubTab(`#${subTabId}`);
            } else {
                // Refresh main tab content
                console.log('Refreshing main tab content:', mainTabId);
                this.loadTabContent(mainTabId);
            }
            
            console.log('✅ Table refresh completed');
        } catch (error) {
            console.error('❌ Error refreshing table:', error);
            // Don't throw the error, just log it
        }
    },

    showNotification(title, message, type = 'info') {
        // Create notification element
        const notificationId = 'notification-' + Date.now();
        const bgColor = type === 'success' ? 'bg-success' : type === 'danger' ? 'bg-danger' : 'bg-info';
        
        const notificationHtml = `
            <div id="${notificationId}" class="toast align-items-center text-white ${bgColor} border-0 position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999;" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}</strong><br>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', notificationHtml);

        // Show and auto-hide notification
        const toast = new bootstrap.Toast(document.getElementById(notificationId), {
            autohide: true,
            delay: 5000
        });
        toast.show();

        // Remove from DOM after hiding
        toast._element.addEventListener('hidden.bs.toast', () => {
            toast._element.remove();
        });
    },

    // ===== FORM GENERATION METHODS =====
    getPhysicalSaleForm() {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="sale_id" name="sale_id" placeholder="Sale ID" required>
                        <label for="sale_id">Sale ID</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="product_id" name="product_id" required>
                            <option value="">Select Product</option>
                            <option value="1">WTI-CRUDE - West Texas Intermediate Crude Oil</option>
                            <option value="2">BRENT-CRUDE - Brent Crude Oil</option>
                            <option value="3">GASOLINE-REG - Regular Gasoline</option>
                            <option value="4">DIESEL-ULSD - Ultra Low Sulfur Diesel</option>
                            <option value="5">NAT-GAS - Natural Gas</option>
                        </select>
                        <label for="product_id">Product</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Quantity" step="0.0001" required>
                        <label for="quantity">Quantity</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="price" name="price" placeholder="Price" step="0.0001" required>
                        <label for="price">Price per Unit</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="currency" name="currency" required>
                            <option value="">Select Currency</option>
                            <option value="USD">USD - US Dollar</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="GBP">GBP - British Pound</option>
                            <option value="JPY">JPY - Japanese Yen</option>
                        </select>
                        <label for="currency">Currency</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" id="delivery_date" name="delivery_date" required>
                        <label for="delivery_date">Delivery Date</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="counterparty_id" name="counterparty_id" required>
                            <option value="">Select Counterparty</option>
                            <option value="1">Shell Trading</option>
                            <option value="2">BP Energy</option>
                            <option value="3">ExxonMobil</option>
                            <option value="4">Total Energies</option>
                        </select>
                        <label for="counterparty_id">Counterparty</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="loading_port_id" name="loading_port_id">
                            <option value="">Select Loading Port</option>
                            <option value="1">USGOM - US Gulf of Mexico</option>
                            <option value="2">SING - Singapore</option>
                            <option value="3">RDAM - Rotterdam</option>
                            <option value="4">FUJAIRAH - Fujairah</option>
                            <option value="5">HOUSTON - Houston Ship Channel</option>
                        </select>
                        <label for="loading_port_id">Loading Port</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="discharge_port_id" name="discharge_port_id">
                            <option value="">Select Discharge Port</option>
                            <option value="1">USGOM - US Gulf of Mexico</option>
                            <option value="2">SING - Singapore</option>
                            <option value="3">RDAM - Rotterdam</option>
                            <option value="4">FUJAIRAH - Fujairah</option>
                            <option value="5">HOUSTON - Houston Ship Channel</option>
                        </select>
                        <label for="discharge_port_id">Discharge Port</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="business_unit_id" name="business_unit_id" required>
                            <option value="">Select Business Unit</option>
                            <option value="1">TRADING - Trading Operations</option>
                            <option value="2">SUPPLY - Supply Chain</option>
                            <option value="3">MARKETING - Marketing & Sales</option>
                            <option value="4">RISK - Risk Management</option>
                        </select>
                        <label for="business_unit_id">Business Unit</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="trader_id" name="trader_id" required>
                            <option value="">Select Trader</option>
                            <option value="1">Administrator</option>
                            <option value="4">Test User</option>
                            <option value="5">Richard</option>
                        </select>
                        <label for="trader_id">Trader</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="status" name="status" required>
                            <option value="draft">Draft</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="invoiced">Invoiced</option>
                            <option value="paid">Paid</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <label for="status">Status</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="notes" name="notes" placeholder="Notes" style="height: 100px"></textarea>
                        <label for="notes">Notes</label>
                    </div>
                </div>
            </div>
        `;
    },

    getFXTradeForm() {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="trade_id" name="trade_id" 
                               placeholder="Trade ID" required>
                        <label for="trade_id">Trade ID</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="counterparty_id" name="counterparty_id" required>
                            <option value="">Select Counterparty</option>
                            <option value="1">Shell Trading</option>
                            <option value="2">BP Energy</option>
                            <option value="3">ExxonMobil</option>
                            <option value="4">Total Energies</option>
                        </select>
                        <label for="counterparty_id">Counterparty</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="base_currency" name="base_currency" required>
                            <option value="">Select Base Currency</option>
                            <option value="USD">USD - US Dollar</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="GBP">GBP - British Pound</option>
                            <option value="JPY">JPY - Japanese Yen</option>
                            <option value="CAD">CAD - Canadian Dollar</option>
                            <option value="AUD">AUD - Australian Dollar</option>
                            <option value="CHF">CHF - Swiss Franc</option>
                        </select>
                        <label for="base_currency">Base Currency</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="quote_currency" name="quote_currency" required>
                            <option value="">Select Quote Currency</option>
                            <option value="USD">USD - US Dollar</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="GBP">GBP - British Pound</option>
                            <option value="JPY">JPY - Japanese Yen</option>
                            <option value="CAD">CAD - Canadian Dollar</option>
                            <option value="AUD">AUD - Australian Dollar</option>
                            <option value="CHF">CHF - Swiss Franc</option>
                        </select>
                        <label for="quote_currency">Quote Currency</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="trade_type" name="trade_type" required>
                            <option value="">Select Trade Type</option>
                            <option value="buy">Buy</option>
                            <option value="sell">Sell</option>
                        </select>
                        <label for="trade_type">Trade Type</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="amount" name="amount" 
                               placeholder="Amount" step="0.0001" min="0" required>
                        <label for="amount">Amount (Base Currency)</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="exchange_rate" name="exchange_rate" 
                               placeholder="Exchange Rate" step="0.00000001" min="0" required>
                        <label for="exchange_rate">Exchange Rate</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" id="settlement_date" name="settlement_date">
                        <label for="settlement_date">Settlement Date</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" id="trade_date" name="trade_date" required>
                        <label for="trade_date">Trade Date</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" id="value_date" name="value_date" required>
                        <label for="value_date">Value Date</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="business_unit_id" name="business_unit_id" required>
                            <option value="">Select Business Unit</option>
                            <option value="1">TRADING - Trading Operations</option>
                            <option value="2">SUPPLY - Supply Chain</option>
                            <option value="3">MARKETING - Marketing & Sales</option>
                            <option value="4">RISK - Risk Management</option>
                        </select>
                        <label for="business_unit_id">Business Unit</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="trader_id" name="trader_id" required>
                            <option value="">Select Trader</option>
                            <option value="1">Administrator</option>
                            <option value="4">Test User</option>
                            <option value="5">Richard</option>
                        </select>
                        <label for="trader_id">Trader</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="executed">Executed</option>
                            <option value="settled">Settled</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <label for="status">Status</label>
                    </div>
                </div>
            </div>
        `;
    },

    getCounterpartyForm() {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Name" required>
                        <label for="name">Name</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="supplier">Supplier</option>
                            <option value="customer">Customer</option>
                            <option value="broker">Broker</option>
                            <option value="bank">Bank</option>
                        </select>
                        <label for="type">Type</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="contact_person" name="contact_person" placeholder="Contact Person">
                        <label for="contact_person">Contact Person</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email">
                        <label for="email">Email</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone">
                        <label for="phone">Phone</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="tax_id" name="tax_id" placeholder="Tax ID">
                        <label for="tax_id">Tax ID</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="address" name="address" placeholder="Address" style="height: 100px"></textarea>
                        <label for="address">Address</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="city" name="city" placeholder="City">
                        <label for="city">City</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="country" name="country" placeholder="Country">
                        <label for="country">Country</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="credit_rating" name="credit_rating" placeholder="Credit Rating">
                        <label for="credit_rating">Credit Rating</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <label for="status">Status</label>
                    </div>
                </div>
            </div>
        `;
    },

    getUserForm() {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                        <label for="username">Username</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                        <label for="email">Email</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required minlength="8">
                        <label for="password">Password</label>
                        <div class="form-text">Minimum 8 characters</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                        <label for="confirm_password">Confirm Password</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Full Name" required>
                        <label for="full_name">Full Name</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="viewer">Viewer</option>
                            <option value="analyst">Analyst</option>
                            <option value="trader">Trader</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                        <label for="role">Role</label>
                    </div>
                </div>
            </div>
            
            <script>
                // Add password confirmation validation
                document.getElementById('confirm_password').addEventListener('input', function() {
                    const password = document.getElementById('password').value;
                    const confirmPassword = this.value;
                    
                    if (password !== confirmPassword) {
                        this.setCustomValidity('Passwords do not match');
                    } else {
                        this.setCustomValidity('');
                    }
                });
                
                document.getElementById('password').addEventListener('input', function() {
                    const confirmPassword = document.getElementById('confirm_password');
                    if (confirmPassword.value) {
                        confirmPassword.dispatchEvent(new Event('input'));
                    }
                });
            </script>
        `;
    },

    // Financial Trade Form Implementation
    getFinancialTradeForm() {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="trade_id" name="trade_id" 
                               placeholder="Trade ID" required>
                        <label for="trade_id">Trade ID</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="commodity_id" name="commodity_id" required>
                            <option value="">Select Commodity</option>
                            <option value="1">WTI-CRUDE - West Texas Intermediate Crude Oil</option>
                            <option value="2">BRENT-CRUDE - Brent Crude Oil</option>
                            <option value="3">GASOLINE-REG - Regular Gasoline</option>
                            <option value="4">DIESEL-ULSD - Ultra Low Sulfur Diesel</option>
                            <option value="5">NAT-GAS - Natural Gas</option>
                        </select>
                        <label for="commodity_id">Commodity</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="trade_type" name="trade_type" required>
                            <option value="">Select Trade Type</option>
                            <option value="buy">Buy</option>
                            <option value="sell">Sell</option>
                            <option value="hedge">Hedge</option>
                        </select>
                        <label for="trade_type">Trade Type</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="contract_type" name="contract_type" required>
                            <option value="">Select Contract Type</option>
                            <option value="futures">Futures</option>
                            <option value="options">Options</option>
                            <option value="swaps">Swaps</option>
                            <option value="forwards">Forwards</option>
                        </select>
                        <label for="contract_type">Contract Type</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="quantity" name="quantity" 
                               placeholder="Quantity" step="0.0001" min="0" required>
                        <label for="quantity">Quantity</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="price" name="price" 
                               placeholder="Price" step="0.0001" min="0" required>
                        <label for="price">Price per Unit</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="currency" name="currency" required>
                            <option value="">Select Currency</option>
                            <option value="USD">USD - US Dollar</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="GBP">GBP - British Pound</option>
                            <option value="JPY">JPY - Japanese Yen</option>
                        </select>
                        <label for="currency">Currency</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" id="settlement_date" name="settlement_date">
                        <label for="settlement_date">Settlement Date</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="counterparty_id" name="counterparty_id" required>
                            <option value="">Select Counterparty</option>
                            <option value="1">Shell Trading</option>
                            <option value="2">BP Energy</option>
                            <option value="3">ExxonMobil</option>
                            <option value="4">Total Energies</option>
                        </select>
                        <label for="counterparty_id">Counterparty</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="business_unit_id" name="business_unit_id" required>
                            <option value="">Select Business Unit</option>
                            <option value="1">TRADING - Trading Operations</option>
                            <option value="2">SUPPLY - Supply Chain</option>
                            <option value="3">MARKETING - Marketing & Sales</option>
                            <option value="4">RISK - Risk Management</option>
                        </select>
                        <label for="business_unit_id">Business Unit</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="trader_id" name="trader_id" required>
                            <option value="">Select Trader</option>
                            <option value="1">Administrator</option>
                            <option value="4">Test User</option>
                            <option value="5">Richard</option>
                        </select>
                        <label for="trader_id">Trader</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="executed">Executed</option>
                            <option value="settled">Settled</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <label for="status">Status</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="margin_requirement" name="margin_requirement" 
                               placeholder="Margin Requirement" step="0.01" min="0">
                        <label for="margin_requirement">Margin Requirement</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="exchange" name="exchange" placeholder="Exchange">
                        <label for="exchange">Exchange</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="contract_month" name="contract_month" 
                               placeholder="Contract Month" pattern="[0-9]{4}-[0-9]{2}" title="Format: YYYY-MM">
                        <label for="contract_month">Contract Month (YYYY-MM)</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="strike_price" name="strike_price" 
                               placeholder="Strike Price" step="0.0001" min="0">
                        <label for="strike_price">Strike Price</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="option_type" name="option_type">
                            <option value="">Select Option Type</option>
                            <option value="call">Call Option</option>
                            <option value="put">Put Option</option>
                        </select>
                        <label for="option_type">Option Type</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="premium" name="premium" 
                               placeholder="Premium" step="0.0001" min="0">
                        <label for="premium">Premium</label>
                    </div>
                </div>
            </div>
            
                         <!-- Conditional Fields for Different Contract Types -->
             <div id="futures-fields" class="contract-type-fields" style="display:none;">
                 <div class="row">
                     <div class="col-md-6">
                         <div class="form-floating mb-3">
                             <input type="text" class="form-control" id="exchange" name="exchange" placeholder="Exchange">
                             <label for="exchange">Exchange</label>
                         </div>
                     </div>
                     <div class="col-md-6">
                         <div class="form-floating mb-3">
                             <input type="text" class="form-control" id="contract_month" name="contract_month" 
                                    placeholder="Contract Month" pattern="[0-9]{4}-[0-9]{2}" title="Format: YYYY-MM">
                             <label for="contract_month">Contract Month (YYYY-MM)</label>
                         </div>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-md-6">
                         <div class="form-floating mb-3">
                             <input type="number" class="form-control" id="margin_requirement" name="margin_requirement" 
                                    placeholder="Margin Requirement" step="0.01" min="0">
                             <label for="margin_requirement">Margin Requirement</label>
                         </div>
                     </div>
                 </div>
             </div>
             
             <div id="options-fields" class="contract-type-fields" style="display:none;">
                 <div class="row">
                     <div class="col-md-4">
                         <div class="form-floating mb-3">
                             <input type="number" class="form-control" id="strike_price" name="strike_price" 
                                    placeholder="Strike Price" step="0.01" min="0">
                             <label for="strike_price">Strike Price</label>
                         </div>
                     </div>
                     <div class="col-md-4">
                         <div class="form-floating mb-3">
                             <select class="form-control" id="option_type" name="option_type">
                                 <option value="">Select Option Type</option>
                                 <option value="call">Call Option</option>
                                 <option value="put">Put Option</option>
                             </select>
                             <label for="option_type">Option Type</label>
                         </div>
                     </div>
                     <div class="col-md-4">
                         <div class="form-floating mb-3">
                             <input type="number" class="form-control" id="premium" name="premium" 
                                    placeholder="Premium" step="0.01" min="0">
                             <label for="premium">Premium</label>
                         </div>
                     </div>
                 </div>
             </div>
             
             <script>
                 function toggleContractTypeFields() {
                     // Hide all conditional fields
                     document.querySelectorAll('.contract-type-fields').forEach(field => {
                         field.style.display = 'none';
                     });
                     
                     // Show relevant fields based on contract type
                     const contractType = document.getElementById('contract_type').value;
                     if (contractType === 'futures') {
                         document.getElementById('futures-fields').style.display = 'block';
                     } else if (contractType === 'options') {
                         document.getElementById('options-fields').style.display = 'block';
                     }
                     
                     // Auto-generate trade ID if empty
                     const tradeIdField = document.getElementById('trade_id');
                     if (!tradeIdField.value && contractType) {
                         const timestamp = new Date().toISOString().replace(/[-:.TZ]/g, '').substring(0, 12);
                         const random = Math.random().toString(36).substring(2, 5).toUpperCase();
                         tradeIdField.value = 'FT' + timestamp + random;
                     }
                 }
                 
                 // Set minimum date to today
                 document.getElementById('settlement_date').min = new Date().toISOString().split('T')[0];
             </script>
        `;
    },
    // Invoice Form Implementation
    getInvoiceForm() {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="invoice_number" name="invoice_number" 
                               placeholder="Invoice Number" required pattern="INV[0-9]{4}[A-Z0-9]+" 
                               title="Invoice number format: INV followed by numbers and letters">
                        <label for="invoice_number">Invoice Number</label>
                        <div class="form-text">Format: INV2024001 (will auto-generate if empty)</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="trade_id" name="trade_id" 
                               placeholder="Trade ID" required>
                        <label for="trade_id">Related Trade ID</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="counterparty_id" name="counterparty_id" required>
                            <option value="">Select Counterparty</option>
                            <option value="1">ABC Energy Corp</option>
                            <option value="2">XYZ Trading Ltd</option>
                            <option value="3">Global Petro Inc</option>
                            <option value="4">Euro Gas Solutions</option>
                            <option value="5">Asia Energy Partners</option>
                        </select>
                        <label for="counterparty_id">Counterparty</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="currency" name="currency" required>
                            <option value="USD">USD - US Dollar</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="GBP">GBP - British Pound</option>
                            <option value="CAD">CAD - Canadian Dollar</option>
                        </select>
                        <label for="currency">Currency</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="amount" name="amount" 
                               placeholder="Amount" step="0.01" min="0" required>
                        <label for="amount">Amount</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" id="invoice_date" name="invoice_date" required>
                        <label for="invoice_date">Invoice Date</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" id="due_date" name="due_date" required>
                        <label for="due_date">Due Date</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="status" name="status" required>
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                            <option value="paid">Paid</option>
                        </select>
                        <label for="status">Status</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="description" name="description" 
                                  placeholder="Description" style="height: 100px;"></textarea>
                        <label for="description">Description</label>
                    </div>
                </div>
            </div>
            
            <script>
                // Auto-generate invoice number if empty
                function generateInvoiceNumber() {
                    const invoiceField = document.getElementById('invoice_number');
                    if (!invoiceField.value) {
                        const timestamp = new Date().toISOString().replace(/[-:.TZ]/g, '').substring(0, 12);
                        const random = Math.random().toString(36).substring(2, 5).toUpperCase();
                        invoiceField.value = 'INV' + timestamp + random;
                    }
                }
                
                // Set default dates
                function setInvoiceDefaultDates() {
                    const today = new Date().toISOString().split('T')[0];
                    const dueDateField = document.getElementById('due_date');
                    const invoiceDateField = document.getElementById('invoice_date');
                    
                    if (!invoiceDateField.value) {
                        invoiceDateField.value = today;
                    }
                    
                    // Set due date to 30 days from invoice date
                    if (!dueDateField.value) {
                        const dueDate = new Date();
                        dueDate.setDate(dueDate.getDate() + 30);
                        dueDateField.value = dueDate.toISOString().split('T')[0];
                    }
                }
                
                // Set default values when form loads
                setTimeout(() => {
                    generateInvoiceNumber();
                    setInvoiceDefaultDates();
                }, 100);
            </script>
        `;
    },
    // Logistics Form Implementation
    getLogisticsForm() {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="logistics_id" name="logistics_id" 
                               placeholder="Logistics ID" required pattern="LOG[0-9]{4}[A-Z0-9]+" 
                               title="Logistics ID format: LOG followed by numbers and letters">
                        <label for="logistics_id">Logistics ID</label>
                        <div class="form-text">Format: LOG2024001 (will auto-generate if empty)</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="trade_id" name="trade_id" 
                               placeholder="Trade ID" required>
                        <label for="trade_id">Related Trade ID</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="carrier_id" name="carrier_id" required>
                            <option value="">Select Carrier</option>
                            <option value="1">Maritime Transport Co</option>
                            <option value="2">Rail Freight Services</option>
                            <option value="3">Truck Logistics Inc</option>
                            <option value="4">Pipeline Operations</option>
                            <option value="5">Air Cargo Express</option>
                        </select>
                        <label for="carrier_id">Carrier</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="shipping_method" name="shipping_method" required>
                            <option value="">Select Shipping Method</option>
                            <option value="tanker">Tanker Ship</option>
                            <option value="pipeline">Pipeline</option>
                            <option value="rail">Rail Tank Car</option>
                            <option value="truck">Tank Truck</option>
                            <option value="barge">Barge</option>
                        </select>
                        <label for="shipping_method">Shipping Method</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="origin" name="origin" 
                               placeholder="Origin" required>
                        <label for="origin">Origin</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="destination" name="destination" 
                               placeholder="Destination" required>
                        <label for="destination">Destination</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" id="departure_date" name="departure_date">
                        <label for="departure_date">Departure Date</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" id="arrival_date" name="arrival_date">
                        <label for="arrival_date">Expected Arrival Date</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="in_transit">In Transit</option>
                            <option value="delivered">Delivered</option>
                        </select>
                        <label for="status">Status</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="tracking_number" name="tracking_number" 
                               placeholder="Tracking Number">
                        <label for="tracking_number">Tracking Number</label>
                    </div>
                </div>
            </div>
            
            <script>
                // Auto-generate logistics ID if empty
                function generateLogisticsId() {
                    const logisticsField = document.getElementById('logistics_id');
                    if (!logisticsField.value) {
                        const timestamp = new Date().toISOString().replace(/[-:.TZ]/g, '').substring(0, 12);
                        const random = Math.random().toString(36).substring(2, 5).toUpperCase();
                        logisticsField.value = 'LOG' + timestamp + random;
                    }
                }
                
                // Set default departure date to today
                function setLogisticsDefaultDates() {
                    const today = new Date().toISOString().split('T')[0];
                    const departureDateField = document.getElementById('departure_date');
                    const arrivalDateField = document.getElementById('arrival_date');
                    
                    if (!departureDateField.value) {
                        departureDateField.value = today;
                    }
                    
                    // Set arrival date to 7 days from departure by default
                    if (!arrivalDateField.value) {
                        const arrivalDate = new Date();
                        arrivalDate.setDate(arrivalDate.getDate() + 7);
                        arrivalDateField.value = arrivalDate.toISOString().split('T')[0];
                    }
                }
                
                // Set default values when form loads
                setTimeout(() => {
                    generateLogisticsId();
                    setLogisticsDefaultDates();
                }, 100);
            </script>
        `;
    },
    // Settlement Form Implementation
    getSettlementForm() {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="settlement_id" name="settlement_id" 
                               placeholder="Settlement ID" required pattern="SET[0-9]{4}[A-Z0-9]+" 
                               title="Settlement ID format: SET followed by numbers and letters">
                        <label for="settlement_id">Settlement ID</label>
                        <div class="form-text">Format: SET2024001 (will auto-generate if empty)</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="trade_id" name="trade_id" 
                               placeholder="Trade ID" required>
                        <label for="trade_id">Related Trade ID</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="counterparty_id" name="counterparty_id" required>
                            <option value="">Select Counterparty</option>
                            <option value="1">ABC Energy Corp</option>
                            <option value="2">XYZ Trading Ltd</option>
                            <option value="3">Global Petro Inc</option>
                            <option value="4">Euro Gas Solutions</option>
                            <option value="5">Asia Energy Partners</option>
                        </select>
                        <label for="counterparty_id">Counterparty</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="currency" name="currency" required>
                            <option value="USD">USD - US Dollar</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="GBP">GBP - British Pound</option>
                            <option value="CAD">CAD - Canadian Dollar</option>
                        </select>
                        <label for="currency">Currency</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="amount" name="amount" 
                               placeholder="Amount" step="0.01" min="0" required>
                        <label for="amount">Settlement Amount</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" id="settlement_date" name="settlement_date" required>
                        <label for="settlement_date">Settlement Date</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="processed">Processed</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                        </select>
                        <label for="status">Status</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="payment_method" name="payment_method">
                            <option value="">Select Payment Method</option>
                            <option value="wire_transfer">Wire Transfer</option>
                            <option value="ach">ACH Transfer</option>
                            <option value="check">Check</option>
                            <option value="letter_of_credit">Letter of Credit</option>
                            <option value="cash">Cash</option>
                        </select>
                        <label for="payment_method">Payment Method</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="reference_number" name="reference_number" 
                               placeholder="Reference Number">
                        <label for="reference_number">Reference Number</label>
                    </div>
                </div>
            </div>
            
            <script>
                // Auto-generate settlement ID if empty
                function generateSettlementId() {
                    const settlementField = document.getElementById('settlement_id');
                    if (!settlementField.value) {
                        const timestamp = new Date().toISOString().replace(/[-:.TZ]/g, '').substring(0, 12);
                        const random = Math.random().toString(36).substring(2, 5).toUpperCase();
                        settlementField.value = 'SET' + timestamp + random;
                    }
                }
                
                // Set default settlement date
                function setSettlementDefaultDate() {
                    const settlementDateField = document.getElementById('settlement_date');
                    
                    if (!settlementDateField.value) {
                        // Default to T+2 settlement
                        const settlementDate = new Date();
                        settlementDate.setDate(settlementDate.getDate() + 2);
                        settlementDateField.value = settlementDate.toISOString().split('T')[0];
                    }
                }
                
                // Set default values when form loads
                setTimeout(() => {
                    generateSettlementId();
                    setSettlementDefaultDate();
                }, 100);
            </script>
        `;
    },
    // Product Form Implementation
    getProductForm() {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="code" name="code" 
                               placeholder="Product Code" required pattern="[A-Z0-9_-]+" 
                               title="Product code must be alphanumeric with hyphens/underscores only">
                        <label for="code">Product Code</label>
                        <div class="form-text">Format: CRUDE-WTI (alphanumeric with hyphens/underscores)</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="product_name" name="product_name" 
                               placeholder="Product Name" required maxlength="100">
                        <label for="product_name">Product Name</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="crude_oil">Crude Oil</option>
                            <option value="natural_gas">Natural Gas</option>
                            <option value="refined_products">Refined Products</option>
                            <option value="petrochemicals">Petrochemicals</option>
                            <option value="power">Power</option>
                            <option value="coal">Coal</option>
                            <option value="renewables">Renewables</option>
                        </select>
                        <label for="category">Category</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="unit_of_measure" name="unit_of_measure" required>
                            <option value="">Select Unit of Measure</option>
                            <option value="barrel">Barrel (bbl)</option>
                            <option value="mmbtu">Million BTU (MMBTU)</option>
                            <option value="gallon">Gallon (gal)</option>
                            <option value="liter">Liter (L)</option>
                            <option value="metric_ton">Metric Ton (MT)</option>
                            <option value="mwh">Megawatt Hour (MWh)</option>
                            <option value="short_ton">Short Ton</option>
                        </select>
                        <label for="unit_of_measure">Unit of Measure</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="active_status" name="active_status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <label for="active_status">Status</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="description" name="description" 
                                  placeholder="Description" style="height: 100px;"></textarea>
                        <label for="description">Description</label>
                    </div>
                </div>
            </div>
            
            <script>
                // Auto-generate product code from name
                function generateProductCode() {
                    const nameField = document.getElementById('product_name');
                    const codeField = document.getElementById('code');
                    
                    if (nameField.value && !codeField.value) {
                        const code = nameField.value
                            .toUpperCase()
                            .replace(/[^A-Z0-9]/g, '-')
                            .replace(/-+/g, '-')
                            .replace(/^-|-$/g, '');
                        codeField.value = code;
                    }
                }
                
                // Add event listener for auto-code generation
                setTimeout(() => {
                    const nameField = document.getElementById('product_name');
                    if (nameField) {
                        nameField.addEventListener('blur', generateProductCode);
                    }
                }, 100);
            </script>
        `;
    },
    // Business Unit Form Implementation
    getBusinessUnitForm() {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="code" name="code" 
                               placeholder="Business Unit Code" required pattern="[A-Z0-9_-]+" 
                               title="Business unit code must be alphanumeric with hyphens/underscores only">
                        <label for="code">Business Unit Code</label>
                        <div class="form-text">Format: NORTH-AMERICA (alphanumeric with hyphens/underscores)</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="business_unit_name" name="business_unit_name" 
                               placeholder="Business Unit Name" required maxlength="100">
                        <label for="business_unit_name">Business Unit Name</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="parent_unit_id" name="parent_unit_id">
                            <option value="">No Parent Unit (Top Level)</option>
                            <option value="1">Corporate HQ</option>
                            <option value="2">North America Region</option>
                            <option value="3">Europe Region</option>
                            <option value="4">Asia Pacific Region</option>
                        </select>
                        <label for="parent_unit_id">Parent Business Unit</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="manager_id" name="manager_id">
                            <option value="">No Manager Assigned</option>
                            <option value="1">John Smith - VP Trading</option>
                            <option value="2">Sarah Johnson - Director Operations</option>
                            <option value="3">Michael Chen - Regional Manager</option>
                            <option value="4">Emma Wilson - Business Development</option>
                        </select>
                        <label for="manager_id">Manager</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="active_status" name="active_status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <label for="active_status">Status</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- Placeholder for visual balance -->
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Hierarchical structure allows nested business units for complex organizations.
                        </small>
                    </div>
                </div>
            </div>
            
            <script>
                // Auto-generate business unit code from name
                function generateBusinessUnitCode() {
                    const nameField = document.getElementById('business_unit_name');
                    const codeField = document.getElementById('code');
                    
                    if (nameField.value && !codeField.value) {
                        const code = nameField.value
                            .toUpperCase()
                            .replace(/[^A-Z0-9]/g, '-')
                            .replace(/-+/g, '-')
                            .replace(/^-|-$/g, '');
                        codeField.value = code;
                    }
                }
                
                // Add event listener for auto-code generation
                setTimeout(() => {
                    const nameField = document.getElementById('business_unit_name');
                    if (nameField) {
                        nameField.addEventListener('blur', generateBusinessUnitCode);
                    }
                }, 100);
            </script>
        `;
    },
    // Broker Form Implementation (Basic)
    getBrokerForm() {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="code" name="code" placeholder="Broker Code" required>
                        <label for="code">Broker Code</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Broker Name" required>
                        <label for="name">Broker Name</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="exchange" name="exchange" placeholder="Exchange">
                        <label for="exchange">Exchange</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="commission_rate" name="commission_rate" 
                               placeholder="Commission Rate" step="0.01">
                        <label for="commission_rate">Commission Rate (%)</label>
                    </div>
                </div>
            </div>
        `;
    },

    // Port Form Implementation (Basic)
    getPortForm() {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="code" name="code" placeholder="Port Code" required>
                        <label for="code">Port Code</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Port Name" required>
                        <label for="name">Port Name</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="country" name="country" placeholder="Country" required>
                        <label for="country">Country</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="port_type" name="port_type" required>
                            <option value="">Select Port Type</option>
                            <option value="oil">Oil Terminal</option>
                            <option value="gas">LNG Terminal</option>
                            <option value="refined">Refined Products</option>
                            <option value="general">General Cargo</option>
                        </select>
                        <label for="port_type">Port Type</label>
                    </div>
                </div>
            </div>
        `;
    },

    // Carrier Form Implementation (Basic)
    getCarrierForm() {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="code" name="code" placeholder="Carrier Code" required>
                        <label for="code">Carrier Code</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Carrier Name" required>
                        <label for="name">Carrier Name</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="transport_type" name="transport_type" required>
                            <option value="">Select Transport Type</option>
                            <option value="maritime">Maritime</option>
                            <option value="rail">Rail</option>
                            <option value="truck">Truck</option>
                            <option value="pipeline">Pipeline</option>
                            <option value="air">Air</option>
                        </select>
                        <label for="transport_type">Transport Type</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="contact_email" name="contact_email" placeholder="Contact Email">
                        <label for="contact_email">Contact Email</label>
                    </div>
                </div>
            </div>
        `;
    },

    // Role Form Implementation (Basic)
    getRoleForm() {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Role Name" required>
                        <label for="name">Role Name</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="description" name="description" placeholder="Description">
                        <label for="description">Description</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="can_trade" name="permissions[]" value="can_trade">
                            <label class="form-check-label" for="can_trade">Can Trade</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="can_view_reports" name="permissions[]" value="can_view_reports">
                            <label class="form-check-label" for="can_view_reports">Can View Reports</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="can_manage_users" name="permissions[]" value="can_manage_users">
                            <label class="form-check-label" for="can_manage_users">Can Manage Users</label>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    // Permission Form Implementation (Basic)
    getPermissionForm() {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Permission Name" required>
                        <label for="name">Permission Name</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="description" name="description" placeholder="Description">
                        <label for="description">Description</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="module" name="module" required>
                            <option value="">Select Module</option>
                            <option value="trading">Trading</option>
                            <option value="operations">Operations</option>
                            <option value="master_data">Master Data</option>
                            <option value="reports">Reports</option>
                            <option value="admin">Administration</option>
                        </select>
                        <label for="module">Module</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="action" name="action" required>
                            <option value="">Select Action</option>
                            <option value="create">Create</option>
                            <option value="read">Read</option>
                            <option value="update">Update</option>
                            <option value="delete">Delete</option>
                        </select>
                        <label for="action">Action</label>
                    </div>
                </div>
            </div>
        `;
    },

//new forms
// Example form generators for each tab

getMarketPriceForm() {
    return `
          <div class="row">
            <div class="col-md-6">
            <div class="form-floating mb-3">
                <select class="form-select" id="market_index" name="market_index" required>
                    <option value="" disabled selected>Loading...</option>
                </select>
                <label for="market_index">Market Index</label>
            </div>

            </div>
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="date" class="form-control" id="closing_date" name="closing_date" required>
                    <label for="closing_date">Closing Date</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="date" class="form-control" id="expiry_date" name="expiry_date" required>
                    <label for="expiry_date">Expiry Date</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="number" class="form-control" id="closing_price" name="closing_price" placeholder="Closing Price" required>
                    <label for="closing_price">Closing Price</label>
                </div>
            </div>
        </div>
    `;
},

getContractTypeForm() {
    return `
        <div class="row">
            <div class="col-md-12">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="contract_type" name="name" placeholder="Contract Type" required>
                    <label for="contract_type">Contract Type</label>
                </div>
            </div>
        </div>
    `;
},

getCommodityNameForm() {
    return `
        <div class="row">
            <div class="col-md-12">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="commodity_name" name="name" placeholder="Commodity Name" required>
                    <label for="commodity_name">Commodity Name</label>
                </div>
            </div>
        </div>
    `;
},

getProductUomForm() {
    return `
        <div class="row">
            <div class="col-md-12">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="product_uom" name="product_uom" placeholder="Unit of Measure" required>
                    <label for="product_uom">Product UOM</label>
                </div>
            </div>
        </div>
    `;
},

getPricingUnitForm() {
    return `
        <div class="row">
            <div class="col-md-12">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="pricing_unit" name="name" placeholder="Pricing Unit" required>
                    <label for="pricing_unit">Pricing Unit</label>
                </div>
            </div>
        </div>
    `;
},

getPricingFormulaForm() {
    return `
        <div class="row">
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="formula_name" name="name" placeholder="Formula Name" required>
                    <label for="formula_name">Formula Name</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <textarea class="form-control" id="formula_details" name="details" placeholder="Formula Details" style="height:100px" required></textarea>
                    <label for="formula_details">Formula Details</label>
                </div>
            </div>
        </div>
    `;
},

getMarketIndexForm() {
    return `
        <div class="row">
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="index_name" name="index_name" placeholder="Index Name" required>
                    <label for="index_name">Index Name</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="index_uom" name="index_uom" placeholder="Index UoM" required>
                    <label for="index_uom">Index UoM</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="exchange" name="exchange" placeholder="Exchange" required>
                    <label for="exchange">Exchange</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="date" class="form-control" id="expiry_date" name="expiry_date" placeholder="Expiry Date" required>
                    <label for="expiry_date">Expiry Date</label>
                </div>
            </div>
        </div>
    `;
},

getPaymentTermsForm() {
    return `
        <div class="row">
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="payment_term_title" name="name" placeholder="Payment Term Title" required>
                    <label for="payment_term_title">Payment Term Title</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <textarea class="form-control" id="payment_terms_description" name="details" placeholder="Payment Terms Described / Elaborated" style="height:100px" required></textarea>
                    <label for="payment_terms_description">Payment Terms Described / Elaborated</label>
                </div>
            </div>
        </div>
    `;
},

getTransferMethodForm() {
    return `
        <div class="row">
            <div class="col-md-12">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="transfer_method" name="name" placeholder="Transfer Method" required>
                    <label for="transfer_method">Transfer Method</label>
                </div>
            </div>
        </div>
    `;
},

getGoverningBodyForm() {
    return `
        <div class="row">
            <div class="col-md-12">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="governing_body" name="name" placeholder="Governing Body" required>
                    <label for="governing_body">Governing Body</label>
                </div>
            </div>
        </div>
    `;
},

getLoadProfitForm() {
    return `
        <div class="row">
            <div class="col-md-12">
                <div class="form-floating mb-3">
                    <input type="number" class="form-control" id="load_profit" name="load_profit" placeholder="Load Profit %" required>
                    <label for="load_profit">Load Profit (%)</label>
                </div>
            </div>
        </div>
    `;
},

getDischargingPortForm() {
    return `
        <div class="row">
            <div class="col-md-12">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="discharging_port" name="name" placeholder="Discharging Port" required>
                    <label for="discharging_port">Discharging Port</label>
                </div>
            </div>
        </div>
    `;
},

getPricingUomForm() {
    return `
        <div class="row">
            <div class="col-md-12">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="pricing_uom" name="name" placeholder="Pricing UOM" required>
                    <label for="pricing_uom">Pricing UOM</label>
                </div>
            </div>
        </div>
    `;
},

getInternalBUForm() {
    return `
        <div class="row">
            <div class="col-md-12">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="internal_bu" name="name" placeholder="Internal Business Unit" required>
                    <label for="internal_bu">Internal BU</label>
                </div>
            </div>
        </div>
    `;
},

getApiFixForm() {
    return `
        <div class="row">
            <div class="col-md-12">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="api_fix" name="name" placeholder="API FIX Details" required>
                    <label for="api_fix">API FIX Trade Capture</label>
                </div>
            </div>
        </div>
    `;
},

getPortfolioForm() {
    return `
        <div class="row">
            <div class="col-md-12">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="portfolio" name="name" placeholder="Portfolio Name" required>
                    <label for="portfolio">Portfolio</label>
                </div>
            </div>
        </div>
    `;
},

getExchangeForm() {
    return `
        <div class="row">
            <div class="col-md-12">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="exchange" name="name" placeholder="Exchange Name" required>
                    <label for="exchange">Exchange</label>
                </div>
            </div>
        </div>
    `;
},

getCurrencyForm() {
    return `
        <div class="row">
            <div class="col-md-12">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="currency" name="name" placeholder="Currency" required>
                    <label for="currency">Currency</label>
                </div>
            </div>
        </div>
    `;
},
getEndOfDayProcessForm() {
    return `
        <div class="eod-process-form">
            <h5 class="mb-4">End of Day Process Configuration</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Retrieve latest prices from external sources</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="retrieve_prices" name="retrieve_prices" checked>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Run custom price curve calculations</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="price_curve_calc" name="price_curve_calc" checked>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Verify that all required prices are available</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="verify_prices" name="verify_prices" checked>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Recompute trade values with the latest prices</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="recompute_trade_values" name="recompute_trade_values" checked>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Calculate exposures for all open positions</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="calc_exposures" name="calc_exposures" checked>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Compute Mark-to-Market (MtM) values</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="compute_mtm" name="compute_mtm" checked>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Calculate P&L (Realized and Unrealized)</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="calc_pnl" name="calc_pnl" checked>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Recompute valuations for physical assets</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="recompute_valuations" name="recompute_valuations" checked>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Update credit exposure for counterparties</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="update_credit_exposure" name="update_credit_exposure" checked>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Generate and validate invoices</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="generate_invoices" name="generate_invoices" checked>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Process payments and send instructions</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="process_payments" name="process_payments" checked>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Export data to ERP and accounting systems</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="export_data" name="export_data" checked>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Generate required reports</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="generate_reports" name="generate_reports" checked>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Distribute reports to respective teams</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="distribute_reports" name="distribute_reports" checked>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Ensure critical applications running early morning</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ensure_apps_running" name="ensure_apps_running" checked>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Ensure users can access updated reports</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ensure_user_access" name="ensure_user_access" checked>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
},

    // ===== UTILITY METHODS =====
async apiCall(endpoint) {
    const url = `${this.config.apiBaseUrl}${endpoint}`;

    try {
        const response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        console.log('API Response:', data);
        return data;
    } catch (error) {
        console.error('API Call Error:', error);
        throw error;
    }
},

async populateMarketIndexSelect() {
    try {
        const data = await this.apiCall('/master-data/market-index.php');
        const select = document.getElementById('market_index');

        // Clear existing options
        select.innerHTML = '<option value="" disabled selected>Select Market Index</option>';
console.log('market-index data:', data);
        if (data.success && Array.isArray(data.data)) {
            data.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;                 // value = index id
                option.textContent = item.index_name;   // label = index name
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading Market Index options:', error);
    }
},
    showLoading(container) {
        container.innerHTML = '<div class="loading"></div>';
    },

    showError(container, message) {
        container.innerHTML = `
            <div class="error-state">
                <i class="bi bi-exclamation-triangle"></i>
                <h5>Error</h5>
                <p>${message}</p>
            </div>
        `;
    },

    showSuccess(message) {
        // Create a temporary success notification
        const notification = document.createElement('div');
        notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="bi bi-check-circle"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    },

    formatDate(dateString) {
        if (!dateString) return '-';
        
        try {
            const date = new Date(dateString);
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return dateString;
        }
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    ETRM.init();
}); 