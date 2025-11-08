# Postman Collection Guide

Panduan lengkap untuk menggunakan Postman Collection Product Import API.

---

## 📦 File Postman

Project ini menyediakan 3 file Postman:

1. **`Product_Import_API.tests/postman_collection.json`** (Recommended)
   - Collection lengkap dengan example responses
   - Test scripts untuk validasi otomatis
   - Auto-save token dan job_id
   - Dokumentasi lengkap

2. **`tests/postman_collection.json`** (Simple)
   - Collection sederhana tanpa example
   - Cocok untuk quick testing

3. **`tests/Product_Import_API.postman_environment.json`**
   - Environment variables untuk Local
   - Pre-configured dengan default values

---

## 🚀 Quick Start

### Step 1: Import Collection

1. Open Postman
2. Click **Import** button
3. Select file: `Product_Import_API.tests/postman_collection.json`
4. Click **Import**

### Step 2: Import Environment (Optional)

1. Click **Import** button
2. Select file: `tests/Product_Import_API.postman_environment.json`
3. Click **Import**
4. Select environment from dropdown (top right)

### Step 3: Update Base URL (if needed)

**Option A: Using Collection Variables**
1. Click on collection name
2. Go to **Variables** tab
3. Update `base_url` value
4. Click **Save**

**Option B: Using Environment**
1. Click environment dropdown (top right)
2. Click **Edit** icon
3. Update `base_url` value
4. Click **Save**

### Step 4: Run Requests

1. Start with **"Login"** request
2. Token will be saved automatically
3. Run other requests as needed

---

## 📋 Collection Structure

```
Product Import API - Complete
│
├── 1. Authentication
│   ├── Register New User
│   ├── Login
│   └── Get Current User Info
│
├── 2. Import Products
│   ├── Upload CSV File
│   ├── Get Import Job Status (Dynamic)
│   └── Get Import Job Status (ID: 1)
│
├── 3. Queue Management
│   └── Get Queue Status
│
└── 4. Health & Info
    └── API Health Check
```

---

## 🔑 Variables

### Collection Variables

| Variable | Description | Auto-populated |
|----------|-------------|----------------|
| `base_url` | API base URL | No (manual) |
| `token` | JWT authentication token | Yes (after login) |
| `job_id` | Import job ID | Yes (after upload) |

### Environment Variables (optional)

| Variable | Description | Default Value |
|----------|-------------|---------------|
| `base_url` | API base URL | `http://localhost/php-redis-import-api/public` |
| `token` | JWT token | Empty (auto-saved) |
| `job_id` | Job ID | Empty (auto-saved) |
| `username` | Default username | `admin` |
| `password` | Default password | `password123` |

---

## 📝 Request Details

### 1. Authentication

#### Register New User
**Method:** `POST`
**Endpoint:** `/api/auth/register`
**Auth:** None required

**Body:**
```json
{
  "username": "testuser",
  "email": "testuser@example.com",
  "password": "password123"
}
```

**Response:** JWT token + user info
**Auto-save:** Token saved to variables

---

#### Login
**Method:** `POST`
**Endpoint:** `/api/auth/login`
**Auth:** None required

**Body:**
```json
{
  "username": "admin",
  "password": "password123"
}
```

**Response:** JWT token + user info
**Auto-save:** Token saved to variables

**⚠️ Important:** Run this request FIRST before using other protected endpoints!

---

#### Get Current User Info
**Method:** `GET`
**Endpoint:** `/api/auth/me`
**Auth:** Bearer Token (automatic)

**Response:** User profile information

---

### 2. Import Products

#### Upload CSV File
**Method:** `POST`
**Endpoint:** `/api/import/products`
**Auth:** Bearer Token (automatic)

**Body:** Form-data
- Key: `file`
- Type: `File`
- Value: Select your CSV file

**CSV Format Required:**
```csv
name,sku,price,stock
Product Name,SKU-001,10000,50
Another Product,SKU-002,25000,100
```

**Response:** Job ID and status "pending"
**Auto-save:** Job ID saved to variables

**Test Scripts:**
- Validates response is 201
- Checks job_id exists
- Verifies status is "pending"
- Ensures response time < 2 seconds (non-blocking)

