<?php

// Simple test for creator query
// Run this with: php test_simple_creator_query.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Channel;

echo "=== Simple Creator Query Test ===\n\n";

// Get a test user
$testUser = User::first();
if (!$testUser) {
    echo "❌ No users found.\n";
    exit(1);
}

$userId = (string) $testUser->_id;
echo "👤 User: {$testUser->name} (ID: {$userId})\n\n";

// Check if user has any channels as creator
echo "🔍 Testing basic creator query...\n";
$creatorChannels = Channel::where('created_id', $userId)->get();
echo "Found " . $creatorChannels->count() . " channels where user is creator\n\n";

if ($creatorChannels->count() === 0) {
    echo "⚠️  No channels found. Creating a test channel...\n";
    
    $testChannel = Channel::create([
        'name' => 'Simple Test Channel',
        'workspace_id' => '507f1f77bcf86cd799439011',
        'team_id' => '507f1f77bcf86cd799439012', 
        'type' => 'public',
        'created_id' => $userId,
        'members' => [$userId]
    ]);
    
    echo "✅ Created channel: {$testChannel->name}\n";
    echo "   ID: {$testChannel->_id}\n";
    echo "   Created by: {$testChannel->created_id}\n";
    echo "   Members: " . json_encode($testChannel->members) . "\n\n";
    
    // Test query again
    echo "🔍 Testing query after creation...\n";
    $creatorChannels = Channel::where('created_id', $userId)->get();
    echo "Found " . $creatorChannels->count() . " channels where user is creator\n";
    
    foreach ($creatorChannels as $channel) {
        echo "   - {$channel->name} (ID: {$channel->_id})\n";
    }
}

// Test the middleware logic directly
echo "\n🧪 Testing middleware logic...\n";
$channels = Channel::where('created_id', $userId)
    ->orWhere('members', $userId)
    ->get();

echo "Middleware query found: " . $channels->count() . " channels\n";
foreach ($channels as $channel) {
    echo "   - {$channel->name}\n";
}

echo "\n=== Test Complete ===\n";

if ($channels->count() > 0) {
    echo "✅ SUCCESS: Query is working!\n";
    echo "The issue might be elsewhere in the request flow.\n";
} else {
    echo "❌ ISSUE: Query is not finding channels.\n";
    echo "This suggests a database or query syntax problem.\n";
}