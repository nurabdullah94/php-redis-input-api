# Sample Data Files

Documentation untuk semua sample CSV files yang tersedia untuk testing.

---

## 📦 Available Sample Files

| File | Rows | Size | Use Case |
|------|------|------|----------|
| `tests/samples/sample.csv` | 10 | ~489 B | Quick testing & demo |
| `tests/samples/sample_1k.csv` | 1,000 | ~38 KB | Small dataset testing |
| `tests/samples/sample_10k.csv` | 10,000 | ~382 KB | Medium dataset testing |
| `tests/samples/sample_50k.csv` | 50,000 | ~1.9 MB | Large dataset testing |
| `tests/samples/sample_100k.csv` | 100,000 | ~5.2 MB | Stress testing |

---

## 📊 File Details

### 1. **tests/samples/sample.csv** (Original)
**Purpose:** Quick demo & validation
**Rows:** 10 products
**Size:** ~489 bytes

**Content:** Hand-picked products with realistic data
- Laptop Dell XPS 13
- iPhone 14 Pro Max
- Samsung Galaxy S23 Ultra
- MacBook Pro M2
- Sony WH-1000XM5
- iPad Air 5th Gen
- Logitech MX Master 3S
- Dell UltraSharp 27
- Canon EOS R6 Mark II
- Sony A7 IV

**Use case:**
- First-time testing
- API validation
- Demo purposes
- Quick verification

---

### 2. **tests/samples/sample_1k.csv**
**Purpose:** Small dataset testing
**Rows:** 1,000 products
**Size:** ~38 KB

**Content:** Generated products with variety
- 5 categories (Laptop, Smartphone, Tablet, Camera, Monitor)
- 5 brands (Apple, Samsung, Dell, HP, Sony)
- SKU format: SKU-000001 to SKU-001000

**Use case:**
- Standard testing
- Performance baseline
- Quick import testing
- Development testing

**Expected processing time:** ~1 minute (single worker)

---

### 3. **tests/samples/sample_10k.csv**
**Purpose:** Medium dataset testing
**Rows:** 10,000 products
**Size:** ~382 KB

**Content:** Generated products with variety
- Same structure as 1k sample
- SKU format: SKU-000001 to SKU-010000

**Use case:**
- Realistic load testing
- Queue monitoring
- Progress tracking testing
- Error handling verification

**Expected processing time:** ~10 minutes (single worker)

---

### 4. **tests/samples/sample_50k.csv**
**Purpose:** Large dataset testing
**Rows:** 50,000 products
**Size:** ~1.9 MB

**Content:** Generated products with variety
- SKU format: SKU-000001 to SKU-050000

**Use case:**
- Large import testing
- Multiple workers testing
- Performance optimization
- Production simulation

**Expected processing time:** ~50 minutes (single worker)

**With 3 workers:** ~17 minutes

---

### 5. **tests/samples/sample_100k.csv** ⭐
**Purpose:** Stress testing & scalability
**Rows:** 100,000 products
**Size:** ~5.2 MB

**Content:** Generated products with rich variety
- 20 categories (Laptop, Smartphone, Tablet, Smartwatch, Headphone, etc.)
- 20 brands (Apple, Samsung, Dell, HP, Lenovo, Asus, etc.)
- 15 adjectives (Pro, Max, Plus, Ultra, Premium, etc.)
- SKU format: BRAND-CATEGORY-NUMBER (e.g., APPL-LAPT-000001)
- Price range: 100,000 - 50,000,000
- Stock range: 0 - 1,000

**Examples:**
```csv
name,sku,price,stock
"Apple Projector Max",APPL-PROJ-000001,45649355,18
"Huawei Monitor Grande",HUAW-MONI-000002,30631041,508
"Corsair Headphone Smart",CORS-HEAD-000003,32820108,954
"Canon Laptop Wireless 10000",CANO-LAPT-100000,37445213,191
```

