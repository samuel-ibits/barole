<?php
/**
 * CRUD API for market_prices
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
    error_log("market_prices API error: " . $e->getMessage());
    sendErrorResponse('Failed to process request: ' . $e->getMessage());
}

/**
 * Handle GET - list records
 */
function handleGet($db) {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(100, (int)($_GET['limit'] ?? 25)));
    $offset = ($page - 1) * $limit;

    $where = '';
    $params = [];
    if (!empty($_GET['search'])) {
        $where = "WHERE market_index_id LIKE ?";
        $params[] = '%' . trim($_GET['search']) . '%';
    }

    $total = $db->query("SELECT COUNT(*) as total FROM market_prices {$where}", $params)->fetch()['total'];

    $sql = "SELECT id, market_index_id, closing_date, expiry_date, closing_price
            FROM market_prices {$where} 
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
    $market_index_id = trim($_POST['market_index'] ?? '');
    $closing_date    = trim($_POST['closing_date'] ?? '');
    $expiry_date     = trim($_POST['expiry_date'] ?? '');
    $closing_price   = trim($_POST['closing_price'] ?? '');

    if ($market_index_id === '' || $closing_date === '' || $expiry_date === '' || $closing_price === '') {
        sendErrorResponse('All fields are required');
        return;
    }

    $newId = $db->insert('market_prices', [
        'market_index_id' => $market_index_id,
        'closing_date'    => $closing_date,
        'expiry_date'     => $expiry_date,
        'closing_price'   => $closing_price
    ]);

    if ($newId) {
        logUserActivity('create_market_price', "Created market_price ID: {$newId}");
        sendSuccessResponse(['id' => $newId], 'Market price created successfully');
    } else {
        sendErrorResponse('Failed to create market price');
    }
}

/**
 * Handle PUT - update
 */
function handleUpdate($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id             = (int)($input['id'] ?? 0);
    $market_index_id= trim($input['market_index_id'] ?? '');
    $closing_date   = trim($input['closing_date'] ?? '');
    $expiry_date    = trim($input['expiry_date'] ?? '');
    $closing_price  = trim($input['closing_price'] ?? '');

    if ($id <= 0 || $market_index_id === '' || $closing_date === '' || $expiry_date === '' || $closing_price === '') {
        sendErrorResponse('ID and all fields are required');
        return;
    }

    $updated = $db->query(
        "UPDATE market_prices 
         SET market_index_id = ?, closing_date = ?, expiry_date = ?, closing_price = ? 
         WHERE id = ?",
        [$market_index_id, $closing_date, $expiry_date, $closing_price, $id]
    );

    if ($updated->rowCount() > 0) {
        logUserActivity('update_market_price', "Updated market_price ID: {$id}");
        sendSuccessResponse(['id' => $id], 'Market price updated successfully');
    } else {
        sendErrorResponse('Failed to update market price');
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

    $deleted = $db->query("DELETE FROM market_prices WHERE id = ?", [$id]);
    if ($deleted->rowCount() > 0) {
        logUserActivity('delete_market_price', "Deleted market_price ID: {$id}");
        sendSuccessResponse(null, 'Market price deleted successfully');
    } else {
        sendErrorResponse('Failed to delete market price');
    }
}
?>
