<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'uzairraza188@gmail.com';

echo "🔑 Getting current OTP for: $email\n";

$otpRecord = \App\Models\Otp::where('email', $email)
    ->where('type', 'registration')
    ->where('is_used', false)
    ->orderBy('created_at', 'desc')
    ->first();

if ($otpRecord) {
    echo "✅ Current OTP: " . $otpRecord->otp . "\n";
    echo "⏰ Expires at: " . $otpRecord->expires_at->format('Y-m-d H:i:s') . "\n";
    echo "📅 Created at: " . $otpRecord->created_at->format('Y-m-d H:i:s') . "\n";
    echo "⏳ Expired: " . ($otpRecord->isExpired() ? 'Yes' : 'No') . "\n";
    echo "🔄 Used: " . ($otpRecord->is_used ? 'Yes' : 'No') . "\n";
} else {
    echo "❌ No active OTP found\n";
}
