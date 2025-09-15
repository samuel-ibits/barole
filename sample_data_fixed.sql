-- Sample Data for ETRM System (Fixed for actual schema)
-- Insert sample data for testing and demonstration

-- Sample Counterparties
INSERT INTO counterparties (code, name, credit_rating, exposure_limit, country, contact_person, email, phone, status) VALUES
('CP001', 'ABC Energy Corp', 'A+', 1000000.00, 'USA', 'John Smith', 'john.smith@abcenergy.com', '+1-555-0101', 'active'),
('CP002', 'XYZ Trading Ltd', 'A', 750000.00, 'UK', 'Sarah Johnson', 'sarah.j@xyztrading.co.uk', '+44-20-1234-5678', 'active'),
('CP003', 'Global Petro Inc', 'BBB+', 500000.00, 'Canada', 'Mike Wilson', 'mike.wilson@globalpetro.ca', '+1-416-555-0123', 'active'),
('CP004', 'Euro Gas Solutions', 'A-', 800000.00, 'Germany', 'Hans Mueller', 'h.mueller@eurogas.de', '+49-30-1234-5678', 'active'),
('CP005', 'Asia Energy Partners', 'BBB', 600000.00, 'Singapore', 'Li Wei', 'li.wei@asiaenergy.sg', '+65-6123-4567', 'active');

-- Sample Products
INSERT INTO products (code, product_name, category, unit_of_measure, description, active_status) VALUES
('PROD001', 'Crude Oil WTI', 'Crude Oil', 'BBL', 'West Texas Intermediate Crude Oil', 'active'),
('PROD002', 'Natural Gas Henry Hub', 'Natural Gas', 'MMBtu', 'Henry Hub Natural Gas', 'active'),
('PROD003', 'Gasoline RBOB', 'Refined Products', 'GAL', 'Reformulated Gasoline Blendstock', 'active'),
('PROD004', 'Diesel ULSD', 'Refined Products', 'GAL', 'Ultra Low Sulfur Diesel', 'active'),
('PROD005', 'Jet Fuel', 'Refined Products', 'GAL', 'Jet A-1 Aviation Fuel', 'active');

-- Sample Business Units
INSERT INTO business_units (code, business_unit_name, active_status) VALUES
('BU001', 'North America Trading', 'active'),
('BU002', 'Europe Trading', 'active'),
('BU003', 'Asia Pacific Trading', 'active'),
('BU004', 'Risk Management', 'active'),
('BU005', 'Operations', 'active');

-- Sample Physical Sales (with all required columns)
INSERT INTO physical_sales (trade_id, product_id, quantity, unit_of_measure, price, currency, delivery_location, delivery_date, counterparty_id, business_unit_id, trader_id, status) VALUES
('PS2024001', 1, 10000.00, 'BBL', 75.50, 'USD', 'Houston, TX', '2024-02-15', 1, 1, 1, 'confirmed'),
('PS2024002', 2, 50000.00, 'MMBtu', 3.25, 'USD', 'London, UK', '2024-02-20', 2, 2, 1, 'executed'),
('PS2024003', 3, 25000.00, 'GAL', 2.85, 'USD', 'New York, NY', '2024-02-25', 3, 1, 1, 'pending'),
('PS2024004', 4, 15000.00, 'GAL', 3.10, 'USD', 'Rotterdam, NL', '2024-03-01', 4, 2, 1, 'confirmed'),
('PS2024005', 5, 8000.00, 'GAL', 2.95, 'USD', 'Singapore', '2024-03-05', 5, 3, 1, 'executed');

-- Sample Invoices (with correct schema)
INSERT INTO invoices (invoice_number, trade_id, counterparty_id, amount, currency, invoice_date, due_date, status) VALUES
('INV2024001', 'PS2024001', 1, 755000.00, 'USD', '2024-02-15', '2024-03-15', 'sent'),
('INV2024002', 'PS2024002', 2, 162500.00, 'USD', '2024-02-20', '2024-03-20', 'paid'),
('INV2024003', 'PS2024003', 3, 71250.00, 'USD', '2024-02-25', '2024-03-25', 'sent'),
('INV2024004', 'PS2024004', 4, 46500.00, 'USD', '2024-03-01', '2024-04-01', 'overdue'),
('INV2024005', 'PS2024005', 5, 23600.00, 'USD', '2024-03-05', '2024-04-05', 'sent');

-- Add more portfolio positions for testing
INSERT INTO portfolio_positions (user_id, product_id, quantity, average_price, current_price, pnl) VALUES
(1, 1, 10000.00, 75.50, 76.20, 7000.00),
(1, 2, 50000.00, 3.25, 3.30, 2500.00),
(1, 3, 25000.00, 2.85, 2.90, 1250.00),
(1, 4, 15000.00, 3.10, 3.15, 750.00),
(1, 5, 8000.00, 2.95, 3.00, 400.00);

