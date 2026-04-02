<?php

// Test channel retrieval with authenticated user (simulating token-based auth)
// Run this with: php test_token_auth.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Channel;
use App\Http\Middleware\Channel\ChannelExistMiddleware;
use Illuminate\Http\Request;

echo "=== Token Authentication Test ===\n\n";

// Get a test user
$testUser = User::first();
if (!$testUser) {
    echo "No users found. Please create a user first.\n";
    exit(1);
}

echo "Testing with authenticated user: {$testUser->name} (ID: {$testUser->_id})\n\n";

// Create a request that simulates token authentication
$request = new Request();

// Simulate the authenticated user being set by auth middleware
// This is what happens when you send a valid token
$request->setUserResolver(function () use ($testUser) {
    return $testUser;
});

echo "=== Simulating Middleware with Token Auth ===\n";

$middleware = new ChannelExistMiddleware();

$next = function ($req) {
    return response()->json(['success' => true]);
};

try {
    // This should extract user ID from the authenticated user
    $response = $middleware->handle($request, $next);
    
    $channels = $request->attributes->get('channels');
    
    if ($channels) {
        echo "✅ Middleware successfully found " . $channels->count() . " channels for authenticated user:\n\n";
        
        foreach ($channels as $channel) {
            $isCreator = (string) $channel->created_id === (string) $testUser->_id;
            $members = collect($channel->members ?? []);
            
            $isMember = $members->contains(function ($member) use ($testUser) {
                if (is_string($member)) {
                    return (string) $member === (string) $testUser->_id;
                }
                if (is_array($member)) {
                    return (string) ($member['user_id'] ?? $member['_id'] ?? $member['id'] ?? '') === (string) $testUser->_id;
                }
                if (is_object($member)) {
                    return (string) (data_get($member, 'user_id') ?? data_get($member, '_id') ?? data_get($member, 'id')) === (string) $testUser->_id;
                }
                return false;
            });
            
            echo "📁 Channel: {$channel->name}\n";
            echo "   ID: {$channel->_id}\n";
            echo "   Creator: " . ($isCreator ? '✅ YES' : '❌ NO') . "\n";
            echo "   Member: " . ($isMember ? '✅ YES' : '❌ NO') . "\n";
            echo "   Type: {$channel->type}\n";
            echo "   Members: " . json_encode($channel->members) . "\n\n";
        }
        
        $creatorCount = $channels->where('created_id', (string) $testUser->_id)->count();
        $memberCount = $channels->filter(function ($channel) use ($testUser) {
            $members = collect($channel->members ?? []);
            return $members->contains(function ($member) use ($testUser) {
                if (is_string($member)) {
                    return (string) $member === (string) $testUser->_id;
                }
                if (is_array($member)) {
                    return (string) ($member['user_id'] ?? $member['_id'] ?? $member['id'] ?? '') === (string) $testUser->_id;
                }
                return false;
            });
        })->count();
        
        echo "📊 Summary:\n";
        echo "   Channels where user is creator: {$creatorCount}\n";
        echo "   Channels where user is member: {$memberCount}\n";
        echo "   Total channels: " . $channels->count() . "\n";
        
    } else {
        echo "❌ No channels found for authenticated user.\n";
        echo "This could mean:\n";
        echo "1. User has no channels (not creator or member of any)\n";
        echo "2. There's an issue with the query\n";
        echo "3. User ID extraction failed\n\n";
        
        // Debug: Check if user ID was extracted correctly
        echo "🔍 Debug Info:\n";
        echo "User from request: " . ($request->user() ? $request->user()->name : 'NULL') . "\n";
        echo "User ID: " . ($request->user() ? $request->user()->_id : 'NULL') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Show all channels for comparison
echo "\n=== All Channels in Database ===\n";
$allChannels = Channel::all();
if ($allChannels->count() > 0) {
    foreach ($allChannels as $channel) {
        echo "📁 {$channel->name} (Created by: {$channel->created_id})\n";
        echo "   Members: " . json_encode($channel->members) . "\n";
    }
} else {
    echo "No channels found in database.\n";
}

echo "\n=== Test Complete ===\n";
echo "\n💡 This simulates what happens when you use a token in Postman.\n";
echo "The middleware should extract the user ID from the authenticated user\n";
echo "and return all channels where that user is creator or member.\n";