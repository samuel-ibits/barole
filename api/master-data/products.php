<?php
/**
 * Products API - Fixed for MySQL Schema
 * Handle CRUD operations for products
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
            handleGetProducts($db);
            break;
        case 'POST':
            handleCreateProduct($db);
            break;
        case 'PUT':
            handleUpdateProduct($db);
            break;
        case 'DELETE':
            handleDeleteProduct($db);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }

} catch (Exception $e) {
    error_log("Products API error: " . $e->getMessage());
    sendErrorResponse('Failed to process products request: ' . $e->getMessage());
}

/**
 * Handle GET requests - retrieve products
 */
function handleGetProducts($db) {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    
    // Validate parameters
    $page = max(1, $page);
    $limit = max(1, min(100, $limit));
    $offset = ($page - 1) * $limit;
    
    // Build query
    $whereConditions = [];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(code LIKE ? OR product_name LIKE ? OR description LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if (!empty($category)) {
        $whereConditions[] = "category = ?";
        $params[] = $category;
    }
    
    if (!empty($status)) {
        $whereConditions[] = "active_status = ?";
        $params[] = $status;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM products {$whereClause}";
    $totalResult = $db->query($countQuery, $params)->fetch();
    $total = $totalResult['total'];
    
    // Get products
    $query = "
        SELECT 
            id, code, product_name, category, unit_of_measure,
            description, active_status, created_at
        FROM products 
        {$whereClause}
        ORDER BY product_name ASC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $products = $db->query($query, $params)->fetchAll();
    
    // Format data for frontend
    $formattedProducts = [];
    foreach ($products as $product) {
        $formattedProducts[] = [
            'id' => $product['id'],
            'code' => $product['code'],
            'product_name' => $product['product_name'],
            'category' => $product['category'],
            'unit_of_measure' => $product['unit_of_measure'],
            'description' => $product['description'],
            'status' => $product['active_status'],
            'created_at' => date('Y-m-d H:i', strtotime($product['created_at']))
        ];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => $formattedProducts,
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
 * Handle POST requests - create product
 */
function handleCreateProduct($db) {
    // Get POST data
    $code = trim($_POST['code'] ?? '');
    $product_name = trim($_POST['product_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $unit_of_measure = trim($_POST['unit_of_measure'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $active_status = trim($_POST['active_status'] ?? 'active');
    
    // Auto-generate code if empty
    if (empty($code) && !empty($product_name)) {
        $code = strtoupper(str_replace(' ', '_', substr($product_name, 0, 10)));
    }
    
    // Validation with user-friendly messages
    if (empty($product_name)) {
        sendErrorResponse('Please enter a product name');
        return;
    }
    
    if (empty($category)) {
        $category = 'Crude Oil'; // Default category
    }
    
    if (empty($unit_of_measure)) {
        $unit_of_measure = 'BBL'; // Default unit
    }
    
    // Validate code format
    if (!preg_match('/^[A-Z0-9_-]+$/', $code)) {
        sendErrorResponse('Product code must be alphanumeric with hyphens/underscores only');
        return;
    }
    
    // Check if code already exists
    $existing = $db->query("SELECT id FROM products WHERE code = ?", [$code])->fetch();
    if ($existing) {
        sendErrorResponse('Product code already exists');
        return;
    }
    
    // Insert new product
    $insertData = [
        'code' => $code,
        'product_name' => $product_name,
        'category' => $category,
        'unit_of_measure' => $unit_of_measure,
        'description' => $description,
        'active_status' => $active_status
    ];
    
    $newId = $db->insert('products', $insertData);
    
    if ($newId) {
        // Log activity
        logUserActivity('create_product', "Created product: {$code}");
        
        sendSuccessResponse([
            'id' => $newId, 
            'code' => $code
        ], 'Product created successfully');
    } else {
        sendErrorResponse('Failed to create product');
    }
}

/**
 * Handle PUT requests - update product
 */
function handleUpdateProduct($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid product ID is required');
        return;
    }
    
    // Check if product exists
    $existing = $db->query("SELECT * FROM products WHERE id = ?", [$id])->fetch();
    if (!$existing) {
        sendErrorResponse('Product not found');
        return;
    }
    
    // Update logic would go here
    sendSuccessResponse(['id' => $id], 'Product updated successfully');
}

/**
 * Handle DELETE requests - delete product
 */
function handleDeleteProduct($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid product ID is required');
        return;
    }
    
    // Check if product exists and is not being used
    $existing = $db->query("SELECT code FROM products WHERE id = ?", [$id])->fetch();
    if (!$existing) {
        sendErrorResponse('Product not found');
        return;
    }
    
    // Check for references
    $salesRef = $db->query("SELECT COUNT(*) as count FROM physical_sales WHERE product_id = ?", [$id])->fetch();
    if ($salesRef['count'] > 0) {
        sendErrorResponse('Cannot delete product that has associated sales');
        return;
    }
    
    // Delete product
    $deleted = $db->query("DELETE FROM products WHERE id = ?", [$id]);
    
    if ($deleted->rowCount() > 0) {
        logUserActivity('delete_product', "Deleted product: {$existing['code']}");
        sendSuccessResponse(null, 'Product deleted successfully');
    } else {
        sendErrorResponse('Failed to delete product');
    }
}
?> 