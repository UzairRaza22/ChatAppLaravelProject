---
sidebar_label: Messages
---

## Messages

### Base URL
```
http://localhost:8000/api/messages
```

### 1. Send Message
**Endpoint:** `POST /send`

**Headers:**
```
Content-Type: application/json or multipart/form-data
token: {login_token}
```

**Request Body (Text Only):**
```json
{
  "channel_id": "507f1f77bcf86cd799439016",
  "message": "Hello, world!"
}
```

**Request Body (With File - Form Data):**
```
channel_id: 507f1f77bcf86cd799439016
message: Hello with file!
file: [file upload]
```

**Success Response:**
```json
{
  "success": true,
  "message": "Message sent successfully",
  "data": {
    "message": {
      "id": "507f1f77bcf86cd799439017",
      "workspace_id": "507f1f77bcf86cd799439011",
      "sender_id": "507f1f77bcf86cd799439012",
      "receiver_id": null,
      "channel_id": "507f1f77bcf86cd799439016",
      "message_type": "text",
      "content": "Hello, world!",
      "file_path": null,
      "file_name": null,
      "file_mime": null,
      "file_download_url": null,
      "sender": {
        "id": "507f1f77bcf86cd799439012",
        "name": "John Doe",
        "email": "john@example.com",
        "is_active": true,
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "updated_at": "2024-01-01T12:00:00.000000Z",
        "created_at": "2024-01-01T12:00:00.000000Z"
      },
      "receiver": null,
      "channel": {
        "id": "507f1f77bcf86cd799439016",
        "name": "general"
      },
      "created_at": "2024-01-01T12:00:00.000000Z",
      "updated_at": "2024-01-01T12:00:00.000000Z",
      "read_by_count": 0,
      "is_read_by_me": false,
      "reactions_summary": []
    }
  }
}
```

---

### 2. Read Messages
**Endpoint:** `GET /read`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "channel_id": "507f1f77bcf86cd799439016",
  "page": 1,
  "per_page": 20
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Messages retrieved successfully",
  "data": {
    "messages": [
      {
        "id": "507f1f77bcf86cd799439017",
        "workspace_id": "507f1f77bcf86cd799439011",
        "sender_id": "507f1f77bcf86cd799439012",
        "receiver_id": null,
        "channel_id": "507f1f77bcf86cd799439016",
        "message_type": "text",
        "content": "Hello, world!",
        "file_path": null,
        "file_name": null,
        "file_mime": null,
        "file_download_url": null,
        "sender": {
          "id": "507f1f77bcf86cd799439012",
          "name": "John Doe",
          "email": "john@example.com",
          "is_active": true,
          "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
          "updated_at": "2024-01-01T12:00:00.000000Z",
          "created_at": "2024-01-01T12:00:00.000000Z"
        },
        "receiver": null,
        "channel": {
          "id": "507f1f77bcf86cd799439016",
          "name": "general"
        },
        "created_at": "2024-01-01T12:00:00.000000Z",
        "updated_at": "2024-01-01T12:00:00.000000Z",
        "read_by_count": 0,
        "is_read_by_me": false,
        "reactions_summary": []
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 1,
      "last_page": 1
    }
  }
}
```

---

### 3. Update Message
**Endpoint:** `PATCH /update`

**Headers:**
```
Content-Type: application/json or multipart/form-data
token: {login_token}
```

**Request Body (Text Only):**
```json
{
  "channel_id": "507f1f77bcf86cd799439016",
  "message_id": "507f1f77bcf86cd799439017",
  "message": "Updated message content"
}
```

**Request Body (With File - Form Data):**
```
channel_id: 507f1f77bcf86cd799439016
message_id: 507f1f77bcf86cd799439017
message: Updated message with file
file: [new file upload]
```

**Success Response:**
```json
{
  "success": true,
  "message": "Message updated successfully",
  "data": {
    "message": {
      "id": "507f1f77bcf86cd799439017",
      "workspace_id": "507f1f77bcf86cd799439011",
      "sender_id": "507f1f77bcf86cd799439012",
      "receiver_id": null,
      "channel_id": "507f1f77bcf86cd799439016",
      "message_type": "text",
      "content": "Updated message content",
      "file_path": null,
      "file_name": null,
      "file_mime": null,
      "file_download_url": null,
      "sender": {
        "id": "507f1f77bcf86cd799439012",
        "name": "John Doe",
        "email": "john@example.com",
        "is_active": true,
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "updated_at": "2024-01-01T12:00:00.000000Z",
        "created_at": "2024-01-01T12:00:00.000000Z"
      },
      "receiver": null,
      "channel": {
        "id": "507f1f77bcf86cd799439016",
        "name": "general"
      },
      "created_at": "2024-01-01T12:00:00.000000Z",
      "updated_at": "2024-01-01T13:00:00.000000Z",
      "read_by_count": 0,
      "is_read_by_me": false,
      "reactions_summary": []
    }
  }
}
```

---

### 4. Delete Message
**Endpoint:** `DELETE /delete`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "channel_id": "507f1f77bcf86cd799439016",
  "message_id": "507f1f77bcf86cd799439017"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Message deleted successfully",
  "data": null
}
```

