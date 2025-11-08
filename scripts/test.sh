#!/bin/bash

# Product Import API - Test Script
# This script tests all API endpoints

# Configuration
BASE_URL="http://localhost/php-redis-import-api/public"
USERNAME="admin"
PASSWORD="password123"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "=========================================="
echo "Product Import API - Test Script"
echo "=========================================="
echo ""

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}→ $1${NC}"
}

# Test 1: Health Check
echo "Test 1: Health Check"
print_info "GET /"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/")
if [ "$RESPONSE" -eq 200 ]; then
    print_success "Health check passed (200)"
else
    print_error "Health check failed ($RESPONSE)"
    exit 1
fi
echo ""

# Test 2: Login
echo "Test 2: Login"
print_info "POST /api/auth/login"
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/auth/login" \
  -H "Content-Type: application/json" \
  -d "{\"username\":\"$USERNAME\",\"password\":\"$PASSWORD\"}")

TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | sed 's/"token":"//')

if [ -z "$TOKEN" ]; then
    print_error "Login failed - No token received"
    echo "Response: $LOGIN_RESPONSE"
    exit 1
else
    print_success "Login successful"
    print_info "Token: ${TOKEN:0:50}..."
fi
echo ""

# Test 3: Get Current User
echo "Test 3: Get Current User"
print_info "GET /api/auth/me"
USER_RESPONSE=$(curl -s -X GET "$BASE_URL/api/auth/me" \
  -H "Authorization: Bearer $TOKEN")

if echo "$USER_RESPONSE" | grep -q '"success":true'; then
    print_success "Get user info successful"
    USERNAME_FROM_API=$(echo $USER_RESPONSE | grep -o '"username":"[^"]*' | sed 's/"username":"//')
    print_info "Username: $USERNAME_FROM_API"
else
    print_error "Get user info failed"
    echo "Response: $USER_RESPONSE"
fi
echo ""

# Test 4: Upload CSV
echo "Test 4: Upload CSV File"
print_info "POST /api/import/products"

if [ ! -f "sample.csv" ]; then
    print_error "sample.csv not found"
    exit 1
fi

UPLOAD_RESPONSE=$(curl -s -X POST "$BASE_URL/api/import/products" \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@sample.csv")

JOB_ID=$(echo $UPLOAD_RESPONSE | grep -o '"job_id":[0-9]*' | sed 's/"job_id"://')

if [ -z "$JOB_ID" ]; then
    print_error "Upload failed - No job_id received"
    echo "Response: $UPLOAD_RESPONSE"
else
    print_success "Upload successful"
    print_info "Job ID: $JOB_ID"
fi
echo ""

# Test 5: Check Job Status
echo "Test 5: Check Job Status"
print_info "GET /api/import/status/$JOB_ID"

# Wait a bit for processing
print_info "Waiting 2 seconds for processing..."
sleep 2

STATUS_RESPONSE=$(curl -s -X GET "$BASE_URL/api/import/status/$JOB_ID" \
  -H "Authorization: Bearer $TOKEN")

if echo "$STATUS_RESPONSE" | grep -q '"success":true'; then
    print_success "Get job status successful"

    STATUS=$(echo $STATUS_RESPONSE | grep -o '"status":"[^"]*' | sed 's/"status":"//')
    TOTAL=$(echo $STATUS_RESPONSE | grep -o '"total":[0-9]*' | sed 's/"total"://')
    SUCCESS=$(echo $STATUS_RESPONSE | grep -o '"success":[0-9]*' | sed 's/"success"://')
    FAILED=$(echo $STATUS_RESPONSE | grep -o '"failed":[0-9]*' | sed 's/"failed"://')

    print_info "Status: $STATUS"
    print_info "Total: $TOTAL"
    print_info "Success: $SUCCESS"
    print_info "Failed: $FAILED"
else
    print_error "Get job status failed"
    echo "Response: $STATUS_RESPONSE"
fi
echo ""

# Test 6: Queue Status
echo "Test 6: Queue Status"
print_info "GET /api/queue/status"
QUEUE_RESPONSE=$(curl -s -X GET "$BASE_URL/api/queue/status" \
  -H "Authorization: Bearer $TOKEN")

if echo "$QUEUE_RESPONSE" | grep -q '"success":true'; then
    print_success "Get queue status successful"

    PENDING=$(echo $QUEUE_RESPONSE | grep -o '"pending_jobs":[0-9]*' | sed 's/"pending_jobs"://')
    print_info "Pending jobs: $PENDING"
else
    print_error "Get queue status failed"
    echo "Response: $QUEUE_RESPONSE"
fi
echo ""

# Test 7: Unauthorized Access
echo "Test 7: Unauthorized Access (should fail)"
print_info "GET /api/import/status/$JOB_ID (without token)"
UNAUTH_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/api/import/status/$JOB_ID")

if [ "$UNAUTH_RESPONSE" -eq 401 ]; then
    print_success "Unauthorized access blocked (401)"
else
    print_error "Unauthorized access not blocked ($UNAUTH_RESPONSE)"
fi
echo ""

# Test 8: Invalid Token
echo "Test 8: Invalid Token (should fail)"
print_info "GET /api/import/status/$JOB_ID (with invalid token)"
INVALID_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/api/import/status/$JOB_ID" \
  -H "Authorization: Bearer invalid-token-123")

if [ "$INVALID_RESPONSE" -eq 401 ]; then
    print_success "Invalid token rejected (401)"
else
    print_error "Invalid token not rejected ($INVALID_RESPONSE)"
fi
echo ""

# Summary
echo "=========================================="
echo "Test Summary"
echo "=========================================="
print_success "All tests completed!"
echo ""
echo "Next steps:"
echo "1. Check worker output to see processing details"
echo "2. Verify products in database:"
echo "   mysql -u root -p product_import -e 'SELECT * FROM products LIMIT 5;'"
echo "3. Check import job details:"
echo "   mysql -u root -p product_import -e 'SELECT * FROM import_jobs WHERE id=$JOB_ID;'"
echo ""
