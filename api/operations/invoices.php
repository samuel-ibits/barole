<?php
/**
 * Invoices API
 * Handle CRUD operations for invoices
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
            handleGetInvoices($db);
            break;
        case 'POST':
            handleCreateInvoice($db);
            break;
        case 'PUT':
            handleUpdateInvoice($db);
            break;
        case 'DELETE':
            handleDeleteInvoice($db);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }

} catch (Exception $e) {
    error_log("Invoices API error: " . $e->getMessage());
    sendErrorResponse('Failed to process invoices request: ' . $e->getMessage());
}

/**
 * Handle GET requests - retrieve invoices
 */
function handleGetInvoices($db) {
    
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
    $countQuery = "SELECT COUNT(*) as total FROM invoices {$whereClause}";
    $totalResult = $db->query($countQuery, $params)->fetch();
    $total = $totalResult['total'];
    
    // Get invoices
    $query = "
        SELECT 
            i.*,
            c.name as counterparty_name
        FROM invoices i
        LEFT JOIN counterparties c ON i.counterparty_id = c.id
        {$whereClause}
        ORDER BY i.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $invoices = $db->query($query, $params)->fetchAll();
    
    // Format data for frontend
    $formattedInvoices = [];
    foreach ($invoices as $invoice) {
        $formattedInvoices[] = [
            'id' => $invoice['id'],
            'invoice_number' => $invoice['invoice_number'],
            'counterparty_name' => $invoice['counterparty_name'],
            'amount' => number_format($invoice['amount'], 2),
            'currency' => $invoice['currency'],
            'due_date' => $invoice['due_date'],
            'status' => $invoice['status'],
            'created_at' => date('Y-m-d H:i', strtotime($invoice['created_at']))
        ];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => $formattedInvoices,
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
 * Handle POST requests - create new invoice
 */
function handleCreateInvoice($db) {
    // Get POST data (accept frontend field names)
    $invoice_number = trim($_POST['invoice_number'] ?? $_POST['invoice_id'] ?? '');
    $trade_id = trim($_POST['trade_id'] ?? '');
    $counterparty_id = (int)($_POST['counterparty_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $currency = trim($_POST['currency'] ?? 'USD');
    $invoice_date = trim($_POST['invoice_date'] ?? date('Y-m-d'));
    $due_date = trim($_POST['due_date'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');
    $description = trim($_POST['description'] ?? '');
    $business_unit_id = (int)($_POST['business_unit_id'] ?? 1);
    
    // Map frontend status values to database enum values
    $statusMap = [
        'pending' => 'draft',
        'confirmed' => 'sent', 
        'executed' => 'paid',
        'draft' => 'draft'
    ];
    
    if (isset($statusMap[$status])) {
        $status = $statusMap[$status];
    }
    
    // Auto-generate invoice_number if empty (max 20 chars)
    if (empty($invoice_number)) {
        $invoice_number = 'INV' . date('ymd') . rand(1000, 9999); // INV250126-1234 = 13 chars
    }
    
    // Validate invoice_number length (max 20 characters)
    if (strlen($invoice_number) > 20) {
        sendErrorResponse('Invoice number must be 20 characters or less');
        return;
    }
    
    // Set default due date if empty (30 days from invoice date)
    if (empty($due_date)) {
        $due_date = date('Y-m-d', strtotime($invoice_date . ' +30 days'));
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
    
    try {
        // Check if invoice_number already exists
        $existing = $db->query("SELECT id FROM invoices WHERE invoice_number = ?", [$invoice_number])->fetch();
        if ($existing) {
            sendErrorResponse('Invoice number already exists');
            return;
        }
        
        // Insert new invoice with correct column names
        $insertData = [
            'invoice_number' => $invoice_number,
            'trade_id' => $trade_id,
            'counterparty_id' => $counterparty_id,
            'amount' => $amount,
            'currency' => $currency,
            'invoice_date' => $invoice_date,
            'due_date' => $due_date,
            'status' => $status,
            'business_unit_id' => $business_unit_id
        ];
        
        // Add optional fields if provided
        if (!empty($description)) {
            $insertData['description'] = $description;
        }
        
        // Build manual insert query to avoid method compatibility issues
        $columns = array_keys($insertData);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $columnList = implode(', ', $columns);
        $values = array_values($insertData);
        
        $insertQuery = "INSERT INTO invoices ({$columnList}) VALUES ({$placeholders})";
        $result = $db->query($insertQuery, $values);
        
        if ($result) {
            $newId = $db->getConnection()->lastInsertId();
            
            // Log activity
            logUserActivity('create_invoice', "Created invoice: {$invoice_number}");
            
            sendSuccessResponse([
                'id' => $newId, 
                'invoice_number' => $invoice_number
            ], 'Invoice created successfully');
        } else {
            sendErrorResponse('Failed to create invoice');
        }
        
    } catch (Exception $e) {
        error_log("Create invoice error: " . $e->getMessage());
        sendErrorResponse('Failed to create invoice: ' . $e->getMessage());
    }
}

/**
 * Handle PUT requests - update existing invoice
 */
function handleUpdateInvoice($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid invoice ID is required', 400);
        return;
    }
    
    // Check if invoice exists
    $existingInvoice = $db->query("SELECT * FROM invoices WHERE id = ?", [$id])->fetch();
    if (!$existingInvoice) {
        sendErrorResponse('Invoice not found', 404);
        return;
    }
    
    // Build update query with only provided fields
    $updateFields = [];
    $params = [];
    
    $allowedFields = [
        'trade_id', 'counterparty_id', 'amount', 'currency', 'invoice_date',
        'due_date', 'status', 'description', 'line_items'
    ];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            if ($field === 'line_items') {
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
        $query = "UPDATE invoices SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $result = $db->query($query, $params);
        
        if ($result) {
            sendSuccessResponse([], 'Invoice updated successfully');
        } else {
            sendErrorResponse('Failed to update invoice');
        }
        
    } catch (Exception $e) {
        error_log("Update invoice error: " . $e->getMessage());
        sendErrorResponse('Failed to update invoice: ' . $e->getMessage());
    }
}

/**
 * Handle DELETE requests - delete invoice
 */
function handleDeleteInvoice($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    
    if ($id <= 0) {
        sendErrorResponse('Valid invoice ID is required', 400);
        return;
    }
    
    try {
        // Check if invoice exists
        $existingInvoice = $db->query("SELECT id, status FROM invoices WHERE id = ?", [$id])->fetch();
        if (!$existingInvoice) {
            sendErrorResponse('Invoice not found', 404);
            return;
        }
        
        // Prevent deletion of paid invoices
        if ($existingInvoice['status'] === 'paid') {
            sendErrorResponse('Cannot delete paid invoices', 400);
            return;
        }
        
        $result = $db->query("DELETE FROM invoices WHERE id = ?", [$id]);
        
        if ($result) {
            sendSuccessResponse([], 'Invoice deleted successfully');
        } else {
            sendErrorResponse('Failed to delete invoice');
        }
        
    } catch (Exception $e) {
        error_log("Delete invoice error: " . $e->getMessage());
        sendErrorResponse('Failed to delete invoice: ' . $e->getMessage());
    }
}
?> 