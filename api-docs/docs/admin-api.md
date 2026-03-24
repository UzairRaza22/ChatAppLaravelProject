---
sidebar_label: Admin API
---

# Admin API Documentation

## Base URL
```
http://localhost:8000/api/admin
```

## Authentication
Most endpoints require an admin access token. Include the token in the Authorization header:
```
Authorization: Bearer {access_token}
```

## Response Format
All responses follow this format:
```json
{
  "success": true,
  "message": "Success message",
  "data": {
    // Response data
  }
}
```

---

## Authentication Endpoints

### 1. Admin Signup
**POST** `/auth/signup`

Create a new admin account. A verification email will be sent.

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "admin@example.com",
  "password": "password123",
  "workspace": "My Workspace"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Signup successful!. Please check your email for verification link.",
  "data": {
    "admin": {
      "id": "64a1b2c3d4e5f6789012345",
      "first_name": "John",
      "last_name": "Doe",
      "email": "admin@example.com",
      "is_active": false,
      "created_at": "2023-07-01T12:00:00.000000Z"
    }
  }
}
```

---

### 2. Verify Signup
**POST** `/auth/verify-signup`

Verify admin account using the token sent to email.

**Request Body:**
```json
{
  "token": "verification_token_here"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Account activated successfully! You can now login.",
  "data": {
    "admin": {
      "id": "64a1b2c3d4e5f6789012345",
      "first_name": "John",
      "last_name": "Doe",
      "email": "admin@example.com",
      "is_active": true,
      "created_at": "2023-07-01T12:00:00.000000Z"
    }
  }
}
```

---

### 3. Admin Login
**POST** `/auth/login`

Authenticate admin and get access token.

**Request Body:**
```json
{
  "email": "admin@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful!",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "admin": {
      "id": "64a1b2c3d4e5f6789012345",
      "first_name": "John",
      "last_name": "Doe",
      "email": "admin@example.com",
      "is_active": true
    }
  }
}
```

---

### 4. Admin Logout
**POST** `/auth/logout`

Logout admin and invalidate access token.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response:**
```json
{
  "success": true,
  "message": "Logout successful!",
  "data": null
}
```

---

### 5. Forgot Password
**POST** `/auth/forgot-password`

Request password reset code.

**Request Body:**
```json
{
  "email": "admin@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password reset code sent to your email.",
  "data": null
}
```

---

### 6. Reset Password
**POST** `/auth/reset-password`

Reset password using the token sent to email.

**Request Body:**
```json
{
  "token": "reset_token_here",
  "password": "new_password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password reset successfully!",
  "data": null
}
```

---

## Management Endpoints (Require Authentication)

### 7. Read Channels
**GET** `/channels/read`

Retrieve channels. Can filter by specific channel.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `channel_id` (optional)

**Response:**
```json
{
  "success": true,
  "message": "Channels retrieved successfully",
  "data": {
    "channels": [
      {
        "id": "64a1b2c3d4e5f6789012346",
        "name": "general",
        "description": "General discussion",
        "workspace_id": "64a1b2c3d4e5f6789012347",
        "created_at": "2023-07-01T12:00:00.000000Z"
      }
    ]
  }
}
```

---

### 8. Read Impersonation Info
**GET** `/impersonate/read`

Get impersonation information and available users to impersonate.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `user_id` (optional)

**Response:**
```json
{
  "success": true,
  "message": "Impersonation data retrieved successfully",
  "data": {
    "users": [
      {
        "id": "64a1b2c3d4e5f6789012348",
        "first_name": "Jane",
        "last_name": "Smith",
        "email": "user@example.com",
        "is_active": true
      }
    ]
  }
}
```

---

### 9. Stop Impersonation
**POST** `/impersonate/stop`

Stop current impersonation session.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response:**
```json
{
  "success": true,
  "message": "Impersonation stopped successfully",
  "data": null
}
```

---

### 10. Read Messages
**GET** `/messages/read`

Retrieve messages. Can filter by specific message.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `message_id` (optional)
- `channel_id` (optional)
- `user_id` (optional)

**Response:**
```json
{
  "success": true,
  "message": "Messages retrieved successfully",
  "data": {
    "messages": [
      {
        "id": "64a1b2c3d4e5f6789012349",
        "content": "Hello world!",
        "user_id": "64a1b2c3d4e5f6789012348",
        "channel_id": "64a1b2c3d4e5f6789012346",
        "created_at": "2023-07-01T12:00:00.000000Z"
      }
    ]
  }
}
```

---

### 11. Read Teams
**GET** `/teams/read`

Retrieve teams. Can filter by specific team.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `team_id` (optional)

**Response:**
```json
{
  "success": true,
  "message": "Teams retrieved successfully",
  "data": {
    "teams": [
      {
        "id": "64a1b2c3d4e5f678901234a",
        "name": "Development Team",
        "description": "Backend development team",
        "workspace_id": "64a1b2c3d4e5f6789012347",
        "created_at": "2023-07-01T12:00:00.000000Z"
      }
    ]
  }
}
```

---

### 12. Read Users
**GET** `/users/read`

Retrieve users. Can filter by specific user.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `user_id` (optional)

**Response:**
```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "data": {
    "users": [
      {
        "id": "64a1b2c3d4e5f6789012348",
        "first_name": "Jane",
        "last_name": "Smith",
        "email": "user@example.com",
        "is_active": true,
        "created_at": "2023-07-01T12:00:00.000000Z"
      }
    ]
  }
}
```

---

### 13. Read Workspaces
**GET** `/workspaces/read`

Retrieve workspaces. Can filter by specific workspace.

**Headers:**
```
Authorization: Bearer {access_token}
```

**Query Parameters:**
- `workspace_id` (optional)

**Response:**
```json
{
  "success": true,
  "message": "Workspaces retrieved successfully",
  "data": {
    "workspaces": [
      {
        "id": "64a1b2c3d4e5f6789012347",
        "name": "My Workspace",
        "description": "Default workspace",
        "created_at": "2023-07-01T12:00:00.000000Z"
      }
    ]
  }
}
```

---

## Error Responses

### Validation Errors (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Authentication Errors (401)
```json
{
  "success": false,
  "message": "Invalid or expired token",
  "data": null
}
```

### Not Found Errors (404)
```json
{
  "success": false,
  "message": "Resource not found",
  "data": null
}
```

### Server Errors (500)
```json
{
  "success": false,
  "message": "Internal server error",
  "data": null
}
```

---

## Rate Limiting
- Authentication endpoints: 5 requests per minute
- Management endpoints: 100 requests per minute

## Notes
- All timestamps are in UTC format (ISO 8601)
- Passwords must be at least 8 characters long
- Admin accounts must be verified before login
- Access tokens expire after 24 hours
- All sensitive operations require valid authentication
