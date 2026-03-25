<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat App Health</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 40px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        h3 {
            color: #1565c0;
        }
        p {
            font-size: 16px;
            color: #333;
        }
        a {
            color: #ff9800;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
    <h3>Welcome to Chat App</h3>
    <p>Your system health endpoint is available at:</p>
    <p>
        <a href="http://178.104.58.236/api/health" target="_blank">Check Health</a>
    </p>
</div>
</body>
</html>