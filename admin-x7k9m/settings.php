<?php
/**
 * Admin Settings Page - Messenger Configuration
 */

session_start();

// Check if user is logged in
require_once __DIR__ . '/session.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../admin/config.php';
require_once __DIR__ . '/../admin/db.php';

$pageId = '';
$enabled = true;
$message = '';
$messageType = '';

// Load current settings
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('messenger_page_id', 'messenger_enabled')");
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['setting_key'] === 'messenger_page_id') {
            $pageId = $row['setting_value'];
        } elseif ($row['setting_key'] === 'messenger_enabled') {
            $enabled = ($row['setting_value'] === 'true');
        }
    }
} catch(PDOException $e) {
    $message = 'Error loading settings: ' . $e->getMessage();
    $messageType = 'error';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pageId = trim($_POST['messenger_page_id'] ?? '');
    $enabled = isset($_POST['messenger_enabled']);
    
    try {
        // Update page ID
        if (!empty($pageId)) {
            $stmt = $db->prepare("
                INSERT INTO site_settings (setting_key, setting_value, setting_type, description)
                VALUES ('messenger_page_id', ?, 'string', 'Messenger Page ID for auto-redirect')
                ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$pageId, $pageId]);
        }
        
        // Update enabled status
        $enabledValue = $enabled ? 'true' : 'false';
        $stmt = $db->prepare("
            INSERT INTO site_settings (setting_key, setting_value, setting_type, description)
            VALUES ('messenger_enabled', ?, 'boolean', 'Enable/disable Messenger auto-redirect')
            ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$enabledValue, $enabledValue]);
        
        $message = 'Settings updated successfully!';
        $messageType = 'success';
        
    } catch(PDOException $e) {
        $message = 'Error saving settings: ' . $e->getMessage();
        $messageType = 'error';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messenger Settings - Berg PC Admin</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        .header {
            background: #1a1230;
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            background: rgba(255,255,255,0.2);
            border-radius: 4px;
            transition: background 0.3s;
        }
        .header a:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .card h2 {
            margin: 0 0 25px 0;
            color: #1a1230;
            border-bottom: 2px solid #0274be;
            padding-bottom: 15px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        .form-group input[type="text"]:focus {
            outline: none;
            border-color: #0274be;
        }
        .form-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        .form-group .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        .btn {
            background: #0274be;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #015a96;
        }
        .message {
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .nav-links {
            margin-bottom: 20px;
        }
        .nav-links a {
            color: #0274be;
            text-decoration: none;
            margin-right: 20px;
            font-weight: 500;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        .help-text {
            color: #666;
            font-size: 14px;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Berg PC Admin</h1>
        <a href="logout.php">Logout</a>
    </div>
    
    <div class="container">
        <div class="nav-links">
            <a href="index.php">Dashboard</a>
            <a href="lawyers.php">Lawyers</a>
            <a href="forms.php">Forms</a>
            <a href="settings.php" style="text-decoration: underline;">Settings</a>
        </div>
        
        <div class="card">
            <h2>Messenger Settings</h2>
            
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label for="messenger_page_id">Messenger Page ID</label>
                    <input type="text" id="messenger_page_id" name="messenger_page_id" 
                           value="<?php echo htmlspecialchars($pageId); ?>" 
                           placeholder="Enter your Facebook Page ID">
                    <div class="help-text">
                        This is your Facebook Page ID that will be used for Messenger auto-redirect.
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="messenger_enabled" name="messenger_enabled" 
                               <?php echo $enabled ? 'checked' : ''; ?>>
                        Enable Messenger Auto-Redirect
                    </label>
                    <div class="help-text">
                        When enabled, users will be automatically redirected to Messenger after form submission.
                    </div>
                </div>
                
                <button type="submit" class="btn">Save Settings</button>
            </form>
        </div>
    </div>
</body>
</html>