<?php
/**
 * Simple Login Test Page
 * Bypasses complex session management for testing
 */

// Start simple session
session_start();

// Basic database connection for testing
require_once 'config/database.php';

$error = '';
$success = '';

// Handle login
if ($_POST['username'] ?? false) {
    $username = trim($_POST['username']);
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        try {
            $db = getDB();
            $stmt = $db->query("SELECT * FROM users WHERE username = ? AND status = 'active'", [$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                
                $success = "Login successful! Redirecting...";
                header("refresh:2;url=index.php");
            } else {
                $error = "Invalid username or password";
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Please enter username and password";
    }
}

// Check if already logged in
if (($_SESSION['logged_in'] ?? false) && !$success) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>ETRM System - Simple Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-container { background: white; border-radius: 15px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1); max-width: 400px; width: 100%; }
        .login-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; text-align: center; border-radius: 15px 15px 0 0; }
        .login-body { padding: 2rem; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>ETRM System</h2>
            <p class="mb-0">Simple Login Test</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? 'admin') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required placeholder="admin123">
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            
            <div class="mt-3 text-center">
                <small class="text-muted">
                    Use this if the main login page has session errors.<br>
                    Default: admin / admin123
                </small>
            </div>
        </div>
    </div>
</body>
</html> 