<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-width: 500px;
            margin: 1rem;
        }
        .icon {
            font-size: 4rem;
            color: #e74c3c;
            margin-bottom: 1rem;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 2rem;
        }
        p {
            color: #7f8c8d;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .ip-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            font-family: 'Courier New', monospace;
            color: #495057;
        }
        .contact-info {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚫</div>
        <h1>Access Denied</h1>
        <p>Sorry, your IP address is not authorized to access this application.</p>
        
        <div class="ip-info">
            Your IP: {{ $ip ?? 'Unknown' }}
        </div>
        
        <p>If you believe this is an error, please contact the system administrator.</p>
        
        <div class="contact-info">
            <p><strong>Error Code:</strong> 403 - Forbidden</p>
            <p><strong>Timestamp:</strong> {{ now()->format('Y-m-d H:i:s T') }}</p>
        </div>
    </div>
</body>
</html>
