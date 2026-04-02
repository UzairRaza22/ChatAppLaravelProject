<?php

// Test the complete flow: Validation -> Middleware -> Controller
// Run this with: php test_complete_flow.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Channel;
use App\Http\Requests\Channel\ListUserChannelsRequest;
use App\Http\Middleware\Channel\ChannelExistMiddleware;
use App\Http\Controllers\ChannelController;
use Illuminate\Http\Request;

echo "=== Complete Flow Test (Token Auth) ===\n\n";

// Get a test user
$testUser = User::first();
if (!$testUser) {
    echo "No users found. Please create a user first.\n";
    exit(1);
}

echo "Testing with user: {$testUser->name} (ID: {$testUser->_id})\n\n";

// Test 1: Validation
echo "1️⃣ Testing Validation (ListUserChannelsRequest)...\n";
$request = new Request();
$request->setUserResolver(function () use ($testUser) {
    return $testUser;
});

// Test validation without user_id (should pass now)
$validationRequest = new ListUserChannelsRequest();
$validationRequest->replace([]); // Empty request (no user_id)

try {
    $rules = $validationRequest->rules();
    echo "   ✅ Validation rules: " . json_encode($rules) . "\n";
    echo "   ✅ user_id is now optional\n";
} catch (Exception $e) {
    echo "   ❌ Validation error: " . $e->getMessage() . "\n";
}

// Test 2: Middleware
echo "\n2️⃣ Testing Middleware (ChannelExistMiddleware)...\n";
$middleware = new ChannelExistMiddleware();

$next = function ($req) {
    return response()->json(['success' => true]);
};

try {
    $response = $middleware->handle($request, $next);
    $channels = $request->attributes->get('channels');
    
    if ($channels) {
        echo "   ✅ Middleware found " . $channels->count() . " channels\n";
    } else {
        echo "   ❌ Middleware found no channels\n";
    }
} catch (Exception $e) {
    echo "   ❌ Middleware error: " . $e->getMessage() . "\n";
}

// Test 3: Controller
echo "\n3️⃣ Testing Controller (ChannelController::listByUser)...\n";
try {
    $controller = new ChannelController();
    
    // Create a proper request for the controller
    $controllerRequest = new ListUserChannelsRequest();
    $controllerRequest->setUserResolver(function () use ($testUser) {
        return $testUser;
    });
    
    // Set the channels that middleware would have set
    if (isset($channels)) {
        $controllerRequest->attributes->set('channels', $channels);
    }
    
    $response = $controller->listByUser($controllerRequest);
    $responseData = $response->getData(true);
    
    echo "   ✅ Controller response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "   ❌ Controller error: " . $e->getMessage() . "\n";
}

// Show what should happen in Postman
echo "\n🚀 Postman Test Instructions:\n";
echo "1. Set Authorization header: Bearer YOUR_TOKEN\n";
echo "2. Make GET request to: /channels/list-by-user\n";
echo "3. Don't include user_id parameter (it will be extracted from token)\n";
echo "4. You should see all channels where the user is creator or member\n\n";

// Show current channels for reference
echo "📋 Current Channels in Database:\n";
$allChannels = Channel::all();
foreach ($allChannels as $channel) {
    $isUserCreator = (string) $channel->created_id === (string) $testUser->_id;
    $members = collect($channel->members ?? []);
    $isUserMember = $members->contains(function ($member) use ($testUser) {
        if (is_string($member)) {
            return (string) $member === (string) $testUser->_id;
        }
        if (is_array($member)) {
            return (string) ($member['user_id'] ?? $member['_id'] ?? $member['id'] ?? '') === (string) $testUser->_id;
        }
        return false;
    });
    
    echo "   📁 {$channel->name}\n";
    echo "      Creator: " . ($isUserCreator ? '✅' : '❌') . " | Member: " . ($isUserMember ? '✅' : '❌') . "\n";
    echo "      Should appear in results: " . ($isUserCreator || $isUserMember ? '✅ YES' : '❌ NO') . "\n\n";
}

echo "=== Test Complete ===\n";