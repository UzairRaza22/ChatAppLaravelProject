<?php

// Verify the actual data structure in your database
// Run this with: php verify_data_structure.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Channel;

echo "=== VERIFY DATA STRUCTURE ===\n\n";

// Get a test user
$testUser = User::first();
if (!$testUser) {
    echo "❌ No users found.\n";
    exit(1);
}

$userId = (string) $testUser->_id;
echo "👤 User: {$testUser->name} (ID: {$userId})\n\n";

// Check if we have the channel from your API example
$exampleChannel = Channel::where('_id', '507f1f77bcf86cd799439016')->first();

if ($exampleChannel) {
    echo "📁 Found your example channel:\n";
    echo "   Name: {$exampleChannel->name}\n";
    echo "   ID: {$exampleChannel->_id}\n";
    echo "   Created by: {$exampleChannel->created_id}\n";
    echo "   Members: " . json_encode($exampleChannel->members, JSON_PRETTY_PRINT) . "\n\n";
    
    // Check if our test user is in this channel
    $isCreator = (string) $exampleChannel->created_id === $userId;
    $isMember = false;
    
    if (is_array($exampleChannel->members)) {
        foreach ($exampleChannel->members as $member) {
            if (is_array($member) && isset($member['user_id']) && (string) $member['user_id'] === $userId) {
                $isMember = true;
                break;
            }
        }
    }
    
    echo "   Our user is creator: " . ($isCreator ? '✅ YES' : '❌ NO') . "\n";
    echo "   Our user is member: " . ($isMember ? '✅ YES' : '❌ NO') . "\n\n";
    
    if (!$isCreator && !$isMember) {
        echo "   ⚠️  Adding our user as a member to test...\n";
        $members = $exampleChannel->members ?? [];
        $members[] = [
            'user_id' => $userId,
            'role' => 'member'
        ];
        $exampleChannel->update(['members' => $members]);
        echo "   ✅ Added user as member\n\n";
    }
} else {
    echo "📁 Example channel not found. Creating test channel...\n";
    
    $exampleChannel = Channel::create([
        '_id' => '507f1f77bcf86cd799439016',
        'name' => 'general',
        'workspace_id' => '507f1f77bcf86cd799439011',
        'team_id' => '507f1f77bcf86cd799439014',
        'type' => 'public',
        'direct_id' => null,
        'created_id' => '507f1f77bcf86cd799439012',
        'members' => [
            [
                'user_id' => '507f1f77bcf86cd799439012',
                'role' => 'admin'
            ],
            [
                'user_id' => $userId,
                'role' => 'member'
            ]
        ]
    ]);
    
    echo "   ✅ Created example channel with our user as member\n\n";
}

// Now test the filtering logic with this real data
echo "🧪 TESTING WITH REAL DATA:\n";

$allChannels = Channel::all();
$userChannels = $allChannels->filter(function ($channel) use ($userId) {
    // Check if user is creator
    if ((string) $channel->created_id === $userId) {
        return true;
    }
    
    // Check if user is member
    $members = $channel->members ?? [];
    
    foreach ($members as $member) {
        if (is_array($member) && isset($member['user_id'])) {
            if ((string) $member['user_id'] === $userId) {
                return true;
            }
        }
    }
    
    return false;
});

echo "   Total channels: " . $allChannels->count() . "\n";
echo "   User's channels: " . $userChannels->count() . "\n\n";

if ($userChannels->count() > 0) {
    echo "   ✅ SUCCESS: Found channels for user\n";
    foreach ($userChannels as $channel) {
        $isCreator = (string) $channel->created_id === $userId;
        $isMember = false;
        $userRole = null;
        
        foreach ($channel->members ?? [] as $member) {
            if (is_array($member) && isset($member['user_id']) && (string) $member['user_id'] === $userId) {
                $isMember = true;
                $userRole = $member['role'] ?? 'unknown';
                break;
            }
        }
        
        echo "      📁 {$channel->name}\n";
        echo "         Creator: " . ($isCreator ? '✅' : '❌') . "\n";
        echo "         Member: " . ($isMember ? '✅' : '❌') . ($userRole ? " (Role: {$userRole})" : '') . "\n";
    }
} else {
    echo "   ❌ FAILURE: No channels found\n";
    echo "   This suggests either:\n";
    echo "   1. The user has no channels\n";
    echo "   2. The data structure is different\n";
    echo "   3. The filtering logic has an issue\n";
}

// Create a simple test case
echo "\n🔧 CREATING SIMPLE TEST CASE:\n";

$testChannel = Channel::updateOrCreate(
    ['name' => 'Simple Test Channel'],
    [
        'workspace_id' => '507f1f77bcf86cd799439011',
        'team_id' => '507f1f77bcf86cd799439014',
        'type' => 'public',
        'created_id' => $userId,
        'members' => [
            [
                'user_id' => $userId,
                'role' => 'admin'
            ]
        ]
    ]
);

echo "   ✅ Created/updated simple test channel\n";
echo "   Name: {$testChannel->name}\n";
echo "   Created by: {$testChannel->created_id}\n";
echo "   Members: " . json_encode($testChannel->members) . "\n\n";

// Test again
$retestChannels = Channel::all()->filter(function ($channel) use ($userId) {
    return (string) $channel->created_id === $userId || 
           collect($channel->members ?? [])->contains(function ($member) use ($userId) {
               return is_array($member) && isset($member['user_id']) && (string) $member['user_id'] === $userId;
           });
});

echo "🔄 RETEST RESULTS:\n";
echo "   User's channels after test creation: " . $retestChannels->count() . "\n";

if ($retestChannels->count() > 0) {
    echo "   ✅ SUCCESS: Test channel is found\n";
    echo "   The filtering logic works with proper data\n";
} else {
    echo "   ❌ FAILURE: Even test channel not found\n";
    echo "   There's a fundamental issue with the logic\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";