# Quick Start Guide

Panduan cepat untuk memulai menggunakan Product Import API dalam 5 menit.

## Prerequisites

Pastikan sudah terinstall:
- PHP 7.4+
- MySQL/MariaDB
- Redis
- Composer

---

## Installation (5 Steps)

### 1. Install Dependencies
```bash
cd c:\laragon\www\php-redis-import-api
composer install
```

### 2. Setup Environment
```bash
cp .env.example .env
```

Edit `.env` jika perlu (default settings biasanya sudah OK untuk Laragon).

### 3. Setup Database
```bash
mysql -u root -p < database/migrations.sql
```

Atau via phpMyAdmin: Import file `database/migrations.sql`

### 4. Start Redis
```bash
redis-server
```

Atau via Laragon menu: Redis → Start

### 5. Start Worker
Buka terminal baru:
```bash
php worker.php
```

---

## Testing (3 Steps)

### 1. Login
```bash
curl -X POST http://localhost/php-redis-import-api/public/api/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"username\":\"admin\",\"password\":\"password123\"}"
```

Copy token dari response.

### 2. Upload CSV
```bash
curl -X POST http://localhost/php-redis-import-api/public/api/import/products \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -F "file=@tests/samples/sample.csv"
```

Copy job_id dari response.

### 3. Check Status
```bash
curl -X GET http://localhost/php-redis-import-api/public/api/import/status/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Using Postman (Recommended)

### 1. Import Collection
- Open Postman
- Import → File → `tests/postman_collection.json`

### 2. Set Base URL
- Collection variables
- `base_url`: `http://localhost/php-redis-import-api/public`

### 3. Run Requests
1. Authentication → Login
2. Import → Upload CSV File
3. Import → Get Import Job Status

---

## Default User

**Username:** admin
**Password:** password123
**Email:** admin@example.com

---

## API Endpoints

| Endpoint | Method | Auth | Description |
|----------|--------|------|-------------|
| `/api/auth/login` | POST | No | Login |
| `/api/auth/register` | POST | No | Register |
| `/api/import/products` | POST | Yes | Upload CSV |
| `/api/import/status/{id}` | GET | Yes | Check status |

---

## Troubleshooting

### Database Connection Error
```bash
# Check MySQL running
mysql -u root -p

# Create database
mysql -u root -p
CREATE DATABASE product_import;
exit;

# Import schema
mysql -u root -p product_import < database/migrations.sql
```

### Redis Connection Error
```bash
# Check Redis
redis-cli ping
# Should return: PONG

# Start Redis
redis-server
```

### Worker Not Processing
```bash
# Kill existing worker
Ctrl+C

# Restart worker
php worker.php
```

---

## Next Steps

1. Read full documentation: [docs/README.md](docs/README.md)
2. See installation guide: [docs/INSTALLATION.md](docs/INSTALLATION.md)
3. Review assessment: [docs/ASSESSMENT.md](docs/ASSESSMENT.md)
4. Try uploading your own CSV files
5. Monitor logs in `logs/` folder

---

## CSV Format

Your CSV must have these columns:
```csv
name,sku,price,stock
Product Name,SKU-001,10000,50
Another Product,SKU-002,25000,100
```

**Rules:**
- Header required
- name: string (required)
- sku: unique string (required)
- price: positive number (required)
- stock: positive integer (required)

---

## File Structure

```
php-redis-import-api/
├── app/              # Application code
├── config/           # Configuration
├── database/         # Database migrations
├── logs/            # Application logs
├── public/          # Web root
├── uploads/         # Uploaded files
├── .env            # Environment config
├── composer.json   # Dependencies
├── worker.php      # Queue worker
└── tests/samples/sample.csv      # Sample data
```

---

## Support

- Full docs: [docs/README.md](docs/README.md)
- Installation: [docs/INSTALLATION.md](docs/INSTALLATION.md)
- Assessment: [docs/ASSESSMENT.md](docs/ASSESSMENT.md)

---

**Happy Coding! 🚀**
