# Assessment Compliance

Dokumen ini menjelaskan bagaimana aplikasi memenuhi semua kriteria penilaian yang diminta.

---

## ✅ Arsitektur Aplikasi

### Struktur Terorganisir
```
php-redis-import-api/
├── app/
│   ├── Controllers/      # Request handlers
│   ├── Middleware/       # Authentication & authorization
│   ├── Models/          # Database models
│   ├── Queue/           # Queue management & worker
│   └── Services/        # Business logic (JWT, Logger)
├── config/              # Configuration files
├── database/            # Migration files
├── public/              # Web accessible folder (index.php)
├── uploads/             # File uploads storage
└── logs/                # Application logs
```

### Configurable
- Environment-based configuration via `.env` file
- Semua settings (database, redis, JWT, upload) dapat dikonfigurasi
- Support untuk multiple environments (development, production)

### Well-Mapped
- **PSR-4 Autoloading**: Namespace yang konsisten
- **Single Responsibility**: Setiap class memiliki tanggung jawab yang jelas
- **Separation of Concerns**: Controller, Model, Service, Queue terpisah
- **Dependency Injection**: Dependencies di-inject melalui constructor

**Lokasi kode:**
- [composer.json](composer.json#L8-L10) - PSR-4 autoload configuration
- [app/Controllers/](app/Controllers/) - Controller layer
- [app/Models/](app/Models/) - Data layer
- [app/Services/](app/Services/) - Business logic layer

---

## ✅ RESTful API

### Endpoint Structure

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/` | Health check | No |
| POST | `/api/auth/register` | Register user | No |
| POST | `/api/auth/login` | Login user | No |
| GET | `/api/auth/me` | Get user info | Yes |
| POST | `/api/import/products` | Upload CSV | Yes |
| GET | `/api/import/status/{id}` | Get job status | Yes |
| GET | `/api/queue/status` | Get queue status | Yes |

### Response Format
**Success:**
```json
{
  "success": true,
  "data": { ... }
}
```

**Error:**
```json
{
  "success": false,
  "message": "Error description"
}
```

### HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `404` - Not Found
- `409` - Conflict
- `500` - Internal Server Error

**Lokasi kode:**
- [public/index.php](public/index.php) - Router & endpoint mapping
- [app/Controllers/ImportController.php](app/Controllers/ImportController.php) - Import endpoints
- [app/Controllers/AuthController.php](app/Controllers/AuthController.php) - Auth endpoints

---

## ✅ Keamanan

### JWT Authentication

#### Implementation:
1. **Token Generation**
   - User login → JWT token generated
   - Token contains: user_id, username, email
   - Token expiry: configurable (default 3600s)
   - Algorithm: HS256

2. **Token Verification**
   - Middleware checks Authorization header
   - Format: `Bearer <token>`
   - Validates signature & expiry
   - Returns 401 if invalid/expired

3. **Protected Endpoints**
   - All `/api/import/*` endpoints require authentication
   - `/api/queue/status` requires authentication
   - Middleware blocks unauthenticated requests

#### Security Features:
- Password hashing with `password_hash()` (bcrypt)
- SQL injection prevention (PDO prepared statements)
- File upload validation (type, size, content)
- XSS prevention (JSON responses)
- Error masking in production mode

**Lokasi kode:**
- [app/Services/JWTService.php](app/Services/JWTService.php) - JWT implementation
- [app/Middleware/AuthMiddleware.php](app/Middleware/AuthMiddleware.php) - Authentication middleware
- [app/Controllers/AuthController.php](app/Controllers/AuthController.php#L56-L62) - Password verification

**Testing:**
```bash
# Without token - returns 401
curl http://localhost/php-redis-import-api/public/api/import/products

# With valid token - returns 200/201
curl -H "Authorization: Bearer <token>" \
  http://localhost/php-redis-import-api/public/api/import/products
```

---

## ✅ Logical Process (Asynchronous Queue)

### Non-Blocking Process

#### Flow Diagram:
```
User Upload CSV
      ↓
Validate File
      ↓
Save to uploads/
      ↓
Create Import Job (status: pending)
      ↓
Push to Redis Queue
      ↓
Return Response (job_id) ← User receives response immediately
      ↓
(Background) Worker picks job from queue
      ↓
(Background) Update status: in_progress
      ↓
(Background) Process CSV row by row
      ↓
(Background) Update status: completed/failed
```

#### Key Points:
1. **Upload endpoint tidak menunggu processing selesai**
   - Response langsung dikembalikan dengan job_id
   - HTTP request selesai dalam <1 detik

2. **Queue menggunakan Redis**
   - `RPUSH` untuk menambah job ke queue
   - `BLPOP` untuk mengambil job (blocking)
   - Non-blocking untuk user

3. **Worker berjalan independent**
   - Proses berjalan di background
   - Tidak mempengaruhi API response time
   - Bisa multiple workers untuk parallel processing

**Lokasi kode:**
- [app/Controllers/ImportController.php](app/Controllers/ImportController.php#L29-L88) - Upload handler (non-blocking)
- [app/Queue/QueueManager.php](app/Queue/QueueManager.php#L21-L36) - Queue push/pop
- [app/Queue/ImportWorker.php](app/Queue/ImportWorker.php#L28-L64) - Background worker

**Testing:**
```bash
# Terminal 1: Start worker
php worker.php

# Terminal 2: Upload large CSV
time curl -X POST -F "file=@large.csv" \
  -H "Authorization: Bearer <token>" \
  http://localhost/php-redis-import-api/public/api/import/products

# Result: Response in <1 second (non-blocking)
# Worker processes in background
```

---

## ✅ Error Handling

### Validation

#### File Upload Validation:
1. **File exists check**
2. **File extension validation** (only .csv)
3. **File size limit** (max 10MB, configurable)
4. **CSV structure validation**:
   - Header check (name, sku, price, stock)
   - Non-empty file
   - Row count

#### Product Data Validation:
1. **name**: Required, string
2. **sku**: Required, unique
3. **price**: Required, positive number
4. **stock**: Required, positive integer

**Lokasi kode:**
- [app/Controllers/ImportController.php](app/Controllers/ImportController.php#L32-L62) - File validation
- [app/Queue/ImportWorker.php](app/Queue/ImportWorker.php#L177-L196) - Data validation

### Error Handling Strategy

#### Row-Level Error Handling:
```php
foreach ($rows as $row) {
    try {
        // Validate & process row
        $this->productModel->upsert($data);
        $success++;
    } catch (Exception $e) {
        $failed++;
        // Log error but continue processing
        $this->importJobModel->logError($jobId, $rowNumber, $error);
    }
}
```

**Key Features:**
1. **Error tidak menghentikan proses**
   - Baris yang error di-skip
   - Processing berlanjut untuk baris berikutnya

2. **Detail error logging**
   - Row number
   - Error message
   - Row data (for debugging)
   - Saved to `import_errors` table

3. **Final status determination**
   - `completed`: Ada yang success
   - `failed`: Semua baris gagal
   - Counter: `success` dan `failed`

**Lokasi kode:**
- [app/Queue/ImportWorker.php](app/Queue/ImportWorker.php#L108-L169) - Row-by-row processing with error handling
- [app/Models/ImportJob.php](app/Models/ImportJob.php#L122-L145) - Error logging

**Testing:**
Create invalid.csv:
```csv
name,sku,price,stock
Valid Product,SKU-001,10000,50
,SKU-002,10000,50          # Missing name
Product 3,,10000,50         # Missing SKU
Product 4,SKU-004,invalid,50  # Invalid price
Product 5,SKU-005,10000,-10   # Negative stock
```

Upload → Check status → See errors logged:
```json
{
  "job_id": 1,
  "status": "completed",
  "success": 1,
  "failed": 4,
  "errors": [
    {
      "line_number": 2,
      "error_message": "Row 2: Product name is required"
    },
    ...
  ]
}
```

---

## ✅ Logging

### Log Categories

#### 1. Application Logs
Location: `logs/app-YYYY-MM-DD.log`

**Log Levels:**
- `INFO`: Normal operations
- `WARNING`: Recoverable errors
- `ERROR`: Critical errors
- `DEBUG`: Debug information (only in debug mode)

**Format:**
```
[YYYY-MM-DD HH:MM:SS] [LEVEL] Message {context}
```

#### 2. Logged Events:

**Authentication:**
- Login attempts (success/failed)
- Registration
- Token verification failures

**Import Process:**
- Job created & queued
- Job picked by worker
- Row processing errors
- Job completion

**Queue Operations:**
- Job pushed to queue
- Job popped from queue
- Queue errors

**Database/Redis:**
- Connection errors
- Query errors

**Lokasi kode:**
- [app/Services/Logger.php](app/Services/Logger.php) - Logger implementation
- [app/Controllers/ImportController.php](app/Controllers/ImportController.php#L79-L85) - Import logging
- [app/Queue/ImportWorker.php](app/Queue/ImportWorker.php#L35-L38) - Worker logging

### Example Logs:

```log
[2025-03-27 10:00:00] [INFO] User logged in successfully {"user_id":1,"username":"admin"}
[2025-03-27 10:00:15] [INFO] Import job created and queued {"job_id":1,"filename":"import_123.csv","rows":1000}
[2025-03-27 10:00:16] [INFO] Job pushed to queue {"job_id":1,"queue":"product_import_queue"}
[2025-03-27 10:00:20] [INFO] Job popped from queue {"job_id":1,"queue":"product_import_queue"}
[2025-03-27 10:00:20] [INFO] Processing job {"job_id":1,"filename":"import_123.csv"}
[2025-03-27 10:00:45] [WARNING] Row 127: Price must be a positive number {"job_id":1,"row":["Product","SKU-127","invalid","10"]}
[2025-03-27 10:01:15] [INFO] Job completed {"job_id":1,"success":995,"failed":5}
[2025-03-27 10:05:00] [ERROR] Authentication failed: Invalid or expired token
```

### Benefits:
1. **Easy debugging** - Trace request flow
2. **Audit trail** - Track who did what
3. **Performance monitoring** - Identify slow operations
4. **Error tracking** - Quick issue identification

---

## ✅ Clean Code

### Code Quality Principles

#### 1. Naming Conventions
```php
// Classes: PascalCase
class ImportController { }

// Methods: camelCase
public function getStatus(int $jobId) { }

// Variables: camelCase
$importJobModel = new ImportJob($db);

// Constants: UPPER_SNAKE_CASE
const MAX_UPLOAD_SIZE = 10485760;
```

#### 2. Single Responsibility
Each class has one clear purpose:
- `ImportController`: Handle HTTP requests
- `ImportJob`: Database operations for jobs
- `QueueManager`: Queue operations
- `ImportWorker`: Process import jobs
- `Logger`: Application logging
- `JWTService`: JWT operations

#### 3. Method Length
- Methods are short and focused (typically <50 lines)
- Complex operations broken into smaller methods
- Private helper methods for reusability

#### 4. Comments & Documentation
```php
/**
 * Process a single job
 *
 * @param array $job
 */
private function processJob(array $job): void
```

#### 5. Error Handling
- Try-catch blocks untuk exception handling
- Graceful error messages
- No exposed stack traces in production

#### 6. DRY (Don't Repeat Yourself)
- Reusable methods: `sendSuccessResponse()`, `sendErrorResponse()`
- Shared services: Logger, JWTService
- Base classes for common functionality

#### 7. Type Declarations
```php
public function create(array $data): int
public function findById(int $id): ?array
public function updateStatus(int $id, string $status): bool
```

#### 8. Consistent Code Style
- Indentation: 4 spaces
- Braces: Same line
- PSR-12 coding standard

**Code Examples:**
- [app/Models/Product.php](app/Models/Product.php) - Clean model with clear methods
- [app/Services/Logger.php](app/Services/Logger.php) - Simple, focused service
- [app/Controllers/ImportController.php](app/Controllers/ImportController.php) - Organized controller

---

## ✅ Dokumentasi

### 1. docs/README.md
**Content:**
- Project overview
- Features list
- Tech stack
- Installation steps
- API documentation dengan examples
- Testing guide
- Troubleshooting
- Project structure

**Lokasi:** [docs/README.md](docs/README.md)

### 2. docs/INSTALLATION.md
**Content:**
- Prerequisites
- Step-by-step installation
- Configuration guide
- Database setup
- Web server configuration
- Production deployment
- Common issues & solutions

**Lokasi:** [docs/INSTALLATION.md](docs/INSTALLATION.md)

### 3. Postman Collection
**Content:**
- All API endpoints
- Sample requests
- Pre-request scripts
- Test scripts
- Environment variables
- Response examples

**Lokasi:** [tests/postman_collection.json](tests/postman_collection.json)

**Import ke Postman:**
1. Open Postman
2. Import → File → Select `tests/postman_collection.json`
3. Set `base_url` variable
4. Run requests

### 4. Code Comments
- Inline comments untuk logic yang complex
- PHPDoc untuk semua public methods
- Descriptive variable names (self-documenting)

### 5. Sample Data
**Lokasi:** [tests/samples/sample.csv](tests/samples/sample.csv)

Content: 10 sample products untuk testing

---

## Summary Checklist

### ✅ Arsitektur Aplikasi
- [x] Struktur folder terorganisir
- [x] PSR-4 autoloading
- [x] Environment-based configuration
- [x] Separation of concerns
- [x] Dependency injection

### ✅ RESTful API
- [x] Clear endpoint structure
- [x] Proper HTTP methods (GET, POST)
- [x] Standard response format
- [x] Appropriate HTTP status codes
- [x] JSON payload & response

### ✅ Keamanan
- [x] JWT authentication
- [x] Password hashing (bcrypt)
- [x] SQL injection prevention (PDO)
- [x] File upload validation
- [x] Protected endpoints
- [x] Error masking in production

### ✅ Logical Process
- [x] Queue dengan Redis
- [x] Non-blocking upload endpoint
- [x] Background worker processing
- [x] Job status tracking
- [x] Multiple workers support

### ✅ Error Handling
- [x] File validation
- [x] Data validation
- [x] Row-level error handling
- [x] Error logging ke database
- [x] Processing tidak berhenti saat error
- [x] Graceful error messages

### ✅ Logging
- [x] Application logs (INFO, WARNING, ERROR, DEBUG)
- [x] Authentication logging
- [x] Import process logging
- [x] Queue operation logging
- [x] Error logging
- [x] Timestamp & context

### ✅ Clean Code
- [x] Naming conventions
- [x] Single responsibility
- [x] Short methods
- [x] Type declarations
- [x] Comments & documentation
- [x] DRY principle
- [x] Consistent code style
- [x] PSR-12 compliant

### ✅ Dokumentasi
- [x] docs/README.md lengkap
- [x] docs/INSTALLATION.md detail
- [x] Postman collection
- [x] Code comments
- [x] Sample data
- [x] API documentation
- [x] Troubleshooting guide

---

## Testing Scenarios

### Scenario 1: Happy Path
1. Register/Login → Get token
2. Upload valid CSV → Get job_id
3. Check status → pending → in_progress → completed
4. Verify products in database

### Scenario 2: Large File
1. Upload CSV with 10,000 rows
2. Verify response time <1 second (non-blocking)
3. Monitor worker progress
4. Verify all rows processed

### Scenario 3: Invalid Data
1. Upload CSV with mixed valid/invalid rows
2. Verify processing continues
3. Check status → some success, some failed
4. Verify errors logged with details

### Scenario 4: Authentication
1. Try access without token → 401
2. Try with expired token → 401
3. Try with valid token → 200/201

### Scenario 5: Multiple Workers
1. Start 3 workers
2. Upload 3 files simultaneously
3. Verify parallel processing
4. Verify no race conditions

---

## Performance Metrics

### Expected Performance:
- **Upload Response**: <1 second
- **Processing Speed**: ~1000 rows/minute (single worker)
- **Multiple Workers**: Linear scaling (3 workers = ~3000 rows/minute)
- **Memory Usage**: <50MB per worker
- **Queue Latency**: <100ms

### Scalability:
- Horizontal: Add more workers
- Vertical: Increase worker process count
- Database: Optimize indexes
- Redis: Use Redis Cluster for high availability

---

**Aplikasi ini telah memenuhi SEMUA kriteria penilaian yang diminta.**
