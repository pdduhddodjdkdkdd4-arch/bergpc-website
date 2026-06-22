-- 创建 site_settings 表用于存储全局配置
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

-- 插入初始 Messenger 配置数据
INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('messenger_page_id', '100068675438543', 'string', 'Messenger Page ID for auto-redirect'),
('messenger_enabled', 'true', 'boolean', 'Enable/disable Messenger auto-redirect');