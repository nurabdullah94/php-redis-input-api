# Utility Scripts

Helper scripts untuk Product Import API.

---

## 🛠️ Available Scripts

### 1. generate_sample.php
**Purpose:** Generate large sample CSV files

**Usage:**
```bash
php generate_sample.php
```

**Output:**
- Creates `sample_100k.csv` dengan 100,000 products
- File size: ~5.2 MB
- Format: name, sku, price, stock

**Configuration:**
Edit file untuk mengubah:
```php
$totalProducts = 100000;  // Change quantity
$categories = [...];       // Customize categories
$brands = [...];           // Customize brands
```

**Features:**
- 20 product categories
- 20 brands
- 15 adjectives for variety
- Random prices (100K - 50M)
- Random stock (0 - 1000)
- Progress indicator
- Performance stats

---

### 2. test.sh
**Purpose:** Automated API testing

**Usage:**
```bash
bash test.sh
```

**Tests Performed:**
1. Health check
2. Login
3. Get current user
4. Upload CSV
5. Check job status
6. Queue status
7. Unauthorized access (should fail)
8. Invalid token (should fail)

**Requirements:**
- API server running
- Database setup
- Redis running
- Worker running
- cURL installed

**Output:**
```
✓ Health check passed (200)
✓ Login successful
✓ Get user info successful
✓ Upload successful (Job ID: 123)
✓ Get job status successful
✓ Get queue status successful
✓ Unauthorized access blocked (401)
✓ Invalid token rejected (401)
```

---

## 📚 Documentation

- **Sample Data Guide:** [../docs/SAMPLE_DATA.md](../docs/SAMPLE_DATA.md)
- **Testing Guide:** [../docs/POSTMAN_GUIDE.md](../docs/POSTMAN_GUIDE.md)

---

## 🔧 Adding New Scripts

Place new utility scripts in this folder:
- Data generators
- Migration scripts
- Cleanup scripts
- Deployment scripts

---

**Total Scripts:** 2 files
