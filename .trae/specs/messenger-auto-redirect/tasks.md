# Messenger 自动跳转与后台设置功能 - 实现计划

## [x] Task 1: 创建数据库表和初始数据
- **Priority**: P0
- **Depends On**: None
- **Description**: 创建 `site_settings` 表用于存储 Messenger 配置，包含初始数据
- **Acceptance Criteria Addressed**: AC-5
- **Test Requirements**:
  - `programmatic`: 验证表结构正确创建
  - `programmatic`: 验证初始数据正确插入
- **Status**: 已完成 - 创建了 SQL 文件和初始化脚本

## [x] Task 2: 创建设置 API 端点
- **Priority**: P0
- **Depends On**: Task 1
- **Description**: 创建 `api/settings.php` 提供 GET 和 POST 方法处理 Messenger 配置
- **Acceptance Criteria Addressed**: FR-4
- **Test Requirements**:
  - `programmatic`: GET 请求返回正确的 JSON 格式（包含 pageId 和 enabled）
  - `programmatic`: POST 请求正确保存配置到数据库
- **Status**: 已完成 - 创建了完整的 API 端点

## [x] Task 3: 创建后台设置页面
- **Priority**: P0
- **Depends On**: Task 2
- **Description**: 创建 `admin-x7k9m/settings.php` 页面，包含 Messenger Page ID 输入框和启用开关
- **Acceptance Criteria Addressed**: FR-3, AC-4, AC-5
- **Test Requirements**:
  - `human-judgment`: 页面显示正确的表单元素
  - `human-judgment`: 保存按钮正常工作并显示成功提示
  - `programmatic`: 在后台导航菜单添加 Settings 链接
- **Status**: 已完成 - 创建了后台设置页面

## [x] Task 4: 修改感谢页面添加自动跳转功能
- **Priority**: P0
- **Depends On**: Task 2
- **Description**: 修改 `practice-areas/business-litigation-thank-you/index.php`，添加从 localStorage 读取数据并自动跳转 Messenger 的逻辑
- **Acceptance Criteria Addressed**: FR-2, AC-2
- **Test Requirements**:
  - `human-judgment`: 页面加载时自动触发 Messenger 跳转
  - `programmatic`: 正确读取 localStorage 中的表单数据
- **Status**: 已完成 - 添加了自动跳转脚本

## [x] Task 5: 修改 MessengerManager 类
- **Priority**: P0
- **Depends On**: Task 2
- **Description**: 修改 `wp-includes/js/csv-manager.js` 中的 MessengerManager 类，改进消息格式为"问题：答案"格式
- **Acceptance Criteria Addressed**: FR-2, AC-3
- **Test Requirements**:
  - `programmatic`: `buildMessage()` 方法生成正确的文本格式
  - `programmatic`: `loadSettings()` 方法正确从 API 加载配置
- **Status**: 已完成 - 修改了 buildMessage 方法

## [x] Task 6: 添加表单提交前的数据缓存
- **Priority**: P0
- **Depends On**: None
- **Description**: 在表单提交前将数据缓存到 localStorage
- **Acceptance Criteria Addressed**: FR-1, AC-1
- **Test Requirements**:
  - `programmatic`: 表单提交前数据正确保存到 localStorage
  - `programmatic`: 数据格式为 `{ "fieldLabel": "fieldValue" }`
- **Status**: 已完成 - 添加了表单数据缓存逻辑

## [ ] Task 7: 测试和验证
- **Priority**: P1
- **Depends On**: Task 1-6
- **Description**: 测试完整流程：表单提交 → 缓存 → 感谢页面 → Messenger 跳转
- **Acceptance Criteria Addressed**: 所有 AC
- **Test Requirements**:
  - `human-judgment`: 测试 iOS/Android/Web 平台跳转
  - `human-judgment`: 测试后台设置功能
- **Status**: 待测试

# Task Dependencies
- Task 2 depends on Task 1
- Task 3 depends on Task 2
- Task 4 depends on Task 2
- Task 5 depends on Task 2
- Task 6 can run parallel with Task 1-3
- Task 7 depends on Task 1-6