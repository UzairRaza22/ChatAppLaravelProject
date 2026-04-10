# Member Management API Testing Guide

## Issue Found
**Problem:** The `listAvailableMembers` and `searchMembers` endpoints expect `workspace_id` in the request, but the routes pass workspace ID as a URL parameter `{id}`.

**Location:** [app/Http/Middleware/Workspace/CheckWorkspaceExistsMiddleware.php](app/Http/Middleware/Workspace/CheckWorkspaceExistsMiddleware.php) line 20

---

## How to Test with Postman

### **Endpoint 1: List Available Members**
Get all verified users that can be added to a workspace (excludes current members)

**Request:**
- **Method:** GET
- **URL:** `http://localhost:8000/api/workspaces/{workspace_id}/available-members`
- **Example:** `http://localhost:8000/api/workspaces/507f1f77bcf86cd799439011/available-members`

**Headers:**
```
Authorization: Bearer {your_jwt_token}
Content-Type: application/json
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Available members retrieved successfully!",
  "data": {
    "available_members": [
      {
        "id": "507f1f77bcf86cd799439012",
        "name": "Jane Doe",
        "email": "jane@example.com",
        "is_active": true,
        "avatar": "https://...",
        "created_at": "2024-01-01T12:00:00.000000Z"
      }
    ],
    "count": 1
  }
}
```

**Expected Errors:**
- `401 Unauthorized` - Missing or invalid JWT token
- `404 Not Found` - Workspace does not exist

---

### **Endpoint 2: Search Members**
Search for users by name or email (minimum 2 characters)

**Request:**
- **Method:** GET
- **URL:** `http://localhost:8000/api/workspaces/{workspace_id}/search-members?query=john`
- **Example:** `http://localhost:8000/api/workspaces/507f1f77bcf86cd799439011/search-members?query=jane`

**Headers:**
```
Authorization: Bearer {your_jwt_token}
Content-Type: application/json
```

**Query Parameters:**
- `query` (required): String to search (minimum 2 characters, maximum 50)

**Response (Success):**
```json
{
  "success": true,
  "message": "Users found successfully!",
  "data": {
    "search_results": [
      {
        "id": "507f1f77bcf86cd799439012",
        "name": "Jane Doe",
        "email": "jane@example.com",
        "is_active": true,
        "avatar": "https://...",
        "created_at": "2024-01-01T12:00:00.000000Z"
      }
    ],
    "count": 1,
    "query": "jane"
  }
}
```

---

### **Endpoint 3: Add Members to Workspace**
Add one or multiple users to a workspace

**Request:**
- **Method:** POST
- **URL:** `http://localhost:8000/api/workspaces/add-members`

**Headers:**
```
Authorization: Bearer {your_jwt_token}
Content-Type: application/json
```

**Body (Option 1 - Using User IDs):**
```json
{
  "workspace_id": "507f1f77bcf86cd799439011",
  "user_ids": [
    "507f1f77bcf86cd799439012",
    "507f1f77bcf86cd799439013"
  ]
}
```

**Body (Option 2 - Using User Emails):**
```json
{
  "workspace_id": "507f1f77bcf86cd799439011",
  "user_emails": [
    "jane@example.com",
    "john@example.com"
  ]
}
```

**Body (Option 3 - Mixed):**
```json
{
  "workspace_id": "507f1f77bcf86cd799439011",
  "user_ids": ["507f1f77bcf86cd799439012"],
  "user_emails": ["jane@example.com"]
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Members added successfully!",
  "data": {
    "workspace": {
      "id": "507f1f77bcf86cd799439011",
      "name": "My Workspace",
      "description": "...",
      "members": [...]
    },
    "added_members": [...],
    "added_count": 2
  }
}
```

---

### **Endpoint 4: Remove Members from Workspace**
Remove one or multiple users from a workspace

**Request:**
- **Method:** DELETE
- **URL:** `http://localhost:8000/api/workspaces/remove-members`

**Headers:**
```
Authorization: Bearer {your_jwt_token}
Content-Type: application/json
```

**Body:**
```json
{
  "workspace_id": "507f1f77bcf86cd799439011",
  "user_ids": [
    "507f1f77bcf86cd799439012",
    "507f1f77bcf86cd799439013"
  ]
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Members removed successfully!"
}
```

---

## Backend Issue to Fix

### Problem in CheckWorkspaceExistsMiddleware
The middleware looks for `workspace_id` in the request body/query:
```php
$workspaceId = data_get($request, 'workspace_id');
```

But the **available-members** and **search-members** routes pass ID as URL parameter:
```
GET /workspaces/{id}/available-members
GET /workspaces/{id}/search-members
```

### Solution
Update the middleware to handle both cases:

**File:** `app/Http/Middleware/Workspace/CheckWorkspaceExistsMiddleware.php`

```php
<?php

namespace App\Http\Middleware\Workspace;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Workspace;

class CheckWorkspaceExistsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Try to get workspace_id from multiple sources
        $workspaceId = data_get($request, 'workspace_id') 
                    ?? $request->route('id')
                    ?? $request->query('workspace_id');

        if (!$workspaceId) {
            return response()->notFound('Workspace ID is required.');
        }

        $workspace = Workspace::where('_id', $workspaceId)->first();

        if (!$workspace) {
            return response()->notFound('Workspace not found.');
        }

        $request->merge(['workspace' => $workspace]);

        return $next($request);
    }
}
```

---

## Quick Test Checklist

- [ ] User is authenticated (has valid JWT token)
- [ ] Workspace exists in database
- [ ] User is a member of the workspace (for workspace creator endpoints)
- [ ] Target users are verified/active (`is_active = true`)
- [ ] Target users are not already members of the workspace
- [ ] Query string is at least 2 characters for search
- [ ] URL parameter format is correct (use workspace `_id`, not `id`)

---

## Frontend Integration Notes

When a frontend developer encounters "could not load user global users list", check:

1. **API Response Status:**
   - `401` → User not authenticated
   - `404` → Workspace doesn't exist
   - `422` → Validation error

2. **Check These Endpoints:**
   - `GET /api/workspaces/{workspace_id}/available-members` (lists all users)
   - `GET /api/workspaces/{workspace_id}/search-members?query=...` (search users)

3. **Required Request Parameters:**
   - `Authorization` header with JWT token
   - Correct workspace ID in URL path
   - User must be authenticated and active

