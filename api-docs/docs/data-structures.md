---
sidebar_label: Data Structures
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
