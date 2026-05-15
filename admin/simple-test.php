<?php
// 最简单的测试页面，不依赖任何其他文件
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .info { background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 8px; margin: 10px 0; }
        code { background-color: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        a { display: inline-block; margin: 5px 0; padding: 8px 16px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        a:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <h1>Simple URL Parameter Test</h1>
    
    <div class="success">
        <h3>✅ PHP is working!</h3>
    </div>
    
    <div class="info">
        <h3>Current URL:</h3>
        <code><?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?></code>
    </div>
    
    <div class="info">
        <h3>$_GET Data:</h3>
        <?php if (empty($_GET)): ?>
            <p><em>No query parameters</em></p>
        <?php else: ?>
            <pre><?php print_r($_GET); ?></pre>
        <?php endif; ?>
    </div>
    
    <div class="info">
        <h3>Test Links:</h3>
        <p><a href="simple-test.php?test=1&param=hello">simple-test.php?test=1&param=hello</a></p>
        <p><a href="simple-test.php?search=&form_id=">simple-test.php?search=&form_id=</a></p>
        <p><a href="forms.php">forms.php</a></p>
        <p><a href="forms.php?search=test">forms.php?search=test</a></p>
    </div>
    
    <div class="info">
        <h3>Debug Info:</h3>
        <pre>
PHP Version: <?php echo phpversion(); ?>

REQUEST_URI: <?php echo $_SERVER['REQUEST_URI']; ?>
QUERY_STRING: <?php echo $_SERVER['QUERY_STRING']; ?>
SCRIPT_NAME: <?php echo $_SERVER['SCRIPT_NAME']; ?>
        </pre>
    </div>
</body>
</html>
