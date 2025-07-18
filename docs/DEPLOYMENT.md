# Deployment Guide

## 👤 Informasi Pribadi

- **Nama**: Nur Taliyah
- **NIM**: 202312030
- **Email**: nurtaliyah164@gmail.com

Panduan untuk deployment ShoeBrand Store E-commerce System ke production environment.

## 🚀 Pre-Deployment Checklist

### ✅ Development Checklist

- [ ] Semua fitur telah ditest
- [ ] Database schema finalized
- [ ] Sample data telah diverifikasi
- [ ] Error handling implemented
- [ ] Security measures in place
- [ ] Documentation complete

### ✅ Security Checklist

- [ ] Password hashing implemented (MD5 → bcrypt recommended)
- [ ] SQL injection protection (prepared statements)
- [ ] XSS protection implemented
- [ ] CSRF tokens added
- [ ] Session security configured
- [ ] Input validation on all forms
- [ ] File upload restrictions (if any)

---

## 🌐 Production Deployment

### 1. **Server Requirements**

#### Minimum Specifications:

- **CPU**: 2 cores, 2.4 GHz
- **RAM**: 1GB
- **Storage**: 10GB SSD
- **OS**: Ubuntu 20.04 LTS / CentOS 8

#### Software Stack:

- **Web Server**: Apache 2.4+ / Nginx 1.18+
- **PHP**: 7.4+ (recommended 8.0+)
- **Database**: MySQL 8.0+ / MariaDB 10.5+
- **SSL**: Let's Encrypt or commercial SSL

### 2. **Installation Steps**

#### A. Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install LAMP stack
sudo apt install apache2 mysql-server php php-mysql php-cli php-curl php-gd php-mbstring php-xml -y

# Enable required Apache modules
sudo a2enmod rewrite ssl headers

# Start services
sudo systemctl start apache2 mysql
sudo systemctl enable apache2 mysql
```

#### B. Database Setup

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database user
mysql -u root -p
CREATE DATABASE shoe_store;
CREATE USER 'shoeadmin'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON shoe_store.* TO 'shoeadmin'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import database (if SQL file exists)
mysql -u shoeadmin -p shoe_store < database/shoe_store.sql
```

#### C. Application Deployment

```bash
# Clone repository to web directory
cd /var/www/html
sudo git clone https://github.com/username/UAS_PWEB.git bus-management
cd bus-management

# Set proper permissions
sudo chown -R www-data:www-data /var/www/html/bus-management
sudo chmod -R 755 /var/www/html/bus-management
sudo chmod -R 775 /var/www/html/bus-management/uploads (if exists)

# Configure database connection
sudo nano config/database.php
```

#### D. Apache Virtual Host

```apache
<VirtualHost *:80>
    ServerName bus.example.com
    DocumentRoot /var/www/html/bus-management

    <Directory /var/www/html/bus-management>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/bus_error.log
    CustomLog ${APACHE_LOG_DIR}/bus_access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName bus.example.com
    DocumentRoot /var/www/html/bus-management

    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key

    <Directory /var/www/html/bus-management>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 3. **Environment Configuration**

#### A. Production Database Config

```php
<?php
// config/database.php - Production
define('DB_HOST', 'localhost');
define('DB_USER', 'busadmin');
define('DB_PASS', 'your_secure_password');
define('DB_NAME', 'bus_management');

// Production settings
define('ENVIRONMENT', 'production');
define('DEBUG_MODE', false);
define('LOG_ERRORS', true);
?>
```

#### B. PHP Configuration

```ini
; /etc/php/8.0/apache2/php.ini
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
session.cookie_secure = On
session.cookie_httponly = On
upload_max_filesize = 5M
post_max_size = 10M
memory_limit = 256M
```

---

## 🔒 Security Hardening

### 1. **Database Security**

```sql
-- Remove test users and databases
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;

-- Create backup user
CREATE USER 'backup'@'localhost' IDENTIFIED BY 'backup_password';
GRANT SELECT, LOCK TABLES ON bus_management.* TO 'backup'@'localhost';
```

### 2. **File Permissions**

```bash
# Set restrictive permissions
find /var/www/html/bus-management -type f -exec chmod 644 {} \;
find /var/www/html/bus-management -type d -exec chmod 755 {} \;

