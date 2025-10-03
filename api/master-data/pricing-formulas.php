<?php
/**
 * CRUD API for pricing_formulas
 */

require_once __DIR__ . '/../../includes/simple_session.php';
requireLogin();

try {
    require_once __DIR__ . '/../../config/database.php';
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            handleGet($db);
            break;
        case 'POST':
            handleCreate($db);
            break;
        case 'PUT':
            handleUpdate($db);
            break;
        case 'DELETE':
            handleDelete($db);
            break;
        default:
            sendErrorResponse('Method not allowed', 405);
    }

} catch (Exception $e) {
    error_log("pricing_formulas API error: " . $e->getMessage());
    sendErrorResponse('Failed to process request: ' . $e->getMessage());
}

/**
 * Handle GET - list records
 */
function handleGet($db) {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(100, (int)($_GET['limit'] ?? 25)));
    $offset = ($page - 1) * $limit;
    $search = trim($_GET['search'] ?? '');

    $where = '';
    $params = [];
    if ($search !== '') {
        $where = "WHERE name LIKE ? OR details LIKE ?";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    $total = $db->query("SELECT COUNT(*) as total FROM pricing_formulas {$where}", $params)->fetch()['total'];

    $sql = "SELECT id, name, details 
            FROM pricing_formulas {$where} 
            ORDER BY id DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $rows = $db->query($sql, $params)->fetchAll();

    sendJSONResponse([
        'success' => true,
        'data' => $rows,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
}

/**
 * Handle POST - create
 */
function handleCreate($db) {
    $name    = trim($_POST['name'] ?? '');
    $details = trim($_POST['details'] ?? '');

    if ($name === '' || $details === '') {
        sendErrorResponse('Name and Details are required');
        return;
    }

    $newId = $db->insert('pricing_formulas', [
        'name'    => $name,
        'details' => $details
    ]);

    if ($newId) {
        logUserActivity('create_pricing_formula', "Created pricing_formula: {$name}");
        sendSuccessResponse(['id' => $newId], 'Pricing formula created successfully');
    } else {
        sendErrorResponse('Failed to create pricing formula');
    }
}

/**
 * Handle PUT - update
 */
function handleUpdate($db) {
    $input   = json_decode(file_get_contents('php://input'), true);
    $id      = (int)($input['id'] ?? 0);
    $name    = trim($input['name'] ?? '');
    $details = trim($input['details'] ?? '');

    if ($id <= 0 || $name === '' || $details === '') {
        sendErrorResponse('ID, Name, and Details are required');
        return;
    }

    $updated = $db->query(
        "UPDATE pricing_formulas SET name = ?, details = ? WHERE id = ?",
        [$name, $details, $id]
    );

    if ($updated->rowCount() > 0) {
        logUserActivity('update_pricing_formula', "Updated pricing_formula: {$id}");
        sendSuccessResponse(['id' => $id], 'Pricing formula updated successfully');
    } else {
        sendErrorResponse('Failed to update pricing formula');
    }
}

/**
 * Handle DELETE - delete
 */
function handleDelete($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);

    if ($id <= 0) {
        sendErrorResponse('Valid ID is required');
        return;
    }

    $deleted = $db->query("DELETE FROM pricing_formulas WHERE id = ?", [$id]);
    if ($deleted->rowCount() > 0) {
        logUserActivity('delete_pricing_formula', "Deleted pricing_formula: {$id}");
        sendSuccessResponse(null, 'Pricing formula deleted successfully');
    } else {
        sendErrorResponse('Failed to delete pricing formula');
    }
}
?>
