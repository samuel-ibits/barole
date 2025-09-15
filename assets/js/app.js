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
        }
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

    // ===== COLUMN DEFINITIONS =====
    getPhysicalSalesColumns() {
        return [
            { field: 'trade_id', title: 'Trade ID' },
            { field: 'counterparty_name', title: 'Counterparty' },
            { field: 'product_name', title: 'Product' },
            { field: 'quantity', title: 'Quantity', type: 'number' },
            { field: 'price', title: 'Price', type: 'currency' },
            { field: 'status', title: 'Status', type: 'status' },
            { field: 'trade_date', title: 'Date', type: 'date' }
        ];
    },

    getFinancialTradesColumns() {
        return [
            { field: 'trade_id', title: 'Trade ID' },
            { field: 'counterparty_name', title: 'Counterparty' },
            { field: 'commodity_name', title: 'Commodity' },
            { field: 'trade_type', title: 'Trade Type' },
            { field: 'contract_type', title: 'Contract Type' },
            { field: 'quantity', title: 'Quantity', type: 'number' },
            { field: 'price', title: 'Price', type: 'currency' },
            { field: 'currency', title: 'Currency' },
            { field: 'total_value', title: 'Total Value', type: 'currency' },
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

        // Special validation for user creation
        if (apiEndpoint.includes('users/create')) {
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

        // Show loading state
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
        saveBtn.disabled = true;

        // Submit to API
        fetch(`api/${apiEndpoint}`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.text(); // Get text first to see what we're receiving
        })
        .then(text => {
            console.log('Response text:', text);
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Raw response:', text);
                
                // Check if response contains HTML (likely an error page)
                if (text.includes('<!DOCTYPE html>') || text.includes('<html>')) {
                    throw new Error('Server returned an error page instead of JSON. Check authentication or API endpoint.');
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

                // Show success message first
                const recordType = apiEndpoint.split('/').pop().replace('.php', '').replace('-', ' ');
                this.showNotification('Success', `${recordType} created successfully!`, 'success');

                // Refresh the current table with a small delay to ensure DB transaction is complete
                setTimeout(() => {
                    try {
                        this.refreshCurrentTable();
                    } catch (refreshError) {
                        console.error('Table refresh failed:', refreshError);
                        this.showNotification('Info', 'Record created successfully. Please refresh the page to see the new data.', 'info');
                    }
                }, 200);
            } else {
                throw new Error(data.message || 'Failed to create record');
            }
        })
        .catch(error => {
            console.error('Create error:', error);
            this.showNotification('Error', error.message || 'Failed to create record', 'danger');
        })
        .finally(() => {
            // Restore button
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
    },

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
                        <input type="text" class="form-control" id="trade_id" name="trade_id" placeholder="Trade ID" required>
                        <label for="trade_id">Trade ID</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="counterparty_id" name="counterparty_id" required>
                            <option value="">Select Counterparty</option>
                            <option value="1">ABC Energy Corp</option>
                            <option value="2">XYZ Trading Ltd</option>
                            <option value="3">Global Petro Inc</option>
                        </select>
                        <label for="counterparty_id">Counterparty</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="product_id" name="product_id" required>
                            <option value="">Select Product</option>
                            <option value="1">Crude Oil WTI</option>
                            <option value="2">Natural Gas</option>
                            <option value="3">Gasoline</option>
                        </select>
                        <label for="product_id">Product</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Quantity" step="0.01" required>
                        <label for="quantity">Quantity</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="price" name="price" placeholder="Price" step="0.01" required>
                        <label for="price">Price per Unit</label>
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
                        <select class="form-control" id="status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="executed">Executed</option>
                        </select>
                        <label for="status">Status</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="location" name="location" placeholder="Location">
                        <label for="location">Location</label>
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
                               placeholder="Trade ID" required pattern="FX[0-9]{4}[A-Z0-9]+" 
                               title="Trade ID format: FX followed by numbers and letters">
                        <label for="trade_id">Trade ID</label>
                        <div class="form-text">Format: FX2024001 (will auto-generate if empty)</div>
                    </div>
                </div>
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
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="business_unit_id" name="business_unit_id" required>
                            <option value="1">North America Trading</option>
                            <option value="2">Europe Trading</option>
                            <option value="3">Asia Pacific Trading</option>
                        </select>
                        <label for="business_unit_id">Business Unit</label>
                    </div>
                </div>
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
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="base_currency" name="base_currency" required onchange="generateFXTradeId()">
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
                        <select class="form-control" id="quote_currency" name="quote_currency" required onchange="generateFXTradeId()">
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
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="amount" name="amount" 
                               placeholder="Amount" step="0.01" min="0" required onchange="calculateFXTotal()">
                        <label for="amount">Amount (Base Currency)</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="exchange_rate" name="exchange_rate" 
                               placeholder="Exchange Rate" step="0.000001" min="0" required onchange="calculateFXTotal()">
                        <label for="exchange_rate">Exchange Rate</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="total_value" name="total_value" 
                               placeholder="Total Value" readonly>
                        <label for="total_value">Total Value (Quote Currency)</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" id="trade_date" name="trade_date" required>
                        <label for="trade_date">Trade Date</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" id="value_date" name="value_date" required>
                        <label for="value_date">Value Date</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" id="settlement_date" name="settlement_date">
                        <label for="settlement_date">Settlement Date</label>
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
                        </select>
                        <label for="status">Status</label>
                    </div>
                </div>
            </div>
            
            <script>
                // Auto-calculate total value for FX trades
                function calculateFXTotal() {
                    const amount = parseFloat(document.getElementById('amount').value) || 0;
                    const rate = parseFloat(document.getElementById('exchange_rate').value) || 0;
                    const total = amount * rate;
                    document.getElementById('total_value').value = total.toFixed(2);
                }
                
                // Set default dates for FX trades
                function setFXDefaultDates() {
                    const today = new Date().toISOString().split('T')[0];
                    const valueDateField = document.getElementById('value_date');
                    const tradeDateField = document.getElementById('trade_date');
                    
                    if (!tradeDateField.value) {
                        tradeDateField.value = today;
                    }
                    
                    // Set value date to T+2 (2 business days)
                    if (!valueDateField.value) {
                        const valueDate = new Date();
                        valueDate.setDate(valueDate.getDate() + 2);
                        valueDateField.value = valueDate.toISOString().split('T')[0];
                    }
                }
                
                // Generate FX trade ID based on currencies
                function generateFXTradeId() {
                    const baseCurrency = document.getElementById('base_currency').value;
                    const quoteCurrency = document.getElementById('quote_currency').value;
                    const tradeIdField = document.getElementById('trade_id');
                    
                    if (baseCurrency && quoteCurrency && !tradeIdField.value) {
                        const timestamp = new Date().toISOString().replace(/[-:.TZ]/g, '').substring(0, 12);
                        const random = Math.random().toString(36).substring(2, 5).toUpperCase();
                        tradeIdField.value = 'FX' + timestamp + random;
                    }
                }
                
                // Set default dates when form loads
                setTimeout(setFXDefaultDates, 100);
            </script>
        `;
    },

    getCounterpartyForm() {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="code" name="code" placeholder="Code" required>
                        <label for="code">Code</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Name" required>
                        <label for="name">Name</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="type" name="type" required>
                            <option value="buyer">Buyer</option>
                            <option value="seller">Seller</option>
                            <option value="both">Both</option>
                        </select>
                        <label for="type">Type</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="country" name="country" placeholder="Country" required>
                        <label for="country">Country</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email">
                        <label for="email">Email</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone">
                        <label for="phone">Phone</label>
                    </div>
                </div>
            </div>
            <div class="form-floating mb-3">
                <textarea class="form-control" id="address" name="address" placeholder="Address" style="height: 100px"></textarea>
                <label for="address">Address</label>
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
                               placeholder="Trade ID" required pattern="FT[0-9]{4}[A-Z0-9]+" 
                               title="Trade ID format: FT followed by numbers and letters">
                        <label for="trade_id">Trade ID</label>
                        <div class="form-text">Format: FT2024001 (will auto-generate if empty)</div>
                    </div>
                </div>
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
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="commodity_id" name="commodity_id" required>
                            <option value="">Select Commodity</option>
                            <option value="1">Crude Oil WTI</option>
                            <option value="2">Natural Gas Henry Hub</option>
                            <option value="3">Gasoline RBOB</option>
                            <option value="4">Diesel ULSD</option>
                            <option value="5">Jet Fuel</option>
                        </select>
                        <label for="commodity_id">Commodity</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="business_unit_id" name="business_unit_id" required>
                            <option value="1">North America Trading</option>
                            <option value="2">Europe Trading</option>
                            <option value="3">Asia Pacific Trading</option>
                        </select>
                        <label for="business_unit_id">Business Unit</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="quantity" name="quantity" 
                               placeholder="Quantity" step="0.01" min="0" required>
                        <label for="quantity">Quantity</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="price" name="price" 
                               placeholder="Price" step="0.01" min="0" required>
                        <label for="price">Price per Unit</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="currency" name="currency" required>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="GBP">GBP</option>
                            <option value="CAD">CAD</option>
                        </select>
                        <label for="currency">Currency</label>
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
                         <select class="form-control" id="contract_type" name="contract_type" required onchange="toggleContractTypeFields()">
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
                        <input type="date" class="form-control" id="settlement_date" name="settlement_date" required>
                        <label for="settlement_date">Settlement Date</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating mb-3">
                        <select class="form-control" id="status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="executed">Executed</option>
                        </select>
                        <label for="status">Status</label>
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

    // ===== UTILITY METHODS =====
    apiCall(endpoint) {
        const url = `${this.config.apiBaseUrl}${endpoint}`;
        
        return fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        });
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