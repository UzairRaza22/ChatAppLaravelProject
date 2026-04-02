<?php

// Debug member query specifically
// Run this with: php debug_member_query.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Channel;

echo "=== Debug Member Query Issue ===\n\n";

// Get test users
$users = User::take(2)->get();
if ($users->count() < 2) {
    echo "❌ Need at least 2 users for testing. Found: " . $users->count() . "\n";
    exit(1);
}

$user1 = $users->first();
$user2 = $users->get(1);

echo "👤 User 1: {$user1->name} (ID: {$user1->_id})\n";
echo "👤 User 2: {$user2->name} (ID: {$user2->_id})\n\n";

// Create test scenario: User 1 creates channel, User 2 is added as member
echo "📁 Setting up test scenario...\n";

// Find or create a channel where User 1 is creator and User 2 is member
$testChannel = Channel::where('created_id', (string) $user1->_id)->first();

if (!$testChannel) {
    echo "   Creating channel by User 1...\n";
    $testChannel = Channel::create([
        'name' => 'Member Test Channel',
        'workspace_id' => '507f1f77bcf86cd799439011',
        'team_id' => '507f1f77bcf86cd799439012',
        'type' => 'public',
        'created_id' => (string) $user1->_id,
        'members' => [(string) $user1->_id] // Initially only creator
    ]);
}

// Add User 2 as member if not already
$members = $testChannel->members ?? [];
if (!in_array((string) $user2->_id, $members)) {
    echo "   Adding User 2 as member...\n";
    $members[] = (string) $user2->_id;
    $testChannel->update(['members' => $members]);
    $testChannel = $testChannel->fresh(); // Reload from database
}

echo "✅ Test channel setup complete:\n";
echo "   📁 Name: {$testChannel->name}\n";
echo "   🆔 ID: {$testChannel->_id}\n";
echo "   🏗️  Created by: {$testChannel->created_id} (User 1)\n";
echo "   👥 Members: " . json_encode($testChannel->members) . "\n\n";

// Test queries for User 2 (should find channel as member)
$user2Id = (string) $user2->_id;
echo "🔍 Testing member queries for User 2 (ID: {$user2Id}):\n\n";

// Test 1: Basic member query
echo "1️⃣ Basic member query:\n";
try {
    $memberChannels1 = Channel::where('members', $user2Id)->get();
    echo "   Channel::where('members', '{$user2Id}') found: " . $memberChannels1->count() . "\n";
    foreach ($memberChannels1 as $channel) {
        echo "      - {$channel->name}\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 2: WhereIn query
echo "\n2️⃣ WhereIn query:\n";
try {
    $memberChannels2 = Channel::whereIn('members', [$user2Id])->get();
    echo "   Channel::whereIn('members', ['{$user2Id}']) found: " . $memberChannels2->count() . "\n";
    foreach ($memberChannels2 as $channel) {
        echo "      - {$channel->name}\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 3: Raw MongoDB query with $in
echo "\n3️⃣ Raw MongoDB \$in query:\n";
try {
    $memberChannels3 = Channel::whereRaw(['members' => ['$in' => [$user2Id]]])->get();
    echo "   Raw \$in query found: " . $memberChannels3->count() . "\n";
    foreach ($memberChannels3 as $channel) {
        echo "      - {$channel->name}\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 4: Raw MongoDB query with direct match
echo "\n4️⃣ Raw MongoDB direct match:\n";
try {
    $memberChannels4 = Channel::whereRaw(['members' => $user2Id])->get();
    echo "   Raw direct match found: " . $memberChannels4->count() . "\n";
    foreach ($memberChannels4 as $channel) {
        echo "      - {$channel->name}\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 5: Using raw MongoDB collection
echo "\n5️⃣ Raw MongoDB collection query:\n";
try {
    $memberChannels5 = Channel::raw(function($collection) use ($user2Id) {
        return $collection->find(['members' => $user2Id]);
    });
    echo "   Raw collection query found: " . count($memberChannels5) . "\n";
    foreach ($memberChannels5 as $channel) {
        echo "      - " . $channel['name'] . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 6: Manual check
echo "\n6️⃣ Manual verification:\n";
$allChannels = Channel::all();
$manualMemberChannels = $allChannels->filter(function($channel) use ($user2Id) {
    $members = $channel->members ?? [];
    return in_array($user2Id, $members);
});

echo "   Manual filter found: " . $manualMemberChannels->count() . " channels\n";
foreach ($manualMemberChannels as $channel) {
    echo "      - {$channel->name} (members: " . json_encode($channel->members) . ")\n";
}

// Test 7: Combined creator + member query for User 2
echo "\n7️⃣ Combined query (creator OR member) for User 2:\n";
try {
    $combinedChannels = Channel::where('created_id', $user2Id)
        ->orWhere('members', $user2Id)
        ->get();
    echo "   Combined query found: " . $combinedChannels->count() . "\n";
    foreach ($combinedChannels as $channel) {
        $isCreator = (string) $channel->created_id === $user2Id;
        $isMember = in_array($user2Id, $channel->members ?? []);
        echo "      - {$channel->name} (Creator: " . ($isCreator ? '✅' : '❌') . ", Member: " . ($isMember ? '✅' : '❌') . ")\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Analysis ===\n";
echo "Expected: User 2 should see the test channel as a member\n";
echo "Channel members array contains User 2 ID: " . (in_array($user2Id, $testChannel->members ?? []) ? '✅ YES' : '❌ NO') . "\n";

if ($manualMemberChannels->count() > 0) {
    echo "✅ Manual filter works - the data is correct\n";
    echo "❌ MongoDB queries are not working - this is a query syntax issue\n";
} else {
    echo "❌ Manual filter doesn't work - this is a data issue\n";
    echo "Check if the member was actually added to the database\n";
}

echo "\n=== Test Complete ===\n";