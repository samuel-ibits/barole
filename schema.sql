-- ETRM System Database Schema
-- Complete database structure for Energy Trading and Risk Management System

-- Create database
CREATE DATABASE IF NOT EXISTS etrm_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE etrm_system;

-- Users & Authentication Tables
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    department VARCHAR(50),
    role ENUM('admin', 'manager', 'trader', 'analyst', 'viewer') NOT NULL DEFAULT 'viewer',
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
);

CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_expires_at (expires_at)
);

CREATE TABLE user_activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Master Data Tables
CREATE TABLE counterparties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    credit_rating VARCHAR(10),
    exposure_limit DECIMAL(15,2),
    country VARCHAR(50),
    address TEXT,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    tax_id VARCHAR(50),
    registration_number VARCHAR(50),
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_name (name),
    INDEX idx_status (status)
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    unit_of_measure VARCHAR(20) NOT NULL,
    description TEXT,
    active_status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_category (category),
    INDEX idx_active_status (active_status)
);

CREATE TABLE business_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    business_unit_name VARCHAR(100) NOT NULL,
    parent_unit_id INT NULL,
    manager_id INT NULL,
    active_status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_unit_id) REFERENCES business_units(id) ON DELETE SET NULL,
    FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_parent_unit_id (parent_unit_id),
    INDEX idx_active_status (active_status)
);

CREATE TABLE brokers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    exchange VARCHAR(50),
    commission_rate DECIMAL(5,4),
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_name (name),
    INDEX idx_status (status)
);

CREATE TABLE ports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    port_name VARCHAR(100) NOT NULL,
    city VARCHAR(50),
    country VARCHAR(50),
    facilities TEXT,
    active_status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_port_name (port_name),
    INDEX idx_active_status (active_status)
);

CREATE TABLE carriers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50),
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_name (name),
    INDEX idx_status (status)
);

-- Trading Operations Tables
CREATE TABLE physical_sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trade_id VARCHAR(20) UNIQUE NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(15,4) NOT NULL,
    unit_of_measure VARCHAR(20) NOT NULL,
    price DECIMAL(15,4) NOT NULL,
    currency VARCHAR(3) NOT NULL,
    delivery_location VARCHAR(100),
    delivery_date DATE,
    counterparty_id INT NOT NULL,
    business_unit_id INT NOT NULL,
    trader_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'executed', 'settled', 'cancelled') NOT NULL DEFAULT 'pending',
    contract_terms TEXT,
    quality_specifications TEXT,
    incoterms VARCHAR(20),
    payment_terms TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (counterparty_id) REFERENCES counterparties(id),
    FOREIGN KEY (business_unit_id) REFERENCES business_units(id),
    FOREIGN KEY (trader_id) REFERENCES users(id),
    INDEX idx_trade_id (trade_id),
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
    exchange_rate DECIMAL(15,6) NOT NULL,
    settlement_date DATE,
    counterparty_id INT NOT NULL,
    business_unit_id INT NOT NULL,
    trader_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'executed', 'settled', 'cancelled') NOT NULL DEFAULT 'pending',
    trade_date DATE,
    value_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (counterparty_id) REFERENCES counterparties(id),
    FOREIGN KEY (business_unit_id) REFERENCES business_units(id),
    FOREIGN KEY (trader_id) REFERENCES users(id),
    INDEX idx_trade_id (trade_id),
    INDEX idx_base_currency (base_currency),
    INDEX idx_quote_currency (quote_currency),
    INDEX idx_counterparty_id (counterparty_id),
    INDEX idx_trader_id (trader_id),
    INDEX idx_status (status),
    INDEX idx_settlement_date (settlement_date)
);

-- Operations Tables
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    trade_id VARCHAR(20) NOT NULL,
    counterparty_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') NOT NULL DEFAULT 'draft',
    description TEXT,
    line_items JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (counterparty_id) REFERENCES counterparties(id),
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_trade_id (trade_id),
    INDEX idx_counterparty_id (counterparty_id),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
);

