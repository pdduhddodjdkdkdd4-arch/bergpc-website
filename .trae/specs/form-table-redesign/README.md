# 表单数据表格重构 - 完整方案

## 概述

本方案将现有的表单数据展示系统重构为使用关系型数据库存储和 AG Grid 组件展示的新系统，解决了原系统中数据难以查看、搜索和管理的问题。

## 主要改进

### 1. 数据存储改进
- **从 JSON 存储改为关系型存储**：使用三个新表分别存储提交记录、字段定义和字段值
- **字段标签友好化**：将晦涩的 `wpforms[fields][13][first]` 改为 "First Name" 这样的易读标签
- **更好的数据查询能力**：支持按多个字段进行高效搜索和过滤

### 2. UI/UX 改进
- **使用 AG Grid**：强大的表格组件，支持虚拟滚动、列配置、搜索过滤等功能
- **所有字段同级展示**：不需要点击 "View" 按钮就能看到所有数据
- **可配置的列显示**：用户可以选择显示/隐藏特定列
- **优化的值展示**：Yes/No 字段显示为标签，空值显示为 "-"，长文本截断并悬浮显示

### 3. 搜索功能
- 支持按姓名搜索
- 支持按邮箱搜索
- 支持按电话搜索
- 支持按日期范围搜索

## 数据库表结构设计

### 1. form_submissions_new（主表）
存储表单提交的基本信息

| 字段 | 类型 | 说明 |
|------|------|------|
| id | BIGINT | 主键 |
| submission_id | VARCHAR | 原表的 submission_id（用于关联） |
| form_id | VARCHAR | 表单ID |
| form_name | VARCHAR | 表单名称 |
| ip | VARCHAR | 提交者IP |
| user_agent | TEXT | 用户代理 |
| submitted_at | DATETIME | 提交时间 |
| created_at/updated_at | TIMESTAMP | 时间戳 |

### 2. form_fields（字段定义表）
存储所有表单字段的元信息

| 字段 | 类型 | 说明 |
|------|------|------|
| id | BIGINT | 主键 |
| form_id | VARCHAR | 表单ID |
| field_key | VARCHAR | 原始字段键名 |
| field_label | VARCHAR | 显示用的字段标签 |
| field_type | VARCHAR | 字段类型 |
| is_searchable | TINYINT | 是否可搜索 |
| display_order | INT | 显示顺序 |
| is_active | TINYINT | 是否启用 |

### 3. form_field_values（字段值表）
存储具体的字段值

| 字段 | 类型 | 说明 |
|------|------|------|
| id | BIGINT | 主键 |
| submission_id | BIGINT | 关联的提交记录ID |
| field_id | BIGINT | 关联的字段ID |
| field_value | TEXT | 字段值 |

## 已扫描的表单

通过全站扫描，我们发现了以下表单：

| 表单ID | 表单名称 |
|--------|----------|
| 2528 | Crypto Fraud & Recovery |
| 4147 | Meta Crypto Scam Ads Investigation |
| 3952 | Coinbase Data Breach |
| 4296 | Business Litigation |
| 4002 | Crypto Business Transactions |
| 4275 | Crypto Litigation |
| 3600 | vCard Disclaimer |
| 3778 | Lawyer List Disclaimer |

## 文件说明

### 规范文档
- `spec.md` - 产品需求文档，包含完整的需求说明和验收标准
- `tasks.md` - 实施计划，分解为具体的可执行任务
- `checklist.md` - 验证清单，用于项目验收

### 迁移脚本
- `migration.sql` - 数据库表结构创建和字段定义初始化脚本
- `migrate_data.php` - PHP 数据迁移脚本，将现有数据迁移到新表结构

## 部署步骤

### 第一步：数据库迁移
1. 备份现有数据库
2. 运行 `migration.sql` 创建新表和字段定义
3. 将 `migrate_data.php` 放到合适的位置
4. 运行 `php migrate_data.php` 进行数据迁移
5. 验证数据迁移成功

### 第二步：修改表单提交逻辑
1. 修改 `api/submit.php`，在保存到原表的同时也保存到新表
2. 保持双写直到验证新系统稳定

### 第三步：实现新的后台页面
1. 引入 AG Grid 库
2. 创建新的 API 接口获取数据
3. 实现搜索和过滤功能
4. 实现列配置功能
5. 优化字段值展示

### 第四步：测试和验收
1. 按照 `checklist.md` 进行全面测试
2. 用户验收测试
3. 修复发现的问题

### 第五步：上线部署
1. 在测试环境充分验证
2. 准备回滚方案
3. 在低峰期部署到生产环境
4. 监控系统运行状态

## 关于 AG Grid

AG Grid 是一个功能强大的 JavaScript 表格组件，具有以下特性：

✅ 支持虚拟滚动，处理大量数据保持流畅
✅ 支持列显示/隐藏配置
✅ 支持列宽调整
✅ 内置搜索和过滤功能
✅ 支持单元格文本截断和悬浮提示
✅ 丰富的主题和样式定制能力

建议使用 AG Grid Community Edition（免费开源版本）。

## 常见问题

### Q: 是否可以保留原有的 form_submissions 表？
A: 是的，建议保留原表作为备份，并在过渡期保持双写。

### Q: 新系统支持导出功能吗？
A: 当前版本没有包含，但 AG Grid 支持导出，可以根据需要添加。

### Q: 如果有新的表单字段怎么办？
A: 系统设计为支持动态字段，新字段会自动添加到 form_fields 表中。

### Q: 如何回滚？
A: 因为我们保留了原表，回滚很简单 - 只需要切换回使用原表的代码即可。

## 技术建议

1. **渐进式迁移**：先在测试环境充分验证，再上生产
2. **监控日志**：部署后密切监控错误日志
3. **性能优化**：根据数据量考虑进一步优化查询索引
4. **备份策略**：迁移前、迁移后都要进行完整备份

## 下一步

请查看 `tasks.md` 了解详细的实施任务分解，然后可以开始逐步实施。
