<?php
/**
 * Physical Sales API - Fixed for MySQL Schema
 * Get physical sales data for trading module
 */

// Load simple session management  
require_once __DIR__ . '/../../includes/simple_session.php';

// Require authentication
requireLogin();

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

try {
    require_once __DIR__ . '/../../config/database.php';
    $db = getDB();

    if ($method === 'POST') {
        // Handle create new physical sale
        handleCreatePhysicalSale($db);
        return;
    }
    
    // Get parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    
    // Validate parameters
    $page = max(1, $page);
    $limit = max(1, min(100, $limit));
    $offset = ($page - 1) * $limit;
    
    // Build query
    $whereConditions = [];
    $params = [];
    
    if (!empty($status)) {
        $whereConditions[] = "ps.status = ?";
        $params[] = $status;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM physical_sales ps {$whereClause}";
    $totalResult = $db->query($countQuery, $params)->fetch();
    $total = $totalResult['total'];
    
    // Get physical sales with correct column names
    $query = "
        SELECT 
            ps.id,
            ps.sale_id,
            ps.quantity,
            ps.price,
            ps.currency,
            ps.delivery_date,
            ps.status,
            ps.notes,
            ps.created_at,
            c.name as counterparty_name,
            p.product_name,
            bu.business_unit_name,
            lp.name as loading_port_name,
            dp.name as discharge_port_name
        FROM physical_sales ps
        LEFT JOIN counterparties c ON ps.counterparty_id = c.id
        LEFT JOIN products p ON ps.product_id = p.id
        LEFT JOIN business_units bu ON ps.business_unit_id = bu.id
        LEFT JOIN ports lp ON ps.loading_port_id = lp.id
        LEFT JOIN ports dp ON ps.discharge_port_id = dp.id
        {$whereClause}
        ORDER BY ps.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $sales = $db->query($query, $params)->fetchAll();
    
    // Format data for frontend
    $formattedSales = [];
    foreach ($sales as $sale) {
        $formattedSales[] = [
            'id' => $sale['id'],
            'sale_id' => $sale['sale_id'],
            'trade_id' => $sale['sale_id'], // Frontend expects trade_id
            'counterparty_name' => $sale['counterparty_name'],
            'counterparty' => $sale['counterparty_name'], // Alternative field name
            'product_name' => $sale['product_name'],
            'product' => $sale['product_name'], // Alternative field name
            'quantity' => number_format($sale['quantity'], 2),
            'price' => '$' . number_format($sale['price'], 2), // Add currency symbol
            'currency' => $sale['currency'],
            'delivery_date' => $sale['delivery_date'],
            'date' => $sale['delivery_date'], // Frontend expects date
            'trade_date' => $sale['delivery_date'], // Frontend table expects trade_date
            'loading_port' => $sale['loading_port_name'],
            'discharge_port' => $sale['discharge_port_name'],
            'status' => $sale['status'],
            'notes' => $sale['notes'],
            'created_at' => date('Y-m-d H:i', strtotime($sale['created_at']))
        ];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => $formattedSales,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ]
    ];
    
    sendJSONResponse($response);
    
} catch (Exception $e) {
    error_log("Physical sales API error: " . $e->getMessage());
    sendErrorResponse('Failed to load physical sales data');
}

function handleCreatePhysicalSale($db) {
    try {
        // Get POST data with correct field names (accept both trade_id and sale_id)
        $sale_id = $_POST['sale_id'] ?? $_POST['trade_id'] ?? '';
        $counterparty_id = (int)($_POST['counterparty_id'] ?? 0);
        $product_id = (int)($_POST['product_id'] ?? 0);
        $business_unit_id = (int)($_POST['business_unit_id'] ?? 1); // Default to business unit 1
        $quantity = (float)($_POST['quantity'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $currency = $_POST['currency'] ?? 'USD';
        $delivery_date = $_POST['delivery_date'] ?? '';
        $loading_port_id = (int)($_POST['loading_port_id'] ?? 0) ?: null;
        $discharge_port_id = (int)($_POST['discharge_port_id'] ?? 0) ?: null;
        $status = $_POST['status'] ?? 'draft';
        $notes = $_POST['notes'] ?? '';
        
        // Map frontend status values to database enum values
        $statusMap = [
            'pending' => 'draft',
            'confirmed' => 'confirmed', 
            'executed' => 'shipped'
        ];
        
        if (isset($statusMap[$status])) {
            $status = $statusMap[$status];
        }
        
        // Auto-generate sale_id if empty (max 20 chars)
        if (empty($sale_id)) {
            $sale_id = 'PS' . date('ymd') . rand(1000, 9999); // PS250126-1234 = 12 chars
        }
        
        // Validate sale_id length (max 20 characters)
        if (strlen($sale_id) > 20) {
            sendErrorResponse('Sale ID must be 20 characters or less');
            return;
        }
        
        // Validation with specific error messages
        if ($counterparty_id <= 0) {
            sendErrorResponse('Please select a valid counterparty');
            return;
        }
        if ($product_id <= 0) {
            sendErrorResponse('Please select a valid product');
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
        
        // Validate delivery date
        if (!empty($delivery_date) && !DateTime::createFromFormat('Y-m-d', $delivery_date)) {
            sendErrorResponse('Invalid delivery date format. Use YYYY-MM-DD');
            return;
        }
        
        // Check if sale_id already exists
        $existing = $db->query("SELECT id FROM physical_sales WHERE sale_id = ?", [$sale_id])->fetch();
        if ($existing) {
            sendErrorResponse('Sale ID already exists');
            return;
        }
        
        // Get current user ID
        $trader_id = getCurrentUserId();
        
        // Insert new physical sale with correct column names
        $insertData = [
            'sale_id' => $sale_id,
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price' => $price,
            'currency' => $currency,
            'delivery_date' => $delivery_date,
            'counterparty_id' => $counterparty_id,
            'business_unit_id' => $business_unit_id,
            'trader_id' => $trader_id,
            'status' => $status
        ];
        
        // Add optional fields if provided
        if ($loading_port_id) {
            $insertData['loading_port_id'] = $loading_port_id;
        }
        if ($discharge_port_id) {
            $insertData['discharge_port_id'] = $discharge_port_id;
        }
        if (!empty($notes)) {
            $insertData['notes'] = $notes;
        }
        
        $newId = $db->insert('physical_sales', $insertData);
        
        if ($newId) {
            // Log activity
            logUserActivity('create_physical_sale', "Created physical sale: {$sale_id}");
            
            sendSuccessResponse([
                'id' => $newId, 
                'sale_id' => $sale_id
            ], 'Physical sale created successfully');
        } else {
            sendErrorResponse('Failed to create physical sale');
        }
        
    } catch (Exception $e) {
        error_log("Create physical sale error: " . $e->getMessage());
        sendErrorResponse('Failed to create physical sale: ' . $e->getMessage());
    }
}
?> 