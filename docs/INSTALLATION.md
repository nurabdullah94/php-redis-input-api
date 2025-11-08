# Installation Guide

Panduan lengkap untuk instalasi dan setup Product Import API.

## Prerequisites

Sebelum memulai, pastikan sistem Anda sudah memiliki:

1. **PHP >= 7.4**
   ```bash
   php -v
   ```

2. **Composer**
   ```bash
   composer --version
   ```

3. **MySQL/MariaDB**
   ```bash
   mysql --version
   ```

4. **Redis Server**
   ```bash
   redis-cli --version
   ```

5. **Apache/Nginx** dengan mod_rewrite enabled

---

## Step-by-Step Installation

### Step 1: Clone/Download Project

```bash
cd c:\laragon\www
git clone <repository-url> php-redis-import-api
cd php-redis-import-api
```

Atau extract ZIP file ke folder `c:\laragon\www\php-redis-import-api`

---

### Step 2: Install Dependencies

```bash
composer install
```

**Troubleshooting:**
- Jika error "composer not found", install composer: https://getcomposer.org/download/
- Jika error "memory limit", edit `php.ini`: `memory_limit = 512M`

---

### Step 3: Configure Environment

```bash
cp .env.example .env
```

Edit file `.env` dan sesuaikan dengan konfigurasi Anda:

```env
# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_NAME=product_import
DB_USER=root
DB_PASS=

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_QUEUE_NAME=product_import_queue

# JWT Configuration
JWT_SECRET=your-random-secret-key-change-this
JWT_EXPIRY=3600

# Application Configuration
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost
```

**PENTING:** Ganti `JWT_SECRET` dengan string random yang aman!

Generate random secret:
```bash
php -r "echo bin2hex(random_bytes(32));"
```

---

### Step 4: Setup Database

#### Option A: MySQL Command Line

```bash
mysql -u root -p < database/migrations.sql
```

#### Option B: phpMyAdmin

1. Buka phpMyAdmin
2. Click tab "SQL"
3. Copy paste isi file `database/migrations.sql`
4. Click "Go"

#### Option C: MySQL Workbench

1. Buka MySQL Workbench
2. Connect ke database
3. File > Run SQL Script
4. Pilih `database/migrations.sql`
5. Execute

#### Verify Database

```bash
mysql -u root -p
```

```sql
USE product_import;
SHOW TABLES;

-- Should show:
-- +---------------------------+
-- | Tables_in_product_import  |
-- +---------------------------+
-- | import_errors             |
-- | import_jobs               |
-- | products                  |
-- | users                     |
-- +---------------------------+
```

---

### Step 5: Set Permissions

**Windows (Laragon):**
- Permissions biasanya sudah OK, skip step ini

**Linux/Mac:**
```bash
chmod 755 uploads/
chmod 755 logs/
chmod +x worker.php
```

---

### Step 6: Start Redis Server

#### Windows (Laragon):

**Option 1: Via Laragon Menu**
1. Buka Laragon
2. Menu > Redis > Start

**Option 2: Command Line**
```bash
redis-server
```

#### Linux:
```bash
sudo systemctl start redis
sudo systemctl enable redis  # Auto start on boot
```

#### Mac:
```bash
brew services start redis
```

#### Verify Redis:
```bash
redis-cli ping
# Should return: PONG
```

---

### Step 7: Configure Web Server

#### Apache (Laragon)

**httpd.conf** - Pastikan mod_rewrite enabled:
```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

**Virtual Host** (optional, untuk custom domain):

Edit: `C:\laragon\etc\apache2\sites-enabled\auto.product-import.test.conf`

```apache
<VirtualHost *:80>
    DocumentRoot "C:/laragon/www/php-redis-import-api/public"
    ServerName product-import.test

    <Directory "C:/laragon/www/php-redis-import-api/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Edit `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 product-import.test
```

Restart Apache via Laragon.

#### Nginx

Create file `/etc/nginx/sites-available/product-import`:

```nginx
server {
    listen 80;
    server_name product-import.local;
    root /var/www/php-redis-import-api/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/product-import /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

### Step 8: Test API

#### Test 1: Health Check

```bash
curl http://localhost/php-redis-import-api/public/
```

Expected response:
```json
{
  "success": true,
  "message": "Product Import API is running",
  "version": "1.0.0",
  "timestamp": "2025-03-27 10:00:00"
}
```

#### Test 2: Login

```bash
curl -X POST http://localhost/php-redis-import-api/public/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password123"}'
```

Expected response:
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "user": {
      "id": 1,
      "username": "admin",
      "email": "admin@example.com"
    }
  }
}
```

---

### Step 9: Start Queue Worker

Buka terminal/command prompt baru:

```bash
cd c:\laragon\www\php-redis-import-api
php worker.php
```

Expected output:
```
===========================================
Product Import Queue Worker
===========================================

Database connected successfully
Redis connected successfully
Queue: product_import_queue

