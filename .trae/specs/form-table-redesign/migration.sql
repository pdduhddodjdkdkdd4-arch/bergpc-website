-- ============================================
-- 表单数据表格重构 - 数据库迁移脚本
-- ============================================

-- 创建新的表单提交主表
CREATE TABLE IF NOT EXISTS `form_submissions_new` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `submission_id` VARCHAR(100) NOT NULL COMMENT '原表的 submission_id',
  `form_id` VARCHAR(50) NOT NULL COMMENT '表单ID',
  `form_name` VARCHAR(255) NOT NULL COMMENT '表单名称',
  `ip` VARCHAR(50) DEFAULT NULL COMMENT '提交者IP',
  `user_agent` TEXT DEFAULT NULL COMMENT '用户代理',
  `submitted_at` DATETIME NOT NULL COMMENT '提交时间',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_submission_id` (`submission_id`),
  KEY `idx_form_id` (`form_id`),
  KEY `idx_submitted_at` (`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='表单提交主表';

-- 创建表单字段定义表
CREATE TABLE IF NOT EXISTS `form_fields` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `form_id` VARCHAR(50) NOT NULL COMMENT '表单ID',
  `field_key` VARCHAR(255) NOT NULL COMMENT '字段键名（如 wpforms[fields][13][first]）',
  `field_label` VARCHAR(255) NOT NULL COMMENT '字段标签（显示名称）',
  `field_type` VARCHAR(50) NOT NULL DEFAULT 'text' COMMENT '字段类型（text, email, phone, date, select, checkbox, textarea等）',
  `is_searchable` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否可搜索',
  `display_order` INT NOT NULL DEFAULT 0 COMMENT '显示顺序',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否启用',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_form_field` (`form_id`, `field_key`),
  KEY `idx_form_id` (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='表单字段定义表';

-- 创建字段值表
CREATE TABLE IF NOT EXISTS `form_field_values` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `submission_id` BIGINT UNSIGNED NOT NULL COMMENT '关联 form_submissions_new.id',
  `field_id` BIGINT UNSIGNED NOT NULL COMMENT '关联 form_fields.id',
  `field_value` TEXT DEFAULT NULL COMMENT '字段值',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_submission` (`submission_id`),
  KEY `idx_field` (`field_id`),
  KEY `idx_value` (`field_value`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='表单字段值表';

-- ============================================
-- 插入表单字段定义
-- ============================================

-- Form 2528: Crypto Fraud & Recovery
INSERT INTO `form_fields` (`form_id`, `field_key`, `field_label`, `field_type`, `is_searchable`, `display_order`) VALUES
('2528', 'wpforms[fields][27]', 'General Contact Disclaimer', 'checkbox', 0, 1),
('2528', 'wpforms[fields][13][first]', 'First Name', 'text', 1, 2),
('2528', 'wpforms[fields][13][last]', 'Last Name', 'text', 1, 3),
('2528', 'wpforms[fields][1]', 'Email', 'email', 1, 4),
('2528', 'wpforms[fields][25]', 'Phone', 'phone', 1, 5),
('2528', 'wpforms[fields][26][address1]', 'Address Line 1', 'text', 0, 6),
('2528', 'wpforms[fields][26][city]', 'City', 'text', 0, 7),
('2528', 'wpforms[fields][26][state]', 'State/Province/Region', 'text', 0, 8),
('2528', 'wpforms[fields][26][country]', 'Country', 'select', 0, 9),
('2528', 'wpforms[fields][17]', 'Current Occupation', 'text', 0, 10),
('2528', 'wpforms[fields][2]', 'Case Details', 'textarea', 0, 11),
('2528', 'wpforms[fields][18]', 'Names and Communication Platforms', 'textarea', 0, 12),
('2528', 'wpforms[fields][19]', 'Amount Lost', 'textarea', 0, 13),
('2528', 'wpforms[fields][3]', 'Honeypot', 'text', 0, 14),
('2528', 'wpforms[fields][30]', 'Transaction Method', 'textarea', 0, 15),
('2528', 'wpforms[fields][20]', 'Banks/Crypto Platforms', 'textarea', 0, 16),
('2528', 'wpforms[fields][21]', 'Last Transaction Date', 'textarea', 0, 17),
('2528', 'wpforms[fields][22]', 'Additional Information', 'textarea', 0, 18),
('2528', 'wpforms[fields][9]', 'Disclaimer Agreement', 'checkbox', 0, 19);

-- Form 4147: Meta Crypto Scam Ads Investigation
INSERT INTO `form_fields` (`form_id`, `field_key`, `field_label`, `field_type`, `is_searchable`, `display_order`) VALUES
('4147', 'wpforms[fields][27]', 'General Contact Disclaimer', 'checkbox', 0, 1),
('4147', 'wpforms[fields][13][first]', 'First Name', 'text', 1, 2),
('4147', 'wpforms[fields][13][last]', 'Last Name', 'text', 1, 3),
('4147', 'wpforms[fields][1]', 'Email', 'email', 1, 4),
('4147', 'wpforms[fields][25]', 'Phone', 'phone', 1, 5),
('4147', 'wpforms[fields][3]', 'Honeypot', 'text', 0, 6),
('4147', 'wpforms[fields][26][address1]', 'Address Line 1', 'text', 0, 7),
('4147', 'wpforms[fields][26][city]', 'City', 'text', 0, 8),
('4147', 'wpforms[fields][26][state]', 'State', 'select', 0, 9),
('4147', 'wpforms[fields][17]', 'Current Occupation', 'text', 0, 10),
('4147', 'wpforms[fields][2]', 'Ad Screenshots/Records', 'textarea', 0, 11),
('4147', 'wpforms[fields][29]', 'Directed to Specific User', 'radio', 0, 12),
('4147', 'wpforms[fields][18]', 'Contact Information', 'textarea', 0, 13),
('4147', 'wpforms[fields][30]', 'Reported to Meta', 'radio', 0, 14),
('4147', 'wpforms[fields][31]', 'Report Result', 'textarea', 0, 15),
('4147', 'wpforms[fields][19]', 'Total Loss Amount', 'textarea', 0, 16),
('4147', 'wpforms[fields][32]', 'First Clicked Ad Date', 'date', 0, 17),
('4147', 'wpforms[fields][33]', 'First Communicated Date', 'date', 0, 18),
('4147', 'wpforms[fields][34]', 'Last Transaction Date', 'date', 0, 19),
('4147', 'wpforms[fields][9]', 'Disclaimer Agreement', 'checkbox', 0, 20);

-- Form 3952: Coinbase Data Breach (类似Form 4147，复用字段结构)
INSERT INTO `form_fields` (`form_id`, `field_key`, `field_label`, `field_type`, `is_searchable`, `display_order`) VALUES
('3952', 'wpforms[fields][27]', 'General Contact Disclaimer', 'checkbox', 0, 1),
('3952', 'wpforms[fields][13][first]', 'First Name', 'text', 1, 2),
('3952', 'wpforms[fields][13][last]', 'Last Name', 'text', 1, 3),
('3952', 'wpforms[fields][1]', 'Email', 'email', 1, 4),
('3952', 'wpforms[fields][25]', 'Phone', 'phone', 1, 5),
('3952', 'wpforms[fields][26][address1]', 'Address Line 1', 'text', 0, 6),
('3952', 'wpforms[fields][26][city]', 'City', 'text', 0, 7),
('3952', 'wpforms[fields][26][state]', 'State/Province/Region', 'text', 0, 8),
('3952', 'wpforms[fields][26][country]', 'Country', 'select', 0, 9),
('3952', 'wpforms[fields][17]', 'Current Occupation', 'text', 0, 10),
('3952', 'wpforms[fields][2]', 'Case Details', 'textarea', 0, 11),
('3952', 'wpforms[fields][3]', 'Honeypot', 'text', 0, 12),
('3952', 'wpforms[fields][9]', 'Disclaimer Agreement', 'checkbox', 0, 13);

-- 其他表单（4296, 4002, 4275, 3600, 3778）使用通用字段结构
-- 可以在需要时添加具体字段定义

-- ============================================
-- 数据迁移说明：
-- 1. 首先运行此脚本创建新表
-- 2. 然后运行 PHP 迁移脚本从 form_submissions 迁移数据
-- 3. 验证数据迁移成功
-- 4. 修改表单提交逻辑，开始双写
-- ============================================
