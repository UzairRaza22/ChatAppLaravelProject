<?php

// Comprehensive verification script for channel retrieval
// Run this with: php verify_channel_retrieval.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Channel;
use App\Models\User;

echo "=== Channel Retrieval Verification ===\n\n";

// Get test users
$users = User::take(2)->get();
if ($users->count() < 2) {
    echo "Need at least 2 users for testing. Found: " . $users->count() . "\n";
    echo "Creating test scenario with available users...\n\n";
}

$user1 = $users->first();
$user2 = $users->count() > 1 ? $users->get(1) : $user1;

echo "User 1: {$user1->name} (ID: {$user1->_id})\n";
echo "User 2: {$user2->name} (ID: {$user2->_id})\n\n";

// Function to test the exact query from middleware
function testChannelQuery($userId) {
    echo "Testing query for user: $userId\n";
    
    // This is the exact query from the middleware
    $channels = Channel::where(function ($query) use ($userId) {
        $query->where('created_id', $userId)  // User is creator
              ->orWhere('members', $userId)  // User is in members array (simple string)
              ->orWhere('members.user_id', $userId)  // User is in members array (object with user_id)
              ->orWhere('members._id', $userId)  // User is in members array (object with _id)
              ->orWhere('members.id', $userId);  // User is in members array (object with id)
    })->get();
    
    echo "Found " . $channels->count() . " channels:\n";
    
    foreach ($channels as $channel) {
        $isCreator = (string) $channel->created_id === $userId;
        $members = collect($channel->members ?? []);
        
        // Check if user is member using the same logic as message middleware
        $isMember = $members->contains(function ($member) use ($userId) {
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
        
        echo "  - {$channel->name} (ID: {$channel->_id})\n";
        echo "    Creator: " . ($isCreator ? 'YES' : 'NO') . " (created_id: {$channel->created_id})\n";
        echo "    Member: " . ($isMember ? 'YES' : 'NO') . "\n";
        echo "    Members: " . json_encode($channel->members) . "\n";
        echo "    Type: {$channel->type}\n\n";
    }
    
    return $channels;
}

// Test 1: Show all channels in database
echo "=== All Channels in Database ===\n";
$allChannels = Channel::all();
foreach ($allChannels as $channel) {
    echo "Channel: {$channel->name}\n";
    echo "  ID: {$channel->_id}\n";
    echo "  Created by: {$channel->created_id}\n";
    echo "  Members: " . json_encode($channel->members) . "\n";
    echo "  Type: {$channel->type}\n\n";
}

// Test 2: Test retrieval for User 1
echo "=== Testing User 1 Channel Retrieval ===\n";
$user1Channels = testChannelQuery((string) $user1->_id);

// Test 3: Test retrieval for User 2 (if different)
if ($user1->_id !== $user2->_id) {
    echo "=== Testing User 2 Channel Retrieval ===\n";
    $user2Channels = testChannelQuery((string) $user2->_id);
}

// Test 4: Create a test scenario if no channels exist
if ($allChannels->count() === 0) {
    echo "=== Creating Test Channels ===\n";
    
    // Create a channel by User 1
    $testChannel1 = Channel::create([
        'name' => 'Test Channel 1',
        'workspace_id' => '507f1f77bcf86cd799439011', // dummy workspace ID
        'team_id' => '507f1f77bcf86cd799439012', // dummy team ID
        'type' => 'public',
        'created_id' => (string) $user1->_id,
        'members' => [(string) $user1->_id]
    ]);
    
    echo "Created channel: {$testChannel1->name} by User 1\n";
    
    // Add User 2 as member
    if ($user1->_id !== $user2->_id) {
        $testChannel1->update([
            'members' => [(string) $user1->_id, (string) $user2->_id]
        ]);
        echo "Added User 2 as member to Test Channel 1\n";
    }
    
    // Create another channel by User 2
    if ($user1->_id !== $user2->_id) {
        $testChannel2 = Channel::create([
            'name' => 'Test Channel 2',
            'workspace_id' => '507f1f77bcf86cd799439011',
            'team_id' => '507f1f77bcf86cd799439012',
            'type' => 'private',
            'created_id' => (string) $user2->_id,
            'members' => [(string) $user2->_id]
        ]);
        
        echo "Created channel: {$testChannel2->name} by User 2\n";
    }
    
    echo "\n=== Re-testing After Channel Creation ===\n";
    echo "User 1 channels:\n";
    testChannelQuery((string) $user1->_id);
    
    if ($user1->_id !== $user2->_id) {
        echo "User 2 channels:\n";
        testChannelQuery((string) $user2->_id);
    }
}

// Test 5: Verify the middleware logic step by step
echo "=== Step-by-Step Middleware Logic Test ===\n";
$testUserId = (string) $user1->_id;

echo "1. Testing created_id query:\n";
$createdChannels = Channel::where('created_id', $testUserId)->get();
echo "   Found " . $createdChannels->count() . " channels created by user\n";

echo "2. Testing members array queries:\n";
$memberChannels1 = Channel::where('members', $testUserId)->get();
echo "   Simple string query: " . $memberChannels1->count() . " channels\n";

$memberChannels2 = Channel::where('members.user_id', $testUserId)->get();
echo "   Object user_id query: " . $memberChannels2->count() . " channels\n";

$memberChannels3 = Channel::where('members._id', $testUserId)->get();
echo "   Object _id query: " . $memberChannels3->count() . " channels\n";

$memberChannels4 = Channel::where('members.id', $testUserId)->get();
echo "   Object id query: " . $memberChannels4->count() . " channels\n";

echo "\n=== Verification Complete ===\n";
echo "If you see channels in the 'All Channels' section but they don't appear\n";
echo "in the user-specific queries, there might be a MongoDB query syntax issue.\n";