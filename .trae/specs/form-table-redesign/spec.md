# 表单数据表格重构 - Product Requirement Document

## Overview
- **Summary**: 重构现有的表单数据展示系统，将 JSON 格式的表单数据转换为关系型数据库存储，并使用 AG Grid 实现功能强大的表格展示
- **Purpose**: 解决当前表单数据展示不完整、难以查询、管理用户看不懂原始 JSON 格式的问题，提供更直观、易用的管理界面
- **Target Users**: 网站管理员、后台数据管理人员

## Goals
1. 将所有表单数据转换为关系型数据库表结构存储，字段名称使用直观的中文/英文标签
2. 使用 AG Grid 实现功能丰富的数据表格，支持：
   - 所有字段同级展示
   - 列自定义显示/隐藏
   - 文本超长截断并悬浮显示
   - 分页和虚拟滚动
   - 搜索过滤（支持电话、邮箱、姓名、填写日期）
3. 保留原有的查看详情功能
4. 将 Yes/No 类型的字段值显示为直观的标签
5. 空值或不存在的字段显示为"-"

## Non-Goals (Out of Scope)
- 不修改表单提交逻辑（保持现有表单提交流程不变）
- 不删除或修改现有的 form_submissions 表（仅做数据迁移）
- 不修改其他管理功能页面（如律师管理等）
- 不实现复杂的数据统计功能（当前版本仅做基础展示和查询）

## Background & Context
当前系统使用 `form_submissions` 表存储表单数据，数据以 JSON 格式存储在 `data` 字段中。这种方式有以下问题：
1. 管理用户无法直接在列表页面查看所有字段，必须点击"View"按钮查看详情
2. JSON 格式的原始字段名不直观（如 wpforms[fields][13][first]）
3. 查询和过滤功能有限
4. Yes/No 类型的选项没有进行直观展示

通过全站扫描，我们发现了以下表单及字段：
- **Form 2528**: Crypto Fraud & Recovery
- **Form 4147**: Meta Crypto Scam Ads Investigation  
- **Form 3952**: Coinbase Data Breach
- **Form 4296**: Business Litigation
- **Form 4002**: Crypto Business Transactions
- **Form 4275**: Crypto Litigation
- **Form 3600**: vCard Disclaimer
- **Form 3778**: Lawyer List Disclaimer

## Functional Requirements
- **FR-1**: 设计并实现新的关系型数据库表结构，支持所有表单字段
- **FR-2**: 开发数据迁移脚本，将现有的 form_submissions 数据迁移到新表结构
- **FR-3**: 修改表单提交处理逻辑，新数据保存到新表结构中
- **FR-4**: 使用 AG Grid 重构后台表单列表页面
- **FR-5**: 实现字段标签映射，将 wpforms 字段 ID 转换为友好的标题
- **FR-6**: 实现搜索功能，支持搜索电话、邮箱、姓名、填写日期
- **FR-7**: 实现列配置功能，允许用户自定义显示/隐藏列
- **FR-8**: 保留原有的查看详情功能
- **FR-9**: 空值或不存在的字段显示为"-"

## Non-Functional Requirements
- **NFR-1**: 页面加载时间不超过 3 秒（即使有大量数据）
- **NFR-2**: AG Grid 支持虚拟滚动，处理 1000+ 条记录保持流畅
- **NFR-3**: 表格列宽支持自适应和手动调整
- **NFR-4**: 搜索响应时间不超过 1 秒

## Constraints
- **Technical**: 
  - 使用现有的 PHP 后端架构
  - 引入 AG Grid 作为前端表格组件
  - 保持与现有数据库的兼容性（MySQL 数据库）
- **Business**: 
  - 需要保留历史数据
  - 不影响现有表单的正常提交
- **Dependencies**: 
  - AG Grid 库
  - jQuery（现有项目已使用）

## Assumptions
1. 现有表单提交处理代码在 `api/submit.php` 中
2. 数据库配置保持不变
3. 现有 form_submissions 表中的 data 字段包含了所有需要的字段数据

## Acceptance Criteria

### AC-1: 数据库表结构设计
- **Given**: 需要存储所有表单数据
- **When**: 设计并创建新的数据库表
- **Then**: 
  - 有一个主表存储提交记录的基本信息
  - 有一个字段定义表存储所有表单字段的元信息
  - 有一个字段值表存储实际的字段值
  - 支持存储不同类型的数据（文本、日期、选择项等）
- **Verification**: programmatic

### AC-2: 数据迁移脚本
- **Given**: 现有 form_submissions 表中有数据
- **When**: 运行迁移脚本
- **Then**:
  - 所有现有数据正确迁移到新表结构
  - 字段值正确映射到对应的字段标签
  - 空值被正确处理为"-"
  - 数据完整性得到保证
- **Verification**: programmatic

### AC-3: AG Grid 集成
- **Given**: 新表结构中有数据
- **When**: 访问后台表单列表页面
- **Then**:
  - 使用 AG Grid 展示数据
  - 所有字段同级展示在表格中
  - 列标题使用友好的字段标签
  - 支持列显示/隐藏配置
  - 支持文本超长截断和悬浮提示
- **Verification**: human-judgment

### AC-4: 搜索功能
- **Given**: 有多个表单提交记录
- **When**: 在搜索框输入关键词
- **Then**:
  - 支持按姓名搜索
  - 支持按邮箱搜索
  - 支持按电话搜索
  - 支持按提交日期范围搜索
  - 搜索结果准确且响应及时
- **Verification**: programmatic + human-judgment

### AC-5: 字段值处理
- **Given**: 表单提交数据包含各种类型的字段
- **When**: 在表格中展示数据
- **Then**:
  - Yes/No 类型的选项显示为标签（如"Yes"、"No"）
  - 空值或不存在的字段显示为"-"
  - 长文本被截断，鼠标悬浮时显示完整内容
- **Verification**: human-judgment

### AC-6: 保留查看详情功能
- **Given**: 有表单提交记录
- **When**: 点击某条记录的查看详情按钮
- **Then**:
  - 弹出详情模态框
  - 显示该记录的所有字段信息
  - 格式清晰易读
- **Verification**: human-judgment

## Open Questions
- [ ] 是否需要支持导出功能（Excel/CSV）？（当前需求中没有明确要求，但这是常见功能）
- [ ] 是否需要支持数据编辑功能？（当前需求中没有明确要求）
- [ ] 是否需要支持按表单类型筛选？
