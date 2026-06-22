<?php
/**
 * 初始化 site_settings 表
 * 运行此脚本创建必要的数据库表和初始数据
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // 创建 site_settings 表
    $createTableSql = "
        CREATE TABLE IF NOT EXISTS `site_settings` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `setting_key` VARCHAR(100) NOT NULL UNIQUE,
            `setting_value` TEXT NOT NULL,
            `setting_type` VARCHAR(50) DEFAULT 'string',
            `description` VARCHAR(255) DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($createTableSql);
    echo "✓ Table 'site_settings' created or already exists\n";
    
    // 插入初始数据（使用 INSERT IGNORE 避免重复插入）
    $insertSql = "
        INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
        ('messenger_page_id', '100068675438543', 'string', 'Messenger Page ID for auto-redirect'),
        ('messenger_enabled', 'true', 'boolean', 'Enable/disable Messenger auto-redirect');
    ";
    
    $db->exec($insertSql);
    echo "✓ Initial Messenger settings inserted\n";
    
    echo "\nDatabase initialization completed successfully!\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>