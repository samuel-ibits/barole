<?php
/**
 * Logistics API
 * Handle CRUD operations for logistics
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
            handleGetLogistics($db);
            break;
        case 'POST':
            handleCreateLogistics($db);
            break;
        case 'PUT':
            handleUpdateLogistics($db);
            break;
        case 'DELETE':
            handleDeleteLogistics($db);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }

} catch (Exception $e) {
    error_log("Logistics API error: " . $e->getMessage());
    sendErrorResponse('Failed to process logistics request: ' . $e->getMessage());
}

/**
 * Handle GET requests - retrieve logistics
 */
function handleGetLogistics($db) {
    
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
    $countQuery = "SELECT COUNT(*) as total FROM logistics {$whereClause}";
    $totalResult = $db->query($countQuery, $params)->fetch();
    $total = $totalResult['total'];
    
    // Get logistics
    $query = "
        SELECT 
            l.*,
            c.name as carrier_name
        FROM logistics l
        LEFT JOIN carriers c ON l.carrier_id = c.id
        {$whereClause}
        ORDER BY l.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $logistics = $db->query($query, $params)->fetchAll();
    
    // Format data for frontend
    $formattedLogistics = [];
    foreach ($logistics as $log) {
        $formattedLogistics[] = [
            'id' => $log['id'],
            'logistics_id' => $log['logistics_id'],
            'carrier_name' => $log['carrier_name'],
            'origin' => $log['origin'],
            'destination' => $log['destination'],
            'shipping_method' => $log['shipping_method'],
            'departure_date' => $log['departure_date'],
            'arrival_date' => $log['arrival_date'],
            'status' => $log['status'],
            'created_at' => date('Y-m-d H:i', strtotime($log['created_at']))
        ];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => $formattedLogistics,
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
 * Handle POST requests - create new logistics entry
 */
function handleCreateLogistics($db) {
    // Get POST data (accept frontend field names)
    $shipment_id = trim($_POST['shipment_id'] ?? $_POST['logistics_id'] ?? '');
    $trade_id = trim($_POST['trade_id'] ?? '');
    $counterparty_id = (int)($_POST['counterparty_id'] ?? 0); // Optional for logistics
    $carrier_id = (int)($_POST['carrier_id'] ?? 1); // Default to first carrier
    $origin_port_id = (int)($_POST['origin_port_id'] ?? $_POST['loading_port_id'] ?? 0);
    $destination_port_id = (int)($_POST['destination_port_id'] ?? $_POST['discharge_port_id'] ?? 0);
    $departure_date = trim($_POST['departure_date'] ?? '');
    $arrival_date = trim($_POST['arrival_date'] ?? $_POST['expected_arrival_date'] ?? '');
    $status = trim($_POST['status'] ?? 'pending');
    $quantity = (float)($_POST['quantity'] ?? 0);
    $business_unit_id = (int)($_POST['business_unit_id'] ?? 1);
    $shipping_method = trim($_POST['shipping_method'] ?? '');
    $origin = trim($_POST['origin'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $tracking_number = trim($_POST['tracking_number'] ?? '');
    
    // Map frontend status values to database enum values (matching schema)
    $statusMap = [
        'pending' => 'pending',
        'confirmed' => 'in_transit', 
        'executed' => 'delivered',
        'draft' => 'pending',
        'cancelled' => 'cancelled'
    ];
    
    if (isset($statusMap[$status])) {
        $status = $statusMap[$status];
    }
    
    // Auto-generate shipment_id if empty (max 20 chars)
    if (empty($shipment_id)) {
        $shipment_id = 'LOG' . date('ymd') . rand(1000, 9999); // LOG250126-1234 = 13 chars
    }
    
    // Validate logistics_id length (max 20 characters)
    if (strlen($shipment_id) > 20) {
        sendErrorResponse('Logistics ID must be 20 characters or less');
        return;
    }
    
    // Validation with specific error messages
    if ($carrier_id <= 0) {
        sendErrorResponse('Please select a valid carrier');
        return;
    }
    
    // Ensure carrier exists or use first available
    $carrierExists = $db->query("SELECT id FROM carriers WHERE id = ? AND status = 'active'", [$carrier_id])->fetch();
    if (!$carrierExists) {
        // Get the first available carrier
        $firstCarrier = $db->query("SELECT id FROM carriers WHERE status = 'active' LIMIT 1")->fetch();
        if ($firstCarrier) {
            $carrier_id = $firstCarrier['id'];
        } else {
            sendErrorResponse('No active carriers available. Please add carriers in Master Data section.');
            return;
        }
    }
    
    // Check if logistics_id already exists
    $existing = $db->query("SELECT id FROM logistics WHERE logistics_id = ?", [$shipment_id])->fetch();
    if ($existing) {
        sendErrorResponse('Logistics ID already exists');
        return;
    }
    
    try {
        // Insert new logistics record (matching database schema)
        $insertData = [
            'logistics_id' => $shipment_id,
            'trade_id' => $trade_id ?: '',  // NOT NULL in schema, use empty string if not provided
            'carrier_id' => $carrier_id,
            'shipping_method' => $shipping_method ?: null,
            'origin' => $origin ?: null,
            'destination' => $destination ?: null,
            'departure_date' => $departure_date ?: null,
            'arrival_date' => $arrival_date ?: null,
            'status' => $status,
            'tracking_number' => $tracking_number ?: null,
            'documents' => null  // JSON field for future use
        ];
        
        // Build manual insert query to avoid method compatibility issues
        $columns = array_keys($insertData);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $columnList = implode(', ', $columns);
        $values = array_values($insertData);
        
        $insertQuery = "INSERT INTO logistics ({$columnList}) VALUES ({$placeholders})";
        $result = $db->query($insertQuery, $values);
        
        if ($result) {
            $newId = $db->getConnection()->lastInsertId();
            
            logUserActivity('create_logistics', "Created logistics: {$shipment_id}");
            sendSuccessResponse([
                'id' => $newId, 
                'logistics_id' => $shipment_id
            ], 'Logistics record created successfully');
        } else {
            sendErrorResponse('Failed to create logistics record');
        }
        
    } catch (Exception $e) {
        error_log("Create logistics error: " . $e->getMessage());
        sendErrorResponse('Failed to create logistics record: ' . $e->getMessage());
    }
}

/**
 * Handle PUT requests - update existing logistics entry
 */
function handleUpdateLogistics($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid logistics ID is required', 400);
        return;
    }
    
    // Check if logistics entry exists
    $existingLogistics = $db->query("SELECT * FROM logistics WHERE id = ?", [$id])->fetch();
    if (!$existingLogistics) {
        sendErrorResponse('Logistics entry not found', 404);
        return;
    }
    
    // Build update query with only provided fields
    $updateFields = [];
    $params = [];
    
    $allowedFields = [
        'trade_id', 'carrier_id', 'shipping_method', 'origin', 'destination',
        'departure_date', 'arrival_date', 'status', 'tracking_number', 'documents'
    ];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            if ($field === 'documents') {
                $updateFields[] = "$field = ?";
                $params[] = !empty($input[$field]) ? json_encode($input[$field]) : null;
            } else {
                $updateFields[] = "$field = ?";
                $params[] = $input[$field];
            }
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
        $query = "UPDATE logistics SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $result = $db->query($query, $params);
        
        if ($result) {
            sendSuccessResponse([], 'Logistics entry updated successfully');
        } else {
            sendErrorResponse('Failed to update logistics entry');
        }
        
    } catch (Exception $e) {
        error_log("Update logistics error: " . $e->getMessage());
        sendErrorResponse('Failed to update logistics entry: ' . $e->getMessage());
    }
}

/**
 * Handle DELETE requests - delete logistics entry
 */
function handleDeleteLogistics($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid logistics ID is required', 400);
        return;
    }
    
    try {
        // Check if logistics entry exists
        $existingLogistics = $db->query("SELECT id, status FROM logistics WHERE id = ?", [$id])->fetch();
        if (!$existingLogistics) {
            sendErrorResponse('Logistics entry not found', 404);
            return;
        }
        
        // Prevent deletion of delivered shipments
        if ($existingLogistics['status'] === 'delivered') {
            sendErrorResponse('Cannot delete delivered shipments', 400);
            return;
        }
        
        $result = $db->query("DELETE FROM logistics WHERE id = ?", [$id]);
        
        if ($result) {
            sendSuccessResponse([], 'Logistics entry deleted successfully');
        } else {
            sendErrorResponse('Failed to delete logistics entry');
        }
        
    } catch (Exception $e) {
        error_log("Delete logistics error: " . $e->getMessage());
        sendErrorResponse('Failed to delete logistics entry: ' . $e->getMessage());
    }
}
?> 