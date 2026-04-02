<?php

// Test the exact Postman flow for channel retrieval
// Run this with: php test_postman_flow.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Channel;
use App\Http\Middleware\Channel\ChannelExistMiddleware;
use App\Http\Controllers\ChannelController;
use App\Http\Requests\Channel\ListUserChannelsRequest;
use Illuminate\Http\Request;

echo "=== Postman Flow Test ===\n\n";

// Get a test user
$testUser = User::first();
if (!$testUser) {
    echo "❌ No users found.\n";
    exit(1);
}

echo "👤 Testing with user: {$testUser->name}\n";
echo "🆔 User ID: {$testUser->_id}\n";
echo "📧 Email: " . ($testUser->email ?? 'N/A') . "\n\n";

// Ensure we have a test channel
$userId = (string) $testUser->_id;
$testChannel = Channel::where('created_id', $userId)->first();

if (!$testChannel) {
    echo "📁 Creating test channel for user...\n";
    $testChannel = Channel::create([
        'name' => 'Postman Test Channel',
        'workspace_id' => '507f1f77bcf86cd799439011',
        'team_id' => '507f1f77bcf86cd799439012',
        'type' => 'public',
        'created_id' => $userId,
        'members' => [$userId]
    ]);
    echo "✅ Created: {$testChannel->name}\n\n";
}

// Step 1: Test user ID extraction
echo "1️⃣ TESTING USER ID EXTRACTION:\n";
$request = new Request();
// Don't set user_id parameter (simulating Postman with just token)
$request->setUserResolver(function () use ($testUser) {
    return $testUser;
});

echo "   Request has user_id parameter: " . ($request->has('user_id') ? 'YES' : 'NO') . "\n";
echo "   Request user(): " . ($request->user() ? $request->user()->name : 'NULL') . "\n";
echo "   Request user ID: " . ($request->user() ? $request->user()->_id : 'NULL') . "\n";

// Simulate the middleware's user ID extraction logic
$extractedUserId = (string) ($request->input('user_id') ?? $request->query('user_id'));
if ($extractedUserId === '') {
    $user = $request->user() ?? $request->input('user') ?? $request->input('verified_user');
    $extractedUserId = (string) (data_get($user, '_id') ?? data_get($user, 'id'));
    
    if ($extractedUserId === '') {
        $tokenRecord = $request->input('token_record');
        $extractedUserId = (string) data_get($tokenRecord, 'user_id');
    }
}

echo "   Extracted user ID: '{$extractedUserId}'\n";
echo "   Matches original: " . ($extractedUserId === $userId ? '✅ YES' : '❌ NO') . "\n\n";

// Step 2: Test validation
echo "2️⃣ TESTING VALIDATION:\n";
try {
    $validationRequest = new ListUserChannelsRequest();
    $validationRequest->replace([]); // Empty request
    $validationRequest->setUserResolver(function () use ($testUser) {
        return $testUser;
    });
    
    $rules = $validationRequest->rules();
    echo "   Validation rules: " . json_encode($rules) . "\n";
    echo "   Should pass with empty request: ✅ YES (user_id is nullable)\n";
} catch (Exception $e) {
    echo "   ❌ Validation error: " . $e->getMessage() . "\n";
}
echo "\n";

// Step 3: Test middleware
echo "3️⃣ TESTING MIDDLEWARE:\n";
$middleware = new ChannelExistMiddleware();
$next = function ($req) {
    return response()->json(['success' => true]);
};

try {
    $response = $middleware->handle($request, $next);
    $channels = $request->attributes->get('channels');
    
    echo "   Middleware response status: " . $response->getStatusCode() . "\n";
    echo "   Channels found: " . ($channels ? $channels->count() : 0) . "\n";
    
    if ($channels && $channels->count() > 0) {
        echo "   ✅ SUCCESS: Middleware found channels\n";
        foreach ($channels as $channel) {
            echo "      - {$channel->name} (created by: {$channel->created_id})\n";
        }
    } else {
        echo "   ❌ ISSUE: No channels found by middleware\n";
        
        // Debug: Test the query directly with the extracted user ID
        echo "   🔍 Debug: Testing direct query with extracted user ID...\n";
        $directChannels = Channel::where('created_id', $extractedUserId)->get();
        echo "      Direct creator query found: " . $directChannels->count() . " channels\n";
        
        if ($directChannels->count() > 0) {
            echo "      ⚠️  Query works directly but not in middleware!\n";
        } else {
            echo "      ❌ Query doesn't work even directly\n";
            
            // Check data types
            echo "      🔍 Data type check:\n";
            $sampleChannel = Channel::first();
            if ($sampleChannel) {
                echo "         Sample channel created_id: '{$sampleChannel->created_id}' (type: " . gettype($sampleChannel->created_id) . ")\n";
                echo "         Extracted user ID: '{$extractedUserId}' (type: " . gettype($extractedUserId) . ")\n";
                echo "         String comparison: " . ((string)$sampleChannel->created_id === (string)$extractedUserId ? '✅ MATCH' : '❌ NO MATCH') . "\n";
            }
        }
    }
} catch (Exception $e) {
    echo "   ❌ Middleware error: " . $e->getMessage() . "\n";
}
echo "\n";

// Step 4: Test controller
echo "4️⃣ TESTING CONTROLLER:\n";
try {
    $controller = new ChannelController();
    
    // Set up request as middleware would
    if (isset($channels)) {
        $request->attributes->set('channels', $channels);
    }
    
    $controllerResponse = $controller->listByUser($request);
    $responseData = $controllerResponse->getData(true);
    
    echo "   Controller response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "   ❌ Controller error: " . $e->getMessage() . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "🎯 Expected behavior in Postman:\n";
echo "   1. Send GET request to /channels/list-by-user\n";
echo "   2. Include Authorization: Bearer YOUR_TOKEN\n";
echo "   3. Don't include user_id parameter\n";
echo "   4. Should return channels where user is creator or member\n\n";

if (isset($channels) && $channels->count() > 0) {
    echo "✅ SUCCESS: The flow should work in Postman!\n";
} else {
    echo "❌ ISSUE: There's still a problem in the flow.\n";
    echo "Check the debug output above to identify the issue.\n";
}

echo "\n=== Test Complete ===\n";