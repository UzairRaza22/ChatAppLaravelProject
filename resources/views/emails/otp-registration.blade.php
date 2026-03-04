<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - Whistle IT</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #7F9CF5 0%, #6B8DD6 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="10" cy="50" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="30" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        .logo {
            font-size: 36px;
            font-weight: bold;
            color: #ffffff;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .logo::before {
            content: '🎵';
            margin-right: 10px;
        }
        .header h2 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 300;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        .header p {
            color: rgba(255,255,255,0.9);
            font-size: 16px;
            position: relative;
            z-index: 1;
        }
        .content {
            padding: 40px 30px;
        }
        .welcome-message {
            text-align: center;
            margin-bottom: 30px;
        }
        .welcome-message h3 {
            color: #2c3e50;
            font-size: 20px;
            margin-bottom: 10px;
        }
        .welcome-message p {
            color: #7f8c8d;
            font-size: 16px;
        }
        .otp-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px dashed #7F9CF5;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
            position: relative;
            overflow: hidden;
        }
        .otp-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(127, 156, 245, 0.1), transparent);
            animation: shimmer 3s infinite;
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        .otp-label {
            font-size: 14px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }
        .otp-code {
            font-size: 24px;
            font-weight: bold;
            color: #7F9CF5;
            letter-spacing: 2px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            background: #ffffff;
            padding: 25px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(127, 156, 245, 0.2);
            position: relative;
            z-index: 1;
            word-break: break-all;
            line-height: 1.4;
            text-align: center;
        }
        .security-notice {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-left: 4px solid #f39c12;
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
        }
        .security-notice h4 {
            color: #856404;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .security-notice h4::before {
            content: '⚠️';
            margin-right: 10px;
            font-size: 20px;
        }
        .security-notice ul {
            color: #856404;
            margin-left: 20px;
        }
        .security-notice li {
            margin-bottom: 8px;
        }
        .steps {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin: 30px 0;
        }
        .steps h4 {
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .steps h4::before {
            content: '📋';
            margin-right: 10px;
        }
        .steps ol {
            color: #495057;
            margin-left: 20px;
        }
        .steps li {
            margin-bottom: 12px;
            line-height: 1.6;
        }
        .footer {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 30px;
            text-align: center;
        }
        .footer p {
            margin-bottom: 10px;
            font-size: 14px;
        }
        .footer .brand {
            font-weight: bold;
            color: #7F9CF5;
        }
        .footer .copyright {
            font-size: 12px;
            color: #95a5a6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Whistle IT</div>
            <h2>Email Verification Required</h2>
            <p>Complete your registration to start using our platform</p>
        </div>

        <div class="content">
            <div class="welcome-message">
                <h3>Hello {{ $name ?? 'There' }}! 👋</h3>
                <p>Thank you for choosing Whistle IT! We're excited to have you on board.</p>
            </div>

            <p>To activate your account and start collaborating, please verify your email address using the verification code below:</p>

            <div class="otp-container">
                <div class="otp-label">Your Verification Code</div>
                <div class="otp-code">{{ $otp }}</div>
            </div>

            <div class="security-notice">
                <h4>Important Security Notice</h4>
                <ul>
                    <li><strong>⏰ This OTP expires in 5 minutes</strong> - Act quickly!</li>
                    <li><strong>🔒 Never share this code</strong> with anyone</li>
                    <li><strong>📧 We'll never ask for your OTP</strong> via email or phone</li>
                    <li><strong>🛡️ This is a one-time use code</strong> - It becomes invalid after use</li>
                </ul>
            </div>

            <div class="steps">
                <h4>Next Steps</h4>
                <ol>
                    <li><strong>Copy</strong> the verification code above</li>
                    <li><strong>Go to</strong> the registration verification page</li>
                    <li><strong>Enter</strong> the verification code in the verification field</li>
                    <li><strong>Complete</strong> your registration with your name and password</li>
                    <li><strong>Start using</strong> Whistle IT immediately! 🚀</li>
                </ol>
            </div>

            <p style="text-align: center; color: #6c757d; font-style: italic;">
                If you didn't request this registration, you can safely ignore this email. 
                The OTP will automatically expire and no action is needed.
            </p>
        </div>

        <div class="footer">
            <p class="brand">🎵 Whistle IT</p>
            <p>Collaboration Platform for Modern Teams</p>
            <p class="copyright">© {{ date('Y') }} Whistle IT. All rights reserved.</p>
            <p style="font-size: 11px;">This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
