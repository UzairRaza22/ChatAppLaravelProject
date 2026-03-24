---
sidebar_label: Channels
---

## Channels

### Base URL
```
http://localhost:8000/api/channel
```

### 1. Create Channel
**Endpoint:** `POST /create`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body (Public/Private Channel):**
```json
{
  "name": "general",
  "workspace_id": "507f1f77bcf86cd799439011",
  "team_id": "507f1f77bcf86cd799439014",
  "type": "public"
}
```

**Request Body (Direct Channel):**
```json
{
  "name": "direct_chat",
  "workspace_id": "507f1f77bcf86cd799439011",
  "type": "direct",
  "direct_user_id": "507f1f77bcf86cd799439013"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Channel created successfully",
  "data": {
    "channel": {
      "id": "507f1f77bcf86cd799439016",
      "_id": "507f1f77bcf86cd799439016",
      "name": "general",
      "workspace_id": "507f1f77bcf86cd799439011",
      "team_id": "507f1f77bcf86cd799439014",
      "type": "public",
      "direct_id": null,
      "created_id": "507f1f77bcf86cd799439012",
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

### 2. Read Channel
**Endpoint:** `GET /read`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "channel_id": "507f1f77bcf86cd799439016"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Channel retrieved successfully",
  "data": {
    "channel": {
      "id": "507f1f77bcf86cd799439016",
      "_id": "507f1f77bcf86cd799439016",
      "name": "general",
      "workspace_id": "507f1f77bcf86cd799439011",
      "team_id": "507f1f77bcf86cd799439014",
      "type": "public",
      "direct_id": null,
      "created_id": "507f1f77bcf86cd799439012",
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

### 3. Update Channel
**Endpoint:** `PATCH /update`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "channel_id": "507f1f77bcf86cd799439016",
  "name": "updated-channel"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Channel updated successfully",
  "data": {
    "channel": {
      "id": "507f1f77bcf86cd799439016",
      "_id": "507f1f77bcf86cd799439016",
      "name": "updated-channel",
      "workspace_id": "507f1f77bcf86cd799439011",
      "team_id": "507f1f77bcf86cd799439014",
      "type": "public",
      "direct_id": null,
      "created_id": "507f1f77bcf86cd799439012",
      "members": [],
      "created_at": "2024-01-01T12:00:00.000000Z",
      "updated_at": "2024-01-01T13:00:00.000000Z"
    }
  }
}
```

---

### 4. Delete Channel
**Endpoint:** `DELETE /delete`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "channel_id": "507f1f77bcf86cd799439016"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Channel deleted successfully",
  "data": null
}
```

---

### 5. Add Member to Channel
**Endpoint:** `POST /add-member`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "channel_id": "507f1f77bcf86cd799439016",
  "user_ids": ["507f1f77bcf86cd799439015"]
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Member added successfully",
  "data": {
    "channel": {
      "id": "507f1f77bcf86cd799439016",
      "_id": "507f1f77bcf86cd799439016",
      "name": "general",
      "workspace_id": "507f1f77bcf86cd799439011",
      "team_id": "507f1f77bcf86cd799439014",
      "type": "public",
      "direct_id": null,
      "created_id": "507f1f77bcf86cd799439012",
      "members": [
        {
          "user_id": "507f1f77bcf86cd799439012",
          "role": "admin"
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

### 6. Remove Member from Channel
**Endpoint:** `DELETE /remove-member`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "channel_id": "507f1f77bcf86cd799439016",
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
