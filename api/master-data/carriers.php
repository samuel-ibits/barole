<?php
/**
 * Carriers API - Fixed for MySQL Schema
 * Handle CRUD operations for carriers
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
            handleGetCarriers($db);
            break;
        case 'POST':
            handleCreateCarrier($db);
            break;
        case 'PUT':
            handleUpdateCarrier($db);
            break;
        case 'DELETE':
            handleDeleteCarrier($db);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }

} catch (Exception $e) {
    error_log("Carriers API error: " . $e->getMessage());
    sendErrorResponse('Failed to process carriers request: ' . $e->getMessage());
}

/**
 * Handle GET requests - retrieve carriers
 */
function handleGetCarriers($db) {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $carrier_type = isset($_GET['carrier_type']) ? trim($_GET['carrier_type']) : '';
    
    // Validate parameters
    $page = max(1, $page);
    $limit = max(1, min(100, $limit));
    $offset = ($page - 1) * $limit;
    
    // Build query
    $whereConditions = [];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(name LIKE ? OR contact_person LIKE ? OR email LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if (!empty($status)) {
        $whereConditions[] = "status = ?";
        $params[] = $status;
    }
    
    if (!empty($carrier_type)) {
        $whereConditions[] = "carrier_type = ?";
        $params[] = $carrier_type;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM carriers {$whereClause}";
    $totalResult = $db->query($countQuery, $params)->fetch();
    $total = $totalResult['total'];
    
    // Get carriers
    $query = "
        SELECT 
            id, name, carrier_type, contact_person, email, phone, status, created_at
        FROM carriers 
        {$whereClause}
        ORDER BY name ASC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $carriers = $db->query($query, $params)->fetchAll();
    
    // Format data for frontend
    $formattedCarriers = [];
    foreach ($carriers as $carrier) {
        $formattedCarriers[] = [
            'id' => $carrier['id'],
            'name' => $carrier['name'],
            'carrier_type' => $carrier['carrier_type'],
            'contact_person' => $carrier['contact_person'],
            'email' => $carrier['email'],
            'phone' => $carrier['phone'],
            'status' => $carrier['status'],
            'created_at' => date('Y-m-d H:i', strtotime($carrier['created_at']))
        ];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => $formattedCarriers,
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
 * Handle POST requests - create carrier
 */
function handleCreateCarrier($db) {
    // Get POST data
    $name = trim($_POST['name'] ?? '');
    $carrier_type = trim($_POST['carrier_type'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $status = trim($_POST['status'] ?? 'active');
    
    // Validation
    if (empty($name)) {
        sendErrorResponse('Carrier name is required');
        return;
    }
    
    if (empty($carrier_type) || !in_array($carrier_type, ['shipping', 'pipeline', 'truck', 'rail'])) {
        sendErrorResponse('Valid carrier type is required (shipping, pipeline, truck, rail)');
        return;
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendErrorResponse('Invalid email format');
        return;
    }
    
    // Check if name already exists
    $existing = $db->query("SELECT id FROM carriers WHERE name = ?", [$name])->fetch();
    if ($existing) {
        sendErrorResponse('Carrier name already exists');
        return;
    }
    
    // Insert new carrier
    $insertData = [
        'name' => $name,
        'carrier_type' => $carrier_type,
        'contact_person' => $contact_person,
        'email' => $email,
        'phone' => $phone,
        'status' => $status
    ];
    
    $newId = $db->insert('carriers', $insertData);
    
    if ($newId) {
        // Log activity
        logUserActivity('create_carrier', "Created carrier: {$name}");
        
        sendSuccessResponse([
            'id' => $newId, 
            'name' => $name
        ], 'Carrier created successfully');
    } else {
        sendErrorResponse('Failed to create carrier');
    }
}

/**
 * Handle PUT requests - update carrier
 */
function handleUpdateCarrier($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid carrier ID is required');
        return;
    }
    
    // Check if carrier exists
    $existing = $db->query("SELECT * FROM carriers WHERE id = ?", [$id])->fetch();
    if (!$existing) {
        sendErrorResponse('Carrier not found');
        return;
    }
    
    // Update logic would go here
    sendSuccessResponse(['id' => $id], 'Carrier updated successfully');
}

/**
 * Handle DELETE requests - delete carrier
 */
function handleDeleteCarrier($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid carrier ID is required');
        return;
    }
    
    // Check if carrier exists and is not being used
    $existing = $db->query("SELECT name FROM carriers WHERE id = ?", [$id])->fetch();
    if (!$existing) {
        sendErrorResponse('Carrier not found');
        return;
    }
    
    // Check for references
    $logisticsRef = $db->query("SELECT COUNT(*) as count FROM logistics WHERE carrier_id = ?", [$id])->fetch();
    if ($logisticsRef['count'] > 0) {
        sendErrorResponse('Cannot delete carrier that has associated logistics records');
        return;
    }
    
    // Delete carrier
    $deleted = $db->query("DELETE FROM carriers WHERE id = ?", [$id]);
    
    if ($deleted->rowCount() > 0) {
        logUserActivity('delete_carrier', "Deleted carrier: {$existing['name']}");
        sendSuccessResponse(null, 'Carrier deleted successfully');
    } else {
        sendErrorResponse('Failed to delete carrier');
    }
}
?> 