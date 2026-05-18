<?php
http_response_code(403);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installer Disabled</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Inter, Arial, sans-serif;
            background: #f6f7fb;
            color: #1f2937;
        }
        .panel {
            width: min(560px, calc(100% - 32px));
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
            padding: 32px;
        }
        h1 {
            margin: 0 0 12px;
            font-size: 28px;
            color: #b91c1c;
        }
        p {
            margin: 0 0 14px;
            line-height: 1.6;
            color: #4b5563;
        }
        a {
            color: #1d4ed8;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="panel">
        <h1>Installer Disabled</h1>
        <p>The legacy installation wizard has been disabled for security reasons.</p>
        <p>Environment setup and schema work should now be handled through controlled configuration and the Laravel migration workspace.</p>
        <p><a href="../admin/login.php">Go to Admin Login</a></p>
    </div>
</body>
</html>
