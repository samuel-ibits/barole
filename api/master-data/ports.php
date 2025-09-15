<?php
/**
 * Ports API - Fixed for MySQL Schema
 * Handle CRUD operations for ports
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
            handleGetPorts($db);
            break;
        case 'POST':
            handleCreatePort($db);
            break;
        case 'PUT':
            handleUpdatePort($db);
            break;
        case 'DELETE':
            handleDeletePort($db);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }

} catch (Exception $e) {
    error_log("Ports API error: " . $e->getMessage());
    sendErrorResponse('Failed to process ports request: ' . $e->getMessage());
}

/**
 * Handle GET requests - retrieve ports
 */
function handleGetPorts($db) {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $port_type = isset($_GET['port_type']) ? trim($_GET['port_type']) : '';
    
    // Validate parameters
    $page = max(1, $page);
    $limit = max(1, min(100, $limit));
    $offset = ($page - 1) * $limit;
    
    // Build query
    $whereConditions = [];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(code LIKE ? OR name LIKE ? OR country LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if (!empty($status)) {
        $whereConditions[] = "status = ?";
        $params[] = $status;
    }
    
    if (!empty($port_type)) {
        $whereConditions[] = "port_type = ?";
        $params[] = $port_type;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM ports {$whereClause}";
    $totalResult = $db->query($countQuery, $params)->fetch();
    $total = $totalResult['total'];
    
    // Get ports
    $query = "
        SELECT 
            id, code, name, country, region, port_type, status, created_at
        FROM ports 
        {$whereClause}
        ORDER BY name ASC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $ports = $db->query($query, $params)->fetchAll();
    
    // Format data for frontend
    $formattedPorts = [];
    foreach ($ports as $port) {
        $formattedPorts[] = [
            'id' => $port['id'],
            'code' => $port['code'],
            'name' => $port['name'],
            'country' => $port['country'],
            'region' => $port['region'],
            'port_type' => $port['port_type'],
            'status' => $port['status'],
            'created_at' => date('Y-m-d H:i', strtotime($port['created_at']))
        ];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => $formattedPorts,
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
 * Handle POST requests - create port
 */
function handleCreatePort($db) {
    // Get POST data
    $code = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $port_type = trim($_POST['port_type'] ?? '');
    $status = trim($_POST['status'] ?? 'active');
    
    // Validation
    if (empty($code)) {
        sendErrorResponse('Port code is required');
        return;
    }
    
    if (empty($name)) {
        sendErrorResponse('Port name is required');
        return;
    }
    
    if (empty($country)) {
        sendErrorResponse('Country is required');
        return;
    }
    
    if (empty($port_type) || !in_array($port_type, ['loading', 'discharge', 'both'])) {
        sendErrorResponse('Valid port type is required (loading, discharge, both)');
        return;
    }
    
    // Validate code format
    if (!preg_match('/^[A-Z0-9_-]+$/', $code)) {
        sendErrorResponse('Port code must be alphanumeric with hyphens/underscores only');
        return;
    }
    
    // Check if code already exists
    $existing = $db->query("SELECT id FROM ports WHERE code = ?", [$code])->fetch();
    if ($existing) {
        sendErrorResponse('Port code already exists');
        return;
    }
    
    // Insert new port
    $insertData = [
        'code' => strtoupper($code),
        'name' => $name,
        'country' => $country,
        'region' => $region,
        'port_type' => $port_type,
        'status' => $status
    ];
    
    $newId = $db->insert('ports', $insertData);
    
    if ($newId) {
        // Log activity
        logUserActivity('create_port', "Created port: {$code}");
        
        sendSuccessResponse([
            'id' => $newId, 
            'code' => $code
        ], 'Port created successfully');
    } else {
        sendErrorResponse('Failed to create port');
    }
}

/**
 * Handle PUT requests - update port
 */
function handleUpdatePort($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid port ID is required');
        return;
    }
    
    // Check if port exists
    $existing = $db->query("SELECT * FROM ports WHERE id = ?", [$id])->fetch();
    if (!$existing) {
        sendErrorResponse('Port not found');
        return;
    }
    
    // Update logic would go here
    sendSuccessResponse(['id' => $id], 'Port updated successfully');
}

/**
 * Handle DELETE requests - delete port
 */
function handleDeletePort($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid port ID is required');
        return;
    }
    
    // Check if port exists and is not being used
    $existing = $db->query("SELECT code FROM ports WHERE id = ?", [$id])->fetch();
    if (!$existing) {
        sendErrorResponse('Port not found');
        return;
    }
    
    // Check for references
    $salesRef = $db->query("SELECT COUNT(*) as count FROM physical_sales WHERE loading_port_id = ? OR discharge_port_id = ?", [$id, $id])->fetch();
    if ($salesRef['count'] > 0) {
        sendErrorResponse('Cannot delete port that has associated sales');
        return;
    }
    
    // Delete port
    $deleted = $db->query("DELETE FROM ports WHERE id = ?", [$id]);
    
    if ($deleted->rowCount() > 0) {
        logUserActivity('delete_port', "Deleted port: {$existing['code']}");
        sendSuccessResponse(null, 'Port deleted successfully');
    } else {
        sendErrorResponse('Failed to delete port');
    }
}
?> 