---
sidebar_label: Authentication
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