**Use case:**
- Stress testing
- Scalability testing
- Worker performance testing
- Queue performance testing
- Database optimization testing
- Production readiness testing

**Expected processing time:**
- Single worker: ~100 minutes (~1.7 hours)
- 3 workers: ~33 minutes
- 5 workers: ~20 minutes
- 10 workers: ~10 minutes

---

## 📋 CSV Format

All sample files follow this format:

```csv
name,sku,price,stock
Product Name,UNIQUE-SKU,Price,Stock Quantity
```

**Columns:**
1. **name** (string) - Product name
2. **sku** (string) - Unique product SKU
3. **price** (decimal) - Price in rupiah (no decimals)
4. **stock** (integer) - Stock quantity

---

## 🚀 Usage

### **Upload via Postman**

1. Open Postman
2. Select "Upload CSV File" request
3. In Body tab:
   - Select `file` field
   - Click "Select Files"
   - Choose your sample file
4. Click "Send"

### **Upload via cURL**

```bash
# Get token first
TOKEN=$(curl -s -X POST http://localhost/php-redis-import-api/public/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password123"}' | jq -r '.data.token')

# Upload 100k sample
curl -X POST http://localhost/php-redis-import-api/public/api/import/products \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@tests/samples/sample_100k.csv"
```

### **Upload via PHP**

```php
$token = 'your-jwt-token';
$file = 'tests/samples/sample_100k.csv';

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'http://localhost/php-redis-import-api/public/api/import/products',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $token"
    ],
    CURLOPT_POSTFIELDS => [
        'file' => new CURLFile($file)
    ]
]);

$response = curl_exec($curl);
curl_close($curl);

$result = json_decode($response, true);
echo "Job ID: " . $result['data']['job_id'];
```

---

## 🧪 Testing Scenarios

### **Scenario 1: Quick Validation**
**File:** `tests/samples/sample.csv` (10 rows)
**Purpose:** Verify basic functionality

1. Upload file
2. Check job status immediately
3. Wait 10 seconds
4. Check status again
5. Verify all 10 products imported

---

### **Scenario 2: Performance Testing**
**File:** `tests/samples/sample_10k.csv` (10,000 rows)
**Purpose:** Measure processing speed

1. Start worker
2. Upload file
3. Monitor worker output
4. Check status every minute
5. Calculate products/minute

---

### **Scenario 3: Multiple Workers**
**File:** `tests/samples/sample_50k.csv` (50,000 rows)
**Purpose:** Test parallel processing

1. Start 3 workers simultaneously
2. Upload file
3. Monitor all workers
4. Verify no duplicate processing
5. Check final results

---

### **Scenario 4: Stress Test**
**File:** `tests/samples/sample_100k.csv` (100,000 rows)
**Purpose:** Test system limits

1. Upload 100k file
2. Monitor system resources (CPU, memory)
3. Check database performance
4. Monitor Redis queue
5. Verify all records processed

---

### **Scenario 5: Error Handling**
**File:** Create invalid CSV with mixed valid/invalid data
**Purpose:** Test error recovery

1. Modify sample file with errors
2. Upload file
3. Verify processing continues
4. Check error logging
5. Verify partial import success

---

## 🔄 Regenerating Sample Files

### **Regenerate All Samples**

```bash
# Regenerate 100k sample
php scripts/generate_sample.php

# Regenerate other sizes
php -r "
// Script to create 1k, 10k, 50k samples
// See scripts/generate_sample.php for full code
"
```

### **Custom Sample Size**

Edit `scripts/generate_sample.php` and change:
```php
$totalProducts = 100000;  // Change to desired size
```

---

## 📊 Performance Benchmarks

### **Single Worker Performance**

