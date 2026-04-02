<?php

// Test all message functions for channel creators
// Run this with: php test_all_message_functions.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Channel;
use App\Http\Middleware\Message\CheckChannelMessageMiddleware;
use App\Http\Middleware\Message\CheckReadMessagesMiddleware;
use App\Http\Middleware\Message\SearchMessageMiddleware;
use Illuminate\Http\Request;

echo "=== Complete Message Functions Test for Creators ===\n\n";

// Get a test user
$testUser = User::first();
if (!$testUser) {
    echo "❌ No users found. Please create a user first.\n";
    exit(1);
}

echo "👤 Testing with user: {$testUser->name} (ID: {$testUser->_id})\n\n";

// Find or create a channel where user is creator
$creatorChannel = Channel::where('created_id', (string) $testUser->_id)->first();

if (!$creatorChannel) {
    echo "📁 Creating test channel where user is creator...\n";
    $creatorChannel = Channel::create([
        'name' => 'Creator Test Channel',
        'workspace_id' => '507f1f77bcf86cd799439011',
        'team_id' => '507f1f77bcf86cd799439012',
        'type' => 'public',
        'created_id' => (string) $testUser->_id,
        'members' => [(string) $testUser->_id]
    ]);
    echo "✅ Created: {$creatorChannel->name}\n\n";
} else {
    echo "📁 Using existing channel: {$creatorChannel->name}\n\n";
}

// Helper function to test middleware
function testMiddleware($middlewareClass, $middlewareName, $request) {
    echo "🧪 Testing {$middlewareName}:\n";
    
    $middleware = new $middlewareClass();
    $next = function ($req) {
        return response()->json(['success' => true]);
    };
    
    try {
        $response = $middleware->handle($request, $next);
        $status = $response->getStatusCode();
        $data = $response->getData(true);
        
        if ($status === 200) {
            echo "   ✅ SUCCESS: Creator can access this function\n";
        } else {
            echo "   ❌ FAILED: Status {$status}\n";
            echo "   📤 Response: " . json_encode($data) . "\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Create base request
$baseRequest = new Request();
$baseRequest->merge(['channel_id' => (string) $creatorChannel->_id]);
$baseRequest->setUserResolver(function () use ($testUser) {
    return $testUser;
});

echo "🔍 Channel Details:\n";
echo "   📁 Name: {$creatorChannel->name}\n";
echo "   🆔 ID: {$creatorChannel->_id}\n";
echo "   🏗️  Created by: {$creatorChannel->created_id}\n";
echo "   👥 Members: " . json_encode($creatorChannel->members) . "\n";
echo "   📊 User is creator: " . ((string) $creatorChannel->created_id === (string) $testUser->_id ? '✅ YES' : '❌ NO') . "\n\n";

// Test 1: Message Creation (CheckChannelMessageMiddleware)
$createRequest = clone $baseRequest;
$createRequest->merge(['message' => 'Test message']);
testMiddleware(CheckChannelMessageMiddleware::class, 'Message Creation', $createRequest);

// Test 2: Message Reading (CheckReadMessagesMiddleware)  
$readRequest = clone $baseRequest;
testMiddleware(CheckReadMessagesMiddleware::class, 'Message Reading', $readRequest);

// Test 3: Message Search (SearchMessageMiddleware)
$searchRequest = new Request();
$searchRequest->merge([
    'query' => 'test',
    'channel_id' => (string) $creatorChannel->_id
]);
$searchRequest->setUserResolver(function () use ($testUser) {
    return $testUser;
});
testMiddleware(SearchMessageMiddleware::class, 'Message Search', $searchRequest);

// Test with a channel where user is NOT creator and NOT member
echo "🧪 Testing with Non-Creator/Non-Member Channel:\n";
$otherChannel = Channel::where('created_id', '!=', (string) $testUser->_id)->first();

if (!$otherChannel) {
    echo "   📁 Creating channel where user is NOT creator/member...\n";
    $otherChannel = Channel::create([
        'name' => 'Other User Channel',
        'workspace_id' => '507f1f77bcf86cd799439011',
        'team_id' => '507f1f77bcf86cd799439012',
        'type' => 'private',
        'created_id' => '507f1f77bcf86cd799439999',
        'members' => ['507f1f77bcf86cd799439999']
    ]);
}

$otherRequest = new Request();
$otherRequest->merge(['channel_id' => (string) $otherChannel->_id]);
$otherRequest->setUserResolver(function () use ($testUser) {
    return $testUser;
});

echo "   📁 Testing with: {$otherChannel->name}\n";
echo "   🏗️  Created by: {$otherChannel->created_id} (not our user)\n";
echo "   👥 Members: " . json_encode($otherChannel->members) . " (user not included)\n\n";

$middleware = new CheckChannelMessageMiddleware();
$next = function ($req) {
    return response()->json(['success' => true]);
};

try {
    $response = $middleware->handle($otherRequest, $next);
    $status = $response->getStatusCode();
    
    if ($status === 403) {
        echo "   ✅ CORRECT: Non-member correctly blocked (Status: {$status})\n";
    } else {
        echo "   ❌ UNEXPECTED: Should have been blocked (Status: {$status})\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n🚀 Postman Test Instructions:\n";
echo "Now you should be able to:\n\n";

echo "1️⃣ CREATE MESSAGE:\n";
echo "   POST /messages/create\n";
echo "   Authorization: Bearer YOUR_TOKEN\n";
echo "   Body: {\n";
echo "     \"channel_id\": \"{$creatorChannel->_id}\",\n";
echo "     \"message\": \"Hello from creator!\"\n";
echo "   }\n\n";

echo "2️⃣ READ MESSAGES:\n";
echo "   GET /messages/read\n";
echo "   Authorization: Bearer YOUR_TOKEN\n";
echo "   Body: {\n";
echo "     \"channel_id\": \"{$creatorChannel->_id}\"\n";
echo "   }\n\n";

echo "3️⃣ SEARCH MESSAGES:\n";
echo "   GET /messages/search\n";
echo "   Authorization: Bearer YOUR_TOKEN\n";
echo "   Query: ?query=test&channel_id={$creatorChannel->_id}\n\n";

echo "4️⃣ UPDATE MESSAGE:\n";
echo "   PATCH /messages/update\n";
echo "   Authorization: Bearer YOUR_TOKEN\n";
echo "   Body: {\n";
echo "     \"channel_id\": \"{$creatorChannel->_id}\",\n";
echo "     \"message_id\": \"YOUR_MESSAGE_ID\",\n";
echo "     \"message\": \"Updated message\"\n";
echo "   }\n\n";

echo "5️⃣ REACT TO MESSAGE:\n";
echo "   POST /messages/react\n";
echo "   Authorization: Bearer YOUR_TOKEN\n";
echo "   Body: {\n";
echo "     \"channel_id\": \"{$creatorChannel->_id}\",\n";
echo "     \"message_ids\": [\"YOUR_MESSAGE_ID\"],\n";
echo "     \"emoji\": \"👍\"\n";
echo "   }\n\n";

echo "=== Test Complete ===\n";
echo "🎉 All message functions should now work for channel creators!\n";