---

#### Get Import Job Status (Dynamic)
**Method:** `GET`
**Endpoint:** `/api/import/status/{{job_id}}`
**Auth:** Bearer Token (automatic)

**URL:** Uses `{{job_id}}` from previous upload

**Response:** Job status with progress
- Status: pending, in_progress, completed, failed
- Total rows, success count, failed count
- Error details (if any)

**Test Scripts:**
- Validates response is 200
- Checks required fields exist
- Displays status in console

---

#### Get Import Job Status (ID: 1)
**Method:** `GET`
**Endpoint:** `/api/import/status/1`
**Auth:** Bearer Token (automatic)

**URL:** Hard-coded to check job ID = 1

**Use case:** Quick check for first import job

---

### 3. Queue Management

#### Get Queue Status
**Method:** `GET`
**Endpoint:** `/api/queue/status`
**Auth:** Bearer Token (automatic)

**Response:**
- Queue name
- Number of pending jobs

**Test Scripts:**
- Validates response structure
- Displays queue info in console

---

### 4. Health & Info

#### API Health Check
**Method:** `GET`
**Endpoint:** `/`
**Auth:** None required

**Response:** API status, version, timestamp

**Test Scripts:**
- Validates API is running
- Checks response time < 500ms

---

## 🧪 Test Scripts

Setiap request memiliki built-in test scripts yang akan:

### Auto-validation
- ✅ Check HTTP status codes
- ✅ Validate response structure
- ✅ Verify required fields exist

### Auto-save variables
- ✅ Save token after login/register
- ✅ Save job_id after upload
- ✅ Available for subsequent requests

### Console logging
- ✅ Display important information
- ✅ Help with debugging
- ✅ Track request flow

### View test results:
1. Send request
2. Go to **Test Results** tab
3. See passed/failed tests

---

## 🔄 Typical Workflow

### Scenario 1: First Time Use

1. **Health Check** (optional)
   - Verify API is running

2. **Login**
   - Get JWT token
   - Token saved automatically

3. **Upload CSV**
   - Select your CSV file
   - Get job_id
   - Job ID saved automatically

4. **Check Status** (multiple times)
   - Use dynamic status request
   - Monitor progress: pending → in_progress → completed

5. **Queue Status** (optional)
   - Check pending jobs

---

### Scenario 2: Testing Multiple Uploads

1. **Login** (once)

2. **Upload CSV #1**
   - job_id saved: 1

3. **Upload CSV #2**
   - job_id updated: 2

4. **Check Status #1**
   - Manually change URL to `/status/1`

5. **Check Status #2**
   - Use dynamic URL `/status/{{job_id}}`

6. **Queue Status**
   - See all pending jobs

---

### Scenario 3: Testing with New User

1. **Register New User**
   - Create account
   - Token saved automatically

2. **Get User Info**
   - Verify registration

3. **Upload CSV**
   - Test with new user

4. **Check Status**
   - Monitor progress

---

## 🎯 Example Responses

### Success Responses

#### Login Success
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

#### Upload Success
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

