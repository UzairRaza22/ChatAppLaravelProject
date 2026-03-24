---
sidebar_label: FCM Tokens
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
