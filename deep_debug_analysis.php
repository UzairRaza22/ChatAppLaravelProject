<?php

// Deep debugging analysis to identify the exact issue
// Run this with: php deep_debug_analysis.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Channel;

echo "=== DEEP DEBUG ANALYSIS ===\n\n";

// Get a test user
$testUser = User::first();
if (!$testUser) {
    echo "❌ No users found.\n";
    exit(1);
}

$userId = (string) $testUser->_id;
echo "🔍 Analyzing for user: {$testUser->name}\n";
echo "📧 Email: " . ($testUser->email ?? 'N/A') . "\n";
echo "🆔 User ID: '{$userId}'\n";
echo "🔢 User ID type: " . gettype($userId) . "\n";
echo "📏 User ID length: " . strlen($userId) . "\n\n";

// Step 1: Show ALL channels with complete details
echo "📋 ALL CHANNELS IN DATABASE:\n";
$allChannels = Channel::all();
echo "Total channels: " . $allChannels->count() . "\n\n";

if ($allChannels->count() === 0) {
    echo "⚠️  No channels found. Let me create test data...\n";
    
    // Create a channel where user is creator
    $creatorChannel = Channel::create([
        'name' => 'Debug Creator Channel',
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
    ]);
    
    // Create a channel where user is member (created by someone else)
    $memberChannel = Channel::create([
        'name' => 'Debug Member Channel',
        'workspace_id' => '507f1f77bcf86cd799439011',
        'team_id' => '507f1f77bcf86cd799439014',
        'type' => 'private',
        'created_id' => '507f1f77bcf86cd799439999', // Different creator
        'members' => [
            [
                'user_id' => '507f1f77bcf86cd799439999',
                'role' => 'admin'
            ],
            [
                'user_id' => $userId,
                'role' => 'member'
            ]
        ]
    ]);
    
    echo "✅ Created test channels\n\n";
    $allChannels = Channel::all();
}

foreach ($allChannels as $index => $channel) {
    echo "📁 Channel #" . ($index + 1) . ": {$channel->name}\n";
    echo "   🆔 ID: {$channel->_id}\n";
    echo "   🏗️  created_id: '{$channel->created_id}' (type: " . gettype($channel->created_id) . ", length: " . strlen($channel->created_id) . ")\n";
    echo "   👥 members (raw): " . json_encode($channel->members, JSON_PRETTY_PRINT) . "\n";
    echo "   📊 members type: " . gettype($channel->members) . "\n";
    echo "   📈 members count: " . (is_array($channel->members) ? count($channel->members) : 'N/A') . "\n";
    
    // Detailed member analysis
    if (is_array($channel->members)) {
        foreach ($channel->members as $memberIndex => $member) {
            echo "      Member #{$memberIndex}: " . json_encode($member) . " (type: " . gettype($member) . ")\n";
            
            if (is_array($member)) {
                echo "         Keys: " . implode(', ', array_keys($member)) . "\n";
                if (isset($member['user_id'])) {
                    echo "         user_id: '{$member['user_id']}' (type: " . gettype($member['user_id']) . ")\n";
                    echo "         Matches our user: " . ((string) $member['user_id'] === $userId ? '✅ YES' : '❌ NO') . "\n";
                }
            }
        }
    }
    
    // Check if user is creator
    $isCreator = (string) $channel->created_id === $userId;
    echo "   🎯 User is creator: " . ($isCreator ? '✅ YES' : '❌ NO') . "\n";
    
    // Check if user is member using different approaches
    $isMemberApproach1 = false;
    $isMemberApproach2 = false;
    $isMemberApproach3 = false;
    
    if (is_array($channel->members)) {
        // Approach 1: Direct array check
        foreach ($channel->members as $member) {
            if (is_array($member) && isset($member['user_id']) && (string) $member['user_id'] === $userId) {
                $isMemberApproach1 = true;
                break;
            }
        }
        
        // Approach 2: Using collect
        $isMemberApproach2 = collect($channel->members)->contains(function ($member) use ($userId) {
            return is_array($member) && isset($member['user_id']) && (string) $member['user_id'] === $userId;
        });
        
        // Approach 3: Using array_filter
        $memberMatches = array_filter($channel->members, function ($member) use ($userId) {
            return is_array($member) && isset($member['user_id']) && (string) $member['user_id'] === $userId;
        });
        $isMemberApproach3 = count($memberMatches) > 0;
    }
    
    echo "   👥 User is member (approach 1): " . ($isMemberApproach1 ? '✅ YES' : '❌ NO') . "\n";
    echo "   👥 User is member (approach 2): " . ($isMemberApproach2 ? '✅ YES' : '❌ NO') . "\n";
    echo "   👥 User is member (approach 3): " . ($isMemberApproach3 ? '✅ YES' : '❌ NO') . "\n";
    
    $shouldShow = $isCreator || $isMemberApproach1;
    echo "   📈 Should show to user: " . ($shouldShow ? '✅ YES' : '❌ NO') . "\n\n";
}

