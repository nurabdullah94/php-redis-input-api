# Product Import API with Redis Queue

REST API untuk import data products dalam jumlah besar menggunakan CSV file dengan processing asynchronous menggunakan Redis Queue.

## Features

- **JWT Authentication** - Secure authentication menggunakan JSON Web Token
- **CSV Upload** - Upload file CSV dengan validasi struktur
- **Asynchronous Processing** - Import diproses di background menggunakan Redis Queue
- **Job Status Tracking** - Monitor status dan progress import job
- **Error Logging** - Detail error logging untuk setiap baris yang gagal
- **RESTful API** - Clean and well-structured API endpoints

## Tech Stack

- **Language**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Queue**: Redis (Predis)
- **Authentication**: JWT (firebase/php-jwt)
- **Web Server**: Apache/Nginx

## Requirements

- PHP >= 7.4
- MySQL/MariaDB >= 5.7
- Redis Server
- Composer
- Apache/Nginx with mod_rewrite

## Installation

### 1. Clone/Download Project

```bash
cd c:\laragon\www
git clone <repository-url> php-redis-import-api
cd php-redis-import-api
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

```bash
cp .env.example .env
```

Edit `.env` file sesuai dengan konfigurasi Anda:

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
JWT_SECRET=your-secret-key-change-this-in-production
JWT_EXPIRY=3600

# Application Configuration
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost
```

### 4. Setup Database

Import database schema:

```bash
mysql -u root -p < database/migrations.sql
```

Atau manual melalui phpMyAdmin/MySQL Workbench dengan menjalankan script di `database/migrations.sql`

### 5. Set Permissions

```bash
chmod 755 uploads/
chmod 755 logs/
chmod +x worker.php
```

### 6. Start Redis Server

**Windows (jika menggunakan Laragon):**
```bash
redis-server
```

**Linux/Mac:**
```bash
sudo systemctl start redis
# atau
brew services start redis
```

### 7. Start Queue Worker

Buka terminal/command prompt baru dan jalankan:

```bash
php worker.php
```

Worker akan berjalan terus menerus dan memproses job dari queue.

### 8. Start Web Server

**Laragon:**
- Pastikan Apache sudah running
- Akses: `http://localhost/php-redis-import-api/public`

**PHP Built-in Server (untuk testing):**
```bash
cd public
php -S localhost:8000
```

## API Documentation

### Base URL

```
http://localhost/php-redis-import-api/public
```

### Authentication

Semua endpoint (kecuali `/api/auth/login` dan `/api/auth/register`) memerlukan JWT token di header:

```
Authorization: Bearer <your-jwt-token>
```

---

### 1. Register User

**Endpoint:** `POST /api/auth/register`

**Body (JSON):**
```json
{
  "username": "admin",
  "email": "admin@example.com",
  "password": "password123"
}
```

**Response Success (201):**
```json
{
  "success": true,
  "data": {
    "message": "User registered successfully",
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

### 2. Login

**Endpoint:** `POST /api/auth/login`

**Body (JSON):**
```json
{
  "username": "admin",
  "password": "password123"
}
```

**Response Success (200):**
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

### 3. Get Current User Info

**Endpoint:** `GET /api/auth/me`

**Headers:**
```
Authorization: Bearer <token>
```

**Response Success (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@example.com",
    "created_at": "2025-03-27 10:00:00"
  }
}
```

---

### 4. Upload CSV for Import

**Endpoint:** `POST /api/import/products`

**Headers:**
```
Authorization: Bearer <token>
Content-Type: multipart/form-data
```

**Form Data:**
- `file`: CSV file (required)

**CSV Format:**
```csv
name,sku,price,stock
Laptop Dell XPS 13,DELL-XPS13-001,15999000,25
iPhone 14 Pro Max,APPLE-IP14PM-256,18999000,50
```

**Response Success (201):**
```json
{
  "success": true,
  "data": {
    "job_id": 123,
    "status": "pending",
    "message": "File uploaded successfully and queued for processing"
  }
}
```

**Response Error (400):**
```json
{
  "success": false,
  "message": "Invalid file type. Only CSV files are allowed"
}
```

