<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

Session::start();
requireAuth();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Query Parameters</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .test { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 8px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        code { background-color: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Test Query Parameters</h1>
    
    <div class="test success">
        <h3>Current URL:</h3>
        <code><?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?></code>
    </div>
    
    <div class="test">
        <h3>$_GET Array:</h3>
        <pre><?php print_r($_GET); ?></pre>
    </div>
    
    <div class="test">
        <h3>Test Links:</h3>
        <p><a href="test-params.php?search=test&form_id=2528">test-params.php?search=test&form_id=2528</a></p>
        <p><a href="test-params.php?search=&form_id=">test-params.php?search=&form_id=</a></p>
        <p><a href="forms.php?search=&form_id=">forms.php?search=&form_id=</a></p>
    </div>
</body>
</html>
