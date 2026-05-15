<?php
/**
 * 简单的 API 测试 - 用于调试
 */

error_reporting(E_ALL);
ini_set('display_errors', 0); // 先关闭错误显示

header('Content-Type: application/json');

try {
    // 测试 1: 简单的 JSON 响应
    if (isset($_GET['test']) && $_GET['test'] === 'simple') {
        echo json_encode([
            'success' => true,
            'message' => 'Simple test works!',
            'php_version' => PHP_VERSION,
            'time' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    // 测试 2: 检查文件是否存在
    if (isset($_GET['test']) && $_GET['test'] === 'files') {
        $config_exists = file_exists(__DIR__ . '/../config.php');
        $db_exists = file_exists(__DIR__ . '/../db.php');
        
        echo json_encode([
            'success' => true,
            'config_exists' => $config_exists,
            'db_exists' => $db_exists,
            'config_path' => realpath(__DIR__ . '/../config.php'),
            'db_path' => realpath(__DIR__ . '/../db.php'),
            'dir' => __DIR__
        ]);
        exit;
    }

    // 测试 3: 数据库连接
    if (isset($_GET['test']) && $_GET['test'] === 'db') {
        require_once __DIR__ . '/../config.php';
        require_once __DIR__ . '/../db.php';
        
        $db = Database::getInstance()->getConnection();
        
        // 检查新表是否存在
        $stmt = $db->query("SHOW TABLES LIKE 'form_submissions_new'");
        $new_table_exists = $stmt->fetch() !== false;
        
        // 获取记录数
        $count = 0;
        if ($new_table_exists) {
            $countStmt = $db->query("SELECT COUNT(*) FROM form_submissions_new");
            $count = $countStmt->fetchColumn();
        }
        
        echo json_encode([
            'success' => true,
            'db_connected' => true,
            'new_table_exists' => $new_table_exists,
            'record_count' => $count
        ]);
        exit;
    }

    // 默认响应
    echo json_encode([
        'success' => true,
        'message' => 'Debug API is working',
        'available_tests' => [
            '?test=simple' => 'Simple JSON response',
            '?test=files' => 'Check file paths',
            '?test=db' => 'Test database connection'
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