#### Status - Completed with Errors
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
    "created_at": "2025-03-27 10:00:00",
    "updated_at": "2025-03-27 10:00:52",
    "errors": [
      {
        "line_number": 45,
        "error_message": "Row 45: SKU is required",
        "row_data": ["Product Name", "", "10000", "50"]
      }
    ],
    "total_errors": 5
  }
}
```

---

### Error Responses

#### Unauthorized (401)
```json
{
  "success": false,
  "message": "Unauthorized",
  "error": "Token not provided"
}
```

#### Invalid File Type (400)
```json
{
  "success": false,
  "message": "Invalid file type. Only CSV files are allowed"
}
```

#### Invalid Credentials (401)
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

---

## 🛠️ Troubleshooting

### Problem: "Token not provided" error

**Cause:** Token variable is empty

**Solution:**
1. Run **Login** request first
2. Check token saved: View Variables
3. Token should appear in `{{token}}` variable

---

### Problem: All requests return 401

**Cause:** Token expired (1 hour expiry)

**Solution:**
1. Run **Login** request again
2. New token will be saved automatically
3. Retry failed requests

---

### Problem: "File not found" when uploading

**Cause:** CSV file path incorrect

**Solution:**
1. Click on file upload field
2. Use **Select Files** button
3. Navigate to your CSV file
4. Ensure file exists and is accessible

---

### Problem: Variables not updating

**Cause:** Test scripts not running

**Solution:**
1. Check **Test Results** tab
2. Ensure tests are passing
3. Try manual save:
   - Right-click response
   - Extract variable
   - Save to collection

---

### Problem: Base URL incorrect

**Cause:** URL doesn't match your setup

**Solution:**

**For Laragon (default):**
```
http://localhost/php-redis-import-api/public
```

**For PHP built-in server:**
```
http://localhost:8000
```

**For custom domain:**
```
http://product-import.test
```

Update in Collection Variables or Environment.

---

## 📊 Monitoring & Debugging

### Console Output

Open Postman Console:
1. View → Show Postman Console
2. See detailed logs for each request
3. View test results and variable changes

### Test Results

After each request:
1. Go to **Test Results** tab
2. See which tests passed/failed
3. View test execution details

### Response Time

Check performance:
1. Look at response time (bottom right)
2. Upload should be < 2 seconds (non-blocking)
3. Other requests should be < 500ms

---

## 🎨 Customization

### Adding New Requests

1. Right-click on folder
2. Select **Add Request**
3. Configure method, URL, headers
4. Add test scripts if needed

### Creating Test Scripts

```javascript
// Test: Check status code
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

// Test: Check response structure
pm.test("Response has data", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('data');
});

// Save variable
var jsonData = pm.response.json();
pm.collectionVariables.set('my_var', jsonData.data.value);
```

### Pre-request Scripts

```javascript
// Set timestamp
pm.collectionVariables.set('timestamp', Date.now());

// Log information
console.log('Sending request to:', pm.request.url);
```

---

## 📚 Advanced Features

### Running Collection

Run all requests automatically:
1. Click collection name
2. Click **Run** button
3. Select requests to run
4. Configure iterations & delay
5. Click **Run Product Import API**

### Exporting Collection

Share with team:
1. Click collection **...** menu
2. Select **Export**
3. Choose Collection v2.1
4. Save file

### Creating Documentation

Generate documentation:
1. Click collection name
2. Click **View Documentation**
3. Share documentation URL

---

## 🔐 Security Notes

### Token Security

- ✅ Token marked as "secret" in environment
- ✅ Auto-expires after 1 hour
- ✅ Transmitted via secure headers
- ⚠️ Don't share exported collections with tokens

### Credentials

Default credentials for testing:
- Username: `admin`
- Password: `password123`

⚠️ **Change default password in production!**

---

## 📝 Cheat Sheet

### Quick Commands

**Import Collection:**
`File → Import → Select JSON → Import`

**Update Variables:**
`Collection → Variables → Update → Save`

**Run Request:**
`Select request → Click Send (or Ctrl+Enter)`

**View Console:**
`View → Show Postman Console`

**Export Collection:**
`Collection ... → Export → Save`

### Common Variables

**Use in URL:**
```
{{base_url}}/api/auth/login
{{base_url}}/api/import/status/{{job_id}}
```

**Use in Headers:**
```
Authorization: Bearer {{token}}
```

**Use in Body:**
```json
{
  "username": "{{username}}",
  "password": "{{password}}"
}
```

---

## ✅ Pre-flight Checklist

Before testing:
- [ ] API server running
- [ ] Database created & migrated
- [ ] Redis server running
- [ ] Queue worker started (`php worker.php`)
- [ ] Collection imported to Postman
- [ ] Base URL configured correctly
- [ ] Have test CSV file ready

---

## 🎉 You're Ready!

Follow the workflow above and start testing the API!

**Recommended Order:**
1. Health Check
2. Login
3. Upload CSV
4. Check Status (multiple times)
5. Queue Status

**Happy Testing! 🚀**

---

## 📞 Need Help?

- Check [docs/README.md](docs/README.md) for API documentation
- Read [docs/INSTALLATION.md](docs/INSTALLATION.md) for setup issues
- See [docs/QUICKSTART.md](docs/QUICKSTART.md) for quick setup

---

**Last Updated:** 2025-03-27
