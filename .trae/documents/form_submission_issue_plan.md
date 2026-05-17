# 表单提交问题分析与修复计划

## 问题诊断

### 问题根源
通过分析代码，发现了**表结构不匹配**的问题：

1. **表单处理器**（`practice-areas/business-litigation-thank-you/index.php`）：
   - 将数据插入到 **`form_submissions`** 表（旧表）
   - 表结构：`(submission_id, form_id, form_name, data, ip, user_agent)`

2. **管理后台**（`admin-x7k9m/forms.php` 和 `forms-api.php`）：
   - 查询的是 **`form_submissions_new`** 表（新表）和 **`form_field_values`** 表
   - 新表结构：`(id, submission_id, form_id, form_name, ip, user_agent, submitted_at)`
   - 字段值表：`form_field_values (submission_id, field_label, field_value)`

### 影响
- 用户提交表单后数据写入旧表
- 管理后台从新表读取，永远看不到新数据

## 修复方案

### 修改 `practice-areas/business-litigation-thank-you/index.php`

需要修改数据库插入逻辑，同时写入：
1. `form_submissions_new` 表 - 主记录
2. `form_field_values` 表 - 字段值记录（拆分 JSON 数据）

### 修改步骤

1. **修改主表插入逻辑**：
   - 改为插入 `form_submissions_new` 表
   - 添加 `submitted_at` 字段
   - 返回新插入记录的 `id`

2. **添加字段值插入逻辑**：
   - 解析 `$formData` 数组
   - 将每个字段插入 `form_field_values` 表
   - 关联到主记录 ID

3. **测试验证**：
   - 提交测试表单
   - 验证数据写入新表
   - 确认管理后台显示数据

## 涉及文件

### 需要修改
1. `practice-areas/business-litigation-thank-you/index.php` - 主表单处理器

### 需要检查其他页面
- 检查是否所有表单处理器都使用相同逻辑
- 如有必要，统一修改所有处理器