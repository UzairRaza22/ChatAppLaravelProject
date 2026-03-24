---
sidebar_label: Workspaces
---

## Workspaces

### Base URL
```
http://localhost:8000/api/workspaces
```

### 1. Create Workspace
**Endpoint:** `POST /create`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "name": "My Workspace",
  "description": "Workspace description"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Workspace created successfully",
  "data": {
    "workspace": {
      "id": "507f1f77bcf86cd799439011",
      "name": "My Workspace",
      "description": "Workspace description",
      "creator_id": "507f1f77bcf86cd799439012",
      "created_at": "2024-01-01T12:00:00.000000Z",
      "updated_at": "2024-01-01T12:00:00.000000Z",
      "members": [
        {
          "id": "507f1f77bcf86cd799439012",
          "name": "John Doe",
          "email": "john@example.com",
          "is_active": true,
          "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
          "updated_at": "2024-01-01T12:00:00.000000Z",
          "created_at": "2024-01-01T12:00:00.000000Z"
        }
      ]
    }
  }
}
```

---

### 2. Read Workspaces
**Endpoint:** `GET /read`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Workspaces retrieved successfully",
  "data": {
    "workspaces": [
      {
        "id": "507f1f77bcf86cd799439011",
        "name": "My Workspace",
        "description": "Workspace description",
        "creator_id": "507f1f77bcf86cd799439012",
        "created_at": "2024-01-01T12:00:00.000000Z",
        "updated_at": "2024-01-01T12:00:00.000000Z",
        "members": []
      }
    ]
  }
}
```

---

### 3. Update Workspace
**Endpoint:** `PATCH /update`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "workspace_id": "507f1f77bcf86cd799439011",
  "name": "Updated Workspace Name",
  "description": "Updated description"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Workspace updated successfully",
  "data": {
    "workspace": {
      "id": "507f1f77bcf86cd799439011",
      "name": "Updated Workspace Name",
      "description": "Updated description",
      "creator_id": "507f1f77bcf86cd799439012",
      "created_at": "2024-01-01T12:00:00.000000Z",
      "updated_at": "2024-01-01T13:00:00.000000Z",
      "members": []
    }
  }
}
```

---

### 4. Delete Workspace
**Endpoint:** `DELETE /delete`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "workspace_id": "507f1f77bcf86cd799439011"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Workspace deleted successfully",
  "data": null
}
```

---

### 5. Add Members to Workspace
**Endpoint:** `POST /add-members`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "workspace_id": "507f1f77bcf86cd799439011",
  "user_ids": ["507f1f77bcf86cd799439013","507f1f77bcf86cd799439014"]
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Members added successfully",
  "data": {
    "workspace": {
      "id": "507f1f77bcf86cd799439011",
      "name": "My Workspace",
      "description": "Workspace description",
      "creator_id": "507f1f77bcf86cd799439012",
      "created_at": "2024-01-01T12:00:00.000000Z",
      "updated_at": "2024-01-01T12:00:00.000000Z",
      "members": [
        {
          "id": "507f1f77bcf86cd799439012",
          "name": "John Doe",
          "email": "john@example.com",
          "is_active": true,
          "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
          "updated_at": "2024-01-01T12:00:00.000000Z",
          "created_at": "2024-01-01T12:00:00.000000Z"
        },
        {
          "id": "507f1f77bcf86cd799439013",
          "name": "Jane Smith",
          "email": "jane@example.com",
          "is_active": true,
          "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
          "updated_at": "2024-01-01T12:00:00.000000Z",
          "created_at": "2024-01-01T12:00:00.000000Z"
        }
      ]
    }
  }
}
```

---

### 6. Remove Members from Workspace
**Endpoint:** `DELETE /remove-members`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "workspace_id": "507f1f77bcf86cd799439011",
  "user_ids": ["507f1f77bcf86cd799439013"]
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Members removed successfully",
  "data": null
}
```