| File | Rows | Expected Time | Speed |
|------|------|---------------|-------|
| tests/samples/sample.csv | 10 | <1 second | N/A |
| tests/samples/sample_1k.csv | 1,000 | ~1 minute | ~1000/min |
| tests/samples/sample_10k.csv | 10,000 | ~10 minutes | ~1000/min |
| tests/samples/sample_50k.csv | 50,000 | ~50 minutes | ~1000/min |
| tests/samples/sample_100k.csv | 100,000 | ~100 minutes | ~1000/min |

### **Multiple Workers Performance**

**3 Workers:**

| File | Rows | Expected Time | Speed |
|------|------|---------------|-------|
| tests/samples/sample_50k.csv | 50,000 | ~17 minutes | ~3000/min |
| tests/samples/sample_100k.csv | 100,000 | ~33 minutes | ~3000/min |

**5 Workers:**

| File | Rows | Expected Time | Speed |
|------|------|---------------|-------|
| tests/samples/sample_100k.csv | 100,000 | ~20 minutes | ~5000/min |

**Note:** Performance may vary based on system resources and database configuration.

---

## 💾 Database Impact

### **Storage Requirements**

| File | Rows | DB Size (approx) |
|------|------|------------------|
| tests/samples/sample.csv | 10 | <1 KB |
| tests/samples/sample_1k.csv | 1,000 | ~100 KB |
| tests/samples/sample_10k.csv | 10,000 | ~1 MB |
| tests/samples/sample_50k.csv | 50,000 | ~5 MB |
| tests/samples/sample_100k.csv | 100,000 | ~10 MB |

**Note:** Actual size depends on database engine and indexing.

---

## 🛠️ Troubleshooting

### **File Upload Fails**

**Error:** "File size exceeds maximum"

**Solution:** Update `.env`:
```env
MAX_UPLOAD_SIZE=10485760  # 10MB
```

For 100k file, increase to:
```env
MAX_UPLOAD_SIZE=20971520  # 20MB
```

---

### **Worker Stops Processing**

**Cause:** PHP timeout or memory limit

**Solution:** Update `php.ini`:
```ini
max_execution_time = 0
memory_limit = 512M
```

---

### **Database Errors**

**Error:** "MySQL server has gone away"

**Solution:** Increase MySQL timeout in `my.cnf`:
```ini
wait_timeout = 28800
max_allowed_packet = 64M
```

---

## 📝 Sample Data Characteristics

### **Product Names**
- Format: `{Brand} {Category} {Adjective} [Number]`
- Examples:
  - Apple Laptop Pro
  - Samsung Smartphone Max 100
  - Dell Monitor Ultra

### **SKU Format**
- **tests/samples/sample.csv:** Manual (e.g., DELL-XPS13-001)
- **1k-50k:** SKU-XXXXXX (6 digits)
- **100k:** BRAND-CATEGORY-XXXXXX (4+4+6 chars)

### **Price Range**
- Realistic prices in IDR
- Range: 100,000 - 50,000,000

### **Stock Range**
- Random quantities
- Range: 0 - 1,000

---

## ✅ Best Practices

### **For Development**
- Use `tests/samples/sample.csv` or `tests/samples/sample_1k.csv`
- Quick feedback loop
- Easy to debug

### **For Testing**
- Use `tests/samples/sample_10k.csv`
- Realistic performance testing
- Reasonable processing time

### **For Production Validation**
- Use `tests/samples/sample_50k.csv` or `tests/samples/sample_100k.csv`
- Stress testing
- Capacity planning

### **For Demos**
- Use `tests/samples/sample.csv`
- Shows real product names
- Quick to complete

---

## 📚 Related Documentation

- [docs/README.md](docs/README.md) - API documentation
- [docs/QUICKSTART.md](docs/QUICKSTART.md) - Quick start guide
- [docs/POSTMAN_GUIDE.md](docs/POSTMAN_GUIDE.md) - Postman usage
- [scripts/generate_sample.php](scripts/generate_sample.php) - Generator script

---

**Last Updated:** 2025-03-27

**Generated By:** [scripts/generate_sample.php](scripts/generate_sample.php)
