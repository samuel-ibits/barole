# ETRM System - cPanel Deployment Guide

## 🚀 Complete Guide to Deploy ETRM2 on cPanel

### **Prerequisites**
- ✅ cPanel hosting account with PHP 7.4+ and MySQL support
- ✅ Domain name configured in cPanel
- ✅ Access to cPanel File Manager or FTP
- ✅ MySQL database creation privileges

---

## **📋 Step 1: Prepare Your Files for Upload**

### **1.1 Create Deployment Package**
```bash
# Create a clean deployment folder (run this on your local machine)
mkdir ETRM_Deploy
cp -r api assets config includes uploads templates docs ETRM_Deploy/
cp index.php login.php logout.php status.php install.php README.md .htaccess ETRM_Deploy/
```

### **1.2 Files to EXCLUDE from Upload**
❌ **DO NOT UPLOAD:**
- `database/` folder (SQLite files)
- `logs/` folder (development logs)
- `plan.md` / `IMPLEMENTATION_PLAN.md`
- `server.log`
- `install_sample_data.php`
- `installed.lock`

---

## **📋 Step 2: cPanel Database Setup**

### **2.1 Create MySQL Database**
1. **Login to cPanel**
2. **Go to "MySQL Databases"**
3. **Create Database:**
   - Database Name: `your_username_etrm` (example: `mysite_etrm`)
   - Click "Create Database"

### **2.2 Create Database User**
1. **In MySQL Databases section:**
   - Username: `your_username_etrm_user`
   - Password: `[Generate Strong Password]`
   - Click "Create User"

### **2.3 Assign User to Database**
1. **Add User to Database:**
   - Select your user and database
   - Grant **ALL PRIVILEGES**
   - Click "Make Changes"

### **2.4 Note Your Database Credentials**
```
Database Host: localhost
Database Name: your_username_etrm
Database User: your_username_etrm_user
Database Pass: [your_password]
```

---

## **📋 Step 3: Upload Files to cPanel**

### **3.1 Using cPanel File Manager**
1. **Open File Manager** in cPanel
2. **Navigate to** `public_html` (or your domain folder)
3. **Upload** your prepared files:
   - Upload as ZIP file for faster transfer
   - Extract in the target folder
4. **Set Folder Permissions:**
   - `uploads/` → 755 or 777
   - `logs/` → 755 (create this folder)
   - `config/` → 644

### **3.2 Using FTP (Alternative)**
```bash
# FTP Upload (if you prefer FTP)
ftp your-domain.com
# Upload all files to public_html or domain folder
```

---

## **📋 Step 4: Configure Database for Production**

### **4.1 Update Database Configuration**
1. **Rename the production config:**
   ```bash
   # In cPanel File Manager or via FTP
   mv config/database.production.php config/database.php
   ```

2. **Edit `config/database.php`** with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_username_etrm');        // Your actual DB name
   define('DB_USER', 'your_username_etrm_user');   // Your actual DB user
   define('DB_PASS', 'your_actual_password');      // Your actual password
   define('DB_CHARSET', 'utf8mb4');
   define('DB_TYPE', 'mysql');
   ```

### **4.2 Import Database Schema**

⚠️ **IMPORTANT: Use the cPanel-compatible schema file**

1. **Open cPanel phpMyAdmin**
2. **Select your ETRM database** (the one you created in Step 2)
3. **Go to "Import" tab**
4. **Upload** `schema_cpanel.sql` file (NOT the regular schema.sql)
5. **Click "Go" to import**

> **Note:** The `schema_cpanel.sql` file is specifically designed for cPanel hosting and removes the `CREATE DATABASE` commands that cause permission errors.

### **4.3 Import Sample Data (Optional)**
1. **In phpMyAdmin "Import" tab**
2. **Upload** `sample_data_fixed.sql`
3. **Click "Go" to import**

### **4.4 Common Database Import Issues**

#### **Issue: "#1044 - Access denied to database"**
```
❌ Error: CREATE DATABASE commands not allowed in shared hosting
✅ Solution: Use schema_cpanel.sql instead of schema.sql
```

#### **Issue: Foreign key constraint errors**
```
✅ Solution: Import tables in the correct order (already handled in schema_cpanel.sql)
```

#### **Issue: JSON column errors**
```
✅ Solution: Ensure MySQL version 5.7+ is selected in cPanel
```

---

## **📋 Step 5: Configure Application Settings**

### **5.1 Update App Configuration**
Edit `config/app.php` if needed:

```php
// Update these for production
define('APP_ENV', 'production');
define('APP_DEBUG', false);
define('APP_URL', 'https://yourdomain.com');
```

### **5.2 Create Required Directories**
```bash
# Create in cPanel File Manager or FTP
mkdir logs
mkdir uploads/reports
chmod 755 uploads
chmod 755 uploads/reports
chmod 755 logs
```

### **5.3 Security Configuration**
Ensure `.htaccess` is uploaded and contains:
```apache
# Already included in your .htaccess
RewriteEngine On
DirectoryIndex index.php