-- Sample Financial Trades
INSERT INTO financial_trades (trade_id, commodity_id, trade_type, contract_type, quantity, price, currency, settlement_date, counterparty_id, business_unit_id, trader_id, status, exchange) VALUES
('FT2024001', 1, 'buy', 'futures', 1000.00, 76.20, 'USD', '2024-03-15', 1, 1, 1, 'confirmed', 'NYMEX'),
('FT2024002', 2, 'sell', 'swaps', 5000.00, 3.30, 'USD', '2024-03-20', 2, 2, 1, 'executed', 'ICE'),
('FT2024003', 3, 'buy', 'options', 2000.00, 2.90, 'USD', '2024-03-25', 3, 1, 1, 'pending', 'CME'),
('FT2024004', 4, 'sell', 'forwards', 1500.00, 3.15, 'USD', '2024-04-01', 4, 2, 1, 'confirmed', 'ICE'),
('FT2024005', 5, 'buy', 'futures', 800.00, 3.00, 'USD', '2024-04-05', 5, 3, 1, 'executed', 'NYMEX');

-- Sample FX Trades  
INSERT INTO fx_trades (trade_id, base_currency, quote_currency, base_amount, quote_amount, exchange_rate, trade_type, settlement_date, counterparty_id, business_unit_id, trader_id, status) VALUES
('FX2024001', 'USD', 'EUR', 100000.00, 92000.00, 0.9200, 'spot', '2024-02-16', 1, 1, 1, 'executed'),
('FX2024002', 'EUR', 'GBP', 75000.00, 65000.00, 0.8667, 'forward', '2024-03-15', 2, 2, 1, 'confirmed'),
('FX2024003', 'USD', 'CAD', 50000.00, 67500.00, 1.3500, 'spot', '2024-02-18', 3, 1, 1, 'executed'),
('FX2024004', 'EUR', 'USD', 60000.00, 66000.00, 1.1000, 'forward', '2024-03-20', 4, 2, 1, 'confirmed'),
('FX2024005', 'USD', 'SGD', 80000.00, 108000.00, 1.3500, 'spot', '2024-02-19', 5, 3, 1, 'executed');

-- Sample Logistics
INSERT INTO logistics (logistics_id, departure_port, arrival_port, carrier_name, vessel_name, departure_date, arrival_date, cargo_description, quantity, status) VALUES
('LOG2024001', 'Houston', 'Rotterdam', 'Global Shipping Co', 'Energy Trader I', '2024-02-15', '2024-02-28', 'Crude Oil', 100000.00, 'in_transit'),
('LOG2024002', 'Singapore', 'Tokyo', 'Asia Maritime', 'Pacific Star', '2024-02-20', '2024-02-25', 'LNG', 75000.00, 'delivered'),
('LOG2024003', 'London', 'New York', 'Atlantic Lines', 'Ocean Pioneer', '2024-02-25', '2024-03-10', 'Refined Products', 50000.00, 'pending'),
('LOG2024004', 'Dubai', 'Mumbai', 'Middle East Shipping', 'Desert Wind', '2024-03-01', '2024-03-05', 'Natural Gas', 60000.00, 'in_transit'),
('LOG2024005', 'Lagos', 'Barcelona', 'African Express', 'Sahara Queen', '2024-03-05', '2024-03-20', 'Crude Oil', 80000.00, 'pending');

-- Sample Settlements
INSERT INTO settlements (settlement_id, trade_id, counterparty_id, amount, currency, settlement_date, settlement_type, status, reference_number) VALUES
('SET2024001', 'PS2024001', 1, 755000.00, 'USD', '2024-03-15', 'wire_transfer', 'completed', 'WT20240001'),
('SET2024002', 'PS2024002', 2, 162500.00, 'USD', '2024-03-20', 'letter_of_credit', 'completed', 'LC20240002'),
('SET2024003', 'FT2024001', 3, 76200.00, 'USD', '2024-03-25', 'wire_transfer', 'pending', 'WT20240003'),
('SET2024004', 'FX2024001', 4, 100000.00, 'USD', '2024-04-01', 'wire_transfer', 'failed', 'WT20240004'),
('SET2024005', 'PS2024005', 5, 23600.00, 'USD', '2024-04-05', 'check', 'pending', 'CK20240005');

-- Sample Risk Alerts (simplified for actual schema)
INSERT INTO risk_alerts (alert_type, severity, message, status) VALUES
('exposure_limit', 'high', 'Counterparty ABC Energy Corp approaching exposure limit', 'active'),
('credit_rating', 'medium', 'Counterparty XYZ Trading Ltd credit rating downgrade', 'active'),
('settlement_failure', 'critical', 'Settlement failed for Global Petro Inc', 'active'),
('price_volatility', 'medium', 'High price volatility detected in Natural Gas', 'active'),
('delivery_delay', 'low', 'Logistics delay for Asia Energy Partners', 'resolved'); 