// Step 2: Test the exact middleware filtering logic
echo "🧪 TESTING MIDDLEWARE FILTERING LOGIC:\n";

$userChannels = $allChannels->filter(function ($channel) use ($userId) {
    echo "   Testing channel: {$channel->name}\n";
    
    // Check if user is creator
    if ((string) $channel->created_id === $userId) {
        echo "      ✅ User is creator - INCLUDE\n";
        return true;
    }
    
    // Check if user is member
    $members = $channel->members ?? [];
    echo "      Members to check: " . json_encode($members) . "\n";
    
    foreach ($members as $member) {
        echo "         Checking member: " . json_encode($member) . "\n";
        
        if (is_array($member) && isset($member['user_id'])) {
            echo "            Member user_id: '{$member['user_id']}'\n";
            echo "            Our user_id: '{$userId}'\n";
            echo "            Match: " . ((string) $member['user_id'] === $userId ? 'YES' : 'NO') . "\n";
            
            if ((string) $member['user_id'] === $userId) {
                echo "      ✅ User is member - INCLUDE\n";
                return true;
            }
        } elseif (is_object($member) && isset($member->user_id)) {
            if ((string) $member->user_id === $userId) {
                echo "      ✅ User is member (object) - INCLUDE\n";
                return true;
            }
        } elseif (is_string($member)) {
            if ((string) $member === $userId) {
                echo "      ✅ User is member (string) - INCLUDE\n";
                return true;
            }
        }
    }
    
    echo "      ❌ User not found - EXCLUDE\n";
    return false;
});

echo "\n📊 FILTERING RESULTS:\n";
echo "   Total channels: " . $allChannels->count() . "\n";
echo "   Channels for user: " . $userChannels->count() . "\n";

if ($userChannels->count() > 0) {
    echo "   ✅ Channels found:\n";
    foreach ($userChannels as $channel) {
        echo "      - {$channel->name}\n";
    }
} else {
    echo "   ❌ NO CHANNELS FOUND FOR USER\n";
    echo "   This indicates the filtering logic is not working correctly.\n";
}

// Step 3: Test with a simple manual check
echo "\n🔍 MANUAL VERIFICATION:\n";
foreach ($allChannels as $channel) {
    $manualCheck = false;
    
    // Check creator
    if ((string) $channel->created_id === $userId) {
        $manualCheck = true;
        echo "   ✅ {$channel->name} - User is CREATOR\n";
        continue;
    }
    
    // Check members manually
    if (is_array($channel->members)) {
        foreach ($channel->members as $member) {
            if (is_array($member) && 
                array_key_exists('user_id', $member) && 
                (string) $member['user_id'] === $userId) {
                $manualCheck = true;
                echo "   ✅ {$channel->name} - User is MEMBER (role: " . ($member['role'] ?? 'unknown') . ")\n";
                break;
            }
        }
    }
    
    if (!$manualCheck) {
        echo "   ❌ {$channel->name} - User has no access\n";
    }
}

echo "\n=== DIAGNOSIS ===\n";

if ($userChannels->count() === 0) {
    echo "🚨 ISSUE IDENTIFIED: The filtering logic is not working\n";
    echo "Possible causes:\n";
    echo "1. Data type mismatch in user ID comparison\n";
    echo "2. Member structure is different than expected\n";
    echo "3. Logic error in the filtering function\n";
    echo "4. Database data is not in the expected format\n\n";
    
    echo "💡 RECOMMENDATIONS:\n";
    echo "1. Check the exact member structure in your database\n";
    echo "2. Verify user ID types and formats\n";
    echo "3. Test with var_dump for detailed type information\n";
    echo "4. Check if members array is properly cast\n";
} else {
    echo "✅ Filtering logic works - the issue might be elsewhere\n";
    echo "Check the middleware implementation or request flow\n";
}

echo "\n=== DEBUG COMPLETE ===\n";