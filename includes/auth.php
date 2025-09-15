<?php
/**
 * Authentication System
 * ETRM System - User authentication and management
 */

// Load configuration
require_once CONFIG_PATH . '/app.php';

/**
 * Authenticate user login
 */
function authenticateUser($username, $password) {
    try {
        $db = getDB();
        
        // Get user by username
        $stmt = $db->query(
            "SELECT * FROM users WHERE username = ? AND status = 'active'",
            [$username]
        );
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'error' => 'Invalid username or password'];
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            // Log failed login attempt
            logFailedLogin($username);
            return ['success' => false, 'error' => 'Invalid username or password'];
        }
        
        // Check if account is locked
        if (isAccountLocked($username)) {
            return ['success' => false, 'error' => 'Account is temporarily locked due to multiple failed login attempts'];
        }
        
        // Create user session
        if (createUserSession($user['id'], $user['username'], $user['role'])) {
            // Log successful login
            logUserActivity($user['id'], 'login', 'User logged in successfully');
            
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role'],
                    'department' => $user['department']
                ]
            ];
        } else {
            return ['success' => false, 'error' => 'Failed to create user session'];
        }
        
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Authentication failed'];
    }
}

/**
 * Log failed login attempt
 */
function logFailedLogin($username) {
    try {
        $db = getDB();
        
        // Get user ID
        $user = $db->fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
        if ($user) {
            logUserActivity($user['id'], 'login_failed', 'Failed login attempt', getClientIP());
        }
        
        // Store failed attempt in session for rate limiting
        if (!isset($_SESSION['failed_logins'])) {
            $_SESSION['failed_logins'] = [];
        }
        
        if (!isset($_SESSION['failed_logins'][$username])) {
            $_SESSION['failed_logins'][$username] = ['count' => 0, 'first_attempt' => time()];
        }
        
        $_SESSION['failed_logins'][$username]['count']++;
        $_SESSION['failed_logins'][$username]['last_attempt'] = time();
        
    } catch (Exception $e) {
        error_log("Failed to log failed login: " . $e->getMessage());
    }
}

/**
 * Check if account is locked
 */
function isAccountLocked($username) {
    if (!isset($_SESSION['failed_logins'][$username])) {
        return false;
    }
    
    $failedLogin = $_SESSION['failed_logins'][$username];
    $maxAttempts = LOGIN_MAX_ATTEMPTS;
    $lockoutTime = LOGIN_LOCKOUT_TIME;
    
    if ($failedLogin['count'] >= $maxAttempts) {
        $timeSinceFirstAttempt = time() - $failedLogin['first_attempt'];
        
        if ($timeSinceFirstAttempt < $lockoutTime) {
            return true;
        } else {
            // Reset failed login count after lockout period
            unset($_SESSION['failed_logins'][$username]);
        }
    }
    
    return false;
}

/**
 * Logout user
 */
function logoutUser() {
    $userId = getCurrentUserId();
    
    if ($userId) {
        logUserActivity($userId, 'logout', 'User logged out');
    }
    
    destroyUserSession();
    
    // Clear failed login attempts
    if (isset($_SESSION['failed_logins'])) {
        unset($_SESSION['failed_logins']);
    }
}

/**
 * Change user password
 */
function changePassword($userId, $currentPassword, $newPassword) {
    try {
        $db = getDB();
        
        // Get current user
        $user = $db->fetchOne("SELECT password_hash FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Current password is incorrect'];
        }
        
        // Validate new password
        if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'error' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long'];
        }
        
        // Hash new password
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password
        $db->update('users', ['password_hash' => $newPasswordHash], 'id = ?', [$userId]);
        
        // Log password change
        logUserActivity($userId, 'password_change', 'Password changed successfully');
        
        return ['success' => true, 'message' => 'Password changed successfully'];
        
    } catch (Exception $e) {
        error_log("Password change error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to change password'];
    }
}

/**
 * Reset user password (admin function)
 */
function resetUserPassword($userId, $newPassword) {
    try {
        $db = getDB();
        
        // Validate new password
        if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'error' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long'];
        }
        
        // Hash new password
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password
        $db->update('users', ['password_hash' => $newPasswordHash], 'id = ?', [$userId]);
        
        // Log password reset
        logUserActivity($userId, 'password_reset', 'Password reset by administrator');
        
        return ['success' => true, 'message' => 'Password reset successfully'];
        
    } catch (Exception $e) {
        error_log("Password reset error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to reset password'];
    }
}

/**
 * Create new user
 */
function createUser($userData) {
    try {
        $db = getDB();
        
        // Validate required fields
        $requiredFields = ['username', 'email', 'full_name', 'role'];
        foreach ($requiredFields as $field) {
            if (empty($userData[$field])) {
                return ['success' => false, 'error' => ucfirst($field) . ' is required'];
            }
        }
        
        // Validate email
        if (!validateEmail($userData['email'])) {
            return ['success' => false, 'error' => 'Invalid email address'];
        }
        
        // Check if username already exists
        $existingUser = $db->fetchOne("SELECT id FROM users WHERE username = ?", [$userData['username']]);
        if ($existingUser) {
            return ['success' => false, 'error' => 'Username already exists'];
        }
        
        // Check if email already exists
        $existingEmail = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$userData['email']]);
        if ($existingEmail) {
            return ['success' => false, 'error' => 'Email address already exists'];
        }
        
        // Generate default password
        $defaultPassword = generateRandomString(12);
        $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);
        
        // Prepare user data
        $userInsertData = [
            'username' => $userData['username'],
            'password_hash' => $passwordHash,
            'email' => $userData['email'],
            'full_name' => $userData['full_name'],
            'department' => $userData['department'] ?? '',
            'role' => $userData['role'],
            'status' => $userData['status'] ?? USER_STATUS_ACTIVE,
            'permissions' => json_encode(getRolePermissions($userData['role']))
        ];
        
        // Insert user
        $userId = $db->insert('users', $userInsertData);
        
        // Log user creation
        logUserActivity(getCurrentUserId(), 'user_create', "Created user: {$userData['username']}");
        
        return [
            'success' => true,
            'message' => 'User created successfully',
            'user_id' => $userId,
            'default_password' => $defaultPassword
        ];
        
    } catch (Exception $e) {
        error_log("User creation error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to create user'];
    }
}

