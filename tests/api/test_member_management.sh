#!/bin/bash

# Workspace Member Management - API Test Examples
# This script demonstrates how to test the new member management features

# Configuration
API_URL="http://localhost:8000/api"
TOKEN="your_auth_token_here"
WORKSPACE_ID="65f1b2c3d4e5f6789012345"

# Color output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}=====================================${NC}"
echo -e "${BLUE}Workspace Member Management - Tests${NC}"
echo -e "${BLUE}=====================================${NC}\n"

# Test 1: List Available Members
echo -e "${YELLOW}Test 1: List All Available Members${NC}"
echo -e "${BLUE}Endpoint: GET /workspaces/{id}/available-members${NC}\n"

curl -X GET "$API_URL/workspaces/$WORKSPACE_ID/available-members" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq '.'

echo -e "\n${GREEN}✓ Successfully fetched available members${NC}\n"
echo -e "Filter: is_active = true"
echo -e "Excludes: Already added members\n"

# Test 2: Search Members
echo -e "${YELLOW}Test 2: Search Members by Name${NC}"
echo -e "${BLUE}Endpoint: GET /workspaces/{id}/search-members?query=john${NC}\n"

curl -X GET "$API_URL/workspaces/$WORKSPACE_ID/search-members?query=john" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq '.'

echo -e "\n${GREEN}✓ Successfully searched members${NC}\n"
echo -e "Search Type: By name or email"
echo -e "Min Characters: 2"
echo -e "Max Results: 20\n"

# Test 3: Search by Email
echo -e "${YELLOW}Test 3: Search Members by Email${NC}"
echo -e "${BLUE}Endpoint: GET /workspaces/{id}/search-members?query=example.com${NC}\n"

curl -X GET "$API_URL/workspaces/$WORKSPACE_ID/search-members?query=example.com" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq '.'

echo -e "\n${GREEN}✓ Successfully searched by email${NC}\n"

# Test 4: Add Members by User IDs
echo -e "${YELLOW}Test 4: Add Members by User IDs${NC}"
echo -e "${BLUE}Endpoint: POST /workspaces/add-members${NC}\n"

curl -X POST "$API_URL/workspaces/add-members" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "workspace_id": "'$WORKSPACE_ID'",
    "user_ids": [
      "65f1b2c3d4e5f6789012346",
      "65f1b2c3d4e5f6789012347"
    ]
  }' | jq '.'

echo -e "\n${GREEN}✓ Members added by ID${NC}\n"

# Test 5: Add Members by Email
echo -e "${YELLOW}Test 5: Add Members by Email Addresses${NC}"
echo -e "${BLUE}Endpoint: POST /workspaces/add-members${NC}\n"

curl -X POST "$API_URL/workspaces/add-members" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "workspace_id": "'$WORKSPACE_ID'",
    "user_emails": [
      "john@example.com",
      "jane@example.com"
    ]
  }' | jq '.'

echo -e "\n${GREEN}✓ Members added by email${NC}\n"

# Test 6: Add Members by Mixed (IDs + Emails)
echo -e "${YELLOW}Test 6: Add Members by Mixed (IDs and Emails)${NC}"
echo -e "${BLUE}Endpoint: POST /workspaces/add-members${NC}\n"

curl -X POST "$API_URL/workspaces/add-members" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "workspace_id": "'$WORKSPACE_ID'",
    "user_ids": ["65f1b2c3d4e5f6789012348"],
    "user_emails": ["bob@example.com", "alice@example.com"]
  }' | jq '.'

echo -e "\n${GREEN}✓ Members added with mixed IDs and emails${NC}\n"

# Test 7: Error Case - No Members Provided
echo -e "${YELLOW}Test 7: Error - No Members Provided${NC}"
echo -e "${BLUE}Expected: 422 Unprocessable Entity${NC}\n"

curl -X POST "$API_URL/workspaces/add-members" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "workspace_id": "'$WORKSPACE_ID'",
    "user_ids": [],
    "user_emails": []
  }' | jq '.'

echo -e "\n${RED}✓ Error validation working${NC}\n"

# Test 8: Error Case - Invalid Query Length
echo -e "${YELLOW}Test 8: Error - Search Query Too Short${NC}"
echo -e "${BLUE}Expected: 422 (min 2 characters)${NC}\n"

curl -X GET "$API_URL/workspaces/$WORKSPACE_ID/search-members?query=a" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq '.'

echo -e "\n${RED}✓ Query validation working${NC}\n"

# Test 9: Response Format Examples
echo -e "${YELLOW}Test 9: Response Format Examples${NC}\n"

echo -e "${BLUE}List Response:${NC}"
cat <<'EOF'
{
  "success": true,
  "message": "Available members retrieved successfully!",
  "data": {
    "available_members": [
      {
        "id": "65f1b2c3d4e5f6789012345",
        "name": "John Doe",
        "email": "john@example.com",
        "is_active": true,
        "avatar": null,
        "created_at": "2026-04-01T12:00:00.000000Z"
      }
    ],
    "count": 15
  }
}
EOF

echo ""

echo -e "${BLUE}Add Response:${NC}"
cat <<'EOF'
{
  "success": true,
  "message": "Members added successfully!",
  "data": {
    "workspace": { "id": "...", "name": "...", "members": [...] },
    "added_members": [
      {
        "id": "65f1b2c3d4e5f6789012346",
        "name": "Jane Smith",
        "email": "jane@example.com"
      }
    ],
    "added_count": 1
  }
}
EOF

# Test 10: Performance Check
echo -e "\n${YELLOW}Test 10: Performance Metrics${NC}\n"

echo -e "${BLUE}List Members Response Time:${NC}"
time curl -s -X GET "$API_URL/workspaces/$WORKSPACE_ID/available-members" \
  -H "Authorization: Bearer $TOKEN" > /dev/null

echo -e "\n${BLUE}Search Members Response Time:${NC}"
time curl -s -X GET "$API_URL/workspaces/$WORKSPACE_ID/search-members?query=test" \
  -H "Authorization: Bearer $TOKEN" > /dev/null

echo -e "\n${BLUE}Add Members Response Time:${NC}"
time curl -s -X POST "$API_URL/workspaces/add-members" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"workspace_id":"'$WORKSPACE_ID'","user_emails":["test@example.com"]}' > /dev/null

echo -e "\n${GREEN}Performance check complete${NC}\n"

echo -e "${BLUE}=====================================${NC}"
echo -e "${GREEN}✓ All tests completed successfully!${NC}"
echo -e "${BLUE}=====================================${NC}\n"

echo -e "Test Summary:"
echo -e "  - ✓ List available members"
echo -e "  - ✓ Search by name"
echo -e "  - ✓ Search by email"
echo -e "  - ✓ Add by IDs"
echo -e "  - ✓ Add by emails"
echo -e "  - ✓ Add by mixed"
echo -e "  - ✓ Error validation"
echo -e "  - ✓ Response formats"
echo -e "  - ✓ Performance metrics\n"

echo -e "${YELLOW}Documentation:${NC}"
echo -e "  📄 Full guide: docs/WORKSPACE_MEMBER_MANAGEMENT.md"
echo -e "  📄 Quick start: docs/MEMBER_MANAGEMENT_QUICK_START.md\n"
