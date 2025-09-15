# ETRM System - PHP/jQuery Rebuild

## Project Overview
Complete rebuild of the Energy Trading and Risk Management (ETRM) system from vanilla JavaScript to PHP/jQuery while preserving all existing functionality.

## Technology Stack
- **Backend**: PHP 8.1+ with MySQL 8.0+
- **Frontend**: jQuery 3.6+, Bootstrap 5, Chart.js 3.9.1
- **Database**: MySQL with proper normalization
- **Server**: Apache/Nginx
- **Security**: PHP Sessions, CSRF protection, input validation

## Project Structure
```
ETRM2/
├── config/                 # Configuration files
├── database/              # Database schema and migrations
├── api/                   # PHP API endpoints
├── assets/                # Static assets (CSS, JS, images)
├── includes/              # PHP includes and classes
├── templates/             # HTML templates
├── uploads/               # File uploads
├── logs/                  # Application logs
└── docs/                  # Documentation
```

## Installation

### Prerequisites
- PHP 8.1 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- Composer (optional, for dependency management)

### Quick Installation

1. **Clone or download the project**
   ```bash
   git clone <repository-url>
   cd ETRM2
   ```

2. **Configure database connection**
   Edit `config/database.php` and update the database settings:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'etrm_system');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

3. **Create MySQL database**
   ```sql
   CREATE DATABASE etrm_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'etrm_user'@'localhost' IDENTIFIED BY 'secure_password_here';
   GRANT ALL PRIVILEGES ON etrm_system.* TO 'etrm_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

4. **Run the installation script**
   Visit `http://your-domain/install.php` in your browser
   This will:
   - Create all database tables
   - Set up initial data
   - Create required directories
   - Create default admin user

5. **Set file permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 logs/
   chmod 755 backups/
   chmod 644 config/database.php
   ```

6. **Access the system**
   - URL: `http://your-domain/`
   - Default login: `admin` / `admin123`
   - **Important**: Change the default password immediately

### Manual Installation

If you prefer manual installation:

1. Import the database schema:
   ```bash
   mysql -u your_username -p etrm_system < database/schema.sql
   ```

2. Create required directories:
   ```bash
   mkdir -p uploads/documents uploads/reports logs backups
   chmod 755 uploads logs backups
   ```

3. Configure your web server to point to the project directory

4. Access the system and log in with default credentials

### Security Considerations

- Change default admin password immediately
- Configure HTTPS in production
- Set up proper file permissions
- Regularly backup the database
- Keep PHP and MySQL updated
- Monitor log files for security issues

## Features
- Multi-role user authentication (Admin, Manager, Trader, Analyst, Viewer)
- Trading operations (Physical Sales, Financial Trades, FX Trades)
- Operations management (Invoices, Logistics, Settlements)
- Risk & Analytics (Portfolio analysis, VaR calculations, Reports)
- Master data management (Counterparties, Products, Business Units)
- Configurable dashboard with real-time updates
- Comprehensive audit logging
- Mobile-responsive design

## Security Features
- Password hashing with bcrypt
- CSRF protection
- Input validation and sanitization
- SQL injection prevention
- XSS protection
- Role-based access control
- Session management

## Development Status
- [x] Project structure setup
- [ ] Database schema creation
- [ ] Authentication system
- [ ] User management
- [ ] Trading operations
- [ ] Operations management
- [ ] Risk & analytics
- [ ] Dashboard system
- [ ] Frontend implementation
- [ ] Testing and deployment

## License
Proprietary - All rights reserved 