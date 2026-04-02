<?php

// Test MongoDB query syntax
// Run this with: php test_mongodb_syntax.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Channel;

echo "=== MongoDB Query Syntax Test ===\n\n";

// Get a test user
$testUser = User::first();
if (!$testUser) {
    echo "❌ No users found.\n";
    exit(1);
}

$userId = (string) $testUser->_id;
echo "👤 User ID: {$userId}\n\n";

// Test 1: Basic where query
echo "1️⃣ Testing basic where query:\n";
try {
    $channels1 = Channel::where('created_id', $userId)->get();
    echo "   Channel::where('created_id', '{$userId}') found: " . $channels1->count() . "\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 2: OrWhere query
echo "\n2️⃣ Testing orWhere query:\n";
try {
    $channels2 = Channel::where('created_id', $userId)
        ->orWhere('members', $userId)
        ->get();
    echo "   Combined query found: " . $channels2->count() . "\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 3: WhereIn query for members
echo "\n3️⃣ Testing whereIn query for members:\n";
try {
    $channels3 = Channel::whereIn('members', [$userId])->get();
    echo "   Channel::whereIn('members', ['{$userId}']) found: " . $channels3->count() . "\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 4: Raw MongoDB query
echo "\n4️⃣ Testing raw MongoDB query:\n";
try {
    $channels4 = Channel::raw(function($collection) use ($userId) {
        return $collection->find([
            '$or' => [
                ['created_id' => $userId],
                ['members' => $userId]
            ]
        ]);
    });
    echo "   Raw MongoDB query found: " . count($channels4) . "\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 5: Create a test channel if none exist
$allChannels = Channel::all();
if ($allChannels->count() === 0) {
    echo "\n5️⃣ No channels exist. Creating test channel:\n";
    try {
        $testChannel = Channel::create([
            'name' => 'MongoDB Test Channel',
            'workspace_id' => '507f1f77bcf86cd799439011',
            'team_id' => '507f1f77bcf86cd799439012',
            'type' => 'public',
            'created_id' => $userId,
            'members' => [$userId]
        ]);
        
        echo "   ✅ Created channel: {$testChannel->name}\n";
        echo "   ID: {$testChannel->_id}\n";
        echo "   Created by: {$testChannel->created_id}\n";
        echo "   Members: " . json_encode($testChannel->members) . "\n";
        
        // Test queries again
        echo "\n   Re-testing queries after creation:\n";
        $retestChannels = Channel::where('created_id', $userId)->get();
        echo "   Creator query now finds: " . $retestChannels->count() . "\n";
        
    } catch (Exception $e) {
        echo "   ❌ Error creating channel: " . $e->getMessage() . "\n";
    }
}

// Test 6: Show all channels with details
echo "\n6️⃣ All channels in database:\n";
$allChannels = Channel::all();
foreach ($allChannels as $channel) {
    echo "   📁 {$channel->name}\n";
    echo "      ID: {$channel->_id}\n";
    echo "      Created by: '{$channel->created_id}' (matches user: " . ((string)$channel->created_id === $userId ? '✅' : '❌') . ")\n";
    echo "      Members: " . json_encode($channel->members) . "\n";
    echo "      User in members: " . (in_array($userId, $channel->members ?? []) ? '✅' : '❌') . "\n\n";
}

echo "=== Test Complete ===\n";

// Final recommendation
if ($allChannels->count() > 0) {
    $userChannels = $allChannels->filter(function($channel) use ($userId) {
        return (string)$channel->created_id === $userId || in_array($userId, $channel->members ?? []);
    });
    
    if ($userChannels->count() > 0) {
        echo "✅ User should see " . $userChannels->count() . " channels\n";
        echo "If Postman is not showing these, the issue is in the request flow.\n";
    } else {
        echo "⚠️  User has no channels as creator or member.\n";
        echo "Create some channels first, then test.\n";
    }
} else {
    echo "⚠️  No channels in database. Create some channels first.\n";
}