---

### 5. Get Import Job Status

**Endpoint:** `GET /api/import/status/{job_id}`

**Headers:**
```
Authorization: Bearer <token>
```

**Response Success (200):**

**Status: Pending**
```json
{
  "success": true,
  "data": {
    "job_id": 123,
    "status": "pending",
    "filename": "products.csv",
    "total": 1000,
    "success": 0,
    "failed": 0,
    "created_at": "2025-03-27 05:00:00",
    "updated_at": "2025-03-27 05:00:00"
  }
}
```

**Status: In Progress**
```json
{
  "success": true,
  "data": {
    "job_id": 123,
    "status": "in_progress",
    "filename": "products.csv",
    "total": 1000,
    "success": 450,
    "failed": 3,
    "created_at": "2025-03-27 05:00:00",
    "updated_at": "2025-03-27 05:00:30"
  }
}
```

**Status: Completed**
```json
{
  "success": true,
  "data": {
    "job_id": 123,
    "status": "completed",
    "filename": "products.csv",
    "total": 1000,
    "success": 995,
    "failed": 5,
    "created_at": "2025-03-27 05:00:00",
    "updated_at": "2025-03-27 05:00:52",
    "errors": [
      {
        "line_number": 45,
        "error_message": "Row 45: SKU is required",
        "row_data": ["Product Name", "", "10000", "50"]
      },
      {
        "line_number": 127,
        "error_message": "Row 127: Price must be a positive number",
        "row_data": ["Product B", "SKU-002", "invalid", "10"]
      }
    ],
    "total_errors": 5
  }
}
```

---

### 6. Get Queue Status

**Endpoint:** `GET /api/queue/status`

**Headers:**
```
Authorization: Bearer <token>
```

**Response Success (200):**
```json
{
  "success": true,
  "data": {
    "queue_name": "product_import_queue",
    "pending_jobs": 3
  }
}
```

---

## Testing with Sample Data

1. **Login atau Register:**
```bash
curl -X POST http://localhost/php-redis-import-api/public/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password123"}'
```

2. **Upload sample CSV:**
```bash
curl -X POST http://localhost/php-redis-import-api/public/api/import/products \
  -H "Authorization: Bearer <your-token>" \
  -F "file=@tests/samples/sample.csv"
```

3. **Check job status:**
```bash
curl -X GET http://localhost/php-redis-import-api/public/api/import/status/1 \
  -H "Authorization: Bearer <your-token>"
```

## Queue Worker Management

### Start Worker

```bash
php worker.php
```

### Stop Worker

Press `Ctrl+C` atau kill process:

```bash
# Find process
ps aux | grep worker.php

# Kill process
kill <PID>
```

### Multiple Workers

Untuk meningkatkan throughput, Anda bisa menjalankan multiple workers:

```bash
# Terminal 1
php worker.php

# Terminal 2
php worker.php

# Terminal 3
php worker.php
```

### Background Process (Linux/Mac)

```bash
# Start in background
nohup php worker.php > logs/worker.log 2>&1 &

# Stop
pkill -f worker.php
```

## Error Handling

### Import Errors

Jika ada baris yang gagal di-import:
- Error akan di-log ke database table `import_errors`
- Import akan tetap berlanjut untuk baris berikutnya
- Status job akan `completed` meskipun ada beberapa error
- Status job akan `failed` hanya jika SEMUA baris gagal

### Validation Rules

**CSV File:**
- Format: `.csv`
- Header required: `name, sku, price, stock`
- Max file size: 10MB (configurable di `.env`)

**Product Data:**
- `name`: Required, string
- `sku`: Required, unique, string
- `price`: Required, positive number (decimal)
- `stock`: Required, positive integer

## Logging

Log files tersimpan di folder `logs/`:

```
logs/
  app-2025-03-27.log
  app-2025-03-28.log
```

Log format:
```
[2025-03-27 10:00:00] [INFO] Job pushed to queue {"job_id":123,"queue":"product_import_queue"}
[2025-03-27 10:00:05] [INFO] Processing job {"job_id":123,"filename":"import_123.csv"}
[2025-03-27 10:00:52] [INFO] Job completed {"job_id":123,"success":995,"failed":5}
```

