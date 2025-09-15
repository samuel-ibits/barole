<?php
require_once __DIR__ . '/../../config/app.php';

// Check authentication
requireAuth();

// Check permissions
requireRole(ROLE_ADMIN);

try {
    $db = getDB();
    
    // Get permissions organized by category
    $query = "
        SELECT 
            id,
            name,
            description,
            category,
            created_at
        FROM permissions
        ORDER BY 
            CASE category
                WHEN 'system' THEN 1
                WHEN 'trading' THEN 2
                WHEN 'operations' THEN 3
                WHEN 'risk' THEN 4
                WHEN 'master_data' THEN 5
                WHEN 'reports' THEN 6
                ELSE 7
            END,
            category,
            name
    ";
    
    $stmt = $db->query($query);
    $permissions = $stmt->fetchAll();
    
    // If no permissions exist, create default permissions
    if (empty($permissions)) {
        $defaultPermissions = [
            // System permissions
            ['name' => 'view_dashboard', 'description' => 'View dashboard', 'category' => 'system'],
            ['name' => 'manage_users', 'description' => 'Manage users', 'category' => 'system'],
            ['name' => 'manage_roles', 'description' => 'Manage roles', 'category' => 'system'],
            ['name' => 'manage_permissions', 'description' => 'Manage permissions', 'category' => 'system'],
            ['name' => 'view_activity', 'description' => 'View user activity', 'category' => 'system'],
            
            // Trading permissions
            ['name' => 'view_trades', 'description' => 'View trades', 'category' => 'trading'],
            ['name' => 'create_trade', 'description' => 'Create trades', 'category' => 'trading'],
            ['name' => 'edit_trade', 'description' => 'Edit trades', 'category' => 'trading'],
            ['name' => 'delete_trade', 'description' => 'Delete trades', 'category' => 'trading'],
            ['name' => 'manage_trades', 'description' => 'Manage all trades', 'category' => 'trading'],
            ['name' => 'approve_trades', 'description' => 'Approve trades', 'category' => 'trading'],
            
            // Operations permissions
            ['name' => 'view_operations', 'description' => 'View operations', 'category' => 'operations'],
            ['name' => 'manage_operations', 'description' => 'Manage operations', 'category' => 'operations'],
            ['name' => 'create_invoice', 'description' => 'Create invoices', 'category' => 'operations'],
            ['name' => 'edit_invoice', 'description' => 'Edit invoices', 'category' => 'operations'],
            ['name' => 'manage_logistics', 'description' => 'Manage logistics', 'category' => 'operations'],
            ['name' => 'manage_settlements', 'description' => 'Manage settlements', 'category' => 'operations'],
            
            // Risk permissions
            ['name' => 'view_risk', 'description' => 'View risk metrics', 'category' => 'risk'],
            ['name' => 'manage_risk', 'description' => 'Manage risk settings', 'category' => 'risk'],
            ['name' => 'view_portfolio', 'description' => 'View portfolio', 'category' => 'risk'],
            ['name' => 'manage_alerts', 'description' => 'Manage risk alerts', 'category' => 'risk'],
            ['name' => 'run_stress_tests', 'description' => 'Run stress tests', 'category' => 'risk'],
            
            // Master data permissions
            ['name' => 'view_master_data', 'description' => 'View master data', 'category' => 'master_data'],
            ['name' => 'manage_master_data', 'description' => 'Manage master data', 'category' => 'master_data'],
            ['name' => 'manage_counterparties', 'description' => 'Manage counterparties', 'category' => 'master_data'],
            ['name' => 'manage_products', 'description' => 'Manage products', 'category' => 'master_data'],
            ['name' => 'manage_business_units', 'description' => 'Manage business units', 'category' => 'master_data'],
            
            // Report permissions
            ['name' => 'generate_reports', 'description' => 'Generate reports', 'category' => 'reports'],
            ['name' => 'view_reports', 'description' => 'View reports', 'category' => 'reports'],
            ['name' => 'export_data', 'description' => 'Export data', 'category' => 'reports'],
            ['name' => 'schedule_reports', 'description' => 'Schedule reports', 'category' => 'reports']
        ];
        
        foreach ($defaultPermissions as $permission) {
            $db->insert('permissions', [
                'name' => $permission['name'],
                'description' => $permission['description'],
                'category' => $permission['category'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Fetch permissions again after creating defaults
        $stmt = $db->query($query);
        $permissions = $stmt->fetchAll();
    }
    
    // Log activity
    logUserActivity(getCurrentUserId(), 'view_permissions', 'Viewed permissions list');
    
    // Send success response
    $response = [
        'success' => true,
        'data' => $permissions
    ];
    
} catch (Exception $e) {
    error_log("Error in users/permissions.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Failed to load permissions'
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 