<?php
require_once __DIR__ . '/../../config/app.php';

// Check authentication
requireAuth();

// Check permissions
requireRole(ROLE_ADMIN);

try {
    $db = getDB();
    
    // Get roles with user counts
    $query = "
        SELECT 
            r.id,
            r.name,
            r.description,
            r.created_at,
            COUNT(u.id) as user_count,
            JSON_ARRAY_LENGTH(r.permissions) as permission_count
        FROM roles r
        LEFT JOIN users u ON r.name = u.role
        GROUP BY r.id, r.name, r.description, r.created_at
        ORDER BY 
            CASE r.name
                WHEN 'admin' THEN 1
                WHEN 'manager' THEN 2
                WHEN 'trader' THEN 3
                WHEN 'analyst' THEN 4
                WHEN 'viewer' THEN 5
                ELSE 6
            END,
            r.name
    ";
    
    $stmt = $db->query($query);
    $roles = $stmt->fetchAll();
    
    // If no roles exist, create default roles
    if (empty($roles)) {
        $defaultRoles = [
            [
                'name' => ROLE_ADMIN,
                'description' => 'System Administrator with full access',
                'permissions' => json_encode(['*'])
            ],
            [
                'name' => ROLE_MANAGER,
                'description' => 'Manager with oversight capabilities',
                'permissions' => json_encode([
                    'view_dashboard', 'manage_users', 'view_trades', 'manage_trades',
                    'view_operations', 'manage_operations', 'view_risk', 'manage_risk',
                    'view_master_data', 'manage_master_data', 'generate_reports'
                ])
            ],
            [
                'name' => ROLE_TRADER,
                'description' => 'Trader with trading capabilities',
                'permissions' => json_encode([
                    'view_dashboard', 'view_trades', 'create_trade', 'edit_trade',
                    'view_operations', 'view_risk', 'view_master_data'
                ])
            ],
            [
                'name' => ROLE_ANALYST,
                'description' => 'Analyst with reporting capabilities',
                'permissions' => json_encode([
                    'view_dashboard', 'view_trades', 'view_operations', 'view_risk',
                    'generate_reports', 'view_master_data'
                ])
            ],
            [
                'name' => ROLE_VIEWER,
                'description' => 'Viewer with read-only access',
                'permissions' => json_encode([
                    'view_dashboard', 'view_trades', 'view_operations', 'view_risk',
                    'view_master_data'
                ])
            ]
        ];
        
        foreach ($defaultRoles as $role) {
            $db->insert('roles', [
                'name' => $role['name'],
                'description' => $role['description'],
                'permissions' => $role['permissions'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Fetch roles again after creating defaults
        $stmt = $db->query($query);
        $roles = $stmt->fetchAll();
    }
    
    // Log activity
    logUserActivity(getCurrentUserId(), 'view_roles', 'Viewed roles list');
    
    // Send success response
    $response = [
        'success' => true,
        'data' => $roles
    ];
    
} catch (Exception $e) {
    error_log("Error in users/roles.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Failed to load roles'
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 