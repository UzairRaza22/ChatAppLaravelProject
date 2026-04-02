<?php

// Test the PHP filtering approach
// Run this with: php test_php_filter_approach.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Channel;

echo "=== PHP Filter Approach Test ===\n\n";

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

// Ensure we have test data
echo "📁 Setting up test channels...\n";

// Channel 1: Created by User 1, User 1 is member
$channel1 = Channel::where('created_id', (string) $user1->_id)->first();
if (!$channel1) {
    $channel1 = Channel::create([
        'name' => 'User 1 Creator Channel',
        'workspace_id' => '507f1f77bcf86cd799439011',
        'team_id' => '507f1f77bcf86cd799439012',
        'type' => 'public',
        'created_id' => (string) $user1->_id,
        'members' => [(string) $user1->_id]
    ]);
    echo "   ✅ Created channel by User 1\n";
}

// Channel 2: Created by User 1, User 2 is added as member
$channel2 = Channel::where('name', 'User 2 Member Channel')->first();
if (!$channel2) {
    $channel2 = Channel::create([
        'name' => 'User 2 Member Channel',
        'workspace_id' => '507f1f77bcf86cd799439011',
        'team_id' => '507f1f77bcf86cd799439012',
        'type' => 'private',
        'created_id' => (string) $user1->_id,
        'members' => [(string) $user1->_id, (string) $user2->_id] // Both users are members
    ]);
    echo "   ✅ Created channel where User 2 is member\n";
}

echo "\n📋 Current channels:\n";
$allChannels = Channel::all();
foreach ($allChannels as $channel) {
    echo "   📁 {$channel->name}\n";
    echo "      Created by: {$channel->created_id}\n";
    echo "      Members: " . json_encode($channel->members) . "\n\n";
}

// Test the PHP filtering approach for User 1
echo "🔍 Testing PHP filter for User 1 (should see channels as creator):\n";
$user1Id = (string) $user1->_id;

$user1Channels = $allChannels->filter(function ($channel) use ($user1Id) {
    // Check if user is creator
    if ((string) $channel->created_id === $user1Id) {
        return true;
    }
    
    // Check if user is member
    $members = $channel->members ?? [];
    
    foreach ($members as $member) {
        if (is_string($member)) {
            if ((string) $member === $user1Id) {
                return true;
            }
        } elseif (is_array($member)) {
            $memberId = $member['user_id'] ?? $member['_id'] ?? $member['id'] ?? '';
            if ((string) $memberId === $user1Id) {
                return true;
            }
        }
    }
    
    return false;
});

echo "   Found " . $user1Channels->count() . " channels for User 1:\n";
foreach ($user1Channels as $channel) {
    $isCreator = (string) $channel->created_id === $user1Id;
    $isMember = in_array($user1Id, $channel->members ?? []);
    echo "      - {$channel->name} (Creator: " . ($isCreator ? '✅' : '❌') . ", Member: " . ($isMember ? '✅' : '❌') . ")\n";
}

// Test the PHP filtering approach for User 2
echo "\n🔍 Testing PHP filter for User 2 (should see channels as member):\n";
$user2Id = (string) $user2->_id;

$user2Channels = $allChannels->filter(function ($channel) use ($user2Id) {
    // Check if user is creator
    if ((string) $channel->created_id === $user2Id) {
        return true;
    }
    
    // Check if user is member
    $members = $channel->members ?? [];
    
    foreach ($members as $member) {
        if (is_string($member)) {
            if ((string) $member === $user2Id) {
                return true;
            }
        } elseif (is_array($member)) {
            $memberId = $member['user_id'] ?? $member['_id'] ?? $member['id'] ?? '';
            if ((string) $memberId === $user2Id) {
                return true;
            }
        }
    }
    
    return false;
});

echo "   Found " . $user2Channels->count() . " channels for User 2:\n";
foreach ($user2Channels as $channel) {
    $isCreator = (string) $channel->created_id === $user2Id;
    $isMember = in_array($user2Id, $channel->members ?? []);
    echo "      - {$channel->name} (Creator: " . ($isCreator ? '✅' : '❌') . ", Member: " . ($isMember ? '✅' : '❌') . ")\n";
}

echo "\n=== Results Analysis ===\n";
echo "User 1 should see: " . $user1Channels->count() . " channels (as creator)\n";
echo "User 2 should see: " . $user2Channels->count() . " channels (as member)\n\n";

if ($user1Channels->count() > 0 && $user2Channels->count() > 0) {
    echo "✅ SUCCESS: PHP filtering approach works!\n";
    echo "This approach will work in the middleware and Postman.\n";
} else {
    echo "⚠️  Check the test data setup above.\n";
    echo "Make sure channels exist with the correct creator/member relationships.\n";
}

echo "\n🚀 This approach should now work in Postman:\n";
echo "   GET /channels/list-by-user\n";
echo "   Authorization: Bearer YOUR_TOKEN\n";
echo "   Should return all channels where you are creator OR member\n";

echo "\n=== Test Complete ===\n";