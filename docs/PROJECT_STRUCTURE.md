# Project Structure

Complete overview of the project structure and file organization.

```
php-redis-import-api/
│
├── app/                                # Application Code
│   ├── Controllers/                    # HTTP Request Handlers
│   │   ├── AuthController.php          # Authentication endpoints
│   │   └── ImportController.php        # Import endpoints
│   │
│   ├── Middleware/                     # Request Middleware
│   │   └── AuthMiddleware.php          # JWT authentication middleware
│   │
│   ├── Models/                         # Database Models
│   │   ├── ImportJob.php               # Import jobs CRUD operations
│   │   └── Product.php                 # Products CRUD operations
│   │
│   ├── Queue/                          # Queue Management
│   │   ├── ImportWorker.php            # Background worker for processing
│   │   └── QueueManager.php            # Redis queue operations
│   │
│   └── Services/                       # Business Logic Services
│       ├── JWTService.php              # JWT token generation & validation
│       └── Logger.php                  # Application logging service
│
├── config/                             # Configuration Files
│   ├── database.php                    # Database connection (Singleton)
│   └── redis.php                       # Redis connection (Singleton)
│
├── database/                           # Database Files
│   └── migrations.sql                  # Database schema & migrations
│
├── logs/                               # Application Logs
│   └── app-YYYY-MM-DD.log              # Daily log files (auto-generated)
│
├── public/                             # Web Accessible Directory
│   ├── .htaccess                       # Apache rewrite rules
│   └── index.php                       # Application entry point & router
│
├── uploads/                            # File Uploads Storage
│   └── .gitkeep                        # Keep folder in git
│
├── vendor/                             # Composer Dependencies (auto-generated)
│   └── autoload.php                    # PSR-4 autoloader
│
├── .env                                # Environment Configuration (local)
├── .env.example                        # Environment Template
├── .gitignore                          # Git ignore rules
├── docs/ASSESSMENT.md                       # Assessment compliance documentation
├── composer.json                       # PHP dependencies & autoload config
├── composer.lock                       # Locked dependency versions
├── docs/INSTALLATION.md                     # Installation guide
├── tests/postman_collection.json             # Postman API collection
├── docs/PROJECT_STRUCTURE.md                # This file
├── docs/QUICKSTART.md                       # Quick start guide
├── docs/README.md                           # Main documentation
├── tests/samples/sample.csv                          # Sample CSV data for testing
└── worker.php                          # Queue worker CLI script
```

---

## Directory Details

### `/app` - Application Code

Main application logic organized by responsibility.

#### `/app/Controllers`
HTTP request handlers that receive requests, validate input, call services/models, and return responses.

**Files:**
- `AuthController.php` - Login, register, user info endpoints
- `ImportController.php` - CSV upload, job status endpoints

**Responsibilities:**
- Validate request input
- Call appropriate models/services
- Format and return JSON responses
- Handle HTTP status codes

---

#### `/app/Middleware`
Request processing middleware that runs before controllers.

**Files:**
- `AuthMiddleware.php` - JWT authentication verification

**Responsibilities:**
- Extract & verify JWT tokens
- Block unauthorized requests
- Add user context to request

---

#### `/app/Models`
Database interaction layer using PDO.

**Files:**
- `Product.php` - Product CRUD operations
- `ImportJob.php` - Import job & error logging

**Responsibilities:**
- Database queries (SELECT, INSERT, UPDATE)
- Data validation at DB level
- Relationships & foreign keys

---

#### `/app/Queue`
Queue management and background processing.

**Files:**
- `QueueManager.php` - Redis queue operations (push/pop)
- `ImportWorker.php` - Background worker process

**Responsibilities:**
- Push jobs to Redis queue
- Pop jobs from queue (blocking)
- Process CSV files row by row
- Update job status & progress
- Error logging

---

#### `/app/Services`
Reusable business logic services.

**Files:**
- `JWTService.php` - JWT token operations
- `Logger.php` - Application logging

**Responsibilities:**
- JWT generation & validation
- Logging (INFO, WARNING, ERROR, DEBUG)
- Shared utilities

---

### `/config` - Configuration

Application configuration and connections.

**Files:**
- `database.php` - PDO MySQL connection (Singleton)
- `redis.php` - Predis Redis client (Singleton)

**Pattern:** Singleton pattern for single connection instances.

---

### `/database` - Database Files

**Files:**
- `migrations.sql` - Database schema creation

**Tables:**
- `products` - Product data
- `import_jobs` - Job tracking
- `import_errors` - Error logging
- `users` - User authentication

---

### `/logs` - Application Logs

Auto-generated log files with format: `app-YYYY-MM-DD.log`

**Content:**
- Timestamped log entries
- Log levels (INFO, WARNING, ERROR, DEBUG)
- Contextual data (JSON)

**Retention:** Implement log rotation (see docs/INSTALLATION.md)

---

### `/public` - Web Root

Public web-accessible directory. Point your web server here.

**Files:**
- `index.php` - Router & application bootstrap
- `.htaccess` - Apache mod_rewrite rules

**Purpose:**
- Single entry point
- Route all requests through index.php
- Protect files outside public/

---

### `/uploads` - File Storage

CSV file uploads storage.

**Format:** `import_<uniqid>.csv`

**Security:**
- Not directly web-accessible
- File type validation on upload
- Size limits enforced

---

### `/vendor` - Dependencies

Composer-managed dependencies (auto-generated).

