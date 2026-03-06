<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Whistle-IT - Laravel Application</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .status {
            background: #e8f5e8;
            border: 1px solid #4caf50;
            color: #2e7d32;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .api-info {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            color: #1565c0;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Whistle-IT</h1>
            <h2>Laravel Docker Application</h2>
        </div>
        
        <div class="status">
            ✅ <strong>Application Status:</strong> Running Successfully
        </div>
        
        <div class="api-info">
            <h3>📡 API Endpoints</h3>
            <ul>
                <li><strong>Base URL:</strong> {{ url('/') }}</li>
                <li><strong>API Base:</strong> {{ url('/api') }}</li>
                <li><strong>Environment:</strong> {{ config('app.env') }}</li>
                <li><strong>Debug Mode:</strong> {{ config('app.debug') ? 'Enabled' : 'Disabled' }}</li>
            </ul>
        </div>
        
        <div class="status">
            <h3>🐳 Docker Services Status</h3>
            <ul>
                <li>✅ Laravel PHP-FPM 8.5</li>
                <li>✅ NGINX Web Server</li>
                <li>✅ MongoDB Database</li>
                <li>✅ Mailhog Email Testing</li>
            </ul>
        </div>
        
        <div class="api-info">
            <h3>🔧 Development Tools</h3>
            <ul>
                <li><strong>Mailhog UI:</strong> <a href="http://localhost:8025" target="_blank">http://localhost:8025</a></li>
                <li><strong>MongoDB:</strong> localhost:27017</li>
                <li><strong>Laravel Version:</strong> {{ app()->version() }}</li>
                <li><strong>PHP Version:</strong> {{ PHP_VERSION }}</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin-top: 30px; color: #666;">
            <p>🎉 Docker setup completed successfully!</p>
            <p><em>Generated on: {{ date('Y-m-d H:i:s') }}</em></p>
        </div>
    </div>
</body>
</html>
