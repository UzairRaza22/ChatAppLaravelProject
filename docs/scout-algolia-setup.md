# Laravel Scout + Algolia Setup for Message Search

## Overview
This setup enables powerful full-text search for chat messages using Laravel Scout and Algolia.

## Configuration

### 1. Environment Variables
Add these to your `.env` file:

```env
# Scout Configuration
SCOUT_DRIVER=algolia

# Algolia Credentials
ALGOLIA_APP_ID=your_algolia_app_id
ALGOLIA_SECRET=your_algolia_admin_api_key
```

### 2. Install Algolia Package
```bash
composer require algolia/algoliasearch-client-php
```

### 3. Model Configuration
The `Message` model is now configured with:
- `Searchable` trait
- Custom `toSearchableArray()` method
- Algolia index name: `messages_index`
- Configurable search attributes and faceting

### 4. Search Index Settings
The Algolia index is configured with:
- **Searchable attributes**: `content`, `sender_name`, `channel_name`
- **Faceting**: `workspace_id`, `channel_id`, `sender_id`, `message_type`
- **Ranking**: Latest messages first, then relevance
- **Highlighting**: Content highlighting enabled

## Usage

### 1. Index Existing Messages
```bash
# Index all existing messages
php artisan scout:index-messages

# Flush and re-index all messages
php artisan scout:index-messages --flush
```

### 2. API Endpoint
Search messages via: `GET /api/messages/search`

**Query Parameters:**
- `query` (required): Search term
- `channel_id` (optional): Filter by specific channel
- `workspace_id` (optional): Filter by workspace
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Results per page (default: 20)

**Example:**
```bash
GET /api/messages/search?query=hello&channel_id=123&page=1&per_page=10
```

### 3. Automatic Indexing
- New messages are automatically indexed when created
- Message updates are synced to Algolia
- Soft-deleted messages are removed from search index

## Features

### Search Capabilities
- Full-text search in message content
- Search by sender name and channel name
- Typo tolerance and relevance ranking
- Faceted search by workspace, channel, sender, and message type

### Performance
- Results ranked by recency first
- Fast response times via Algolia CDN
- Efficient pagination support

### Security
- Workspace-based filtering ensures users only see messages they have access to
- Unretrievable attributes prevent sensitive data exposure

## Testing

### 1. Test Search Locally
```bash
# Create some test messages
php artisan tinker
>>> Message::create([...]);

# Index the messages
php artisan scout:index-messages

# Test the search endpoint
curl -X GET "http://localhost:8000/api/messages/search?query=test"
```

### 2. Monitor Algolia Dashboard
Check your Algolia dashboard to:
- Monitor search operations
- View search analytics
- Configure additional settings

## Troubleshooting

### Common Issues

1. **"Index not found" error**
   - Run `php artisan scout:index-messages` to create the index

2. **No search results**
   - Ensure messages are indexed
   - Check Algolia credentials in `.env`
   - Verify search query parameters

3. **Slow search performance**
   - Check Algolia query performance in dashboard
   - Consider adding more specific filters

### Debug Commands
```bash
# Check Scout configuration
php artisan config:show scout

# Test Algolia connection
php artisan tinker
>>> Message::search('test')->get();
```

## Advanced Configuration

### Custom Search Ranking
Modify `config/scout.php` to adjust ranking:
```php
'ranking' => [
    'desc(created_at)',  // Latest first
    'typo',              // Typo tolerance
    'proximity',         // Word proximity
    'exact',             // Exact matches
    'custom'             // Custom ranking
],
```

### Additional Searchable Attributes
Update `Message::toSearchableArray()` to include more fields:
```php
public function toSearchableArray(): array
{
    return [
        'id' => (string) $this->_id,
        'content' => $this->content,
        'file_name' => $this->file_name,  // Add file names to search
        // ... other fields
    ];
}
```
