# Product Import API with Redis Queue

REST API untuk import data products dalam jumlah besar menggunakan CSV file dengan processing asynchronous menggunakan Redis Queue.

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Redis](https://img.shields.io/badge/Redis-Queue-red)](https://redis.io/)
[![JWT](https://img.shields.io/badge/Auth-JWT-orange)](https://jwt.io/)

---

## ⚡ Quick Start

```bash
# 1. Install dependencies
composer install

# 2. Setup database
mysql -u root -p < database/migrations.sql

# 3. Configure environment
cp .env.example .env

# 4. Start Redis
redis-server

# 5. Start worker
php worker.php

# 6. Test API
curl http://localhost/php-redis-import-api/public/
```

**📖 For detailed setup:** [docs/QUICKSTART.md](docs/QUICKSTART.md)

---

## 🎯 Features

- ✅ **JWT Authentication** - Secure token-based authentication
- ✅ **Asynchronous Processing** - Redis Queue untuk non-blocking import
- ✅ **CSV Import** - Support file CSV dengan validasi lengkap
- ✅ **Job Tracking** - Real-time status monitoring
- ✅ **Error Handling** - Row-level error logging tanpa stop processing
- ✅ **Multiple Workers** - Parallel processing support
- ✅ **Complete Logging** - Comprehensive application logs
- ✅ **Production Ready** - Clean code, security, scalability

---

## 📁 Project Structure

```
php-redis-import-api/
├── app/                    # Application code
│   ├── Controllers/        # HTTP request handlers
│   ├── Middleware/         # Authentication middleware
│   ├── Models/            # Database models
│   ├── Queue/             # Queue management & worker
│   └── Services/          # Business logic services
│
├── config/                # Configuration files
├── database/              # Database migrations
├── public/                # Web root (index.php)
├── uploads/               # CSV file storage
├── logs/                  # Application logs
│
├── docs/                  # 📚 Complete documentation (12 files)
├── tests/                 # 🧪 Testing files & samples
│   ├── samples/           # Sample CSV files (10, 1K, 10K, 50K, 100K)
│   └── *.postman_collection.json
│
└── scripts/               # 🛠️ Utility scripts
    ├── generate_sample.php
    └── test.sh
```

---

## 📚 Documentation

### 🚀 Getting Started
| Document | Description | Time |
|----------|-------------|------|
| **[docs/QUICKSTART.md](docs/QUICKSTART.md)** | 5-minute quick start | 10 min |
| **[docs/INSTALLATION.md](docs/INSTALLATION.md)** | Complete installation guide | 30 min |
| **[docs/README.md](docs/README.md)** | Full API documentation | 45 min |

### 🔧 Technical
| Document | Description |
|----------|-------------|
| **[docs/PROJECT_STRUCTURE.md](docs/PROJECT_STRUCTURE.md)** | Architecture & code organization |
| **[docs/POSTMAN_GUIDE.md](docs/POSTMAN_GUIDE.md)** | Postman collection guide |
| **[docs/SAMPLE_DATA.md](docs/SAMPLE_DATA.md)** | Sample files documentation |

### 📊 Reference
| Document | Description |
|----------|-------------|
| **[docs/ASSESSMENT.md](docs/ASSESSMENT.md)** | Requirements compliance |
| **[docs/CHECKLIST.md](docs/CHECKLIST.md)** | Completion checklist |
| **[docs/SUMMARY.md](docs/SUMMARY.md)** | Project overview |

### 📖 All Documentation
**[docs/DOCUMENTATION.md](docs/DOCUMENTATION.md)** - Central documentation hub with complete index

---

## 🔌 API Endpoints

### Authentication
```
POST   /api/auth/register       # Register new user
POST   /api/auth/login          # Login and get JWT token
GET    /api/auth/me             # Get current user info
```

### Import Products
```
POST   /api/import/products     # Upload CSV file (non-blocking)
GET    /api/import/status/{id}  # Get import job status
```

### Queue Management
```
GET    /api/queue/status        # Get queue status
```

### Health Check
```
GET    /                        # API health check
```

**📖 Full API Reference:** [docs/README.md#api-documentation](docs/README.md#api-documentation)

---

## 🧪 Testing

### Postman Collections
```
tests/
├── postman_collection.json (Simple)
├── Product_Import_API.postman_collection.json (Complete with examples)
└── Product_Import_API.postman_environment.json
```

### Sample CSV Files
```
tests/samples/
├── sample.csv          # 10 products - Quick demo
├── sample_1k.csv       # 1,000 products - Standard testing
├── sample_10k.csv      # 10,000 products - Performance testing
├── sample_50k.csv      # 50,000 products - Large dataset
└── sample_100k.csv     # 100,000 products - Stress testing
```

**📖 Testing Guide:** [docs/POSTMAN_GUIDE.md](docs/POSTMAN_GUIDE.md)

---

## 🛠️ Utility Scripts

### Generate Sample Data
```bash
php scripts/generate_sample.php
```
Generates 100,000 sample products in CSV format.

### Automated Testing
```bash
bash scripts/test.sh
```
Runs automated API tests.

**📖 Scripts Documentation:** [docs/SAMPLE_DATA.md](docs/SAMPLE_DATA.md)

---

## 💻 Requirements

- PHP >= 7.4
- MySQL/MariaDB >= 5.7
- Redis Server
- Composer
- Apache/Nginx with mod_rewrite

---

## 📦 Installation

### Quick Install
```bash
# Clone project
cd c:\laragon\www\php-redis-import-api

# Install dependencies
composer install

# Setup environment
cp .env.example .env
# Edit .env with your configuration

# Setup database
mysql -u root -p < database/migrations.sql

# Start services
redis-server                    # Terminal 1
php worker.php                  # Terminal 2
```

**📖 Detailed Installation:** [docs/INSTALLATION.md](docs/INSTALLATION.md)

---

## 🎓 Usage Example

### 1. Login
```bash
curl -X POST http://localhost/php-redis-import-api/public/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password123"}'
```

### 2. Upload CSV
```bash
curl -X POST http://localhost/php-redis-import-api/public/api/import/products \
  -H "Authorization: Bearer <your-token>" \
  -F "file=@tests/samples/sample.csv"
```

Response:
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

### 3. Check Status
```bash
curl -X GET http://localhost/php-redis-import-api/public/api/import/status/123 \
  -H "Authorization: Bearer <your-token>"
```

**📖 More Examples:** [docs/README.md#testing-with-sample-data](docs/README.md#testing-with-sample-data)

---

## 🔒 Security

- ✅ JWT authentication with token expiry
- ✅ Password hashing with bcrypt
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ File upload validation (type, size, content)
- ✅ XSS prevention (JSON responses)
- ✅ Error masking in production mode

**📖 Security Details:** [docs/README.md#security-features](docs/README.md#security-features)

---

## 🚀 Performance

| Metric | Value |
|--------|-------|
| Upload Response Time | <1 second (non-blocking) |
| Processing Speed | ~1000 products/minute (single worker) |
| Multiple Workers | Linear scaling (3 workers = 3000/min) |
| Memory Usage | <50MB per worker |
| Supported File Size | Up to 10MB (configurable) |

**📖 Performance Guide:** [docs/ASSESSMENT.md#performance--scalability](docs/ASSESSMENT.md#performance--scalability)

---

## 🤝 Contributing

1. Fork the project
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

---

## 📄 License

This project is licensed under the MIT License.

---

## 📞 Support

- 📚 **Documentation:** [docs/DOCUMENTATION.md](docs/DOCUMENTATION.md)
- 🐛 **Issues:** Create an issue in repository
- 💬 **Questions:** Check [docs/INSTALLATION.md#troubleshooting](docs/INSTALLATION.md#troubleshooting)

---

## 📊 Project Stats

- **Total Files:** 40+ files
- **Documentation:** 12 files (~120 KB)
- **Source Code:** 13 PHP files (~2,500 lines)
- **Test Files:** 11 files (Postman + Samples)
- **Code Quality:** PSR-4, Clean Code principles
- **Documentation Coverage:** 100%
- **Status:** Production Ready ✓

---

## 🎯 Quick Links

### For New Users
1. [Quick Start Guide](docs/QUICKSTART.md) - Setup in 5 minutes
2. [Postman Guide](docs/POSTMAN_GUIDE.md) - Test with Postman
3. [Sample Data Guide](docs/SAMPLE_DATA.md) - Use sample files

### For Developers
1. [Installation Guide](docs/INSTALLATION.md) - Complete setup
2. [Project Structure](docs/PROJECT_STRUCTURE.md) - Code organization
3. [API Documentation](docs/README.md) - Full reference

### For Evaluators
1. [Project Summary](docs/SUMMARY.md) - Overview
2. [Assessment](docs/ASSESSMENT.md) - Requirements compliance
3. [Checklist](docs/CHECKLIST.md) - Verification

---

## 📝 Recent Updates

**Version 1.0.1** (2025-03-27)
- Fixed MySQL 8.0+ compatibility (`line_number` instead of `row_number`)
- Organized documentation into `docs/` folder
- Moved test files to `tests/` folder
- Added utility scripts to `scripts/` folder

See [docs/CHANGELOG.md](docs/CHANGELOG.md) for complete history.

---

## ✨ Highlights

- 🚀 Non-blocking uploads (<1 second response)
- 📊 Process 100,000 products efficiently
- 🔄 Multiple workers for parallel processing
- 📝 Complete error logging with row details
- 🎯 Real-time job status tracking
- 🔒 Secure JWT authentication
- 📚 Comprehensive documentation (120 KB)
- 🧪 Ready-to-use Postman collections
- 🎨 Clean code architecture (PSR-4)
- ✅ Production ready

---

<div align="center">

**Ready to start? → [docs/QUICKSTART.md](docs/QUICKSTART.md) 🚀**

**Developed with ❤️ for efficient product import processing**

</div>
