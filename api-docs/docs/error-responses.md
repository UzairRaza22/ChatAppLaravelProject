---
sidebar_label: Error Responses
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
