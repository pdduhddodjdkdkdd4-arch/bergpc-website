# Berg PC 管理后台 - 任务清单

## [x] Task 1: 项目目录结构与基础配置
- **Priority**: P0
- **Depends On**: None
- **Description**: 
  - 创建管理后台目录结构 `admin/`
  - 创建数据存储目录 `data/` 及子目录 `data/submissions/`
  - 创建配置文件 `admin/config.php`（管理路径、账号密码哈希、数据库路径等）
  - 创建 `.htaccess` 规则：隐藏管理入口、保护data目录、API路由
  - 创建 `data/lawyers.json` 初始文件（从现有律师信息提取）
- **Acceptance Criteria Addressed**: 隐藏路由登录系统
- **Test Requirements**:
  - `programmatic` TR-1.1: 访问 `/admin/` 返回404或重定向到首页
  - `programmatic` TR-1.2: 访问隐藏路径 `/admin-x7k9m/` 显示登录页面
  - `programmatic` TR-1.3: `data/` 目录无法通过URL直接访问
  - `programmatic` TR-1.4: `data/lawyers.json` 包含现有4位律师的数据

## [x] Task 2: 登录认证系统
- **Priority**: P0
- **Depends On**: Task 1
- **Description**: 
  - 创建 `admin/login.php` 登录页面（简洁UI）
  - 创建 `admin/auth.php` 认证逻辑（密码使用password_hash/password_verify）
  - 创建 `admin/session.php` 会话管理（检查登录状态、超时处理）
  - 登录成功后跳转到管理后台首页
  - 登录失败显示通用错误提示（不区分账号/密码错误）
  - 未登录访问任何管理页面自动重定向到登录页
- **Acceptance Criteria Addressed**: 隐藏路由登录系统
- **Test Requirements**:
  - `programmatic` TR-2.1: 正确账号密码登录成功，跳转到后台首页
  - `programmatic` TR-2.2: 错误账号密码登录失败，显示错误提示
  - `programmatic` TR-2.3: 未登录访问 `admin-x7k9m/index.php` 重定向到登录页
  - `programmatic` TR-2.4: 登录后关闭浏览器再打开，会话仍有效（或超时后需重新登录）

## [x] Task 3: 管理后台框架与导航
- **Priority**: P0
- **Depends On**: Task 2
- **Description**: 
  - 创建 `admin/index.php` 后台首页（仪表盘）
  - 创建 `admin/header.php` 公共头部（导航菜单、用户信息、退出按钮）
  - 创建 `admin/footer.php` 公共底部
  - 导航菜单：仪表盘、表单管理、律师管理
  - 响应式布局，使用纯CSS（不引入框架）
- **Acceptance Criteria Addressed**: 隐藏路由登录系统
- **Test Requirements**:
  - `programmatic` TR-3.1: 登录后显示仪表盘页面
  - `programmatic` TR-3.2: 导航菜单包含3个入口：仪表盘、表单管理、律师管理
  - `programmatic` TR-3.3: 点击退出按钮后跳转到登录页

## [x] Task 4: 表单提交API
- **Priority**: P0
- **Depends On**: Task 1
- **Description**: 
  - 创建 `api/submit.php` 表单提交端点
  - 实现蜜罐字段检测（隐藏字段被填写则丢弃）
  - 实现必填字段验证
  - 数据存储到 `data/submissions/{form_id}.json`（追加模式）
  - 每条记录包含：唯一ID、提交时间、表单ID、所有字段数据、IP地址
  - 修改前端HTML表单的action和JS，指向新的API端点
  - 保持前端表单UI不变
- **Acceptance Criteria Addressed**: 表单提交API
- **Test Requirements**:
  - `programmatic` TR-4.1: 提交表单后数据出现在对应的JSON文件中
  - `programmatic` TR-4.2: 蜜罐字段被填写时数据不被存储
  - `programmatic` TR-4.3: 必填字段缺失时返回验证错误
  - `programmatic` TR-4.4: 每条记录包含唯一ID和提交时间戳