**Main Libraries:**
- `predis/predis` - Redis client
- `firebase/php-jwt` - JWT implementation
- `vlucas/phpdotenv` - Environment variable loader

---

## Root Files

### Configuration Files

| File | Purpose |
|------|---------|
| `.env` | Environment variables (local, not in git) |
| `.env.example` | Environment template |
| `.gitignore` | Git ignore rules |
| `composer.json` | PHP dependencies & PSR-4 autoload |

### Documentation Files

| File | Purpose |
|------|---------|
| `docs/README.md` | Main documentation with API reference |
| `docs/INSTALLATION.md` | Installation & setup guide |
| `docs/QUICKSTART.md` | Quick start in 5 minutes |
| `docs/ASSESSMENT.md` | Assessment criteria compliance |
| `docs/PROJECT_STRUCTURE.md` | This file - project structure overview |

### Executable Files

| File | Purpose |
|------|---------|
| `worker.php` | Queue worker CLI script |

### Data Files

| File | Purpose |
|------|---------|
| `tests/samples/sample.csv` | Sample CSV data for testing |
| `tests/postman_collection.json` | Postman API collection |

---

## Code Organization Principles

### 1. Separation of Concerns
- Controllers handle HTTP
- Models handle database
- Services handle business logic
- Queue handles async processing

### 2. Single Responsibility
Each class/file has one clear purpose.

### 3. PSR-4 Autoloading
```php
namespace App\Controllers;  // Maps to app/Controllers/
namespace App\Models;       // Maps to app/Models/
namespace App\Services;     // Maps to app/Services/
```

### 4. Dependency Injection
Dependencies passed through constructors:
```php
public function __construct(PDO $db, QueueManager $queue)
{
    $this->db = $db;
    $this->queue = $queue;
}
```

### 5. Configuration Management
All config in `.env` file:
- Database credentials
- Redis connection
- JWT secret
- App settings

---

## File Naming Conventions

### PHP Files
- **Classes:** PascalCase (e.g., `ImportController.php`)
- **Namespaces:** Match directory structure
- **One class per file**

### Configuration Files
- **Lowercase with extension:** `database.php`, `redis.php`

### Documentation Files
- **UPPERCASE:** `docs/README.md`, `docs/INSTALLATION.md`

---

## Key Entry Points

### Web Requests
```
Browser/Postman
    ↓
public/index.php (Router)
    ↓
Controllers (Handle request)
    ↓
Models/Services (Business logic)
    ↓
JSON Response
```

### Queue Worker
```
CLI: php worker.php
    ↓
app/Queue/ImportWorker.php
    ↓
QueueManager::pop() from Redis
    ↓
Process CSV with Models
    ↓
Update job status
```

---

## Data Flow

### Upload Flow
```
POST /api/import/products
    ↓
ImportController::upload()
    ↓
Validate file
    ↓
Save to uploads/
    ↓
ImportJob::create() → Database
    ↓
QueueManager::push() → Redis
    ↓
Return job_id (non-blocking)
```

### Processing Flow
```
ImportWorker::start()
    ↓
QueueManager::pop() ← Redis (blocking)
    ↓
Read CSV file
    ↓
For each row:
    - Validate
    - Product::upsert() → Database
    - Log errors if any
    ↓
ImportJob::updateProgress()
    ↓
Wait for next job...
```

---

## Security Architecture

### Authentication Layer
```
Request with Authorization header
    ↓
AuthMiddleware::handle()
    ↓
JWTService::verifyToken()
    ↓
Valid? Continue : 401 Unauthorized
```

### Data Protection
1. **SQL Injection:** PDO prepared statements
2. **XSS:** JSON responses (auto-escaped)
3. **File Upload:** Type & size validation
4. **Passwords:** bcrypt hashing
5. **Tokens:** HS256 signing

---

## Scalability Considerations

### Horizontal Scaling
- **Multiple Workers:** Run `php worker.php` on multiple servers
- **Load Balancer:** Distribute HTTP requests
- **Redis Cluster:** For high availability

### Vertical Scaling
- **Database:** Optimize indexes, query caching
- **Redis:** Increase memory, persistence config
- **PHP:** Increase memory_limit, max_execution_time

### Performance
- **Queue:** Non-blocking uploads
- **Database:** Indexed columns (sku, status, created_at)
- **Connection Pooling:** Singleton connections

---

## Maintenance

### Daily Tasks
- Monitor logs in `/logs`
- Check queue size: `redis-cli LLEN product_import_queue`
- Verify worker running: `ps aux | grep worker.php`

### Weekly Tasks
- Review error logs
- Check disk space (uploads, logs)
- Database optimization: `OPTIMIZE TABLE products`

### Monthly Tasks
- Log rotation
- Database backup
- Dependency updates: `composer update`

---

## Development Workflow

### Adding New Endpoint
1. Create/update Controller in `app/Controllers/`
2. Add route in `public/index.php`
3. Add authentication if needed
4. Update Postman collection
5. Update documentation

### Adding New Feature
1. Plan architecture
2. Create necessary Models/Services
3. Update Controllers
4. Add tests
5. Update documentation

### Debugging
1. Check logs: `logs/app-*.log`
2. Enable debug mode: `.env` → `APP_DEBUG=true`
3. Check worker output
4. Use Postman for API testing

---

This structure promotes maintainability, scalability, and clean code principles. Each component has a clear responsibility and can be modified independently.
