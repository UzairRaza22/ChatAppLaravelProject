<?php

// Simple test script to verify channel retrieval functionality
// Run this with: php test_channel_retrieval.php

require_once 'vendor/autoload.php';

// You'll need to set up your Laravel app context
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Channel;
use App\Models\User;

echo "=== Channel Retrieval Test ===\n\n";

// Test function to simulate the middleware logic
function testChannelRetrieval($userId) {
    echo "Testing channel retrieval for user ID: $userId\n";
    
    // This is the same query from the updated middleware
    $channels = Channel::where(function ($query) use ($userId) {
        $query->where('members', $userId)  // Simple string in array
              ->orWhere('members.user_id', $userId)  // Object with user_id field
              ->orWhere('members._id', $userId)  // Object with _id field
              ->orWhere('members.id', $userId);  // Object with id field
    })->orWhere('created_id', $userId)->get();
    
    echo "Found " . $channels->count() . " channels:\n";
    
    foreach ($channels as $channel) {
        echo "- Channel: {$channel->name} (ID: {$channel->_id})\n";
        echo "  Created by: {$channel->created_id}\n";
        echo "  Members: " . json_encode($channel->members) . "\n";
        echo "  Type: {$channel->type}\n\n";
    }
    
    return $channels;
}

// Get a test user (you might need to adjust this based on your data)
$testUser = User::first();
if (!$testUser) {
    echo "No users found in database. Please create a user first.\n";
    exit(1);
}

echo "Using test user: {$testUser->name} (ID: {$testUser->_id})\n\n";

// Test the channel retrieval
$channels = testChannelRetrieval((string) $testUser->_id);

// Additional verification
echo "=== Verification ===\n";
echo "Channels where user is creator: " . $channels->where('created_id', (string) $testUser->_id)->count() . "\n";

$memberChannels = $channels->filter(function ($channel) use ($testUser) {
    $members = collect($channel->members ?? []);
    return $members->contains(function ($member) use ($testUser) {
        if (is_string($member)) {
            return $member === (string) $testUser->_id;
        }
        if (is_array($member)) {
            return ($member['user_id'] ?? $member['_id'] ?? $member['id'] ?? '') === (string) $testUser->_id;
        }
        return false;
    });
});

echo "Channels where user is member: " . $memberChannels->count() . "\n";
echo "Total unique channels: " . $channels->count() . "\n";

echo "\n=== Test Complete ===\n";