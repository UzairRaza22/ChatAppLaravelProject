---
sidebar_label: Teams
---

## Teams

### Base URL
```
http://localhost:8000/api/teams
```

### 1. Create Team
**Endpoint:** `POST /create`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "name": "Development Team",
  "description": "Backend development team",
  "workspace_id": "507f1f77bcf86cd799439011"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Team created successfully",
  "data": {
    "team": {
      "id": "507f1f77bcf86cd799439014",
      "name": "Development Team",
      "description": "Backend development team",
      "workspace_id": "507f1f77bcf86cd799439011",
      "creator_id": "507f1f77bcf86cd799439012",
      "members_count": 1,
      "members": [
        {
          "user_id": "507f1f77bcf86cd799439012",
          "role": "admin"
        }
      ],
      "created_at": "2024-01-01T12:00:00.000000Z",
      "updated_at": "2024-01-01T12:00:00.000000Z"
    }
  }
}
```

---

### 2. Read Teams
**Endpoint:** `GET /read`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "workspace_id": "507f1f77bcf86cd799439016"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Teams retrieved successfully",
  "data": {
    "teams": [
      {
        "id": "507f1f77bcf86cd799439014",
        "name": "Development Team",
        "description": "Backend development team",
        "workspace_id": "507f1f77bcf86cd799439011",
        "creator_id": "507f1f77bcf86cd799439012",
        "members_count": 2,
        "members": [
          {
            "user_id": "507f1f77bcf86cd799439012",
            "role": "admin"
          },
          {
            "user_id": "507f1f77bcf86cd799439013",
            "role": "member"
          }
        ],
        "created_at": "2024-01-01T12:00:00.000000Z",
        "updated_at": "2024-01-01T12:00:00.000000Z"
      }
    ]
  }
}
```

---

### 3. Update Team
**Endpoint:** `PUT /update`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "team_id": "507f1f77bcf86cd799439014",
  "name": "Updated Team Name",
  "description": "Updated team description"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Team updated successfully",
  "data": {
    "team": {
      "id": "507f1f77bcf86cd799439014",
      "name": "Updated Team Name",
      "description": "Updated team description",
      "workspace_id": "507f1f77bcf86cd799439011",
      "creator_id": "507f1f77bcf86cd799439012",
      "members_count": 2,
      "members": [
        {
          "user_id": "507f1f77bcf86cd799439012",
          "role": "admin"
        },
        {
          "user_id": "507f1f77bcf86cd799439013",
          "role": "member"
        }
      ],
      "created_at": "2024-01-01T12:00:00.000000Z",
      "updated_at": "2024-01-01T13:00:00.000000Z"
    }
  }
}
```

---

### 4. Delete Team
**Endpoint:** `DELETE /delete`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "team_id": "507f1f77bcf86cd799439014"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Team deleted successfully",
  "data": null
}
```

---

### 5. Add Member to Team
**Endpoint:** `POST /add-member`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "team_id": "507f1f77bcf86cd799439014",
  "user_ids": ["507f1f77bcf86cd799439015"]
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Member added successfully",
  "data": {
    "team": {
      "id": "507f1f77bcf86cd799439014",
      "name": "Development Team",
      "description": "Backend development team",
      "workspace_id": "507f1f77bcf86cd799439011",
      "creator_id": "507f1f77bcf86cd799439012",
      "members_count": 3,
      "members": [
        {
          "user_id": "507f1f77bcf86cd799439012",
          "role": "admin"
        },
        {
          "user_id": "507f1f77bcf86cd799439013",
          "role": "member"
        },
        {
          "user_id": "507f1f77bcf86cd799439015",
          "role": "member"
        }
      ],
      "created_at": "2024-01-01T12:00:00.000000Z",
      "updated_at": "2024-01-01T14:00:00.000000Z"
    }
  }
}
```

---

### 6. Remove Member from Team
**Endpoint:** `POST /remove-member`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "team_id": "507f1f77bcf86cd799439014",
  "user_ids": ["507f1f77bcf86cd799439015"]
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Member removed successfully",
  "data": null
}
```
