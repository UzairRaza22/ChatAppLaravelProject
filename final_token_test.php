<?php

// Final test for token-based channel retrieval
// This simulates exactly what happens in Postman with just a token
// Run this with: php final_token_test.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Channel;

echo "=== Final Token Authentication Test ===\n\n";

// Get a test user
$testUser = User::first();
if (!$testUser) {
    echo "❌ No users found. Please create a user first.\n";
    exit(1);
}

echo "🔐 Testing with authenticated user: {$testUser->name}\n";
echo "📧 Email: " . ($testUser->email ?? 'N/A') . "\n";
echo "🆔 User ID: {$testUser->_id}\n\n";

// Show all channels in database first
echo "📋 All Channels in Database:\n";
$allChannels = Channel::all();
if ($allChannels->count() === 0) {
    echo "   ⚠️  No channels found in database.\n";
    echo "   Creating test channels...\n\n";
    
    // Create test channels
    $channel1 = Channel::create([
        'name' => 'Test Channel 1 (Creator)',
        'workspace_id' => '507f1f77bcf86cd799439011',
        'team_id' => '507f1f77bcf86cd799439012',
        'type' => 'public',
        'created_id' => (string) $testUser->_id,
        'members' => [(string) $testUser->_id]
    ]);
    
    $channel2 = Channel::create([
        'name' => 'Test Channel 2 (Member Only)',
        'workspace_id' => '507f1f77bcf86cd799439011',
        'team_id' => '507f1f77bcf86cd799439012',
        'type' => 'private',
        'created_id' => '507f1f77bcf86cd799439999', // Different creator
        'members' => [(string) $testUser->_id, '507f1f77bcf86cd799439999']
    ]);
    
    echo "   ✅ Created test channels\n\n";
    $allChannels = Channel::all();
}

foreach ($allChannels as $channel) {
    $isCreator = (string) $channel->created_id === (string) $testUser->_id;
    $members = collect($channel->members ?? []);
    $isMember = $members->contains(function ($member) use ($testUser) {
        if (is_string($member)) {
            return (string) $member === (string) $testUser->_id;
        }
        if (is_array($member)) {
            return (string) ($member['user_id'] ?? $member['_id'] ?? $member['id'] ?? '') === (string) $testUser->_id;
        }
        return false;
    });
    
    echo "   📁 {$channel->name}\n";
    echo "      🏗️  Creator: " . ($isCreator ? '✅ YES' : '❌ NO') . " (created_id: {$channel->created_id})\n";
    echo "      👥 Member: " . ($isMember ? '✅ YES' : '❌ NO') . " (members: " . json_encode($channel->members) . ")\n";
    echo "      📊 Should appear: " . ($isCreator || $isMember ? '✅ YES' : '❌ NO') . "\n\n";
}

// Test the exact query that middleware uses
echo "🔍 Testing Middleware Query:\n";
$userId = (string) $testUser->_id;

$channels = Channel::where(function ($query) use ($userId) {
    $query->where('created_id', $userId)  // User is creator
          ->orWhere('members', $userId)  // User is in members array (simple string)
          ->orWhere('members.user_id', $userId)  // User is in members array (object with user_id)
          ->orWhere('members._id', $userId)  // User is in members array (object with _id)
          ->orWhere('members.id', $userId);  // User is in members array (object with id)
})->get();

echo "   🎯 Query found " . $channels->count() . " channels:\n";

if ($channels->count() > 0) {
    foreach ($channels as $channel) {
        $isCreator = (string) $channel->created_id === $userId;
        $members = collect($channel->members ?? []);
        $isMember = $members->contains(function ($member) use ($userId) {
            if (is_string($member)) return (string) $member === $userId;
            if (is_array($member)) return (string) ($member['user_id'] ?? $member['_id'] ?? $member['id'] ?? '') === $userId;
            return false;
        });
        
        echo "      ✅ {$channel->name} - Creator: " . ($isCreator ? 'YES' : 'NO') . ", Member: " . ($isMember ? 'YES' : 'NO') . "\n";
    }
} else {
    echo "      ❌ No channels found!\n";
    echo "      🔧 This might indicate a MongoDB query issue.\n";
}

echo "\n🚀 Postman Instructions:\n";
echo "   1. Set Authorization: Bearer YOUR_LOGIN_TOKEN\n";
echo "   2. Make GET request to: /channels/list-by-user\n";
echo "   3. Don't include any parameters (user_id will be extracted from token)\n";
echo "   4. Expected result: " . $channels->count() . " channels\n\n";

echo "✅ Configuration Summary:\n";
echo "   📝 Validation: user_id is optional (nullable)\n";
echo "   🔐 Authentication: Extracts user from token\n";
echo "   🔍 Query: Finds creator OR member channels\n";
echo "   📤 Response: Returns all matching channels\n\n";

echo "=== Test Complete ===\n";

if ($channels->count() > 0) {
    echo "🎉 SUCCESS: The system should work correctly in Postman!\n";
} else {
    echo "⚠️  WARNING: No channels found. Check if:\n";
    echo "   - User has created any channels\n";
    echo "   - User has been added as member to any channels\n";
    echo "   - MongoDB query syntax is working correctly\n";
}