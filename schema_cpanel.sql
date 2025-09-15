-- ETRM System Database Schema - cPanel Compatible
-- Complete database structure for Energy Trading and Risk Management System
-- NOTE: Create the database through cPanel first, then run this script

-- Users & Authentication Tables
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_role (role)
);

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    permissions JSON,
    is_system_role BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role_name (role_name)
);

CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(100) UNIQUE NOT NULL,
    display_name VARCHAR(150) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_permission_name (permission_name),
    INDEX idx_category (category)
);

CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);

CREATE TABLE user_activity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_created_at (created_at)
);

-- Master Data Tables
CREATE TABLE counterparties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('supplier', 'customer', 'broker', 'bank') NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    country VARCHAR(50),
    tax_id VARCHAR(50),
    credit_rating VARCHAR(10),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_type (type),
    INDEX idx_status (status)
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    unit_of_measure VARCHAR(20) NOT NULL,
    description TEXT,
    specifications JSON,
    active_status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_product_name (product_name),
    INDEX idx_category (category),
    INDEX idx_active_status (active_status)
);

CREATE TABLE business_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    business_unit_name VARCHAR(100) NOT NULL,
    parent_unit_id INT NULL,
    manager_id INT NULL,
    budget DECIMAL(15,2),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_unit_id) REFERENCES business_units(id),
    FOREIGN KEY (manager_id) REFERENCES users(id),
    INDEX idx_code (code),
    INDEX idx_business_unit_name (business_unit_name),
    INDEX idx_parent_unit_id (parent_unit_id),
    INDEX idx_status (status)
);

CREATE TABLE brokers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    commission_rate DECIMAL(5,4),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_status (status)
);

CREATE TABLE ports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(50) NOT NULL,
    region VARCHAR(50),
    port_type ENUM('loading', 'discharge', 'both') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_name (name),
    INDEX idx_country (country),
    INDEX idx_status (status)
);

CREATE TABLE carriers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    carrier_type ENUM('shipping', 'pipeline', 'truck', 'rail') NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_carrier_type (carrier_type),
    INDEX idx_status (status)
);

-- Trading Tables
CREATE TABLE physical_sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id VARCHAR(20) UNIQUE NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(15,4) NOT NULL,
    price DECIMAL(15,4) NOT NULL,
    currency VARCHAR(3) NOT NULL,
    delivery_date DATE NOT NULL,
    counterparty_id INT NOT NULL,
    loading_port_id INT,
    discharge_port_id INT,
    business_unit_id INT NOT NULL,
    trader_id INT NOT NULL,
    status ENUM('draft', 'confirmed', 'shipped', 'delivered', 'invoiced', 'paid', 'cancelled') NOT NULL DEFAULT 'draft',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (counterparty_id) REFERENCES counterparties(id),
    FOREIGN KEY (loading_port_id) REFERENCES ports(id),
    FOREIGN KEY (discharge_port_id) REFERENCES ports(id),
    FOREIGN KEY (business_unit_id) REFERENCES business_units(id),
    FOREIGN KEY (trader_id) REFERENCES users(id),
    INDEX idx_sale_id (sale_id),
    INDEX idx_product_id (product_id),
    INDEX idx_counterparty_id (counterparty_id),
    INDEX idx_trader_id (trader_id),
    INDEX idx_status (status),
    INDEX idx_delivery_date (delivery_date)
);

CREATE TABLE financial_trades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trade_id VARCHAR(20) UNIQUE NOT NULL,
    commodity_id INT NOT NULL,
    trade_type ENUM('buy', 'sell', 'hedge') NOT NULL,
    contract_type ENUM('futures', 'options', 'swaps', 'forwards') NOT NULL,
    quantity DECIMAL(15,4) NOT NULL,
    price DECIMAL(15,4) NOT NULL,
    currency VARCHAR(3) NOT NULL,
    settlement_date DATE,
    counterparty_id INT NOT NULL,
    business_unit_id INT NOT NULL,
    trader_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'executed', 'settled', 'cancelled') NOT NULL DEFAULT 'pending',
    margin_requirement DECIMAL(15,2),
    exchange VARCHAR(50),
    contract_month VARCHAR(7),
    strike_price DECIMAL(15,4),
    option_type ENUM('call', 'put'),
    premium DECIMAL(15,4),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (commodity_id) REFERENCES products(id),
    FOREIGN KEY (counterparty_id) REFERENCES counterparties(id),
    FOREIGN KEY (business_unit_id) REFERENCES business_units(id),
    FOREIGN KEY (trader_id) REFERENCES users(id),
    INDEX idx_trade_id (trade_id),
    INDEX idx_commodity_id (commodity_id),
    INDEX idx_counterparty_id (counterparty_id),
    INDEX idx_trader_id (trader_id),
    INDEX idx_status (status),
    INDEX idx_settlement_date (settlement_date)
);

