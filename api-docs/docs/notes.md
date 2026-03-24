---
sidebar_label: Notes
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