## [x] Task 5: 表单管理界面
- **Priority**: P1
- **Depends On**: Task 3, Task 4
- **Description**: 
  - 创建 `admin/forms.php` 表单管理页面
  - 实现表单列表展示（表格形式，显示：提交时间、姓名、邮箱、表单类型、状态）
  - 实现搜索筛选（按关键词、表单类型、日期范围）
  - 实现分页（每页20条）
  - 实现单条/批量删除（二次确认弹窗）
  - 实现查看详情（点击记录展开完整字段）
  - 实现下载功能：下载全部CSV、下载勾选CSV
  - 实现刷新按钮
- **Acceptance Criteria Addressed**: 表单数据管理
- **Test Requirements**:
  - `programmatic` TR-5.1: 表单列表正确显示提交记录
  - `programmatic` TR-5.2: 搜索筛选功能正确过滤记录
  - `programmatic` TR-5.3: 分页功能正常，每页20条
  - `programmatic` TR-5.4: 删除记录后JSON文件同步更新
  - `programmatic` TR-5.5: 下载的CSV文件包含正确的数据
  - `programmatic` TR-5.6: 下载勾选数据只包含勾选的记录
  - `human-judgement` TR-5.7: 二次确认弹窗UI清晰友好

## [x] Task 6: 律师数据管理与JSON存储
- **Priority**: P0
- **Depends On**: Task 3
- **Description**: 
  - 创建 `admin/lawyers.php` 律师管理页面
  - 实现律师列表展示（姓名、职位、图片缩略图、操作按钮）
  - 实现新增律师表单（名称、图片URL或上传、简介）
  - 实现编辑律师（回填现有数据）
  - 实现删除律师（二次确认弹窗）
  - 数据读写 `data/lawyers.json`
  - 每位律师数据结构：`{id, name, slug, title, image, bio, created_at, updated_at}`
  - 图片上传保存到 `wp-content/uploads/lawyers/{slug}.{ext}`
  - 删除律师时同步删除上传的图片文件
- **Acceptance Criteria Addressed**: 律师CRUD管理, 图片上传与管理
- **Test Requirements**:
  - `programmatic` TR-6.1: 新增律师后 `data/lawyers.json` 包含新记录
  - `programmatic` TR-6.2: 编辑律师后JSON数据同步更新
  - `programmatic` TR-6.3: 删除律师后JSON中移除对应记录
  - `programmatic` TR-6.4: 上传图片保存到 `wp-content/uploads/lawyers/` 目录
  - `programmatic` TR-6.5: 删除律师后对应图片文件被删除
  - `programmatic` TR-6.6: 使用在线URL时图片不被下载
  - `human-judgement` TR-6.7: 新增/删除/编辑的二次确认弹窗清晰友好

## [x] Task 7: 律师个人页面模板化生成
- **Priority**: P0
- **Depends On**: Task 6
- **Description**: 
  - 创建 `admin/templates/lawyer_profile.tpl` 模板文件（基于tracy-moberg页面提取）
  - 模板占位符：`{{name}}`, `{{slug}}`, `{{image}}`, `{{bio}}`, `{{title}}`, `{{page_title}}`, `{{meta_description}}`, `{{json_ld}}`
  - 创建 `admin/generate_lawyer_page.php` 页面生成逻辑
  - 新增律师时：创建 `lawyers/{slug}/index.htm` 目录和文件
  - 编辑律师时：重新生成 `lawyers/{slug}/index.htm`
  - 删除律师时：删除 `lawyers/{slug}/` 整个目录
  - 生成的页面需包含完整的header/footer/popup结构
- **Acceptance Criteria Addressed**: 律师个人页面模板化生成
- **Test Requirements**:
  - `programmatic` TR-7.1: 新增律师后 `lawyers/{slug}/index.htm` 文件存在
  - `programmatic` TR-7.2: 生成的页面包含正确的律师姓名、图片、简介
  - `programmatic` TR-7.3: 生成的页面包含完整的header和footer
  - `programmatic` TR-7.4: 编辑律师后页面内容同步更新
  - `programmatic` TR-7.5: 删除律师后对应目录不存在
  - `human-judgement` TR-7.6: 生成的页面视觉效果与现有律师页面一致

