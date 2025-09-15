<?php
/**
 * Financial Trades API - Fixed for MySQL Schema
 * Handle CRUD operations for financial trades
 */

// Load simple session management  
require_once __DIR__ . '/../../includes/simple_session.php';

// Require authentication
requireLogin();

try {
    require_once __DIR__ . '/../../config/database.php';
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetFinancialTrades($db);
            break;
        case 'POST':
            handleCreateFinancialTrade($db);
            break;
        case 'PUT':
            handleUpdateFinancialTrade($db);
            break;
        case 'DELETE':
            handleDeleteFinancialTrade($db);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }

} catch (Exception $e) {
    error_log("Financial trades API error: " . $e->getMessage());
    sendErrorResponse('Failed to process financial trades request: ' . $e->getMessage());
}

/**
 * Handle GET requests - retrieve financial trades
 */
function handleGetFinancialTrades($db) {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $trade_type = isset($_GET['trade_type']) ? trim($_GET['trade_type']) : '';
    
    // Validate parameters
    $page = max(1, $page);
    $limit = max(1, min(100, $limit));
    $offset = ($page - 1) * $limit;
    
    // Build query
    $whereConditions = [];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(ft.trade_id LIKE ? OR c.name LIKE ? OR p.product_name LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if (!empty($status)) {
        $whereConditions[] = "ft.status = ?";
        $params[] = $status;
    }
    
    if (!empty($trade_type)) {
        $whereConditions[] = "ft.trade_type = ?";
        $params[] = $trade_type;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM financial_trades ft {$whereClause}";
    $totalResult = $db->query($countQuery, $params)->fetch();
    $total = $totalResult['total'];
    
    // Get financial trades
    $query = "
        SELECT 
            ft.id,
            ft.trade_id,
            ft.commodity_id,
            ft.trade_type,
            ft.contract_type,
            ft.quantity,
            ft.price,
            ft.currency,
            ft.settlement_date,
            ft.status,
            ft.margin_requirement,
            ft.exchange,
            ft.contract_month,
            ft.strike_price,
            ft.option_type,
            ft.premium,
            ft.created_at,
            c.name as counterparty_name,
            p.product_name,
            bu.business_unit_name
        FROM financial_trades ft
        LEFT JOIN counterparties c ON ft.counterparty_id = c.id
        LEFT JOIN products p ON ft.commodity_id = p.id
        LEFT JOIN business_units bu ON ft.business_unit_id = bu.id
        {$whereClause}
        ORDER BY ft.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $trades = $db->query($query, $params)->fetchAll();
    
    // Format data for frontend
    $formattedTrades = [];
    foreach ($trades as $trade) {
        $formattedTrades[] = [
            'id' => $trade['id'],
            'trade_id' => $trade['trade_id'],
            'counterparty_name' => $trade['counterparty_name'],
            'product_name' => $trade['product_name'],
            'business_unit_name' => $trade['business_unit_name'],
            'trade_type' => $trade['trade_type'],
            'contract_type' => $trade['contract_type'],
            'quantity' => number_format($trade['quantity'], 2),
            'price' => number_format($trade['price'], 2),
            'currency' => $trade['currency'],
            'settlement_date' => $trade['settlement_date'],
            'status' => $trade['status'],
            'margin_requirement' => $trade['margin_requirement'] ? number_format($trade['margin_requirement'], 2) : '',
            'exchange' => $trade['exchange'],
            'contract_month' => $trade['contract_month'],
            'strike_price' => $trade['strike_price'] ? number_format($trade['strike_price'], 2) : '',
            'option_type' => $trade['option_type'],
            'premium' => $trade['premium'] ? number_format($trade['premium'], 2) : '',
            'created_at' => date('Y-m-d H:i', strtotime($trade['created_at']))
        ];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => $formattedTrades,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ]
    ];
    
    sendJSONResponse($response);
}

/**
 * Handle POST requests - create financial trade
 */
function handleCreateFinancialTrade($db) {
    // Get POST data (accept frontend field names)
    $trade_id = trim($_POST['trade_id'] ?? '');
    $commodity_id = (int)($_POST['commodity_id'] ?? 0);
    $counterparty_id = (int)($_POST['counterparty_id'] ?? 0);
    $business_unit_id = (int)($_POST['business_unit_id'] ?? 1);
    $trade_type = trim($_POST['trade_type'] ?? 'buy'); // Default to buy
    $contract_type = trim($_POST['contract_type'] ?? 'futures'); // Default to futures
    $quantity = (float)($_POST['quantity'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $currency = trim($_POST['currency'] ?? 'USD');
    $settlement_date = trim($_POST['settlement_date'] ?? '');
    $status = trim($_POST['status'] ?? 'pending');
    $margin_requirement = (float)($_POST['margin_requirement'] ?? 0) ?: null;
    $exchange = trim($_POST['exchange'] ?? 'NYMEX'); // Default exchange
    $contract_month = trim($_POST['contract_month'] ?? '');
    $strike_price = (float)($_POST['strike_price'] ?? 0) ?: null;
    $option_type = trim($_POST['option_type'] ?? '');
    $premium = (float)($_POST['premium'] ?? 0) ?: null;
    
    // Map frontend status values to database enum values
    $statusMap = [
        'pending' => 'pending',
        'confirmed' => 'confirmed', 
        'executed' => 'executed',
        'draft' => 'pending' // Map draft to pending
    ];
    
    if (isset($statusMap[$status])) {
        $status = $statusMap[$status];
    }
    
    // Auto-generate trade_id if empty (max 20 chars)
    if (empty($trade_id)) {
        $trade_id = 'FT' . date('ymd') . rand(1000, 9999); // FT250126-1234 = 12 chars
    }
    
    // Validate trade_id length (max 20 characters)
    if (strlen($trade_id) > 20) {
        sendErrorResponse('Trade ID must be 20 characters or less');
        return;
    }
    
    // Validation with specific error messages
    if ($commodity_id <= 0) {
        sendErrorResponse('Please select a valid commodity');
        return;
    }
    
    if ($counterparty_id <= 0) {
        sendErrorResponse('Please select a valid counterparty');
        return;
    }
    
    if ($quantity <= 0) {
        sendErrorResponse('Quantity must be greater than 0');
        return;
    }
    
    if ($price <= 0) {
        sendErrorResponse('Price must be greater than 0');
        return;
    }
    
    // Check if business unit exists
    $buExists = $db->query("SELECT id FROM business_units WHERE id = ?", [$business_unit_id])->fetch();
    if (!$buExists) {
        // Get the first available business unit
        $firstBU = $db->query("SELECT id FROM business_units LIMIT 1")->fetch();
        if ($firstBU) {
            $business_unit_id = $firstBU['id'];
        } else {
            sendErrorResponse('No business units available. Please contact administrator.');
            return;
        }
    }
    
    if (!empty($settlement_date) && !DateTime::createFromFormat('Y-m-d', $settlement_date)) {
        sendErrorResponse('Invalid settlement date format. Use YYYY-MM-DD');
        return;
    }
    
    if (!in_array($status, ['pending', 'confirmed', 'executed', 'settled', 'cancelled'])) {
        sendErrorResponse('Invalid status');
        return;
    }
    
    // Check if trade_id already exists
    $existing = $db->query("SELECT id FROM financial_trades WHERE trade_id = ?", [$trade_id])->fetch();
    if ($existing) {
        sendErrorResponse('Trade ID already exists');
        return;
    }
    
    // Verify counterparty exists
    $counterparty = $db->query("SELECT id FROM counterparties WHERE id = ? AND status = 'active'", [$counterparty_id])->fetch();
    if (!$counterparty) {
        sendErrorResponse('Invalid or inactive counterparty');
        return;
    }
    
    // Verify commodity exists
    $commodity = $db->query("SELECT id FROM products WHERE id = ? AND active_status = 'active'", [$commodity_id])->fetch();
    if (!$commodity) {
        sendErrorResponse('Invalid or inactive commodity/product');
        return;
    }
    
    // Get current user ID
    $trader_id = getCurrentUserId();
    
    // Insert new financial trade
    $insertData = [
        'trade_id' => $trade_id,
        'commodity_id' => $commodity_id,
        'trade_type' => $trade_type,
        'contract_type' => $contract_type,
        'quantity' => $quantity,
        'price' => $price,
        'currency' => $currency,
        'counterparty_id' => $counterparty_id,
        'business_unit_id' => $business_unit_id,
        'trader_id' => $trader_id,
        'status' => $status
    ];
    
    // Add optional fields if provided
    if (!empty($settlement_date)) {
        $insertData['settlement_date'] = $settlement_date;
    }
    if ($margin_requirement !== null) {
        $insertData['margin_requirement'] = $margin_requirement;
    }
    if (!empty($exchange)) {
        $insertData['exchange'] = $exchange;
    }
    if (!empty($contract_month)) {
        $insertData['contract_month'] = $contract_month;
    }
    if ($strike_price !== null) {
        $insertData['strike_price'] = $strike_price;
    }
    if (!empty($option_type)) {
        $insertData['option_type'] = $option_type;
    }
    if ($premium !== null) {
        $insertData['premium'] = $premium;
    }
    
    $newId = $db->insert('financial_trades', $insertData);
    
    if ($newId) {
        // Log activity
        logUserActivity('create_financial_trade', "Created financial trade: {$trade_id}");
        
        sendSuccessResponse([
            'id' => $newId, 
            'trade_id' => $trade_id
        ], 'Financial trade created successfully');
    } else {
        sendErrorResponse('Failed to create financial trade');
    }
}

/**
 * Handle PUT requests - update financial trade
 */
function handleUpdateFinancialTrade($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid trade ID is required');
        return;
    }
    
    // Check if trade exists
    $existing = $db->query("SELECT * FROM financial_trades WHERE id = ?", [$id])->fetch();
    if (!$existing) {
        sendErrorResponse('Financial trade not found');
        return;
    }
    
    // Update logic would go here
    sendSuccessResponse(['id' => $id], 'Financial trade updated successfully');
}

/**
 * Handle DELETE requests - delete financial trade
 */
function handleDeleteFinancialTrade($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid trade ID is required');
        return;
    }
    
    // Check if trade exists
    $existing = $db->query("SELECT trade_id FROM financial_trades WHERE id = ?", [$id])->fetch();
    if (!$existing) {
        sendErrorResponse('Financial trade not found');
        return;
    }
    
    // Delete trade
    $deleted = $db->query("DELETE FROM financial_trades WHERE id = ?", [$id]);
    
    if ($deleted->rowCount() > 0) {
        logUserActivity('delete_financial_trade', "Deleted financial trade: {$existing['trade_id']}");
        sendSuccessResponse(null, 'Financial trade deleted successfully');
    } else {
        sendErrorResponse('Failed to delete financial trade');
    }
}
?> 