# Block access to sensitive files
<Files "*.log">
    Deny from all
</Files>
<Files "config/*">
    Deny from all
</Files>
```

---

## **📋 Step 6: Test Your Deployment**

### **6.1 Access Your Application**
1. **Visit:** `https://yourdomain.com`
2. **You should see:** ETRM login page
3. **Test login with:**
   - Username: `admin`
   - Password: `admin123`

### **6.2 Verify Functionality**
- ✅ Login works
- ✅ Dashboard loads
- ✅ Navigation between tabs works
- ✅ Data loads (if sample data imported)
- ✅ Reports generation works

### **6.3 Database Connection Test**
If you see database connection errors:
1. **Check** `config/database.php` credentials
2. **Verify** database name format (usually `username_dbname`)
3. **Confirm** user has ALL PRIVILEGES on the database

---

## **📋 Step 7: Production Optimization**

### **7.1 SSL Certificate**
1. **Enable SSL** in cPanel (Let's Encrypt)
2. **Force HTTPS** by updating .htaccess:
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

### **7.2 Error Logging**
1. **Enable PHP error logging** in cPanel
2. **Monitor** `logs/php_errors.log` for issues

### **7.3 Backup Setup**
1. **Enable automatic backups** in cPanel
2. **Export database** regularly via phpMyAdmin

---

## **🔧 Troubleshooting Common Issues**

### **Database Connection Errors**
```php
// Check these in config/database.php:
- Correct database name format (username_dbname)
- Correct user permissions
- MySQL service running
```

### **Database Import Errors**
```sql
-- Error: "#1044 - Access denied for user to database"
-- Solution: Use schema_cpanel.sql (no CREATE DATABASE commands)

-- Error: "Unknown column type 'JSON'"
-- Solution: Select MySQL 5.7+ in cPanel PHP settings
```

### **File Permission Issues**
```bash
# Fix in cPanel File Manager:
uploads/ → 755
logs/ → 755
config/ → 644
```

### **.htaccess Issues**
```apache
# If mod_rewrite not working, contact hosting provider
# Some hosts require explicit AllowOverride All
```

### **PHP Version**
- Ensure PHP 7.4+ is selected in cPanel
- Check PHP extensions: PDO, PDO_MySQL enabled

---

## **📞 Post-Deployment Checklist**

- [ ] Database connected successfully
- [ ] Used schema_cpanel.sql for import
- [ ] Login system working
- [ ] All tabs/pages loading
- [ ] File uploads working (if applicable)
- [ ] SSL certificate active
- [ ] Error logging enabled
- [ ] Backup system configured
- [ ] Admin password changed from default

---

## **🔐 Security Recommendations**

1. **Change default admin password immediately**
2. **Remove or rename install.php after deployment**
3. **Set proper file permissions**
4. **Enable SSL/HTTPS**
5. **Regular security updates**
6. **Monitor error logs**

---

## **📱 Support**

If you encounter issues:
1. Check cPanel error logs
2. Verify database credentials
3. Use schema_cpanel.sql for database import
4. Contact your hosting provider for server-specific issues
5. Check PHP version and extensions

---

**🎉 Your ETRM System should now be live on your domain!** 