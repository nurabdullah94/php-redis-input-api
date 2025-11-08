# Changelog

## [1.0.2] - 2025-11-08

### Fixed
- **Windows Compatibility**: Made PCNTL extension optional in worker (not available on Windows)
  - Added `function_exists()` checks for `pcntl_signal()` and `pcntl_signal_dispatch()`
  - Worker now runs without errors on Windows environments
- **Environment Variables Loading**: Added fallback for environment variable loading
  - Added `getenv()` fallback for `$_ENV` variables
  - Improved compatibility across different PHP configurations

### Changed Files
- `app/Queue/ImportWorker.php` - Made PCNTL functions optional
- `config/database.php` - Added getenv() fallback for environment variables
- `config/redis.php` - Added getenv() fallback for environment variables
- `worker.php` - Added error handling for Dotenv loading
- `public/index.php` - Added error handling for Dotenv loading

---

## [1.0.1] - 2025-03-27

### Fixed
- **MySQL 8.0+ Compatibility**: Changed column name from `row_number` to `line_number` in `import_errors` table to avoid conflict with MySQL 8.0+ reserved keyword `ROW_NUMBER()`

### Changed Files
- `database/migrations.sql` - Updated table schema
- `app/Models/ImportJob.php` - Updated `logError()` method and `getErrors()` query
- `app/Controllers/ImportController.php` - Updated error response mapping
- `docs/README.md` - Updated documentation
- `docs/ASSESSMENT.md` - Updated documentation
- `docs/POSTMAN_GUIDE.md` - Updated documentation
- `docs/SUMMARY.md` - Updated documentation
- `tests/postman_collection.json` - Updated example responses
- `Product_Import_API.tests/postman_collection.json` - Updated example responses

### Migration Required
If you already created the database with the old schema, run this SQL:

```sql
ALTER TABLE import_errors CHANGE COLUMN row_number line_number INT NOT NULL;
```

---

## [1.0.0] - 2025-03-27

### Added
- Initial release
- JWT Authentication
- CSV Upload & Import
- Redis Queue Processing
- Background Worker
- Job Status Tracking
- Error Logging
- Complete API Documentation
- Postman Collections
- Sample Data Generator (100k products)

### Features
- Asynchronous processing with Redis Queue
- Row-level error handling
- Non-blocking uploads
- Multiple workers support
- Comprehensive logging
- Clean code architecture (PSR-4)
- Production ready

### Documentation
- docs/README.md - Complete API documentation
- docs/INSTALLATION.md - Installation guide
- docs/QUICKSTART.md - Quick start guide
- docs/ASSESSMENT.md - Requirements compliance
- docs/PROJECT_STRUCTURE.md - Architecture documentation
- docs/POSTMAN_GUIDE.md - Postman usage guide
- docs/SAMPLE_DATA.md - Sample data documentation

### Sample Files
- tests/samples/sample.csv (10 products)
- tests/samples/sample_1k.csv (1,000 products)
- tests/samples/sample_10k.csv (10,000 products)
- tests/samples/sample_50k.csv (50,000 products)
- tests/samples/sample_100k.csv (100,000 products)