/**
 * Update user
 */
function updateUser($userId, $userData) {
    try {
        $db = getDB();
        
        // Check if user exists
        $existingUser = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
        if (!$existingUser) {
            return ['success' => false, 'error' => 'User not found'];
        }
        
        // Validate email if provided
        if (isset($userData['email']) && !validateEmail($userData['email'])) {
            return ['success' => false, 'error' => 'Invalid email address'];
        }
        
        // Check if email already exists (excluding current user)
        if (isset($userData['email'])) {
            $existingEmail = $db->fetchOne(
                "SELECT id FROM users WHERE email = ? AND id != ?",
                [$userData['email'], $userId]
            );
            if ($existingEmail) {
                return ['success' => false, 'error' => 'Email address already exists'];
            }
        }
        
        // Prepare update data
        $updateData = [];
        $allowedFields = ['email', 'full_name', 'department', 'role', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($userData[$field])) {
                $updateData[$field] = $userData[$field];
            }
        }
        
        // Update permissions if role changed
        if (isset($userData['role'])) {
            $updateData['permissions'] = json_encode(getRolePermissions($userData['role']));
        }
        
        if (empty($updateData)) {
            return ['success' => false, 'error' => 'No valid fields to update'];
        }
        
        // Update user
        $db->update('users', $updateData, 'id = ?', [$userId]);
        
        // Log user update
        logUserActivity(getCurrentUserId(), 'user_update', "Updated user: {$existingUser['username']}");
        
        return ['success' => true, 'message' => 'User updated successfully'];
        
    } catch (Exception $e) {
        error_log("User update error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to update user'];
    }
}

/**
 * Delete user
 */
function deleteUser($userId) {
    try {
        $db = getDB();
        
        // Check if user exists
        $user = $db->fetchOne("SELECT username FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }
        
        // Prevent deletion of admin users
        $adminUser = $db->fetchOne("SELECT role FROM users WHERE id = ?", [$userId]);
        if ($adminUser && $adminUser['role'] === ROLE_ADMIN) {
            return ['success' => false, 'error' => 'Cannot delete administrator users'];
        }
        
        // Delete user
        $db->delete('users', 'id = ?', [$userId]);
        
        // Log user deletion
        logUserActivity(getCurrentUserId(), 'user_delete', "Deleted user: {$user['username']}");
        
        return ['success' => true, 'message' => 'User deleted successfully'];
        
    } catch (Exception $e) {
        error_log("User deletion error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to delete user'];
    }
}

/**
 * Get user by ID
 */
function getUserById($userId) {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM users WHERE id = ?", [$userId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Get user error: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all users with pagination
 */
function getUsers($page = 1, $pageSize = DEFAULT_PAGE_SIZE, $filters = []) {
    try {
        $db = getDB();
        
        $whereConditions = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters['role'])) {
            $whereConditions[] = "role = ?";
            $params[] = $filters['role'];
        }
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM users {$whereClause}";
        $totalResult = $db->fetchOne($countSql, $params);
        $totalRecords = $totalResult['total'];
        
        // Calculate pagination
        $offset = ($page - 1) * $pageSize;
        
        // Get users
        $sql = "SELECT id, username, email, full_name, department, role, status, created_at, last_login 
                FROM users {$whereClause} 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $pageSize;
        $params[] = $offset;
        
        $users = $db->fetchAll($sql, $params);
        
        return [
            'users' => $users,
            'total_records' => $totalRecords,
            'current_page' => $page,
            'page_size' => $pageSize,
            'total_pages' => ceil($totalRecords / $pageSize)
        ];
        
    } catch (Exception $e) {
        error_log("Get users error: " . $e->getMessage());
        return ['users' => [], 'total_records' => 0, 'current_page' => 1, 'page_size' => $pageSize, 'total_pages' => 0];
    }
}

/**
 * Get user activity logs
 */
function getUserActivityLogs($userId = null, $page = 1, $pageSize = DEFAULT_PAGE_SIZE) {
    try {
        $db = getDB();
        
        $whereConditions = [];
        $params = [];
        
        if ($userId) {
            $whereConditions[] = "user_id = ?";
            $params[] = $userId;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM user_activity_logs {$whereClause}";
        $totalResult = $db->fetchOne($countSql, $params);
        $totalRecords = $totalResult['total'];
        
        // Calculate pagination
        $offset = ($page - 1) * $pageSize;
        
        // Get logs
        $sql = "SELECT l.*, u.username, u.full_name 
                FROM user_activity_logs l 
                LEFT JOIN users u ON l.user_id = u.id 
                {$whereClause} 
                ORDER BY l.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $pageSize;
        $params[] = $offset;
        
        $logs = $db->fetchAll($sql, $params);
        
        return [
            'logs' => $logs,
            'total_records' => $totalRecords,
            'current_page' => $page,
            'page_size' => $pageSize,
            'total_pages' => ceil($totalRecords / $pageSize)
        ];
        
    } catch (Exception $e) {
        error_log("Get activity logs error: " . $e->getMessage());
        return ['logs' => [], 'total_records' => 0, 'current_page' => 1, 'page_size' => $pageSize, 'total_pages' => 0];
    }
} 