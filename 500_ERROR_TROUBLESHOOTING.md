# 🚨 500 Internal Server Error - Troubleshooting Guide

## **The 500 Internal Server Error Fix for cPanel**

### **🔍 Common Causes & Solutions**

The 500 Internal Server Error is usually caused by one of these issues on cPanel shared hosting:

---

## **🔥 IMMEDIATE FIXES (Try These First)**

### **1. Fix .htaccess File Issues**

**Problem:** Complex .htaccess rules cause conflicts on shared hosting.

**Solution:** Replace your current `.htaccess` file with the simplified version:

1. **Backup your current .htaccess:**
   ```bash
   # In cPanel File Manager, rename .htaccess to .htaccess_backup
   ```

2. **Upload the new .htaccess_cpanel file:**
   ```bash
   # Rename .htaccess_cpanel to .htaccess
   ```

3. **If that still fails, use the minimal version:**
   ```bash
   # Rename .htaccess_minimal to .htaccess
   ```

### **2. Fix Configuration Issues**

**Problem:** Hardcoded localhost URLs and development settings.

**Solution:** Update your configuration files:

1. **Replace config/app.php:**
   ```bash
   # Rename config/app_production.php to config/app.php
   ```

2. **Update your database credentials in config/database.php**

---

## **📋 Step-by-Step Debugging Process**

### **Step 1: Check Error Logs**

1. **In cPanel, go to "Error Logs"**
2. **Look for recent entries** when you tried to access your site
3. **Common error patterns:**
   ```
   [error] Invalid command 'Header'
   [error] .htaccess: Invalid command 'Order'
   [error] PHP Parse error: syntax error
   [error] PHP Fatal error: require_once(): failed opening
   ```

### **Step 2: Test with Minimal Configuration**

1. **Create a simple test file** `test.php`:
   ```php
   <?php
   echo "PHP is working!";
   phpinfo();
   ?>
   ```

2. **Access:** `https://yourdomain.com/test.php`
3. **If this works:** PHP is fine, issue is in your application
4. **If this fails:** Server configuration problem

### **Step 3: Progressive Testing**

1. **Start with minimal .htaccess:**
   ```apache
   RewriteEngine On
   DirectoryIndex index.php
   Options -Indexes
   ```

2. **Test basic index.php access**

3. **Gradually add back .htaccess rules** until you find the problematic one

---

## **🔧 Specific Configuration Fixes**

### **Fix 1: Apache Version Compatibility**

**Replace deprecated Apache directives:**

❌ **Old (causes 500 errors):**
```apache
<Files "*.log">
    Order allow,deny
    Deny from all
</Files>
```

✅ **New (cPanel compatible):**
```apache
<Files "*.log">
    Require all denied
</Files>
```

### **Fix 2: PHP Settings**

**Problem:** PHP directives in .htaccess not allowed on shared hosting.

❌ **Remove these from .htaccess:**
```apache
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
```

✅ **Set these via cPanel "PHP Configuration" instead**

### **Fix 3: Module Dependencies**

**Problem:** Missing or disabled Apache modules.

**Check these modules are enabled:**
- mod_rewrite
- mod_deflate (optional)
- mod_expires (optional)
- mod_headers (often disabled on shared hosting)

---

## **📁 File Permission Issues**

### **Set Correct Permissions:**

```bash
# Files
chmod 644 *.php
chmod 644 *.html
chmod 644 .htaccess

# Directories
chmod 755 api/
chmod 755 assets/
chmod 755 config/
chmod 755 includes/
chmod 755 uploads/
chmod 755 logs/
```

### **Special Permissions:**
```bash
# Writable directories
chmod 777 uploads/
chmod 777 logs/
chmod 755 config/ (readable only)
```

---

## **🐛 PHP Version & Extensions**

### **Check PHP Version:**
1. **In cPanel, go to "Select PHP Version"**
2. **Use PHP 7.4 or higher**
3. **Enable these extensions:**
   - PDO
   - PDO_MySQL
   - mbstring
   - openssl
   - curl
   - gd (optional)

### **Check for Syntax Errors:**
```bash
# If you have SSH access, run:
php -l index.php
php -l config/app.php
php -l config/database.php
```

---

## **🔍 Database Connection Issues**

### **Test Database Connection:**

Create `test_db.php`:
```php
<?php
try {
    $dsn = "mysql:host=localhost;dbname=YOUR_DB_NAME;charset=utf8mb4";
    $pdo = new PDO($dsn, 'YOUR_DB_USER', 'YOUR_DB_PASS');
    echo "Database connection successful!";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>
```

**Common database issues:**
- Incorrect database name format
- Wrong user permissions
- Database server down

---

## **⚡ Quick Emergency Fix**

**If nothing else works, try this minimal setup:**

1. **Create emergency index.php:**
   ```php
   <?php
   echo "<h1>ETRM System</h1>";
   echo "<p>Basic test page</p>";
   echo "<p>PHP Version: " . phpversion() . "</p>";
   ?>
   ```

2. **Use minimal .htaccess:**
   ```apache
   RewriteEngine On
   DirectoryIndex index.php
   Options -Indexes
   ```

3. **Test this works, then gradually add back features**

---

## **📞 Hosting Provider Issues**

### **Contact your hosting provider if:**
- Test PHP file doesn't work
- Error logs show server configuration errors
- Apache modules are missing
- PHP extensions can't be enabled

### **Questions to ask your hosting provider:**
1. "Is mod_rewrite enabled on my account?"
2. "What PHP version and extensions are available?"
3. "Are there any restrictions on .htaccess directives?"
4. "Can you check the server error logs for my domain?"

---

## **✅ Final Checklist**

- [ ] Used .htaccess_cpanel or .htaccess_minimal
- [ ] Updated config/app.php with production settings
- [ ] Configured database credentials correctly
- [ ] Set proper file permissions
- [ ] PHP 7.4+ with PDO extensions enabled
- [ ] Database connection tested
- [ ] Error logs checked
- [ ] Test.php file works

---

## **🎯 Most Common Solution**

**90% of 500 errors on cPanel are fixed by:**

1. **Replace .htaccess** with the simplified version
2. **Update config/app.php** with dynamic URL detection
3. **Set correct file permissions**
4. **Check database credentials**

**Try these four steps first before diving deeper!**

---

**💡 Remember:** Always check the cPanel Error Logs first - they will tell you exactly what's causing the 500 error! 