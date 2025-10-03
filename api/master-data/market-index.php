
<?php
/**
 * Market Index API
 * Handle CRUD operations for market_index table
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
    error_log("market_index API error: " . $e->getMessage());
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
        $where = "WHERE index_name LIKE ? OR exchange LIKE ?";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    // Total count
    $countSql = "SELECT COUNT(*) as total FROM market_index {$where}";
    $total = $db->query($countSql, $params)->fetch()['total'];

    // Data query
    $sql = "SELECT id, index_name, index_uom, exchange, expiry_date, created_at
            FROM market_index
            {$where}
            ORDER BY id DESC
            LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $rows = $db->query($sql, $params)->fetchAll();
    $data=[
        'success' => true,
        'data' => $rows,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ]
    ];
    sendJSONResponse($data);
}

/**
 * Handle POST - create
 */
function handleCreate($db) {
    $index_name = trim($_POST['index_name'] ?? '');
    $index_uom = trim($_POST['index_uom'] ?? '');
    $exchange = trim($_POST['exchange'] ?? '');
    $expiry_date = trim($_POST['expiry_date'] ?? '');

    if ($index_name === '' || $index_uom === '' || $exchange === '' || $expiry_date === '') {
        sendErrorResponse('All fields are required');
        return;
    }

    $newId = $db->insert('market_index', [
        'index_name' => $index_name,
        'index_uom' => $index_uom,
        'exchange' => $exchange,
        'expiry_date' => $expiry_date
    ]);

    if ($newId) {
        logUserActivity('create_market_index', "Created Market Index: {$index_name}");
        sendSuccessResponse(['id' => $newId], 'Market Index created successfully');
    } else {
        sendErrorResponse('Failed to create Market Index');
    }
}

/**
 * Handle PUT - update
 */
function handleUpdate($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);

    if ($id <= 0) {
        sendErrorResponse('Valid ID is required');
        return;
    }

    $fields = [];
    $params = [];

    if (isset($input['index_name'])) {
        $fields[] = "index_name = ?";
        $params[] = trim($input['index_name']);
    }
    if (isset($input['index_uom'])) {
        $fields[] = "index_uom = ?";
        $params[] = trim($input['index_uom']);
    }
    if (isset($input['exchange'])) {
        $fields[] = "exchange = ?";
        $params[] = trim($input['exchange']);
    }
    if (isset($input['expiry_date'])) {
        $fields[] = "expiry_date = ?";
        $params[] = trim($input['expiry_date']);
    }

    if (empty($fields)) {
        sendErrorResponse('No fields to update');
        return;
    }

    $params[] = $id;
    $sql = "UPDATE market_index SET " . implode(', ', $fields) . " WHERE id = ?";
    $updated = $db->query($sql, $params);

    if ($updated->rowCount() > 0) {
        logUserActivity('update_market_index', "Updated Market Index ID: {$id}");
        sendSuccessResponse(['id' => $id], 'Market Index updated successfully');
    } else {
        sendErrorResponse('Failed to update Market Index');
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

    $deleted = $db->query("DELETE FROM market_index WHERE id = ?", [$id]);
    if ($deleted->rowCount() > 0) {
        logUserActivity('delete_market_index', "Deleted Market Index ID: {$id}");
        sendSuccessResponse(null, 'Market Index deleted successfully');
    } else {
        sendErrorResponse('Failed to delete Market Index');
    }
}
?>