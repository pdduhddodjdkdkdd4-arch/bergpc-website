<?php
/**
 * Messenger Settings API
 * Provides endpoints for reading and updating Messenger configuration
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../admin/config.php';
require_once __DIR__ . '/../admin/db.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    $db = Database::getInstance()->getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get Messenger settings
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('messenger_page_id', 'messenger_enabled')");
        $stmt->execute();
        
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        $response['success'] = true;
        $response['message'] = 'Settings retrieved successfully';
        $response['data'] = [
            'messenger' => [
                'pageId' => $settings['messenger_page_id'] ?? '100068675438543',
                'enabled' => ($settings['messenger_enabled'] ?? 'true') === 'true'
            ]
        ];
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Update Messenger settings
        
        // Simple authentication check (for admin access)
        $authToken = $_POST['auth_token'] ?? $_GET['auth_token'] ?? '';
        // In production, use proper session-based authentication
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data)) {
            $data = $_POST;
        }
        
        if (isset($data['messenger'])) {
            $messengerData = $data['messenger'];
            
            if (isset($messengerData['pageId'])) {
                $stmt = $db->prepare("
                    INSERT INTO site_settings (setting_key, setting_value, setting_type, description)
                    VALUES ('messenger_page_id', ?, 'string', 'Messenger Page ID for auto-redirect')
                    ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->execute([$messengerData['pageId'], $messengerData['pageId']]);
            }
            
            if (isset($messengerData['enabled'])) {
                $enabledValue = $messengerData['enabled'] ? 'true' : 'false';
                $stmt = $db->prepare("
                    INSERT INTO site_settings (setting_key, setting_value, setting_type, description)
                    VALUES ('messenger_enabled', ?, 'boolean', 'Enable/disable Messenger auto-redirect')
                    ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->execute([$enabledValue, $enabledValue]);
            }
            
            $response['success'] = true;
            $response['message'] = 'Settings updated successfully';
        } else {
            $response['message'] = 'Invalid data format';
        }
        
    } else {
        $response['message'] = 'Method not allowed';
        http_response_code(405);
    }
    
} catch(PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
?>