<?php

// Debug script to find why creator channels are not showing
// Run this with: php debug_creator_channels.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Channel;

echo "=== Debug Creator Channels Issue ===\n\n";

// Get a test user
$testUser = User::first();
if (!$testUser) {
    echo "❌ No users found. Please create a user first.\n";
    exit(1);
}

$userId = (string) $testUser->_id;
echo "🔍 Debugging for user: {$testUser->name}\n";
echo "📧 Email: " . ($testUser->email ?? 'N/A') . "\n";
echo "🆔 User ID: {$userId}\n";
echo "🔢 User ID Length: " . strlen($userId) . "\n\n";

// Step 1: Show ALL channels in database
echo "📋 ALL CHANNELS IN DATABASE:\n";
$allChannels = Channel::all();
echo "Total channels found: " . $allChannels->count() . "\n\n";

if ($allChannels->count() === 0) {
    echo "⚠️  No channels found. Creating test channels...\n";
    
    // Create a channel where user is creator
    $testChannel = Channel::create([
        'name' => 'Debug Test Channel',
        'workspace_id' => '507f1f77bcf86cd799439011',
        'team_id' => '507f1f77bcf86cd799439012',
        'type' => 'public',
        'created_id' => $userId,
        'members' => [$userId]
    ]);
    
    echo "✅ Created test channel: {$testChannel->name}\n";
    echo "   Created by: {$testChannel->created_id}\n";
    echo "   Members: " . json_encode($testChannel->members) . "\n\n";
    
    $allChannels = Channel::all();
}

foreach ($allChannels as $channel) {
    echo "📁 Channel: {$channel->name}\n";
    echo "   🆔 ID: {$channel->_id}\n";
    echo "   🏗️  created_id: '{$channel->created_id}' (length: " . strlen($channel->created_id) . ")\n";
    echo "   👥 members: " . json_encode($channel->members) . "\n";
    echo "   📊 Type: {$channel->type}\n";
    
    // Check if this user is creator
    $isCreator = (string) $channel->created_id === $userId;
    echo "   🎯 Is Creator: " . ($isCreator ? '✅ YES' : '❌ NO');
    if (!$isCreator) {
        echo " ('{$channel->created_id}' !== '{$userId}')";
    }
    echo "\n";
    
    // Check if this user is member
    $members = collect($channel->members ?? []);
    $isMember = $members->contains(function ($member) use ($userId) {
        if (is_string($member)) {
            return (string) $member === $userId;
        }
        if (is_array($member)) {
            return (string) ($member['user_id'] ?? $member['_id'] ?? $member['id'] ?? '') === $userId;
        }
        return false;
    });
    echo "   👥 Is Member: " . ($isMember ? '✅ YES' : '❌ NO') . "\n";
    echo "   📈 Should Show: " . ($isCreator || $isMember ? '✅ YES' : '❌ NO') . "\n\n";
}

// Step 2: Test individual queries
echo "🔍 TESTING INDIVIDUAL QUERIES:\n\n";

echo "1️⃣ Testing creator query:\n";
$creatorChannels = Channel::where('created_id', $userId)->get();
echo "   Query: Channel::where('created_id', '{$userId}')\n";
echo "   Found: " . $creatorChannels->count() . " channels\n";
foreach ($creatorChannels as $channel) {
    echo "   - {$channel->name} (created_id: '{$channel->created_id}')\n";
}
echo "\n";

echo "2️⃣ Testing member queries:\n";
$memberChannels1 = Channel::where('members', $userId)->get();
echo "   Query: Channel::where('members', '{$userId}')\n";
echo "   Found: " . $memberChannels1->count() . " channels\n";

$memberChannels2 = Channel::where('members.user_id', $userId)->get();
echo "   Query: Channel::where('members.user_id', '{$userId}')\n";
echo "   Found: " . $memberChannels2->count() . " channels\n";

$memberChannels3 = Channel::where('members._id', $userId)->get();
echo "   Query: Channel::where('members._id', '{$userId}')\n";
echo "   Found: " . $memberChannels3->count() . " channels\n";

$memberChannels4 = Channel::where('members.id', $userId)->get();
echo "   Query: Channel::where('members.id', '{$userId}')\n";
echo "   Found: " . $memberChannels4->count() . " channels\n\n";

// Step 3: Test the exact middleware query
echo "3️⃣ Testing exact middleware query:\n";
$middlewareChannels = Channel::where(function ($query) use ($userId) {
    $query->where('created_id', $userId)  // User is creator
          ->orWhere('members', $userId)  // User is in members array (simple string)
          ->orWhere('members.user_id', $userId)  // User is in members array (object with user_id)
          ->orWhere('members._id', $userId)  // User is in members array (object with _id)
          ->orWhere('members.id', $userId);  // User is in members array (object with id)
})->get();

echo "   Found: " . $middlewareChannels->count() . " channels\n";
foreach ($middlewareChannels as $channel) {
    echo "   - {$channel->name}\n";
}
echo "\n";

// Step 4: Test with raw MongoDB query
echo "4️⃣ Testing raw MongoDB queries:\n";
try {
    $rawCreatorChannels = Channel::raw(function($collection) use ($userId) {
        return $collection->find(['created_id' => $userId]);
    });
    echo "   Raw creator query found: " . count($rawCreatorChannels) . " channels\n";
    
    $rawMemberChannels = Channel::raw(function($collection) use ($userId) {
        return $collection->find(['members' => $userId]);
    });
    echo "   Raw member query found: " . count($rawMemberChannels) . " channels\n";
    
} catch (Exception $e) {
    echo "   Raw query error: " . $e->getMessage() . "\n";
}

// Step 5: Check data types
echo "\n5️⃣ DATA TYPE ANALYSIS:\n";
$sampleChannel = $allChannels->first();
if ($sampleChannel) {
    echo "   Sample channel created_id type: " . gettype($sampleChannel->created_id) . "\n";
    echo "   Sample channel created_id value: '{$sampleChannel->created_id}'\n";
    echo "   User ID type: " . gettype($userId) . "\n";
    echo "   User ID value: '{$userId}'\n";
    echo "   Types match: " . (gettype($sampleChannel->created_id) === gettype($userId) ? '✅ YES' : '❌ NO') . "\n";
    
    if (isset($sampleChannel->members) && is_array($sampleChannel->members) && count($sampleChannel->members) > 0) {
        $firstMember = $sampleChannel->members[0];
        echo "   Sample member type: " . gettype($firstMember) . "\n";
        echo "   Sample member value: " . (is_string($firstMember) ? "'{$firstMember}'" : json_encode($firstMember)) . "\n";
    }
}

echo "\n=== Debug Complete ===\n";

if ($middlewareChannels->count() === 0) {
    echo "🚨 ISSUE IDENTIFIED:\n";
    echo "The middleware query is not finding any channels.\n";
    echo "This could be due to:\n";
    echo "1. Data type mismatch (string vs ObjectId)\n";
    echo "2. MongoDB query syntax issues\n";
    echo "3. Incorrect field names\n";
    echo "4. Database connection issues\n\n";
    
    echo "💡 RECOMMENDATIONS:\n";
    echo "1. Check if created_id and members are stored as strings or ObjectIds\n";
    echo "2. Verify MongoDB Laravel package version and syntax\n";
    echo "3. Test with direct MongoDB queries\n";
} else {
    echo "✅ SUCCESS: Middleware query is working!\n";
    echo "Found " . $middlewareChannels->count() . " channels for the user.\n";
}