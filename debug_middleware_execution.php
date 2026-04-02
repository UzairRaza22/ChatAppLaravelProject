<?php

// Debug the actual middleware execution step by step
// Run this with: php debug_middleware_execution.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Channel;
use App\Http\Middleware\Channel\ChannelExistMiddleware;
use App\Http\Middleware\Message\CheckChannelMessageMiddleware;
use Illuminate\Http\Request;

echo "=== DEBUG MIDDLEWARE EXECUTION ===\n\n";

// Get test user
$testUser = User::first();
if (!$testUser) {
    echo "❌ No users found.\n";
    exit(1);
}

echo "👤 Testing with user: {$testUser->name} (ID: {$testUser->_id})\n\n";

// Step 1: Test user ID extraction in middleware
echo "1️⃣ TESTING USER ID EXTRACTION:\n";

$request = new Request();
$request->setUserResolver(function () use ($testUser) {
    return $testUser;
});

echo "   Request user(): " . ($request->user() ? $request->user()->name : 'NULL') . "\n";
echo "   Request user ID: " . ($request->user() ? $request->user()->_id : 'NULL') . "\n";

// Simulate the middleware's user ID extraction logic exactly
$channelId = (string) ($request->route('id') ?? $request->input('channel_id') ?? $request->query('channel_id'));
$userId = (string) ($request->input('user_id') ?? $request->query('user_id'));

echo "   Channel ID from request: '{$channelId}'\n";
echo "   User ID from request params: '{$userId}'\n";

if ($userId === '') {
    $user = $request->user() ?? $request->input('user') ?? $request->input('verified_user');
    $userId = (string) (data_get($user, '_id') ?? data_get($user, 'id'));
    
    if ($userId === '') {
        $tokenRecord = $request->input('token_record');
        $userId = (string) data_get($tokenRecord, 'user_id');
    }
}

echo "   Final extracted user ID: '{$userId}'\n";
echo "   User ID type: " . gettype($userId) . "\n";
echo "   User ID length: " . strlen($userId) . "\n\n";

// Step 2: Test the channel filtering logic with debug output
echo "2️⃣ TESTING CHANNEL FILTERING WITH DEBUG:\n";

$allChannels = Channel::all();
echo "   Total channels in database: " . $allChannels->count() . "\n\n";

// Create a custom filter function with detailed logging
$userChannels = $allChannels->filter(function ($channel) use ($userId) {
    echo "   🔍 Checking channel: {$channel->name}\n";
    echo "      Channel ID: {$channel->_id}\n";
    echo "      Created by: '{$channel->created_id}' (type: " . gettype($channel->created_id) . ")\n";
    
    // Check if user is creator
    $isCreator = (string) $channel->created_id === $userId;
    echo "      Creator check: '{$channel->created_id}' === '{$userId}' = " . ($isCreator ? 'TRUE' : 'FALSE') . "\n";
    
    if ($isCreator) {
        echo "      ✅ INCLUDE: User is creator\n\n";
        return true;
    }
    
    // Check if user is member
    $members = $channel->members ?? [];
    echo "      Members array: " . json_encode($members) . "\n";
    echo "      Members type: " . gettype($members) . "\n";
    echo "      Members count: " . (is_array($members) ? count($members) : 'N/A') . "\n";
    
    if (!is_array($members)) {
        echo "      ❌ EXCLUDE: Members is not an array\n\n";
        return false;
    }
    
    foreach ($members as $index => $member) {
        echo "         Member #{$index}: " . json_encode($member) . " (type: " . gettype($member) . ")\n";
        
        if (is_array($member)) {
            echo "            Array keys: " . implode(', ', array_keys($member)) . "\n";
            
            if (isset($member['user_id'])) {
                echo "            user_id: '{$member['user_id']}' (type: " . gettype($member['user_id']) . ")\n";
                echo "            Comparison: '{$member['user_id']}' === '{$userId}' = " . ((string) $member['user_id'] === $userId ? 'TRUE' : 'FALSE') . "\n";
                
                if ((string) $member['user_id'] === $userId) {
                    echo "      ✅ INCLUDE: User found in members\n\n";
                    return true;
                }
            } else {
                echo "            No 'user_id' key found\n";
            }
        } elseif (is_object($member) && isset($member->user_id)) {
            echo "            Object user_id: '{$member->user_id}'\n";
            if ((string) $member->user_id === $userId) {
                echo "      ✅ INCLUDE: User found in members (object)\n\n";
                return true;
            }
        } elseif (is_string($member)) {
            echo "            String member: '{$member}'\n";
            if ((string) $member === $userId) {
                echo "      ✅ INCLUDE: User found in members (string)\n\n";
                return true;
            }
        }
    }
    
    echo "      ❌ EXCLUDE: User not found in members\n\n";
    return false;
});

echo "📊 FILTERING RESULTS:\n";
echo "   Channels found: " . $userChannels->count() . "\n";

if ($userChannels->count() > 0) {
    echo "   ✅ SUCCESS: Found channels for user\n";
    foreach ($userChannels as $channel) {
        echo "      - {$channel->name}\n";
    }
} else {
    echo "   ❌ FAILURE: No channels found for user\n";
}

// Step 3: Test the actual middleware
echo "\n3️⃣ TESTING ACTUAL MIDDLEWARE:\n";

$middleware = new ChannelExistMiddleware();
$next = function ($req) {
    return response()->json(['success' => true]);
};

try {
    echo "   Calling ChannelExistMiddleware...\n";
    $response = $middleware->handle($request, $next);
    $channels = $request->attributes->get('channels');
    
    echo "   Response status: " . $response->getStatusCode() . "\n";
    echo "   Channels set in request: " . ($channels ? $channels->count() : 'NULL') . "\n";
    
    if ($channels && $channels->count() > 0) {
        echo "   ✅ Middleware SUCCESS\n";
        foreach ($channels as $channel) {
            echo "      - {$channel->name}\n";
        }
    } else {
        echo "   ❌ Middleware FAILURE: No channels set\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Middleware ERROR: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

// Step 4: Test message middleware
echo "\n4️⃣ TESTING MESSAGE MIDDLEWARE:\n";

// Find a channel where user should have access
$testChannel = $userChannels->first();
if ($testChannel) {
    echo "   Testing with channel: {$testChannel->name} (ID: {$testChannel->_id})\n";
    
    $messageRequest = new Request();
    $messageRequest->merge(['channel_id' => (string) $testChannel->_id]);
    $messageRequest->setUserResolver(function () use ($testUser) {
        return $testUser;
    });
    
    $messageMiddleware = new CheckChannelMessageMiddleware();
    
    try {
        $messageResponse = $messageMiddleware->handle($messageRequest, $next);
        $messageStatus = $messageResponse->getStatusCode();
        
        echo "   Message middleware status: {$messageStatus}\n";
        
        if ($messageStatus === 200) {
            echo "   ✅ Message middleware SUCCESS\n";
        } else {
            echo "   ❌ Message middleware FAILURE\n";
            $data = $messageResponse->getData(true);
            echo "   Response: " . json_encode($data) . "\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Message middleware ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ⚠️  No channels available for message testing\n";
}

echo "\n=== EXECUTION DEBUG COMPLETE ===\n";

// Summary
if (isset($channels) && $channels->count() > 0) {
    echo "✅ Channel retrieval is working\n";
} else {
    echo "❌ Channel retrieval is NOT working\n";
}

if (isset($messageStatus) && $messageStatus === 200) {
    echo "✅ Message functionality is working\n";
} else {
    echo "❌ Message functionality is NOT working\n";
}