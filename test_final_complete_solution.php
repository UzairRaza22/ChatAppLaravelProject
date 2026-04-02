<?php

// Final comprehensive test with correct member structure
// Run this with: php test_final_complete_solution.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Channel;
use App\Http\Middleware\Channel\ChannelExistMiddleware;
use App\Http\Middleware\Message\CheckChannelMessageMiddleware;
use Illuminate\Http\Request;

echo "=== Final Complete Solution Test ===\n\n";

// Get test users
$users = User::take(2)->get();
if ($users->count() < 2) {
    echo "❌ Need at least 2 users. Found: " . $users->count() . "\n";
    exit(1);
}

$user1 = $users->first();
$user2 = $users->get(1);

echo "👤 User 1: {$user1->name} (ID: {$user1->_id})\n";
echo "👤 User 2: {$user2->name} (ID: {$user2->_id})\n\n";

// Clean up and create fresh test data
echo "🧹 Setting up fresh test data...\n";

// Delete existing test channels
Channel::where('name', 'like', 'Final Test%')->delete();

// Create Channel 1: User 1 is creator and admin
$channel1 = Channel::create([
    'name' => 'Final Test Creator Channel',
    'workspace_id' => '507f1f77bcf86cd799439011',
    'team_id' => '507f1f77bcf86cd799439014',
    'type' => 'public',
    'created_id' => (string) $user1->_id,
    'members' => [
        [
            'user_id' => (string) $user1->_id,
            'role' => 'admin'
        ]
    ]
]);

// Create Channel 2: User 1 is creator, User 2 is member
$channel2 = Channel::create([
    'name' => 'Final Test Member Channel',
    'workspace_id' => '507f1f77bcf86cd799439011',
    'team_id' => '507f1f77bcf86cd799439014',
    'type' => 'private',
    'created_id' => (string) $user1->_id,
    'members' => [
        [
            'user_id' => (string) $user1->_id,
            'role' => 'admin'
        ],
        [
            'user_id' => (string) $user2->_id,
            'role' => 'member'
        ]
    ]
]);

echo "✅ Created test channels with correct member structure\n\n";

echo "📋 Test Data Summary:\n";
echo "   📁 {$channel1->name}\n";
echo "      Created by: {$channel1->created_id} (User 1)\n";
echo "      Members: " . json_encode($channel1->members) . "\n\n";

echo "   📁 {$channel2->name}\n";
echo "      Created by: {$channel2->created_id} (User 1)\n";
echo "      Members: " . json_encode($channel2->members) . "\n\n";

// Test 1: Channel retrieval for User 1 (should see both as creator)
echo "1️⃣ Testing channel retrieval for User 1 (creator):\n";

$request1 = new Request();
$request1->setUserResolver(function () use ($user1) {
    return $user1;
});

$middleware = new ChannelExistMiddleware();
$next = function ($req) {
    return response()->json(['success' => true]);
};

try {
    $response1 = $middleware->handle($request1, $next);
    $channels1 = $request1->attributes->get('channels');
    
    echo "   Found: " . ($channels1 ? $channels1->count() : 0) . " channels\n";
    
    if ($channels1 && $channels1->count() > 0) {
        foreach ($channels1 as $channel) {
            $isCreator = (string) $channel->created_id === (string) $user1->_id;
            $isMember = false;
            $userRole = null;
            
            foreach ($channel->members ?? [] as $member) {
                if (is_array($member) && isset($member['user_id']) && (string) $member['user_id'] === (string) $user1->_id) {
                    $isMember = true;
                    $userRole = $member['role'] ?? 'unknown';
                    break;
                }
            }
            
            echo "      ✅ {$channel->name} (Creator: " . ($isCreator ? 'YES' : 'NO') . ", Member: " . ($isMember ? 'YES' : 'NO');
            if ($userRole) echo ", Role: {$userRole}";
            echo ")\n";
        }
    } else {
        echo "   ❌ No channels found for User 1\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 2: Channel retrieval for User 2 (should see channel 2 as member)
echo "\n2️⃣ Testing channel retrieval for User 2 (member):\n";

$request2 = new Request();
$request2->setUserResolver(function () use ($user2) {
    return $user2;
});

try {
    $response2 = $middleware->handle($request2, $next);
    $channels2 = $request2->attributes->get('channels');
    
    echo "   Found: " . ($channels2 ? $channels2->count() : 0) . " channels\n";
    
    if ($channels2 && $channels2->count() > 0) {
        foreach ($channels2 as $channel) {
            $isCreator = (string) $channel->created_id === (string) $user2->_id;
            $isMember = false;
            $userRole = null;
            
            foreach ($channel->members ?? [] as $member) {
                if (is_array($member) && isset($member['user_id']) && (string) $member['user_id'] === (string) $user2->_id) {
                    $isMember = true;
                    $userRole = $member['role'] ?? 'unknown';
                    break;
                }
            }
            
            echo "      ✅ {$channel->name} (Creator: " . ($isCreator ? 'YES' : 'NO') . ", Member: " . ($isMember ? 'YES' : 'NO');
            if ($userRole) echo ", Role: {$userRole}";
            echo ")\n";
        }
    } else {
        echo "   ❌ No channels found for User 2\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 3: Message creation for User 2 in channel 2 (should work now)
echo "\n3️⃣ Testing message creation for User 2 in member channel:\n";

$messageRequest = new Request();
$messageRequest->merge(['channel_id' => (string) $channel2->_id]);
$messageRequest->setUserResolver(function () use ($user2) {
    return $user2;
});

$messageMiddleware = new CheckChannelMessageMiddleware();

try {
    $messageResponse = $messageMiddleware->handle($messageRequest, $next);
    $status = $messageResponse->getStatusCode();
    
    if ($status === 200) {
        echo "   ✅ SUCCESS: User 2 can send messages to member channel\n";
    } else {
        echo "   ❌ FAILED: User 2 cannot send messages (Status: {$status})\n";
        $data = $messageResponse->getData(true);
        echo "   Response: " . json_encode($data) . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🚀 Postman Test Results:\n";
echo "   User 1 should see: " . (isset($channels1) ? $channels1->count() : 0) . " channels (as creator)\n";
echo "   User 2 should see: " . (isset($channels2) ? $channels2->count() : 0) . " channels (as member)\n";
echo "   User 2 can send messages: " . (isset($status) && $status === 200 ? '✅ YES' : '❌ NO') . "\n";

echo "\n📝 Postman Instructions:\n";
echo "1. GET /channels/list-by-user\n";
echo "   Authorization: Bearer YOUR_TOKEN\n";
echo "   Should return channels where you are creator OR member\n\n";

echo "2. POST /messages/create\n";
echo "   Authorization: Bearer YOUR_TOKEN\n";
echo "   Body: {\"channel_id\": \"CHANNEL_ID\", \"message\": \"Hello!\"}\n";
echo "   Should work for channels where you are creator OR member\n\n";

echo "=== Test Complete ===\n";

$success = (isset($channels1) && $channels1->count() > 0) && 
           (isset($channels2) && $channels2->count() > 0) && 
           (isset($status) && $status === 200);

if ($success) {
    echo "🎉 SUCCESS: All functionality is working correctly!\n";
    echo "✅ Channel retrieval works for creators and members\n";
    echo "✅ Message creation works for creators and members\n";
    echo "✅ Correct member structure is being used\n";
} else {
    echo "⚠️  Some issues remain. Check the test output above.\n";
}