CREATE TABLE logistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    logistics_id VARCHAR(20) UNIQUE NOT NULL,
    trade_id VARCHAR(20) NOT NULL,
    carrier_id INT NOT NULL,
    shipping_method VARCHAR(50),
    origin VARCHAR(100),
    destination VARCHAR(100),
    departure_date DATE,
    arrival_date DATE,
    status ENUM('pending', 'in_transit', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    tracking_number VARCHAR(100),
    documents JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (carrier_id) REFERENCES carriers(id),
    INDEX idx_logistics_id (logistics_id),
    INDEX idx_trade_id (trade_id),
    INDEX idx_carrier_id (carrier_id),
    INDEX idx_status (status),
    INDEX idx_departure_date (departure_date)
);

CREATE TABLE settlements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    settlement_id VARCHAR(20) UNIQUE NOT NULL,
    trade_id VARCHAR(20) NOT NULL,
    counterparty_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) NOT NULL,
    settlement_date DATE NOT NULL,
    status ENUM('pending', 'processed', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(50),
    reference_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (counterparty_id) REFERENCES counterparties(id),
    INDEX idx_settlement_id (settlement_id),
    INDEX idx_trade_id (trade_id),
    INDEX idx_counterparty_id (counterparty_id),
    INDEX idx_status (status),
    INDEX idx_settlement_date (settlement_date)
);

-- Risk & Analytics Tables
CREATE TABLE portfolio_positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(15,4) NOT NULL,
    average_price DECIMAL(15,4) NOT NULL,
    current_price DECIMAL(15,4) NOT NULL,
    pnl DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_user_id (user_id),
    INDEX idx_product_id (product_id),
    INDEX idx_created_at (created_at)
);

CREATE TABLE risk_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_type VARCHAR(50) NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    message TEXT NOT NULL,
    related_entity_type VARCHAR(50),
    related_entity_id INT,
    status ENUM('active', 'acknowledged', 'resolved') NOT NULL DEFAULT 'active',
    acknowledged_by INT,
    acknowledged_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (acknowledged_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_alert_type (alert_type),
    INDEX idx_severity (severity),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

CREATE TABLE portfolio_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    total_exposure DECIMAL(15,2) NOT NULL,
    var_95 DECIMAL(15,2),
    var_99 DECIMAL(15,2),
    pnl DECIMAL(15,2) NOT NULL,
    margin_utilization DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date (date),
    INDEX idx_created_at (created_at)
);

CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_type VARCHAR(50) NOT NULL,
    parameters JSON,
    generated_by INT NOT NULL,
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES users(id),
    INDEX idx_report_type (report_type),
    INDEX idx_generated_by (generated_by),
    INDEX idx_created_at (created_at)
);

-- Dashboard & Configuration Tables
CREATE TABLE dashboard_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    layout JSON,
    refresh_interval INT DEFAULT 300,
    widgets_config JSON,
    user_preferences JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_config (user_id),
    INDEX idx_user_id (user_id)
);

CREATE TABLE dashboard_widgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    widget_type VARCHAR(50) NOT NULL,
    position INT NOT NULL,
    config JSON,
    enabled BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_widget_type (widget_type),
    INDEX idx_enabled (enabled)
);

-- System Tables
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_table_name (table_name),
    INDEX idx_created_at (created_at)
);

CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
);

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('system_name', 'ETRM System', 'System display name'),
('system_version', '2.0.0', 'System version'),
('maintenance_mode', 'false', 'System maintenance mode'),
('default_page_size', '25', 'Default pagination size'),
('max_file_size', '10485760', 'Maximum file upload size in bytes'),
('session_timeout', '3600', 'Session timeout in seconds'),
('password_min_length', '8', 'Minimum password length'),
('login_max_attempts', '5', 'Maximum login attempts before lockout'),
('login_lockout_time', '900', 'Login lockout time in seconds');

-- Create default admin user (password: admin123)
INSERT INTO users (username, password_hash, email, full_name, role, status) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@etrm.com', 'System Administrator', 'admin', 'active');

-- Create indexes for better performance
CREATE INDEX idx_trades_status_date ON physical_sales(status, created_at);
CREATE INDEX idx_trades_status_date ON financial_trades(status, created_at);
CREATE INDEX idx_trades_status_date ON fx_trades(status, created_at);
CREATE INDEX idx_invoices_status_date ON invoices(status, due_date);
CREATE INDEX idx_logistics_status_date ON logistics(status, departure_date);
CREATE INDEX idx_settlements_status_date ON settlements(status, settlement_date); 