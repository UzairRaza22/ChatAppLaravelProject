---
title: Chat Application API Documentation
description: Complete API documentation for the Laravel Chat Application
sidebar_label: API Endpoints
---

# Chat Application API Documentation

## Table of Contents

- [Authentication](#authentication)
- [Workspaces](#workspaces)
- [Teams](#teams)
- [Channels](#channels)
- [Messages](#messages)
- [FCM Tokens](#fcm-tokens)

---

## Authentication

### Base URL
```
http://localhost:8000/api/auth
```

### 1. User Signup
**Endpoint:** `POST /signup`

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "workspace": "My Workspace",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "User registered successfully. Please check your email for verification.",
  "data": {
    "user": {
      "id": "507f1f77bcf86cd799439011",
      "name": "John Doe",
      "email": "john@example.com",
      "is_active": false,
      "access_token": null,
      "updated_at": "2024-01-01T12:00:00.000000Z",
      "created_at": "2024-01-01T12:00:00.000000Z"
    }
  }
}
```

---

### 2. Verify Signup
**Endpoint:** `POST /verify-signup`

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "token": "verification_token_here"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Email verified successfully. You can now login.",
  "data": {
    "user": {
      "id": "507f1f77bcf86cd799439011",
      "name": "John Doe",
      "email": "john@example.com",
      "is_active": true,
      "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
      "updated_at": "2024-01-01T12:00:00.000000Z",
      "created_at": "2024-01-01T12:00:00.000000Z"
    }
  }
}
```

---

### 3. User Login
**Endpoint:** `POST /login`

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": "507f1f77bcf86cd799439011",
      "name": "John Doe",
      "email": "john@example.com",
      "is_active": true,
      "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
      "updated_at": "2024-01-01T12:00:00.000000Z",
      "created_at": "2024-01-01T12:00:00.000000Z"
    }
  }
}
```

---

### 4. Forgot Password
**Endpoint:** `POST /forgot-password`

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "john@example.com"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Password reset link sent to your email",
  "data": null
}
```

---

### 5. Reset Password
**Endpoint:** `POST /reset-password`

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "token": "reset_token_here",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Password reset successfully",
  "data": null
}
```

---

### 6. User Logout
**Endpoint:** `POST /logout`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Logout successful",
  "data": null
}
```

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

---

## FCM Tokens

### Base URL
```
http://localhost:8000/api/fcm
```

### 1. Register FCM Token
**Endpoint:** `POST /devices/fcm-token`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "fcm_token": "fcm_device_token_here"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "FCM token registered successfully",
  "data": null
}
```

---

### 2. Unregister FCM Token
**Endpoint:** `DELETE /devices/fcm-token`

**Headers:**
```
Content-Type: application/json
token: {login_token}
```

**Request Body:**
```json
{
  "fcm_token": "fcm_device_token_here"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "FCM token unregistered successfully",
  "data": null
}
```

---

## MongoDB Data Structures

### User Collection
```javascript
{
  _id: ObjectId("507f1f77bcf86cd799439011"),
  id: "507f1f77bcf86cd799439011",
  name: "John Doe",
  email: "john@example.com",
  password: "hashed_password",
  is_active: true,
  access_token: "jwt_token_here",
  created_at: ISODate("2024-01-01T12:00:00Z"),
  updated_at: ISODate("2024-01-01T12:00:00Z")
}
```

### Workspace Collection
```javascript
{
  _id: ObjectId("507f1f77bcf86cd799439011"),
  id: "507f1f77bcf86cd799439011",
  name: "My Workspace",
  description: "Workspace description",
  creator_id: "507f1f77bcf86cd799439012",
  members: [
    {
      user_id: "507f1f77bcf86cd799439012",
      role: "admin"
    }
  ],
  created_at: ISODate("2024-01-01T12:00:00Z"),
  updated_at: ISODate("2024-01-01T12:00:00Z")
}
```

### Team Collection
```javascript
{
  _id: ObjectId("507f1f77bcf86cd799439014"),
  id: "507f1f77bcf86cd799439014",
  name: "Development Team",
  description: "Backend development team",
  workspace_id: "507f1f77bcf86cd799439011",
  creator_id: "507f1f77bcf86cd799439012",
  members: [
    {
      user_id: "507f1f77bcf86cd799439012",
      role: "admin"
    },
    {
      user_id: "507f1f77bcf86cd799439013",
      role: "member"
    }
  ],
  created_at: ISODate("2024-01-01T12:00:00Z"),
  updated_at: ISODate("2024-01-01T12:00:00Z")
}
```

### Channel Collection
```javascript
{
  _id: ObjectId("507f1f77bcf86cd799439016"),
  id: "507f1f77bcf86cd799439016",
  name: "general",
  workspace_id: "507f1f77bcf86cd799439011",
  team_id: "507f1f77bcf86cd799439014",
  type: "public",
  direct_id: null,
  created_id: "507f1f77bcf86cd799439012",
  members: [
    {
      user_id: "507f1f77bcf86cd799439012",
      role: "admin"
    }
  ],
  created_at: ISODate("2024-01-01T12:00:00Z"),
  updated_at: ISODate("2024-01-01T12:00:00Z")
}
```

### Message Collection
```javascript
{
  _id: ObjectId("507f1f77bcf86cd799439017"),
  id: "507f1f77bcf86cd799439017",
  workspace_id: "507f1f77bcf86cd799439011",
  sender_id: "507f1f77bcf86cd799439012",
  receiver_id: null,
  channel_id: "507f1f77bcf86cd799439016",
  message_type: "text",
  content: "Hello, world!",
  file_path: "workspaces/507f1f77bcf86cd799439011/messages/document.pdf",
  file_name: "document.pdf",
  file_mime: "application/pdf",
  read_by: ["507f1f77bcf86cd799439013"],
  reactions: {
    "👍": ["507f1f77bcf86cd799439012"]
  },
  created_at: ISODate("2024-01-01T12:00:00Z"),
  updated_at: ISODate("2024-01-01T12:00:00Z"),
  deleted_at: null
}
```

---

## Error Responses

All endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error message description",
  "errors": [
    {
      "field": "field_name",
      "message": "Validation error message"
    }
  ],
  "data": null
}
```

### Common HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

---

## File Upload Specifications

### Supported File Types
- Images: `jpg`, `jpeg`, `png`, `gif`, `webp`
- Documents: `pdf`, `doc`, `docx`, `xls`, `xlsx`, `ppt`, `pptx`, `txt`
- Archives: `zip`
- Media: `mp4`, `mp3`

### File Size Limits
- Maximum file size: `10MB`
- Files are stored in MongoDB GridFS
- File paths follow pattern: `workspaces/{workspace_id}/messages/{filename}`

---

## Authentication

### Token Types
1. **Login Token**: Used for authenticated API calls
2. **Signup Verification Token**: Used for email verification
3. **Forgot Password Token**: Used for password reset

### Token Header Format
```
token: {your_token_here}
```

### Token Expiration
- Login tokens expire based on your Laravel configuration
- Verification tokens expire after 24 hours
- Verify Signup and Password reset tokens expire after 5 minutes

---

## Notes

- All datetime fields are in UTC format
- All IDs are strings in responses but stored as ObjectId in MongoDB
- File uploads require `multipart/form-data` content type
- Pagination uses 1-based indexing
- All endpoints require appropriate authentication unless specified
- The API supports both JSON and form-data requests for message operations
