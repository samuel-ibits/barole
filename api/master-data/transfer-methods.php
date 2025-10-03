<?php
/**
 * Generic CRUD API Template
 * Replace transfer_methods, {singular}, and column mappings
 */

// Load session management
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
    error_log("transfer_methods API error: " . $e->getMessage());
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
        $where = "WHERE name LIKE ?";
        $params[] = '%' . $search . '%';
    }

    $total = $db->query("SELECT COUNT(*) as total FROM transfer_methods {$where}", $params)->fetch()['total'];

    $sql = "SELECT * FROM transfer_methods {$where} ORDER BY id DESC LIMIT ? OFFSET ?";
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
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        sendErrorResponse('Name is required');
        return;
    }

    $newId = $db->insert('transfer_methods', ['name' => $name]);
    if ($newId) {
        logUserActivity('create_{singular}', "Created {singular}: {$name}");
        sendSuccessResponse(['id' => $newId], '{singular} created successfully');
    } else {
        sendErrorResponse('Failed to create {singular}');
    }
}

/**
 * Handle PUT - update
 */
function handleUpdate($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    $name = trim($input['name'] ?? '');

    if ($id <= 0 || $name === '') {
        sendErrorResponse('ID and Name are required');
        return;
    }

    $updated = $db->query("UPDATE transfer_methods SET name = ? WHERE id = ?", [$name, $id]);
    if ($updated->rowCount() > 0) {
        logUserActivity('update_{singular}', "Updated {singular}: {$id}");
        sendSuccessResponse(['id' => $id], '{singular} updated successfully');
    } else {
        sendErrorResponse('Failed to update {singular}');
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

    $deleted = $db->query("DELETE FROM transfer_methods WHERE id = ?", [$id]);
    if ($deleted->rowCount() > 0) {
        logUserActivity('delete_{singular}', "Deleted {singular}: {$id}");
        sendSuccessResponse(null, '{singular} deleted successfully');
    } else {
        sendErrorResponse('Failed to delete {singular}');
    }
}
?>
