<?php

// Test creator message sending functionality
// Run this with: php test_creator_message.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Channel;
use App\Http\Middleware\Message\CheckChannelMessageMiddleware;
use Illuminate\Http\Request;

echo "=== Creator Message Test ===\n\n";

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
    echo "📁 No channels found where user is creator. Creating test channel...\n";
    $creatorChannel = Channel::create([
        'name' => 'Test Creator Channel',
        'workspace_id' => '507f1f77bcf86cd799439011',
        'team_id' => '507f1f77bcf86cd799439012',
        'type' => 'public',
        'created_id' => (string) $testUser->_id,
        'members' => [(string) $testUser->_id]
    ]);
    echo "✅ Created test channel: {$creatorChannel->name}\n\n";
} else {
    echo "📁 Found existing channel where user is creator: {$creatorChannel->name}\n\n";
}

// Test the middleware logic
echo "🔍 Testing Channel Access Logic:\n";
$userId = (string) $testUser->_id;
$members = collect($creatorChannel->members ?? []);

// Check if user is creator
$isCreator = (string) $creatorChannel->created_id === $userId;
echo "   🏗️  Is Creator: " . ($isCreator ? '✅ YES' : '❌ NO') . " (created_id: {$creatorChannel->created_id})\n";

// Check if user is member
$isMemberInArray = $members->contains(function ($member) use ($userId) {
    if (is_string($member)) {
        return (string) $member === $userId;
    }
    if (is_array($member)) {
        return (string) ($member['user_id'] ?? $member['_id'] ?? $member['id'] ?? '') === $userId;
    }
    if (is_object($member)) {
        return (string) (data_get($member, 'user_id') ?? data_get($member, '_id') ?? data_get($member, 'id')) === $userId;
    }
    return false;
});
echo "   👥 Is Member: " . ($isMemberInArray ? '✅ YES' : '❌ NO') . " (members: " . json_encode($creatorChannel->members) . ")\n";

// Combined check (creator OR member)
$canSendMessage = $isCreator || $isMemberInArray;
echo "   📝 Can Send Message: " . ($canSendMessage ? '✅ YES' : '❌ NO') . "\n\n";

// Test the actual middleware
echo "🧪 Testing CheckChannelMessageMiddleware:\n";

$request = new Request();
$request->merge(['channel_id' => (string) $creatorChannel->_id]);
$request->setUserResolver(function () use ($testUser) {
    return $testUser;
});

$middleware = new CheckChannelMessageMiddleware();

$next = function ($req) {
    return response()->json(['success' => true, 'message' => 'Message can be sent']);
};

try {
    $response = $middleware->handle($request, $next);
    $responseData = $response->getData(true);
    
    if ($response->getStatusCode() === 200) {
        echo "   ✅ SUCCESS: Middleware allows message creation\n";
        echo "   📤 Response: " . json_encode($responseData) . "\n";
    } else {
        echo "   ❌ FAILED: Middleware blocked message creation\n";
        echo "   🚫 Status: " . $response->getStatusCode() . "\n";
        echo "   📤 Response: " . json_encode($responseData) . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ ERROR: " . $e->getMessage() . "\n";
}

// Test with a channel where user is NOT creator and NOT member
echo "\n🧪 Testing with Non-Member Channel:\n";
$otherChannel = Channel::where('created_id', '!=', (string) $testUser->_id)->first();

if (!$otherChannel) {
    echo "   📁 Creating test channel where user is NOT creator/member...\n";
    $otherChannel = Channel::create([
        'name' => 'Other User Channel',
        'workspace_id' => '507f1f77bcf86cd799439011',
        'team_id' => '507f1f77bcf86cd799439012',
        'type' => 'private',
        'created_id' => '507f1f77bcf86cd799439999', // Different user
        'members' => ['507f1f77bcf86cd799439999'] // Different user
    ]);
}

$request2 = new Request();
$request2->merge(['channel_id' => (string) $otherChannel->_id]);
$request2->setUserResolver(function () use ($testUser) {
    return $testUser;
});

try {
    $response2 = $middleware->handle($request2, $next);
    $responseData2 = $response2->getData(true);
    
    if ($response2->getStatusCode() === 403) {
        echo "   ✅ CORRECT: Middleware correctly blocked non-member\n";
        echo "   🚫 Response: " . json_encode($responseData2) . "\n";
    } else {
        echo "   ❌ UNEXPECTED: Middleware should have blocked this\n";
        echo "   📤 Status: " . $response2->getStatusCode() . "\n";
        echo "   📤 Response: " . json_encode($responseData2) . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n🚀 Postman Test Instructions:\n";
echo "1. Set Authorization: Bearer YOUR_LOGIN_TOKEN\n";
echo "2. Make POST request to: /messages/send (or your message endpoint)\n";
echo "3. Include channel_id: {$creatorChannel->_id}\n";
echo "4. Include message content in body\n";
echo "5. Should work now for channels where you are creator!\n\n";

echo "=== Test Complete ===\n";

if ($canSendMessage) {
    echo "🎉 SUCCESS: Creator should be able to send messages to their channels!\n";
} else {
    echo "⚠️  WARNING: There might still be an issue with the logic.\n";
}