# Protect sensitive files
chmod 600 /var/www/html/bus-management/config/database.php
chmod 600 /var/www/html/bus-management/.env (if exists)
```

### 3. **Apache Security**

```apache
# .htaccess security headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options DENY
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set Content-Security-Policy "default-src 'self'"
</IfModule>

# Hide sensitive files
<Files "*.sql">
    Order allow,deny
    Deny from all
</Files>

<Files "config/*.php">
    Order allow,deny
    Deny from all
</Files>
```

---

## 📊 Monitoring & Maintenance

### 1. **Backup Strategy**

#### Daily Database Backup

```bash
#!/bin/bash
# /etc/cron.daily/mysql-backup

DATE=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/backup/mysql"
DB_NAME="bus_management"

mkdir -p $BACKUP_DIR

mysqldump -u backup -p'backup_password' $DB_NAME > $BACKUP_DIR/bus_management_$DATE.sql

# Keep only last 7 days
find $BACKUP_DIR -name "bus_management_*.sql" -mtime +7 -delete

# Compress old backups
find $BACKUP_DIR -name "bus_management_*.sql" -mtime +1 -exec gzip {} \;
```

#### Weekly File Backup

```bash
#!/bin/bash
# /etc/cron.weekly/file-backup

DATE=$(date +"%Y%m%d")
tar -czf /backup/files/bus_management_$DATE.tar.gz /var/www/html/bus-management

# Keep only last 4 weeks
find /backup/files -name "bus_management_*.tar.gz" -mtime +28 -delete
```

### 2. **Log Monitoring**

```bash
# Check Apache error logs
sudo tail -f /var/log/apache2/bus_error.log

# Check PHP errors
sudo tail -f /var/log/php_errors.log

# Check MySQL slow queries
sudo tail -f /var/log/mysql/slow.log
```

### 3. **Performance Monitoring**

```bash
# Monitor disk usage
df -h

# Monitor memory usage
free -h

# Monitor MySQL processes
mysqladmin -u root -p processlist

# Check Apache status
sudo systemctl status apache2
```

---

## 🔧 Troubleshooting

### Common Issues:

#### 1. Database Connection Errors

```bash
# Check MySQL status
sudo systemctl status mysql

# Check connection
mysql -u busadmin -p -e "SELECT 1;"

# Review MySQL logs
sudo tail -f /var/log/mysql/error.log
```

#### 2. Permission Issues

```bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/html/bus-management
sudo chmod -R 755 /var/www/html/bus-management
```

#### 3. SSL Certificate Issues

```bash
# Check certificate expiry
openssl x509 -in /path/to/certificate.crt -text -noout | grep "Not After"

# Renew Let's Encrypt certificate
sudo certbot renew
```

---

## 📈 Performance Optimization

### 1. **Database Optimization**

```sql
-- Add indexes for better performance
CREATE INDEX idx_schedules_departure ON schedules(departure_time);
CREATE INDEX idx_tickets_schedule_seat ON tickets(schedule_id, seat_number);

-- Optimize tables monthly
OPTIMIZE TABLE users, routes, buses, schedules, passengers, tickets, transactions, activity_logs;
```

### 2. **Apache Optimization**

```apache
# Enable compression
LoadModule deflate_module modules/mod_deflate.so
<Location />
    SetOutputFilter DEFLATE
    SetEnvIfNoCase Request_URI \
        \.(?:gif|jpe?g|png)$ no-gzip dont-vary
</Location>

# Enable caching
LoadModule expires_module modules/mod_expires.so
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
</IfModule>
```

### 3. **PHP Optimization**

```ini
; Enable OPcache
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=7963
opcache.revalidate_freq=60
```

---

## 🚨 Emergency Procedures

### 1. **Quick Recovery**

```bash
# Stop application
sudo systemctl stop apache2

# Restore from backup
mysql -u root -p bus_management < /backup/mysql/latest_backup.sql

# Restore files
tar -xzf /backup/files/latest_backup.tar.gz -C /var/www/html/

# Fix permissions
sudo chown -R www-data:www-data /var/www/html/bus-management

# Start application
sudo systemctl start apache2
```

### 2. **Rollback Deployment**

```bash
# Switch to previous version
cd /var/www/html
sudo mv bus-management bus-management-new
sudo mv bus-management-backup bus-management

# Restart services
sudo systemctl restart apache2
```

---
