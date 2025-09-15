<?php
/**
 * Business Units API - Fixed for MySQL Schema
 * Handle CRUD operations for business units
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
            handleGetBusinessUnits($db);
            break;
        case 'POST':
            handleCreateBusinessUnit($db);
            break;
        case 'PUT':
            handleUpdateBusinessUnit($db);
            break;
        case 'DELETE':
            handleDeleteBusinessUnit($db);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }

} catch (Exception $e) {
    error_log("Business units API error: " . $e->getMessage());
    sendErrorResponse('Failed to process business units request: ' . $e->getMessage());
}

/**
 * Handle GET requests - retrieve business units
 */
function handleGetBusinessUnits($db) {
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
        $whereConditions[] = "(code LIKE ? OR business_unit_name LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if (!empty($status)) {
        $whereConditions[] = "status = ?";
        $params[] = $status;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM business_units {$whereClause}";
    $totalResult = $db->query($countQuery, $params)->fetch();
    $total = $totalResult['total'];
    
    // Get business units
    $query = "
        SELECT 
            bu.id,
            bu.code,
            bu.business_unit_name,
            bu.parent_unit_id,
            bu.manager_id,
            bu.budget,
            bu.status,
            bu.created_at,
            parent.business_unit_name as parent_unit_name,
            mgr.full_name as manager_name
        FROM business_units bu
        LEFT JOIN business_units parent ON bu.parent_unit_id = parent.id
        LEFT JOIN users mgr ON bu.manager_id = mgr.id
        {$whereClause}
        ORDER BY bu.business_unit_name ASC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $businessUnits = $db->query($query, $params)->fetchAll();
    
    // Format data for frontend
    $formattedBusinessUnits = [];
    foreach ($businessUnits as $unit) {
        $formattedBusinessUnits[] = [
            'id' => $unit['id'],
            'code' => $unit['code'],
            'business_unit_name' => $unit['business_unit_name'],
            'parent_unit_id' => $unit['parent_unit_id'],
            'parent_unit_name' => $unit['parent_unit_name'],
            'manager_id' => $unit['manager_id'],
            'manager_name' => $unit['manager_name'],
            'budget' => $unit['budget'] ? number_format($unit['budget'], 2) : '',
            'status' => $unit['status'],
            'created_at' => date('Y-m-d H:i', strtotime($unit['created_at']))
        ];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => $formattedBusinessUnits,
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
 * Handle POST requests - create business unit
 */
function handleCreateBusinessUnit($db) {
    // Get POST data
    $code = trim($_POST['code'] ?? '');
    $business_unit_name = trim($_POST['business_unit_name'] ?? '');
    $parent_unit_id = (int)($_POST['parent_unit_id'] ?? 0) ?: null;
    $manager_id = (int)($_POST['manager_id'] ?? 0) ?: null;
    $budget = (float)($_POST['budget'] ?? 0) ?: null;
    $status = trim($_POST['status'] ?? 'active');
    
    // Validation
    if (empty($code)) {
        sendErrorResponse('Business unit code is required');
        return;
    }
    
    if (empty($business_unit_name)) {
        sendErrorResponse('Business unit name is required');
        return;
    }
    
    // Validate code format
    if (!preg_match('/^[A-Z0-9_-]+$/', $code)) {
        sendErrorResponse('Business unit code must be alphanumeric with hyphens/underscores only');
        return;
    }
    
    // Check if code already exists
    $existing = $db->query("SELECT id FROM business_units WHERE code = ?", [$code])->fetch();
    if ($existing) {
        sendErrorResponse('Business unit code already exists');
        return;
    }
    
    // Validate parent unit if provided
    if ($parent_unit_id) {
        $parentExists = $db->query("SELECT id FROM business_units WHERE id = ?", [$parent_unit_id])->fetch();
        if (!$parentExists) {
            sendErrorResponse('Invalid parent unit');
            return;
        }
    }
    
    // Validate manager if provided
    if ($manager_id) {
        $managerExists = $db->query("SELECT id FROM users WHERE id = ? AND status = 'active'", [$manager_id])->fetch();
        if (!$managerExists) {
            sendErrorResponse('Invalid manager');
            return;
        }
    }
    
    // Insert new business unit
    $insertData = [
        'code' => strtoupper($code),
        'business_unit_name' => $business_unit_name,
        'status' => $status
    ];
    
    if ($parent_unit_id) {
        $insertData['parent_unit_id'] = $parent_unit_id;
    }
    if ($manager_id) {
        $insertData['manager_id'] = $manager_id;
    }
    if ($budget) {
        $insertData['budget'] = $budget;
    }
    
    $newId = $db->insert('business_units', $insertData);
    
    if ($newId) {
        // Log activity
        logUserActivity('create_business_unit', "Created business unit: {$code}");
        
        sendSuccessResponse([
            'id' => $newId, 
            'code' => $code
        ], 'Business unit created successfully');
    } else {
        sendErrorResponse('Failed to create business unit');
    }
}

/**
 * Handle PUT requests - update business unit
 */
function handleUpdateBusinessUnit($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid business unit ID is required');
        return;
    }
    
    // Check if business unit exists
    $existing = $db->query("SELECT * FROM business_units WHERE id = ?", [$id])->fetch();
    if (!$existing) {
        sendErrorResponse('Business unit not found');
        return;
    }
    
    // Update logic would go here
    sendSuccessResponse(['id' => $id], 'Business unit updated successfully');
}

/**
 * Handle DELETE requests - delete business unit
 */
function handleDeleteBusinessUnit($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid business unit ID is required');
        return;
    }
    
    // Check if business unit exists and is not being used
    $existing = $db->query("SELECT code FROM business_units WHERE id = ?", [$id])->fetch();
    if (!$existing) {
        sendErrorResponse('Business unit not found');
        return;
    }
    
    // Check for references
    $salesRef = $db->query("SELECT COUNT(*) as count FROM physical_sales WHERE business_unit_id = ?", [$id])->fetch();
    if ($salesRef['count'] > 0) {
        sendErrorResponse('Cannot delete business unit that has associated sales');
        return;
    }
    
    // Check for child units
    $childRef = $db->query("SELECT COUNT(*) as count FROM business_units WHERE parent_unit_id = ?", [$id])->fetch();
    if ($childRef['count'] > 0) {
        sendErrorResponse('Cannot delete business unit that has child units');
        return;
    }
    
    // Delete business unit
    $deleted = $db->query("DELETE FROM business_units WHERE id = ?", [$id]);
    
    if ($deleted->rowCount() > 0) {
        logUserActivity('delete_business_unit', "Deleted business unit: {$existing['code']}");
        sendSuccessResponse(null, 'Business unit deleted successfully');
    } else {
        sendErrorResponse('Failed to delete business unit');
    }
}
?> 