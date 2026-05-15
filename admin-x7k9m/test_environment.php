<?php
/**
 * 快速测试脚本 - 验证环境和数据库连接
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=" . str_repeat("=", 50) . "\n";
echo "环境测试脚本\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// 1. 测试 PHP 版本
echo "[1/5] PHP 版本: " . PHP_VERSION . "\n";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "[OK] PHP 版本符合要求\n";
} else {
    echo "[!] PHP 版本可能过旧\n";
}

// 2. 检查文件路径
echo "\n[2/5] 检查文件路径...\n";
$configFile = __DIR__ . '/config.php';
$dbFile = __DIR__ . '/db.php';
echo "config.php = {$configFile}\n";
echo "db.php = {$dbFile}\n";

if (file_exists($configFile)) {
    echo "[OK] config.php 存在\n";
} else {
    echo "[X] config.php 不存在\n";
    exit(1);
}

if (file_exists($dbFile)) {
    echo "[OK] db.php 存在\n";
} else {
    echo "[X] db.php 不存在\n";
    exit(1);
}

// 3. 加载配置
echo "\n[3/5] 加载配置文件...\n";
require_once $configFile;
require_once $dbFile;
echo "[OK] 配置文件加载成功\n";

// 4. 测试数据库连接
echo "\n[4/5] 测试数据库连接...\n";
try {
    $db = Database::getInstance()->getConnection();
    echo "[OK] 数据库连接成功\n";
    
    $stmt = $db->query("SELECT COUNT(*) FROM form_submissions");
    $count = $stmt->fetchColumn();
    echo "form_submissions 表中有 {$count} 条记录\n";
    
    $stmt = $db->query("SHOW TABLES LIKE 'form_submissions_new'");
    if ($stmt->fetch()) {
        echo "[OK] form_submissions_new 表存在\n";
    } else {
        echo "[!] form_submissions_new 表不存在，请先运行 migration.sql\n";
    }
    
    $stmt = $db->query("SHOW TABLES LIKE 'form_field_values'");
    if ($stmt->fetch()) {
        echo "[OK] form_field_values 表存在\n";
    } else {
        echo "[!] form_field_values 表不存在，请先运行 migration.sql\n";
    }
    
} catch (Exception $e) {
    echo "[X] 数据库连接失败: " . $e->getMessage() . "\n";
    exit(1);
}

// 5. 检查字段配置
echo "\n[5/5] 检查字段配置...\n";
if (defined('FORM_CONFIGS')) {
    echo "[OK] FORM_CONFIGS 已定义，包含 " . count(FORM_CONFIGS) . " 个表单\n";
    foreach (FORM_CONFIGS as $formId => $config) {
        echo "  - 表单 {$formId}: " . $config['name'] . "\n";
    }
} else {
    echo "[!] FORM_CONFIGS 未定义\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "[✅] 环境检查通过！可以运行 migrate_data.php 进行数据迁移\n";
echo str_repeat("=", 50) . "\n";
