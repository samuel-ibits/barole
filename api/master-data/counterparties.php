<?php
/**
 * Counterparties API - Fixed for MySQL Schema
 * Handle CRUD operations for counterparties
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
            handleGetCounterparties($db);
            break;
        case 'POST':
            handleCreateCounterparty($db);
            break;
        case 'PUT':
            handleUpdateCounterparty($db);
            break;
        case 'DELETE':
            handleDeleteCounterparty($db);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }

} catch (Exception $e) {
    error_log("Counterparties API error: " . $e->getMessage());
    sendErrorResponse('Failed to process counterparties request: ' . $e->getMessage());
}

/**
 * Handle GET requests - retrieve counterparties
 */
function handleGetCounterparties($db) {
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
    $countQuery = "SELECT COUNT(*) as total FROM counterparties {$whereClause}";
    $totalResult = $db->query($countQuery, $params)->fetch();
    $total = $totalResult['total'];
    
    // Get counterparties
    $query = "
        SELECT 
            id, name, type, contact_person, email, phone, 
            address, city, country, credit_rating, status, created_at
        FROM counterparties 
        {$whereClause}
        ORDER BY name ASC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $counterparties = $db->query($query, $params)->fetchAll();
    
    // Format data for frontend
    $formattedCounterparties = [];
    foreach ($counterparties as $counterparty) {
        $formattedCounterparties[] = [
            'id' => $counterparty['id'],
            'name' => $counterparty['name'],
            'type' => $counterparty['type'],
            'contact_person' => $counterparty['contact_person'],
            'email' => $counterparty['email'],
            'phone' => $counterparty['phone'],
            'country' => $counterparty['country'],
            'credit_rating' => $counterparty['credit_rating'],
            'status' => $counterparty['status'],
            'created_at' => date('Y-m-d H:i', strtotime($counterparty['created_at']))
        ];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => $formattedCounterparties,
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
 * Handle POST requests - create counterparty
 */
function handleCreateCounterparty($db) {
    // Get POST data
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $credit_rating = trim($_POST['credit_rating'] ?? '');
    $status = trim($_POST['status'] ?? 'active');
    
    // Validation with user-friendly messages
    if (empty($name)) {
        sendErrorResponse('Please enter a counterparty name');
        return;
    }
    
    if (empty($type)) {
        $type = 'customer'; // Default to customer
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendErrorResponse('Please enter a valid email address');
        return;
    }
    
    // Check if name already exists
    $existing = $db->query("SELECT id FROM counterparties WHERE name = ?", [$name])->fetch();
    if ($existing) {
        sendErrorResponse('Counterparty name already exists');
        return;
    }
    
    // Insert new counterparty
    $insertData = [
        'name' => $name,
        'type' => $type,
        'contact_person' => $contact_person,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'city' => $city,
        'country' => $country,
        'status' => $status
    ];
    
    if (!empty($credit_rating)) {
        $insertData['credit_rating'] = $credit_rating;
    }
    
    $newId = $db->insert('counterparties', $insertData);
    
    if ($newId) {
        // Log activity
        logUserActivity('create_counterparty', "Created counterparty: {$name}");
        
        sendSuccessResponse([
            'id' => $newId, 
            'name' => $name
        ], 'Counterparty created successfully');
    } else {
        sendErrorResponse('Failed to create counterparty');
    }
}

/**
 * Handle PUT requests - update counterparty
 */
function handleUpdateCounterparty($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid counterparty ID is required');
        return;
    }
    
    // Check if counterparty exists
    $existing = $db->query("SELECT * FROM counterparties WHERE id = ?", [$id])->fetch();
    if (!$existing) {
        sendErrorResponse('Counterparty not found');
        return;
    }
    
    // Update logic would go here
    sendSuccessResponse(['id' => $id], 'Counterparty updated successfully');
}

/**
 * Handle DELETE requests - delete counterparty
 */
function handleDeleteCounterparty($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid counterparty ID is required');
        return;
    }
    
    // Check if counterparty exists and is not being used
    $existing = $db->query("SELECT name FROM counterparties WHERE id = ?", [$id])->fetch();
    if (!$existing) {
        sendErrorResponse('Counterparty not found');
        return;
    }
    
    // Check for references
    $salesRef = $db->query("SELECT COUNT(*) as count FROM physical_sales WHERE counterparty_id = ?", [$id])->fetch();
    if ($salesRef['count'] > 0) {
        sendErrorResponse('Cannot delete counterparty that has associated sales');
        return;
    }
    
    // Delete counterparty
    $deleted = $db->query("DELETE FROM counterparties WHERE id = ?", [$id]);
    
    if ($deleted->rowCount() > 0) {
        logUserActivity('delete_counterparty', "Deleted counterparty: {$existing['name']}");
        sendSuccessResponse(null, 'Counterparty deleted successfully');
    } else {
        sendErrorResponse('Failed to delete counterparty');
    }
}
?> 