CREATE TABLE fx_trades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trade_id VARCHAR(20) UNIQUE NOT NULL,
    base_currency VARCHAR(3) NOT NULL,
    quote_currency VARCHAR(3) NOT NULL,
    trade_type ENUM('buy', 'sell') NOT NULL,
    amount DECIMAL(15,4) NOT NULL,
    exchange_rate DECIMAL(15,8) NOT NULL,
    settlement_date DATE,
    trade_date DATE NOT NULL,
    value_date DATE NOT NULL,
    counterparty_id INT NOT NULL,
    business_unit_id INT NOT NULL,
    trader_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'executed', 'settled', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (counterparty_id) REFERENCES counterparties(id),
    FOREIGN KEY (business_unit_id) REFERENCES business_units(id),
    FOREIGN KEY (trader_id) REFERENCES users(id),
    INDEX idx_trade_id (trade_id),
    INDEX idx_base_currency (base_currency),
    INDEX idx_counterparty_id (counterparty_id),
    INDEX idx_trader_id (trader_id),
    INDEX idx_status (status),
    INDEX idx_trade_date (trade_date)
);

-- Operations Tables
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(20) UNIQUE NOT NULL,
    sale_id INT,
    counterparty_id INT NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) NOT NULL,
    status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') NOT NULL DEFAULT 'draft',
    line_items JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES physical_sales(id),
    FOREIGN KEY (counterparty_id) REFERENCES counterparties(id),
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_counterparty_id (counterparty_id),
    INDEX idx_status (status),
    INDEX idx_invoice_date (invoice_date),
    INDEX idx_due_date (due_date)
);

CREATE TABLE logistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    logistics_id VARCHAR(20) UNIQUE NOT NULL,
    sale_id INT NOT NULL,
    carrier_id INT,
    vessel_name VARCHAR(100),
    loading_port_id INT,
    discharge_port_id INT,
    loading_date DATE,
    discharge_date DATE,
    status ENUM('planned', 'loading', 'in_transit', 'delivered', 'cancelled') NOT NULL DEFAULT 'planned',
    documents JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES physical_sales(id),
    FOREIGN KEY (carrier_id) REFERENCES carriers(id),
    FOREIGN KEY (loading_port_id) REFERENCES ports(id),
    FOREIGN KEY (discharge_port_id) REFERENCES ports(id),
    INDEX idx_logistics_id (logistics_id),
    INDEX idx_sale_id (sale_id),
    INDEX idx_carrier_id (carrier_id),
    INDEX idx_status (status),
    INDEX idx_loading_date (loading_date)
);

CREATE TABLE settlements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    settlement_id VARCHAR(20) UNIQUE NOT NULL,
    trade_id INT,
    invoice_id INT,
    settlement_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) NOT NULL,
    payment_method VARCHAR(50),
    reference_number VARCHAR(50),
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (trade_id) REFERENCES financial_trades(id),
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    INDEX idx_settlement_id (settlement_id),
    INDEX idx_trade_id (trade_id),
    INDEX idx_invoice_id (invoice_id),
    INDEX idx_status (status),
    INDEX idx_settlement_date (settlement_date)
);

-- Risk & Analytics Tables
CREATE TABLE portfolio_positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    position_date DATE NOT NULL,
    commodity_id INT NOT NULL,
    business_unit_id INT NOT NULL,
    long_position DECIMAL(15,4) DEFAULT 0,
    short_position DECIMAL(15,4) DEFAULT 0,
    net_position DECIMAL(15,4) DEFAULT 0,
    market_value DECIMAL(15,2) DEFAULT 0,
    currency VARCHAR(3) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (commodity_id) REFERENCES products(id),
    FOREIGN KEY (business_unit_id) REFERENCES business_units(id),
    UNIQUE KEY unique_position (position_date, commodity_id, business_unit_id),
    INDEX idx_position_date (position_date),
    INDEX idx_commodity_id (commodity_id),
    INDEX idx_business_unit_id (business_unit_id)
);

CREATE TABLE risk_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_type VARCHAR(50) NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    entity_type VARCHAR(50),
    entity_id INT,
    threshold_value DECIMAL(15,4),
    current_value DECIMAL(15,4),
    status ENUM('active', 'acknowledged', 'resolved', 'dismissed') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    acknowledged_at TIMESTAMP NULL,
    acknowledged_by INT NULL,
    FOREIGN KEY (acknowledged_by) REFERENCES users(id),
    INDEX idx_alert_type (alert_type),
    INDEX idx_severity (severity),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Reporting Tables
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    report_type VARCHAR(50) NOT NULL,
    format VARCHAR(20) NOT NULL,
    start_date DATE,
    end_date DATE,
    status ENUM('pending', 'generating', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    file_name VARCHAR(255),
    file_path VARCHAR(500),
    parameters JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id),
    
    INDEX idx_report_id (report_id),
    INDEX idx_user_id (user_id),
    INDEX idx_category (category),
    INDEX idx_report_type (report_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

CREATE TABLE report_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    frequency ENUM('daily', 'weekly', 'monthly', 'quarterly') NOT NULL,
    execution_time TIME NOT NULL,
    email_recipients TEXT NOT NULL,
    status ENUM('active', 'paused', 'cancelled') NOT NULL DEFAULT 'active',
    next_execution TIMESTAMP,
    last_execution TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id),
    
    INDEX idx_schedule_id (schedule_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_next_execution (next_execution),
    INDEX idx_created_at (created_at)
);

-- Dashboard & Configuration Tables
CREATE TABLE dashboard_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    layout JSON,
    refresh_interval INT DEFAULT 300,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_id (user_id)
); 