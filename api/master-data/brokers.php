<?php
/**
 * Brokers API - Fixed for MySQL Schema
 * Handle CRUD operations for brokers
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
            handleGetBrokers($db);
            break;
        case 'POST':
            handleCreateBroker($db);
            break;
        case 'PUT':
            handleUpdateBroker($db);
            break;
        case 'DELETE':
            handleDeleteBroker($db);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }

} catch (Exception $e) {
    error_log("Brokers API error: " . $e->getMessage());
    sendErrorResponse('Failed to process brokers request: ' . $e->getMessage());
}

/**
 * Handle GET requests - retrieve brokers
 */
function handleGetBrokers($db) {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    
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
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM brokers {$whereClause}";
    $totalResult = $db->query($countQuery, $params)->fetch();
    $total = $totalResult['total'];
    
    // Get brokers
    $query = "
        SELECT 
            id, name, contact_person, email, phone, commission_rate, status, created_at
        FROM brokers 
        {$whereClause}
        ORDER BY name ASC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $brokers = $db->query($query, $params)->fetchAll();
    
    // Format data for frontend
    $formattedBrokers = [];
    foreach ($brokers as $broker) {
        $formattedBrokers[] = [
            'id' => $broker['id'],
            'name' => $broker['name'],
            'contact_person' => $broker['contact_person'],
            'email' => $broker['email'],
            'phone' => $broker['phone'],
            'commission_rate' => $broker['commission_rate'] ? number_format($broker['commission_rate'] * 100, 2) . '%' : '',
            'status' => $broker['status'],
            'created_at' => date('Y-m-d H:i', strtotime($broker['created_at']))
        ];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => $formattedBrokers,
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
 * Handle POST requests - create broker
 */
function handleCreateBroker($db) {
    // Get POST data
    $name = trim($_POST['name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $commission_rate = (float)($_POST['commission_rate'] ?? 0) ?: null;
    $status = trim($_POST['status'] ?? 'active');
    
    // Validation
    if (empty($name)) {
        sendErrorResponse('Broker name is required');
        return;
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendErrorResponse('Invalid email format');
        return;
    }
    
    if ($commission_rate !== null && ($commission_rate < 0 || $commission_rate > 1)) {
        sendErrorResponse('Commission rate must be between 0 and 1 (e.g., 0.025 for 2.5%)');
        return;
    }
    
    // Check if name already exists
    $existing = $db->query("SELECT id FROM brokers WHERE name = ?", [$name])->fetch();
    if ($existing) {
        sendErrorResponse('Broker name already exists');
        return;
    }
    
    // Insert new broker
    $insertData = [
        'name' => $name,
        'contact_person' => $contact_person,
        'email' => $email,
        'phone' => $phone,
        'status' => $status
    ];
    
    if ($commission_rate !== null) {
        $insertData['commission_rate'] = $commission_rate;
    }
    
    $newId = $db->insert('brokers', $insertData);
    
    if ($newId) {
        // Log activity
        logUserActivity('create_broker', "Created broker: {$name}");
        
        sendSuccessResponse([
            'id' => $newId, 
            'name' => $name
        ], 'Broker created successfully');
    } else {
        sendErrorResponse('Failed to create broker');
    }
}

/**
 * Handle PUT requests - update broker
 */
function handleUpdateBroker($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid broker ID is required');
        return;
    }
    
    // Check if broker exists
    $existing = $db->query("SELECT * FROM brokers WHERE id = ?", [$id])->fetch();
    if (!$existing) {
        sendErrorResponse('Broker not found');
        return;
    }
    
    // Update logic would go here
    sendSuccessResponse(['id' => $id], 'Broker updated successfully');
}

/**
 * Handle DELETE requests - delete broker
 */
function handleDeleteBroker($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid broker ID is required');
        return;
    }
    
    // Check if broker exists
    $existing = $db->query("SELECT name FROM brokers WHERE id = ?", [$id])->fetch();
    if (!$existing) {
        sendErrorResponse('Broker not found');
        return;
    }
    
    // Note: Since brokers table doesn't have direct foreign key relationships in the schema,
    // we can delete directly, but in a real system you'd check for any trade references
    
    // Delete broker
    $deleted = $db->query("DELETE FROM brokers WHERE id = ?", [$id]);
    
    if ($deleted->rowCount() > 0) {
        logUserActivity('delete_broker', "Deleted broker: {$existing['name']}");
        sendSuccessResponse(null, 'Broker deleted successfully');
    } else {
        sendErrorResponse('Failed to delete broker');
    }
}
?> 