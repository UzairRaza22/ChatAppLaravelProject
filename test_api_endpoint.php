<?php

// Test the actual API endpoint for channel retrieval
// Run this with: php test_api_endpoint.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Http\Middleware\Channel\ChannelExistMiddleware;
use Illuminate\Http\Request;

echo "=== API Endpoint Test ===\n\n";

// Get a test user
$testUser = User::first();
if (!$testUser) {
    echo "No users found. Please create a user first.\n";
    exit(1);
}

echo "Testing with user: {$testUser->name} (ID: {$testUser->_id})\n\n";

// Simulate the middleware call
$request = new Request();
$request->merge(['user_id' => (string) $testUser->_id]);

$middleware = new ChannelExistMiddleware();

// Create a simple closure to capture the result
$next = function ($req) {
    return response()->json(['success' => true]);
};

echo "=== Simulating Middleware Call ===\n";
try {
    $response = $middleware->handle($request, $next);
    
    // Check if channels were set in the request
    $channels = $request->attributes->get('channels');
    
    if ($channels) {
        echo "Middleware found " . $channels->count() . " channels:\n";
        foreach ($channels as $channel) {
            echo "- {$channel->name} (ID: {$channel->_id})\n";
            echo "  Created by: {$channel->created_id}\n";
            echo "  Members: " . json_encode($channel->members) . "\n\n";
        }
    } else {
        echo "No channels found by middleware.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "=== Test Complete ===\n";