## Database Schema

### Table: products
```sql
id INT AUTO_INCREMENT PRIMARY KEY
name VARCHAR(255) NOT NULL
sku VARCHAR(100) NOT NULL UNIQUE
price DECIMAL(10,2) NOT NULL
stock INT NOT NULL DEFAULT 0
created_at DATETIME
updated_at DATETIME
```

### Table: import_jobs
```sql
id INT AUTO_INCREMENT PRIMARY KEY
filename VARCHAR(255) NOT NULL
original_filename VARCHAR(255) NOT NULL
status ENUM('pending','in_progress','completed','failed')
total INT NOT NULL DEFAULT 0
success INT NOT NULL DEFAULT 0
failed INT NOT NULL DEFAULT 0
error_message TEXT NULL
user_id INT NULL
created_at DATETIME
updated_at DATETIME
```

### Table: import_errors
```sql
id INT AUTO_INCREMENT PRIMARY KEY
import_job_id INT NOT NULL
line_number INT NOT NULL
error_message TEXT NOT NULL
row_data TEXT NULL
created_at DATETIME
FOREIGN KEY (import_job_id) REFERENCES import_jobs(id) ON DELETE CASCADE
```

### Table: users
```sql
id INT AUTO_INCREMENT PRIMARY KEY
username VARCHAR(100) NOT NULL UNIQUE
email VARCHAR(255) NOT NULL UNIQUE
password VARCHAR(255) NOT NULL
api_token VARCHAR(255) NULL UNIQUE
created_at DATETIME
updated_at DATETIME
```

## Security Features

1. **JWT Authentication** - Secure token-based authentication
2. **Password Hashing** - Using PHP `password_hash()` with bcrypt
3. **SQL Injection Prevention** - Using PDO prepared statements
4. **File Upload Validation** - Type, size, and content validation
5. **CORS Headers** - Configurable cross-origin resource sharing
6. **Error Masking** - Production mode hides sensitive error details

## Performance Optimization

1. **Batch Processing** - Worker processes rows one by one but updates progress every 100 rows
2. **Connection Pooling** - Reuses database and Redis connections
3. **Asynchronous Processing** - Main API doesn't wait for import completion
4. **Multiple Workers** - Can run multiple workers for parallel processing
5. **Indexed Columns** - Database indexes on frequently queried columns

## Troubleshooting

### Redis Connection Error
```
Error: Redis connection failed
```
**Solution:** Pastikan Redis server sudah running:
```bash
redis-cli ping
# Should return: PONG
```

### Database Connection Error
```
Error: Database connection failed
```
**Solution:** Check credentials di `.env` dan pastikan MySQL running

### Worker Not Processing Jobs
**Solution:**
1. Check Redis connection: `redis-cli ping`
2. Check queue size: `redis-cli llen product_import_queue`
3. Restart worker: `Ctrl+C` then `php worker.php`

### File Upload Error
**Solution:**
1. Check folder permissions: `chmod 755 uploads/`
2. Check PHP upload settings in `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
```

## Project Structure

```
php-redis-import-api/
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   └── ImportController.php
│   ├── Middleware/
│   │   └── AuthMiddleware.php
│   ├── Models/
│   │   ├── ImportJob.php
│   │   └── Product.php
│   ├── Queue/
│   │   ├── ImportWorker.php
│   │   └── QueueManager.php
│   └── Services/
│       ├── JWTService.php
│       └── Logger.php
├── config/
│   ├── database.php
│   └── redis.php
├── database/
│   └── migrations.sql
├── logs/
├── public/
│   ├── .htaccess
│   └── index.php
├── uploads/
├── .env.example
├── .gitignore
├── composer.json
├── docs/README.md
├── tests/samples/sample.csv
└── worker.php
```

## Contributing

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is open-sourced software.

## Support

Untuk pertanyaan atau issue, silakan buat issue di repository ini.

---

**Developed with ❤️ for efficient product import processing**