Worker started. Waiting for jobs...
Queue: product_import_queue
Press Ctrl+C to stop
```

**Biarkan terminal ini tetap berjalan!**

---

### Step 10: Test Upload

#### Using cURL:

```bash
curl -X POST http://localhost/php-redis-import-api/public/api/import/products \
  -H "Authorization: Bearer <your-token-from-login>" \
  -F "file=@tests/samples/sample.csv"
```

#### Using Postman:

1. Import collection: `tests/postman_collection.json`
2. Set variable `base_url`: `http://localhost/php-redis-import-api/public`
3. Run "Login" request
4. Run "Upload CSV File" request
5. Run "Get Import Job Status" request

---

## Verification Checklist

- [ ] PHP 7.4+ installed
- [ ] Composer installed
- [ ] MySQL/MariaDB installed and running
- [ ] Redis installed and running
- [ ] Database `product_import` created
- [ ] All tables created (products, import_jobs, import_errors, users)
- [ ] Dependencies installed (`vendor` folder exists)
- [ ] `.env` file configured
- [ ] Apache/Nginx running
- [ ] API health check returns success
- [ ] Can login and get JWT token
- [ ] Worker running and waiting for jobs
- [ ] Can upload CSV file
- [ ] Can check job status

---

## Common Issues & Solutions

### Issue 1: "Database connection failed"

**Causes:**
- MySQL not running
- Wrong credentials in `.env`
- Database not created

**Solutions:**
```bash
# Check MySQL running
mysql -u root -p

# Create database manually
mysql -u root -p
CREATE DATABASE product_import;
exit;

# Run migrations
mysql -u root -p product_import < database/migrations.sql
```

---

### Issue 2: "Redis connection failed"

**Causes:**
- Redis not running
- Wrong host/port in `.env`

**Solutions:**
```bash
# Check Redis running
redis-cli ping

# Start Redis (Windows)
redis-server

# Start Redis (Linux)
sudo systemctl start redis

# Check Redis config
redis-cli
> CONFIG GET bind
> CONFIG GET port
```

---

### Issue 3: "Cannot write to uploads/ folder"

**Causes:**
- Permission denied

**Solutions:**
```bash
# Windows - usually OK, check folder exists
dir uploads

# Linux/Mac
chmod 755 uploads/
chown www-data:www-data uploads/  # For Apache
```

---

### Issue 4: "404 Not Found" on all endpoints

**Causes:**
- `.htaccess` not working
- mod_rewrite not enabled
- Wrong DocumentRoot

**Solutions:**

**Apache:**
```bash
# Enable mod_rewrite
a2enmod rewrite
systemctl restart apache2

# Check .htaccess exists
ls -la public/.htaccess
```

**Nginx:**
- Check nginx config has `try_files` directive
- Restart nginx: `sudo systemctl restart nginx`

---

### Issue 5: Worker not processing jobs

**Causes:**
- Worker not running
- Redis queue name mismatch
- PHP error in worker

**Solutions:**
```bash
# Check worker running
ps aux | grep worker.php

# Check queue has jobs
redis-cli
> LLEN product_import_queue

# Run worker with debug
php -d display_errors=1 worker.php

# Check logs
tail -f logs/app-*.log
```

---

### Issue 6: "Class not found" errors

**Causes:**
- Composer autoload not generated
- Wrong namespace

**Solutions:**
```bash
# Regenerate autoload
composer dump-autoload

# Clear composer cache
composer clear-cache
composer install
```

---

## Running in Production

### 1. Update `.env`

```env
APP_ENV=production
APP_DEBUG=false
```

### 2. Secure JWT Secret

```bash
# Generate strong secret
php -r "echo bin2hex(random_bytes(32));"
```

Update `.env`:
```env
JWT_SECRET=<generated-secret>
```

### 3. Setup Supervisor for Worker (Linux)

Create `/etc/supervisor/conf.d/product-import-worker.conf`:

```ini
[program:product-import-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/php-redis-import-api/worker.php
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/php-redis-import-api/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start product-import-worker:*
```

### 4. Setup Log Rotation

Create `/etc/logrotate.d/product-import`:

```
/var/www/php-redis-import-api/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
}
```

### 5. Setup SSL (HTTPS)

Using Let's Encrypt:
```bash
sudo certbot --nginx -d product-import.example.com
```

### 6. Secure File Permissions

```bash
chown -R www-data:www-data /var/www/php-redis-import-api
chmod -R 755 /var/www/php-redis-import-api
chmod -R 775 /var/www/php-redis-import-api/uploads
chmod -R 775 /var/www/php-redis-import-api/logs
```

---

## Next Steps

1. Read [docs/README.md](docs/README.md) for API documentation
2. Import [tests/postman_collection.json](tests/postman_collection.json) to Postman
3. Test all endpoints
4. Upload sample data using [tests/samples/sample.csv](tests/samples/sample.csv)
5. Monitor logs in `logs/` folder
6. Check worker output for processing status

---

## Support

Jika mengalami masalah, check:
1. Logs di folder `logs/`
2. PHP error log
3. Apache/Nginx error log
4. Redis log
5. MySQL error log

Atau buat issue di repository ini.
