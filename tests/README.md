# Testing Files

All testing resources untuk Product Import API.

---

## 📦 Postman Collections

### 1. postman_collection.json
**Simple collection** untuk quick testing
- 7 basic requests
- All endpoints covered
- Minimal configuration

### 2. Product_Import_API.postman_collection.json ⭐ RECOMMENDED
**Complete collection** dengan features lengkap:
- 7 requests dengan test scripts
- 15+ example responses
- Auto-save token & job_id
- Automated validation tests
- Console logging
- Pre-request scripts

### 3. Product_Import_API.postman_environment.json
**Environment configuration** untuk Postman:
- Pre-configured base_url
- Default credentials
- Variable placeholders

---

## 📊 Sample Data

Folder: `samples/`

| File | Rows | Size | Use Case |
|------|------|------|----------|
| `sample.csv` | 10 | ~489 B | Quick demo |
| `sample_1k.csv` | 1,000 | ~38 KB | Standard testing |
| `sample_10k.csv` | 10,000 | ~382 KB | Performance testing |
| `sample_50k.csv` | 50,000 | ~1.9 MB | Large dataset |
| `sample_100k.csv` | 100,000 | ~5.2 MB | Stress testing |

### CSV Format
```csv
name,sku,price,stock
Product Name,UNIQUE-SKU,100000,50
```

---

## 🚀 Quick Start

### Using Postman

1. **Import Collection**
   ```
   File → Import → Select Product_Import_API.postman_collection.json
   ```

2. **Import Environment** (optional)
   ```
   File → Import → Select Product_Import_API.postman_environment.json
   ```

3. **Update Base URL**
   - Collection Variables or Environment
   - Set `base_url`: `http://localhost/php-redis-import-api/public`

4. **Run Tests**
   - Login request first
   - Upload CSV
   - Check status

### Using cURL

```bash
# Get token
TOKEN=$(curl -s -X POST http://localhost/php-redis-import-api/public/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password123"}' | jq -r '.data.token')

# Upload sample
curl -X POST http://localhost/php-redis-import-api/public/api/import/products \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@samples/sample.csv"
```

---

## 🧪 Test Scenarios

### Scenario 1: Quick Validation
**File:** `samples/sample.csv` (10 rows)
1. Upload file
2. Check status immediately
3. Verify 10 products imported

### Scenario 2: Performance Testing
**File:** `samples/sample_10k.csv` (10,000 rows)
1. Start worker
2. Upload file
3. Monitor processing speed
4. Calculate products/minute

### Scenario 3: Stress Test
**File:** `samples/sample_100k.csv` (100,000 rows)
1. Upload file
2. Monitor system resources
3. Check database performance
4. Verify all records processed

---

## 📚 Documentation

- **Complete Guide:** [../docs/POSTMAN_GUIDE.md](../docs/POSTMAN_GUIDE.md)
- **Sample Data Guide:** [../docs/SAMPLE_DATA.md](../docs/SAMPLE_DATA.md)
- **API Documentation:** [../docs/README.md](../docs/README.md)

---

## 🛠️ Generate More Samples

```bash
php ../scripts/generate_sample.php
```

Creates `sample_100k.csv` with 100,000 products.

---

**Total Test Files:** 8 files | ~12 MB
