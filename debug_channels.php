<?php

// Debug script to test channel functionality
// Run this with: php debug_channels.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Channel;
use App\Models\User;

echo "=== Channel Debug Script ===\n\n";

// Get a test user
$testUser = User::first();
if (!$testUser) {
    echo "No users found. Please create a user first.\n";
    exit(1);
}

$userId = (string) $testUser->_id;
echo "Testing with user: {$testUser->name} (ID: {$userId})\n\n";

// Test 1: Show all channels and their member structures
echo "=== All Channels ===\n";
$allChannels = Channel::all();
foreach ($allChannels as $channel) {
    echo "Channel: {$channel->name} (ID: {$channel->_id})\n";
    echo "  Created by: {$channel->created_id}\n";
    echo "  Members: " . json_encode($channel->members) . "\n";
    echo "  Type: {$channel->type}\n\n";
}

// Test 2: Test the updated query
echo "=== Testing Updated Query ===\n";
$channels = Channel::where(function ($query) use ($userId) {
    $query->where('members', 'all', [$userId])  // MongoDB $in operator for simple strings
          ->orWhere('members.user_id', $userId)  // Object with user_id field
          ->orWhere('members._id', $userId)  // Object with _id field
          ->orWhere('members.id', $userId);  // Object with id field
})->orWhere('created_id', $userId)->get();

echo "Found " . $channels->count() . " channels for user {$userId}:\n";
foreach ($channels as $channel) {
    echo "- {$channel->name} (Created by: {$channel->created_id})\n";
}

// Test 3: Test membership checking logic
echo "\n=== Testing Membership Logic ===\n";
foreach ($allChannels as $channel) {
    $members = collect($channel->members ?? []);
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
    
    $isCreator = (string) $channel->created_id === $userId;
    
    echo "Channel {$channel->name}: Member=" . ($isMember ? 'YES' : 'NO') . ", Creator=" . ($isCreator ? 'YES' : 'NO') . "\n";
}

echo "\n=== Debug Complete ===\n";