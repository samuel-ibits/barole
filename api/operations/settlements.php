<?php
/**
 * Settlements API
 * Handle CRUD operations for settlements
 */

// Load simple session management  
require_once __DIR__ . '/../../includes/simple_session.php';

// Load database configuration
require_once __DIR__ . '/../../config/database.php';

// Require authentication
requireLogin();

try {
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetSettlements($db);
            break;
        case 'POST':
            handleCreateSettlement($db);
            break;
        case 'PUT':
            handleUpdateSettlement($db);
            break;
        case 'DELETE':
            handleDeleteSettlement($db);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }

} catch (Exception $e) {
    error_log("Settlements API error: " . $e->getMessage());
    sendErrorResponse('Failed to process settlements request: ' . $e->getMessage());
}

/**
 * Handle GET requests - retrieve settlements
 */
function handleGetSettlements($db) {
    
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
        $whereConditions[] = "status = ?";
        $params[] = $status;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM settlements {$whereClause}";
    $totalResult = $db->query($countQuery, $params)->fetch();
    $total = $totalResult['total'];
    
    // Get settlements
    $query = "
        SELECT 
            s.*,
            c.name as counterparty_name
        FROM settlements s
        LEFT JOIN counterparties c ON s.counterparty_id = c.id
        {$whereClause}
        ORDER BY s.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $settlements = $db->query($query, $params)->fetchAll();
    
    // Format data for frontend
    $formattedSettlements = [];
    foreach ($settlements as $settlement) {
        $formattedSettlements[] = [
            'id' => $settlement['id'],
            'settlement_id' => $settlement['settlement_id'],
            'counterparty_name' => $settlement['counterparty_name'],
            'amount' => number_format($settlement['amount'], 2),
            'currency' => $settlement['currency'],
            'settlement_date' => $settlement['settlement_date'],
            'payment_method' => $settlement['payment_method'],
            'status' => $settlement['status'],
            'created_at' => date('Y-m-d H:i', strtotime($settlement['created_at']))
        ];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => $formattedSettlements,
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
 * Handle POST requests - create new settlement
 */
function handleCreateSettlement($db) {
    // Get POST data (accept frontend field names)
    $settlement_id = trim($_POST['settlement_id'] ?? '');
    $trade_id = trim($_POST['trade_id'] ?? '');
    $counterparty_id = (int)($_POST['counterparty_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $currency = trim($_POST['currency'] ?? 'USD');
    $settlement_date = trim($_POST['settlement_date'] ?? date('Y-m-d'));
    $status = trim($_POST['status'] ?? 'pending');
    $payment_method = trim($_POST['payment_method'] ?? 'wire_transfer');
    $reference_number = trim($_POST['reference_number'] ?? '');
    $business_unit_id = (int)($_POST['business_unit_id'] ?? 1);
    
    // Auto-generate settlement_id if empty (max 20 chars)
    if (empty($settlement_id)) {
        $settlement_id = 'SET' . date('ymd') . rand(1000, 9999);
    }
    
    // Validate settlement_id length (max 20 characters)
    if (strlen($settlement_id) > 20) {
        sendErrorResponse('Settlement ID must be 20 characters or less');
        return;
    }
    
    // Validation with specific error messages
    if ($counterparty_id <= 0) {
        sendErrorResponse('Please select a valid counterparty');
        return;
    }
    
    if ($amount <= 0) {
        sendErrorResponse('Amount must be greater than 0');
        return;
    }
    
    // Validate counterparty ID
    if ($counterparty_id <= 0) {
        $errors[] = 'Valid counterparty ID is required';
    }
    
    // Check if settlement_id already exists
    $existing = $db->query("SELECT id FROM settlements WHERE settlement_id = ?", [$settlement_id])->fetch();
    if ($existing) {
        sendErrorResponse('Settlement ID already exists');
        return;
    }
    
    try {
        // Insert new settlement record
        $insertData = [
            'settlement_id' => $settlement_id,
            'trade_id' => $trade_id,
            'counterparty_id' => $counterparty_id,
            'amount' => $amount,
            'currency' => $currency,
            'settlement_date' => $settlement_date,
            'status' => $status,
            'payment_method' => $payment_method,
            'business_unit_id' => $business_unit_id
        ];
        
        // Add optional fields if provided
        if (!empty($reference_number)) {
            $insertData['reference_number'] = $reference_number;
        }
        
        // Build manual insert query to avoid method compatibility issues
        $columns = array_keys($insertData);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $columnList = implode(', ', $columns);
        $values = array_values($insertData);
        
        $insertQuery = "INSERT INTO settlements ({$columnList}) VALUES ({$placeholders})";
        $result = $db->query($insertQuery, $values);
        
        if ($result) {
            $newId = $db->getConnection()->lastInsertId();
            
            logUserActivity('create_settlement', "Created settlement: {$settlement_id}");
            sendSuccessResponse([
                'id' => $newId, 
                'settlement_id' => $settlement_id
            ], 'Settlement created successfully');
        } else {
            sendErrorResponse('Failed to create settlement');
        }
        
    } catch (Exception $e) {
        error_log("Create settlement error: " . $e->getMessage());
        sendErrorResponse('Failed to create settlement: ' . $e->getMessage());
    }
}

/**
 * Handle PUT requests - update existing settlement
 */
function handleUpdateSettlement($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid settlement ID is required', 400);
        return;
    }
    
    // Check if settlement exists
    $existingSettlement = $db->fetchOne("SELECT * FROM settlements WHERE id = ?", [$id]);
    if (!$existingSettlement) {
        sendErrorResponse('Settlement not found', 404);
        return;
    }
    
    // Prevent updates to completed or failed settlements
    if (in_array($existingSettlement['status'], ['completed', 'failed'])) {
        sendErrorResponse('Cannot update completed or failed settlements', 400);
        return;
    }
    
    // Build update query with only provided fields
    $updateFields = [];
    $params = [];
    
    $allowedFields = [
        'trade_id', 'counterparty_id', 'amount', 'currency', 'settlement_date',
        'status', 'payment_method', 'reference_number'
    ];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $input[$field];
        }
    }
    
    if (empty($updateFields)) {
        sendErrorResponse('No fields to update', 400);
        return;
    }
    
    // Add updated_at timestamp
    $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
    $params[] = $id;
    
    try {
        $query = "UPDATE settlements SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $result = $db->execute($query, $params);
        
        if ($result) {
            sendJSONResponse([
                'success' => true,
                'message' => 'Settlement updated successfully'
            ]);
        } else {
            sendErrorResponse('Failed to update settlement');
        }
        
    } catch (Exception $e) {
        error_log("Update settlement error: " . $e->getMessage());
        sendErrorResponse('Database error occurred');
    }
}

/**
 * Handle DELETE requests - delete settlement
 */
function handleDeleteSettlement($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid settlement ID is required', 400);
        return;
    }
    
    try {
        // Check if settlement exists
        $existingSettlement = $db->fetchOne("SELECT id, status FROM settlements WHERE id = ?", [$id]);
        if (!$existingSettlement) {
            sendErrorResponse('Settlement not found', 404);
            return;
        }
        
        // Prevent deletion of completed settlements
        if ($existingSettlement['status'] === 'completed') {
            sendErrorResponse('Cannot delete completed settlements', 400);
            return;
        }
        
        $result = $db->execute("DELETE FROM settlements WHERE id = ?", [$id]);
        
        if ($result) {
            sendJSONResponse([
                'success' => true,
                'message' => 'Settlement deleted successfully'
            ]);
        } else {
            sendErrorResponse('Failed to delete settlement');
        }
        
    } catch (Exception $e) {
        error_log("Delete settlement error: " . $e->getMessage());
        sendErrorResponse('Database error occurred');
    }
}
?> 