## [x] Task 8: 律师列表页动态化
- **Priority**: P0
- **Depends On**: Task 7
- **Description**: 
  - 将 `lawyers/index.htm` 重命名为 `lawyers/index.php`
  - 在律师卡片网格区域，用PHP读取 `data/lawyers.json` 动态渲染卡片
  - 每个卡片包含：照片链接、姓名链接、职位描述
  - 卡片链接指向 `lawyers/{slug}/index.htm`
  - 保持现有的CSS样式和布局不变
  - 添加 `.htaccess` 规则：访问 `/lawyers/` 时优先加载 `index.php`
  - 确保网格布局自动适应律师数量
- **Acceptance Criteria Addressed**: 律师CRUD管理
- **Test Requirements**:
  - `programmatic` TR-8.1: 访问 `/lawyers/` 页面正确显示所有律师卡片
  - `programmatic` TR-8.2: 新增律师后列表页自动显示新卡片
  - `programmatic` TR-8.3: 删除律师后列表页自动移除对应卡片
  - `programmatic` TR-8.4: 点击卡片正确跳转到律师个人页面
  - `human-judgement` TR-8.5: 列表页视觉效果与原页面一致

## [x] Task 9: 前端表单改造
- **Priority**: P1
- **Depends On**: Task 4
- **Description**: 
  - 修改所有包含WPForms表单的HTML页面
  - 将表单action改为 `/api/submit.php`
  - 添加隐藏字段 `form_id` 标识表单类型
  - 修改JS提交逻辑，调用新API端点
  - 保持前端UI完全不变
  - 涉及页面：
    - `practice-areas/crypto-litigation/fraud-recovery/index.htm`
    - `practice-areas/crypto-litigation/2025-meta-crypto-scam-ads-investigation/index.htm`
    - `practice-areas/crypto-litigation/2025-coinbase-data-breach/index.htm`
    - `practice-areas/business-litigation/index.htm` 及子页面
    - `practice-areas/crypto-blockchain-business-transactions/index.htm`
    - `practice-areas/crypto-litigation/index.htm`
    - 所有律师个人页面（Disclaimer弹窗表单）
    - `lawyers/index.htm`（Disclaimer弹窗表单）
- **Acceptance Criteria Addressed**: 表单提交API
- **Test Requirements**:
  - `programmatic` TR-9.1: 所有表单的action指向 `/api/submit.php`
  - `programmatic` TR-9.2: 提交表单后数据正确存储到对应JSON文件
  - `programmatic` TR-9.3: 表单前端UI与修改前完全一致
  - `human-judgement` TR-9.4: 表单提交流程用户体验流畅

## [x] Task 10: 安全加固与最终测试
- **Priority**: P1
- **Depends On**: Task 2, Task 5, Task 6, Task 8, Task 9
- **Description**: 
  - CSRF防护（表单提交token验证）
  - XSS防护（输出转义）
  - 文件上传安全（类型检查、大小限制、重命名）
  - 目录遍历防护
  - 管理路径可配置（在config.php中修改）
  - 全流程集成测试
- **Acceptance Criteria Addressed**: 所有需求
- **Test Requirements**:
  - `programmatic` TR-10.1: CSRF token验证阻止非法表单提交
  - `programmatic` TR-10.2: XSS攻击向量被正确转义
  - `programmatic` TR-10.3: 上传非图片文件被拒绝
  - `programmatic` TR-10.4: 上传超过大小限制的文件被拒绝
  - `programmatic` TR-10.5: 修改config.php中的管理路径后，旧路径不可访问

# Task Dependencies
- Task 2 depends on Task 1
- Task 3 depends on Task 2
- Task 4 depends on Task 1
- Task 5 depends on Task 3, Task 4
- Task 6 depends on Task 3
- Task 7 depends on Task 6
- Task 8 depends on Task 7
- Task 9 depends on Task 4
- Task 10 depends on Task 2, Task 5, Task 6, Task 8, Task 9