---

### 5. Download File
**Endpoint:** `GET /download`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Query Parameters:**
```
?path=workspaces/{workspace_id}/messages/{filename}
```

**Example URL:**
```
http://localhost:8000/api/messages/download?path=workspaces/507f1f77bcf86cd799439011/messages/document.pdf
```

**Success Response:**
```
Binary file stream with appropriate Content-Type and Content-Disposition headers
```

---

### 6. Search Messages
**Endpoint:** `GET /search`

**Authentication:**
```
token: {login_token}
```

**Query Parameters:**
- `query` (string, required): search term, 1-255 characters
- `channel_id` (string, optional): channel ObjectId
- `workspace_id` (string, optional): workspace ObjectId
- `per_page` (integer, optional): results per page (1-100)
- `page` (integer, optional): page number

**Request Example (Basic Search):**
```javascript
fetch('http://localhost:8000/api/messages/search?query=hello', {
  method: 'GET',
  headers: {
    'token': 'your_login_token_here',
    'Accept': 'application/json'
  }
})
```

**Request Example (Channel Filter):**
```javascript
fetch('http://localhost:8000/api/messages/search?query=hello&channel_id=69bc3b61cddfc56ef6030849', {
  method: 'GET',
  headers: {
    'token': 'your_login_token_here',
    'Accept': 'application/json'
  }
})
```

**Request Example (Pagination):**
```javascript
fetch('http://localhost:8000/api/messages/search?query=test&per_page=10&page=2', {
  method: 'GET',
  headers: {
    'token': 'your_login_token_here',
    'Accept': 'application/json'
  }
})
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Messages retrieved successfully!",
  "errors": null,
  "data": [
    {
      "id": "69bc3bbf5a68d1daf00107e6",
      "content": "Hello Ahmed",
      "message_type": "text",
      "has_file": false,
      "created_at": "2026-03-19T18:09:03.876000Z",
      "updated_at": "2026-03-19T18:09:03.876000Z",
      "sender": {
        "id": "69b986ba02b7cf076f0955b3",
        "name": "Ahmed"
      },
      "channel": {
        "id": "69bc3b61cddfc56ef6030849",
        "name": "Islamic Channel",
        "type": "public"
      }
    }
  ]
}
```

---

### 6. Mark Messages as Read
**Endpoint:** `POST /read-by`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "channel_id": "507f1f77bcf86cd799439016",
  "message_ids": ["507f1f77bcf86cd799439017", "507f1f77bcf86cd799439018"]
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Messages marked as read successfully",
  "data": {
    "updated": 2
  }
}
```

---

### 7. React to Message
**Endpoint:** `POST /react`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "channel_id": "507f1f77bcf86cd799439016",
  "message_ids": ["507f1f77bcf86cd799439017"],
  "emoji": "👍"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Reaction updated successfully",
  "data": {
    "message": {
      "id": "507f1f77bcf86cd799439017",
      "workspace_id": "507f1f77bcf86cd799439011",
      "sender_id": "507f1f77bcf86cd799439012",
      "receiver_id": null,
      "channel_id": "507f1f77bcf86cd799439016",
      "message_type": "text",
      "content": "Hello, world!",
      "file_path": null,
      "file_name": null,
      "file_mime": null,
      "file_download_url": null,
      "sender": {
        "id": "507f1f77bcf86cd799439012",
        "name": "John Doe",
        "email": "john@example.com",
        "is_active": true,
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "updated_at": "2024-01-01T12:00:00.000000Z",
        "created_at": "2024-01-01T12:00:00.000000Z"
      },
      "receiver": null,
      "channel": {
        "id": "507f1f77bcf86cd799439016",
        "name": "general"
      },
      "created_at": "2024-01-01T12:00:00.000000Z",
      "updated_at": "2024-01-01T12:00:00.000000Z",
      "read_by_count": 1,
      "is_read_by_me": false,
      "reactions_summary": [
        {
          "emoji": "👍",
          "count": 1,
          "reacted_by_me": true,
          "reacted_by": ["507f1f77bcf86cd799439012"]
        }
      ]
    }
  }
}
```
