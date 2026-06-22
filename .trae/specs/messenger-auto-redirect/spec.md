# Messenger 自动跳转与后台设置功能 Spec

## Overview
- **Summary**: 实现表单数据本地缓存、感谢页面自动跳转 Messenger、以及后台 Messenger ID 设置功能
- **Purpose**: 提升用户体验，实现表单提交后自动引导用户通过 Messenger 进行沟通，并提供管理员配置能力
- **Target Users**: 网站访问者（表单提交用户）和管理员

## Goals
1. 表单提交后先缓存到 localStorage，再提交到数据库
2. 感谢页面自动获取缓存数据，整合成"问题：答案"格式并跳转 Messenger
3. 后台管理系统新增 Messenger ID 设置功能
4. 生成 SQL 代码用于创建必要的数据库表

## Non-Goals (Out of Scope)
- 不修改现有的表单验证逻辑
- 不改变现有的数据库表结构（除新增设置表外）
- 不添加新的前端框架

## Background & Context
- 当前表单提交后直接进入感谢页面，没有自动跳转 Messenger 的功能
- MessengerManager 类已存在，但配置是硬编码的
- 表单配置已在 config.php 中定义，包含字段标签映射

## Functional Requirements

### FR-1: 表单数据本地缓存
- **FR-1.1**: 表单提交前将数据缓存到 localStorage
- **FR-1.2**: 缓存数据以可读的字段标签为键名
- **FR-1.3**: 新提交覆盖旧数据（只保留最新一条）

### FR-2: 感谢页面自动跳转 Messenger
- **FR-2.1**: 感谢页面加载时读取 localStorage 中的表单数据
- **FR-2.2**: 将数据整合成"问题：答案"格式的文本
- **FR-2.3**: 自动调用 Messenger 跳转方法
- **FR-2.4**: Messenger 预填充整合后的文本内容

### FR-3: 后台 Messenger ID 设置
- **FR-3.1**: 在后台管理系统中添加设置页面
- **FR-3.2**: 提供 Messenger Page ID 输入框
- **FR-3.3**: 提供启用/禁用开关
- **FR-3.4**: 保存配置到数据库

### FR-4: 设置 API
- **FR-4.1**: 提供 GET 接口获取 Messenger 配置
- **FR-4.2**: 提供 POST 接口保存 Messenger 配置

## Non-Functional Requirements
- **NFR-1**: localStorage 缓存数据应在用户会话结束后清理
- **NFR-2**: Messenger 跳转应支持 iOS、Android 和 Web 平台
- **NFR-3**: 设置页面应具有管理员权限验证

## Constraints
- **Technical**: 使用现有 PHP + MySQL 架构，不引入新框架
- **Business**: 保持现有表单提交流程不变
- **Dependencies**: localStorage API（现代浏览器支持）

## Assumptions
- 用户浏览器支持 localStorage
- 用户设备可能安装了 Messenger App（移动端）
- 管理员知道正确的 Messenger Page ID

## Acceptance Criteria

### AC-1: 表单数据缓存
- **Given**: 用户提交表单
- **When**: 表单数据准备提交到数据库之前
- **Then**: 表单数据以 `{ "fieldLabel": "fieldValue" }` 格式保存到 localStorage，key 为 `formData`
- **Verification**: `programmatic`

### AC-2: 感谢页面自动跳转
- **Given**: 用户成功提交表单并进入感谢页面
- **When**: 页面加载完成
- **Then**: 自动读取 localStorage 中的表单数据，整合成"问题：答案"格式，并调用 Messenger 跳转方法
- **Verification**: `human-judgment`

### AC-3: Messenger 预填充文本格式
- **Given**: 表单数据为 `{ "Name": "John Doe", "Email": "john@example.com" }`
- **When**: 生成 Messenger 消息文本
- **Then**: 文本格式为：
  ```
  Case Report Details:
  
  Name：John Doe
  Email：john@example.com
  
  Please assist with my case. Thank you!
  ```
- **Verification**: `programmatic`

### AC-4: 后台设置页面访问
- **Given**: 管理员已登录后台
- **When**: 访问 `/admin-x7k9m/settings.php`
- **Then**: 显示 Messenger 设置表单，包含 Page ID 输入框和启用开关
- **Verification**: `human-judgment`

### AC-5: 保存 Messenger 设置
- **Given**: 管理员在设置页面输入新的 Page ID
- **When**: 点击保存按钮
- **Then**: 设置保存到数据库，前端 MessengerManager 自动加载新配置
- **Verification**: `programmatic`

## Open Questions
- [ ] 是否需要在感谢页面显示"正在跳转 Messenger"的提示？
- [ ] 如果用户浏览器禁用了 localStorage，如何处理？

## Technical Design

### 数据库表设计
```sql
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

INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('messenger_page_id', '100068675438543', 'string', 'Messenger Page ID'),
('messenger_enabled', 'true', 'boolean', 'Enable Messenger auto-redirect');
```

### localStorage 数据结构
```json
{
  "formData": {
    "First Name": "John",
    "Last Name": "Doe",
    "Email": "john@example.com",
    "Phone": "+1234567890",
    "Case Details": "I need help with..."
  }
}
```

### Messenger 消息格式
```
Case Report Details:

First Name：John
Last Name：Doe
Email：john@example.com
Phone：+1234567890
Case Details：I need help with...

Please assist with my case. Thank you!
```