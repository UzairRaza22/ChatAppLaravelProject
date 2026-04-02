<?php

// Test with the correct member structure: [{"user_id": "...", "role": "..."}]
// Run this with: php test_correct_member_structure.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Channel;
use App\Http\Middleware\Channel\ChannelExistMiddleware;
use Illuminate\Http\Request;

echo "=== Test Correct Member Structure ===\n\n";

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

// Create test channels with the correct member structure
echo "📁 Creating channels with correct member structure...\n";

// Channel 1: User 1 is creator and admin member
$channel1 = Channel::updateOrCreate(
    ['name' => 'Correct Structure Test 1'],
    [
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
    ]
);

echo "   ✅ Channel 1: {$channel1->name}\n";
echo "      Created by: {$channel1->created_id}\n";
echo "      Members: " . json_encode($channel1->members) . "\n\n";

// Channel 2: User 1 is creator, User 2 is member
$channel2 = Channel::updateOrCreate(
    ['name' => 'Correct Structure Test 2'],
    [
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
    ]
);

echo "   ✅ Channel 2: {$channel2->name}\n";
echo "      Created by: {$channel2->created_id}\n";
echo "      Members: " . json_encode($channel2->members) . "\n\n";

// Test the filtering logic for User 1 (should see both channels as creator)
echo "🔍 Testing for User 1 (should see both as creator):\n";
$user1Id = (string) $user1->_id;

$allChannels = Channel::all();
$user1Channels = $allChannels->filter(function ($channel) use ($user1Id) {
    // Check if user is creator
    if ((string) $channel->created_id === $user1Id) {
        return true;
    }
    
    // Check if user is member with correct structure
    $members = $channel->members ?? [];
    foreach ($members as $member) {
        if (is_array($member) && isset($member['user_id'])) {
            if ((string) $member['user_id'] === $user1Id) {
                return true;
            }
        }
    }
    
    return false;
});

echo "   Found " . $user1Channels->count() . " channels for User 1:\n";
foreach ($user1Channels as $channel) {
    $isCreator = (string) $channel->created_id === $user1Id;
    
    // Check if user is member
    $isMember = false;
    foreach ($channel->members ?? [] as $member) {
        if (is_array($member) && isset($member['user_id']) && (string) $member['user_id'] === $user1Id) {
            $isMember = true;
            break;
        }
    }
    
    echo "      - {$channel->name} (Creator: " . ($isCreator ? '✅' : '❌') . ", Member: " . ($isMember ? '✅' : '❌') . ")\n";
}

// Test the filtering logic for User 2 (should see channel 2 as member)
echo "\n🔍 Testing for User 2 (should see channel 2 as member):\n";
$user2Id = (string) $user2->_id;

$user2Channels = $allChannels->filter(function ($channel) use ($user2Id) {
    // Check if user is creator
    if ((string) $channel->created_id === $user2Id) {
        return true;
    }
    
    // Check if user is member with correct structure
    $members = $channel->members ?? [];
    foreach ($members as $member) {
        if (is_array($member) && isset($member['user_id'])) {
            if ((string) $member['user_id'] === $user2Id) {
                return true;
            }
        }
    }
    
    return false;
});

echo "   Found " . $user2Channels->count() . " channels for User 2:\n";
foreach ($user2Channels as $channel) {
    $isCreator = (string) $channel->created_id === $user2Id;
    
    // Check if user is member
    $isMember = false;
    $userRole = null;
    foreach ($channel->members ?? [] as $member) {
        if (is_array($member) && isset($member['user_id']) && (string) $member['user_id'] === $user2Id) {
            $isMember = true;
            $userRole = $member['role'] ?? 'unknown';
            break;
        }
    }
    
    echo "      - {$channel->name} (Creator: " . ($isCreator ? '✅' : '❌') . ", Member: " . ($isMember ? '✅' : '❌');
    if ($isMember) {
        echo ", Role: {$userRole}";
    }
    echo ")\n";
}

// Test the middleware with User 2
echo "\n🧪 Testing ChannelExistMiddleware with User 2:\n";

$request = new Request();
$request->setUserResolver(function () use ($user2) {
    return $user2;
});

$middleware = new ChannelExistMiddleware();
$next = function ($req) {
    return response()->json(['success' => true]);
};

try {
    $response = $middleware->handle($request, $next);
    $channels = $request->attributes->get('channels');
    
    echo "   Middleware found: " . ($channels ? $channels->count() : 0) . " channels\n";
    
    if ($channels && $channels->count() > 0) {
        echo "   ✅ SUCCESS: Middleware working with correct member structure!\n\n";
        
        foreach ($channels as $channel) {
            $isCreator = (string) $channel->created_id === $user2Id;
            
            // Check member status and role
            $isMember = false;
            $userRole = null;
            foreach ($channel->members ?? [] as $member) {
                if (is_array($member) && isset($member['user_id']) && (string) $member['user_id'] === $user2Id) {
                    $isMember = true;
                    $userRole = $member['role'] ?? 'unknown';
                    break;
                }
            }
            
            echo "      📁 {$channel->name}\n";
            echo "         Creator: " . ($isCreator ? '✅' : '❌') . "\n";
            echo "         Member: " . ($isMember ? '✅' : '❌') . ($userRole ? " (Role: {$userRole})" : '') . "\n";
            echo "         Members: " . json_encode($channel->members) . "\n\n";
        }
    } else {
        echo "   ❌ ISSUE: Middleware still not finding channels\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "🚀 Expected Postman Results:\n";
echo "   User 1 should see: " . $user1Channels->count() . " channels (as creator)\n";
echo "   User 2 should see: " . $user2Channels->count() . " channels (as member)\n";

echo "\n=== Test Complete ===\n";

if (isset($channels) && $channels->count() > 0) {
    echo "🎉 SUCCESS: The middleware now handles the correct member structure!\n";
    echo "Your Postman requests should now work correctly.\n";
} else {
    echo "⚠️  Check if the member structure in your database matches the expected format.\n";
}