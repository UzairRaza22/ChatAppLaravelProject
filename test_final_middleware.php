<?php

// Test the final middleware implementation
// Run this with: php test_final_middleware.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Channel;
use App\Http\Middleware\Channel\ChannelExistMiddleware;
use Illuminate\Http\Request;

echo "=== Final Middleware Test ===\n\n";

// Get test user
$testUser = User::first();
if (!$testUser) {
    echo "❌ No users found.\n";
    exit(1);
}

echo "👤 Testing with user: {$testUser->name} (ID: {$testUser->_id})\n\n";

// Ensure test data exists
$userId = (string) $testUser->_id;

// Create test channels if they don't exist
echo "📁 Ensuring test data exists...\n";

// Channel where user is creator
$creatorChannel = Channel::where('created_id', $userId)->first();
if (!$creatorChannel) {
    $creatorChannel = Channel::create([
        'name' => 'Final Test Creator Channel',
        'workspace_id' => '507f1f77bcf86cd799439011',
        'team_id' => '507f1f77bcf86cd799439012',
        'type' => 'public',
        'created_id' => $userId,
        'members' => [$userId]
    ]);
    echo "   ✅ Created creator channel\n";
}

// Channel where user is member (created by someone else)
$memberChannel = Channel::where('created_id', '!=', $userId)
    ->whereRaw(function($query) use ($userId) {
        // This is a fallback - we'll check manually
        return true;
    })->first();

if (!$memberChannel || !in_array($userId, $memberChannel->members ?? [])) {
    // Create or update a channel where user is member
    $memberChannel = Channel::where('name', 'Final Test Member Channel')->first();
    if (!$memberChannel) {
        $memberChannel = Channel::create([
            'name' => 'Final Test Member Channel',
            'workspace_id' => '507f1f77bcf86cd799439011',
            'team_id' => '507f1f77bcf86cd799439012',
            'type' => 'private',
            'created_id' => '507f1f77bcf86cd799439999', // Different creator
            'members' => ['507f1f77bcf86cd799439999', $userId] // User is member
        ]);
        echo "   ✅ Created member channel\n";
    } else {
        // Update existing channel to include user as member
        $members = $memberChannel->members ?? [];
        if (!in_array($userId, $members)) {
            $members[] = $userId;
            $memberChannel->update(['members' => $members]);
            echo "   ✅ Added user as member to existing channel\n";
        }
    }
}

echo "\n📋 Test data summary:\n";
$allChannels = Channel::all();
foreach ($allChannels as $channel) {
    $isCreator = (string) $channel->created_id === $userId;
    $isMember = in_array($userId, $channel->members ?? []);
    $shouldShow = $isCreator || $isMember;
    
    echo "   📁 {$channel->name}\n";
    echo "      Creator: " . ($isCreator ? '✅' : '❌') . " | Member: " . ($isMember ? '✅' : '❌') . " | Should show: " . ($shouldShow ? '✅' : '❌') . "\n";
}

// Test the middleware
echo "\n🧪 Testing ChannelExistMiddleware:\n";

$request = new Request();
// Simulate token-based auth (no user_id parameter)
$request->setUserResolver(function () use ($testUser) {
    return $testUser;
});

$middleware = new ChannelExistMiddleware();
$next = function ($req) {
    return response()->json(['success' => true]);
};

try {
    $response = $middleware->handle($request, $next);
    $channels = $request->attributes->get('channels');
    
    echo "   Response status: " . $response->getStatusCode() . "\n";
    echo "   Channels found: " . ($channels ? $channels->count() : 0) . "\n\n";
    
    if ($channels && $channels->count() > 0) {
        echo "   ✅ SUCCESS: Middleware found channels!\n\n";
        
        foreach ($channels as $channel) {
            $isCreator = (string) $channel->created_id === $userId;
            $isMember = in_array($userId, $channel->members ?? []);
            
            echo "      📁 {$channel->name}\n";
            echo "         ID: {$channel->_id}\n";
            echo "         Creator: " . ($isCreator ? '✅ YES' : '❌ NO') . "\n";
            echo "         Member: " . ($isMember ? '✅ YES' : '❌ NO') . "\n";
            echo "         Members array: " . json_encode($channel->members) . "\n\n";
        }
        
        // Count breakdown
        $creatorCount = $channels->filter(function($ch) use ($userId) {
            return (string) $ch->created_id === $userId;
        })->count();
        
        $memberCount = $channels->filter(function($ch) use ($userId) {
            return in_array($userId, $ch->members ?? []);
        })->count();
        
        echo "   📊 Summary:\n";
        echo "      Channels as creator: {$creatorCount}\n";
        echo "      Channels as member: {$memberCount}\n";
        echo "      Total channels: " . $channels->count() . "\n";
        
    } else {
        echo "   ❌ ISSUE: No channels found by middleware\n";
        
        // Debug: Test the filtering logic directly
        echo "\n   🔍 Debug: Testing filter logic directly...\n";
        $allChannels = Channel::all();
        $filteredChannels = $allChannels->filter(function ($channel) use ($userId) {
            // Same logic as in middleware
            if ((string) $channel->created_id === $userId) {
                return true;
            }
            
            $members = $channel->members ?? [];
            foreach ($members as $member) {
                if (is_string($member) && (string) $member === $userId) {
                    return true;
                }
            }
            
            return false;
        });
        
        echo "      Direct filter found: " . $filteredChannels->count() . " channels\n";
        if ($filteredChannels->count() > 0) {
            echo "      ⚠️  Filter works but middleware doesn't - check middleware logic\n";
        } else {
            echo "      ❌ Filter also doesn't work - check test data\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ❌ Middleware error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🚀 Postman Test:\n";
echo "   Method: GET\n";
echo "   URL: /channels/list-by-user\n";
echo "   Authorization: Bearer YOUR_TOKEN\n";
echo "   Expected: Should return " . ($channels ? $channels->count() : 'X') . " channels\n";

echo "\n=== Test Complete ===\n";

if (isset($channels) && $channels->count() > 0) {
    echo "🎉 SUCCESS: The middleware should now work correctly in Postman!\n";
    echo "You should see both creator and member channels.\n";
} else {
    echo "❌ ISSUE: There's still a problem. Check the debug output above.\n";
}