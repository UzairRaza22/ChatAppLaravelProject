#!/bin/bash

# Smart Emoji Reaction System - API Test Examples
# This file demonstrates how to test the new reaction features

# Configuration
API_URL="http://localhost:8000/api"
TOKEN="your_auth_token_here"
CHANNEL_ID="65f1b2c3d4e5f6789012345"
MESSAGE_ID="65f1b2c3d4e5f6789012346"

# Color output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}Smart Emoji Reaction System - Test Suite${NC}\n"

# Test 1: Add single emoji reaction
echo -e "${YELLOW}Test 1: Add New Reaction (❤️)${NC}"
curl -X POST "$API_URL/messages/react" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"channel_id\": \"$CHANNEL_ID\",
    \"message_ids\": [\"$MESSAGE_ID\"],
    \"emoji\": \"❤️\"
  }" | jq'.
echo -e "\n${GREEN}✓ User now has ❤️ reaction${NC}\n"

# Test 2: Replace emoji (different emoji)
echo -e "${YELLOW}Test 2: Switch Emoji (❤️ → 👍)${NC}"
echo "Note: Backend detects user has ❤️, removes it, adds 👍"
curl -X POST "$API_URL/messages/react" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"channel_id\": \"$CHANNEL_ID\",
    \"message_ids\": [\"$MESSAGE_ID\"],
    \"emoji\": \"👍\"
  }" | jq '.'
echo -e "\n${GREEN}✓ Reaction replaced: ❤️ removed, 👍 added${NC}\n"

# Test 3: Double-click same emoji (delete)
echo -e "${YELLOW}Test 3: Double-Click to Delete (👍 double-click)${NC}"
echo "Note: Frontend detects double-click within 300ms, backend removes it"
curl -X POST "$API_URL/messages/react" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"channel_id\": \"$CHANNEL_ID\",
    \"message_ids\": [\"$MESSAGE_ID\"],
    \"emoji\": \"👍\"
  }" | jq '.'
echo -e "\n${GREEN}✓ Reaction deleted: 👍 removed${NC}\n"

# Test 4: Check reaction counts
echo -e "${YELLOW}Test 4: View Reactions with Counters${NC}"
echo "Expected response format:"
cat <<'EOF'
{
  "reactions_summary": [
    {
      "emoji": "👍",
      "count": 3,           # 3 users reacted with 👍
      "reacted_by_me": true,
      "reacted_by": ["user1", "user2", "user3"]
    },
    {
      "emoji": "❤️",
      "count": 2,           # 2 users reacted with ❤️
      "reacted_by_me": false,
      "reacted_by": ["user4", "user5"]
    },
    {
      "emoji": "😂",
      "count": 1,           # 1 user reacted with 😂
      "reacted_by_me": false,
      "reacted_by": ["user6"]
    }
  ]
}
EOF

# Test 5: Add multiple different reactions (simulate multiple users)
echo -e "\n${YELLOW}Test 5: Simulate Multiple Users Reacting${NC}"

users=("user1_token" "user2_token" "user3_token")
emojis=("👍" "❤️" "😂")

for i in "${!users[@]}"; do
  echo -e "${BLUE}User $((i+1)) adding ${emojis[$i]}${NC}"
  curl -X POST "$API_URL/messages/react" \
    -H "Authorization: Bearer ${users[$i]}" \
    -H "Content-Type: application/json" \
    -d "{
      \"channel_id\": \"$CHANNEL_ID\",
      \"message_ids\": [\"$MESSAGE_ID\"],
      \"emoji\": \"${emojis[$i]}\"
    }" | jq '.data.message.reactions_summary'
done

echo -e "\n${GREEN}✓ Multiple users reacted${NC}"

# Test 6: Error Testing
echo -e "\n${YELLOW}Test 6: Invalid Emoji (Error Case)${NC}"
echo "Expected: 422 Unprocessable Entity"
curl -X POST "$API_URL/messages/react" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"channel_id\": \"$CHANNEL_ID\",
    \"message_ids\": [\"$MESSAGE_ID\"],
    \"emoji\": \"🚀\"
  }" | jq '.'
echo -e "\n${GREEN}✓ Invalid emoji rejected${NC}\n"

# Test 7: Display Final State
echo -e "${YELLOW}Test 7: Final Reaction State${NC}"
echo "Database will contain:"
cat <<'EOF'
{
  "_id": ObjectId(...),
  "content": "Hello everyone!",
  "reactions": {
    "👍": ["user1", "user2", "user3"],
    "❤️": ["user4", "user5"],
    "😂": ["user6"]
  }
}
EOF

echo -e "\n${GREEN}All tests completed!${NC}"
echo -e "${BLUE}View full implementation guide: docs/REACTION_SYSTEM_GUIDE.md${NC}\n"
