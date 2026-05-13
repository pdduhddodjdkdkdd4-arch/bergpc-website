# Berg PC 管理后台 - 产品需求规格文档

## Why

当前网站是WordPress静态导出站点，所有表单无法提交（缺少PHP后端），律师信息修改需要手动编辑HTML文件。需要一个轻量级管理后台来：1) 让表单真正可用并管理提交数据；2) 通过界面化管理律师信息而非手动改HTML；3) 保护管理入口不被公开访问。

## What Changes

* 新增PHP管理后台（隐藏路由入口 + 登录验证）

* 新增表单提交API（替代WPForms的PHP后端）+ 表单数据管理界面

* 新增律师CRUD管理界面 + 律师数据JSON存储 + 动态页面生成

* **BREAKING**: 表单提交方式从WPForms AJAX改为自定义PHP API

## Impact

* Affected code: 所有包含WPForms表单的HTML页面（约8个页面）

* Affected data: 新增 `data/` 目录存储JSON数据

* Affected structure: 新增 `admin/` 目录作为管理后台

## ADDED Requirements

### Requirement: 隐藏路由登录系统

系统 SHALL 提供一个通过非公开URL路径访问的管理后台入口，并要求账号密码验证后才能进入。

#### Scenario: 访问隐藏入口

* **WHEN** 用户访问非公开的管理后台URL（如 `/admin-x7k9m/`）

* **THEN** 显示登录页面

#### Scenario: 正确登录

* **WHEN** 用户输入正确的账号密码

* **THEN** 跳转到管理后台首页，创建会话

#### Scenario: 错误登录

* **WHEN** 用户输入错误的账号密码

* **THEN** 显示错误提示，不泄露具体是账号还是密码错误

#### Scenario: 直接访问常规路径

* **WHEN** 用户访问 `/admin/` 或其他常见管理路径

* **THEN** 返回404或重定向到首页，不暴露管理后台存在

#### Scenario: 未登录访问管理页面

* **WHEN** 未登录用户直接访问管理后台的任何页面

* **THEN** 重定向到登录页面

### Requirement: 表单提交API

系统 SHALL 提供PHP API端点接收前端表单提交，将数据存储到JSON文件中。

#### Scenario: 表单提交成功

* **WHEN** 用户在网站前端提交表单

* **THEN** 数据被存储到 `data/submissions/{form_id}.json`，返回成功响应

#### Scenario: 蜜罐字段触发

* **WHEN** 提交的表单中蜜罐字段被填写

* **THEN** 静默丢弃该提交，不存储，返回成功响应（不告知机器人）

#### Scenario: 必填字段验证

* **WHEN** 提交的表单缺少必填字段

* **THEN** 返回验证错误信息

### Requirement: 表单数据管理

系统 SHALL 提供表单提交数据的列表查看、筛选、删除、下载功能。

#### Scenario: 查看表单列表

* **WHEN** 管理员进入表单管理页面

* **THEN** 显示所有表单提交记录，按提交时间倒序排列

#### Scenario: 筛选查询

* **WHEN** 管理员输入搜索关键词或选择筛选条件

* **THEN** 列表只显示匹配的记录

#### Scenario: 删除记录

* **WHEN** 管理员选择一条或多条记录并确认删除

* **THEN** 显示二次确认弹窗，确认后从JSON文件中移除对应记录

#### Scenario: 下载全部数据

* **WHEN** 管理员点击"下载全部"

* **THEN** 生成包含所有提交记录的CSV文件并下载

#### Scenario: 下载勾选数据

* **WHEN** 管理员勾选部分记录后点击"下载勾选"

* **THEN** 生成仅包含勾选记录的CSV文件并下载

#### Scenario: 分页浏览

* **WHEN** 提交记录超过每页显示数量（默认20条）

* **THEN** 显示分页控件，支持翻页

#### Scenario: 刷新数据

* **WHEN** 管理员点击刷新按钮

* **THEN** 重新加载最新数据

### Requirement: 律师CRUD管理

系统 SHALL 提供律师信息的增删改查功能，数据存储在JSON文件中。

#### Scenario: 新增律师

* **WHEN** 管理员填写律师名称、图片URL或上传图片、律师简介并提交

* **THEN** 显示二次确认弹窗，确认后：1) 数据写入 `data/lawyers.json`；2) 基于模板生成律师个人页面 `lawyers/{slug}/index.htm`；3) 更新 `lawyers/index.htm` 律师列表添加新卡片

#### Scenario: 编辑律师

* **WHEN** 管理员修改律师信息并提交

* **THEN** 显示二次确认弹窗，确认后更新JSON数据、重新生成律师个人页面、更新列表页

#### Scenario: 删除律师

* **WHEN** 管理员选择删除律师并确认

* **THEN** 显示二次确认弹窗，确认后：1) 从JSON中移除；2) 删除律师个人页面目录；3) 更新列表页移除卡片；4) 删除该律师上传的图片文件

#### Scenario: 查看律师列表

* **WHEN** 管理员进入律师管理页面

* **THEN** 显示所有律师的列表，包含姓名、职位、图片缩略图

### Requirement: 律师个人页面模板化生成

系统 SHALL 使用模板文件生成律师个人展示页面。

#### Scenario: 基于模板生成页面

* **WHEN** 新增或编辑律师时

* **THEN** 使用 `admin/templates/lawyer_profile.tpl` 模板，替换占位符（`{{name}}`, `{{image}}`, `{{bio}}`, `{{slug}}`等），生成完整的HTML页面

#### Scenario: 点击律师卡片跳转

* **WHEN** 用户在律师列表页点击律师卡片

* **THEN** 跳转到该律师的个人展示页面

### Requirement: 图片上传与管理

系统 SHALL 支持律师图片的上传和删除。

#### Scenario: 上传图片

* **WHEN** 管理员上传律师图片

* **THEN** 图片保存到 `wp-content/uploads/lawyers/{slug}.jpg`，返回相对路径

#### Scenario: 使用在线图片URL

* **WHEN** 管理员填写在线图片URL而非上传

* **THEN** 直接使用该URL作为图片源，不下载到本地

#### Scenario: 删除律师时清理图片

* **WHEN** 删除律师且该律师的图片是本地上传的

* **THEN** 删除对应的图片文件

## MODIFIED Requirements

### Requirement: 前端表单提交方式

原WPForms AJAX提交方式需改为调用自定义PHP API。

* 原方式：`POST /wp-admin/admin-ajax.php` (WPForms处理)

* 新方式：`POST /api/submit.php` (自定义PHP处理)

## REMOVED Requirements

### Requirement: WPForms后端依赖

**Reason**: 静态站点无PHP后端，WPForms无法工作
**Migration**: 使用自定义PHP API替代WPForms AJAX端点

## 技术方案分析

### 共享主机兼容性

* **PHP**: 共享主机普遍支持PHP 7.4+，本方案使用纯PHP，无需框架

* **文件读写**: 共享主机允许PHP读写文件，JSON文件存储可行

* **Session**: PHP Session在共享主机上可用

* **文件上传**: 共享主机通常允许文件上传（需检查upload\_max\_filesize）

* **.htaccess**: Apache共享主机支持URL重写

### 数据存储方案选择

| 格式       | 读取              | 写入              | 查询               | 筛选 | 推荐     |
| -------- | --------------- | --------------- | ---------------- | -- | ------ |
| JSON     | ✅ `json_decode` | ✅ `json_encode` | ✅ `array_filter` | ✅  | **推荐** |
| CSV      | ✅ `fgetcsv`     | ⚠️ 复杂           | ⚠️ 需逐行           | ⚠️ | 不推荐    |
| Markdown | ⚠️ 需解析          | ⚠️ 需序列化         | ❌                | ❌  | 不推荐    |

**结论：使用JSON格式存储**，PHP原生支持，读写方便，支持筛选查询。

### 律师列表页动态方案

| 方案          | 原理                                     | 共享主机兼容 | 复杂度 | 推荐     |
| ----------- | -------------------------------------- | ------ | --- | ------ |
| **PHP模板渲染** | 将lawyers/index.htm改为index.php，读取JSON渲染 | ✅      | 低   | **推荐** |
| JS动态加载      | 用fetch读取JSON，JS动态生成HTML                | ✅      | 中   | 备选     |
| 脚本修改HTML    | 每次CRUD操作时用PHP脚本修改index.htm             | ✅      | 高   | 不推荐    |

**结论：将lawyers/index.htm改为lawyers/index.php**，PHP直接读取 `data/lawyers.json` 渲染律师卡片列表。律师个人页面也使用PHP模板。

### 现有表单字段清单

| 表单                             | 主要字段                                                       |
| ------------------------------ | ---------------------------------------------------------- |
| Crypto Fraud & Recovery (2528) | 姓名、邮箱、电话、地址、职业、案件描述、交互人姓名、转账金额、交易方式、银行/平台、最后交易日期、附加信息、免责声明 |
| Meta Crypto Scam Ads (4147)    | 同上 + 通讯应用、首次/最后交互日期、发现诈骗日期                                 |
| Coinbase Data Breach (3952)    | 姓名、邮箱、电话、地址、职业、案件描述、附加信息、免责声明                              |
| Business Litigation (4296)     | 姓名、案件描述、邮箱、电话、地址、附加信息、免责声明                                 |
| Crypto Business (4002)         | 同Business Litigation                                       |
| Crypto Litigation (4275)       | 同Business Litigation                                       |
| vCard/Disclaimer (3600)        | 姓名、免责声明                                                    |
| 律师列表Disclaimer (3778)          | 姓名、免责声明                                                    |

## Constraints

* **Technical**: 共享主机虚拟服务器，仅支持PHP + Apache，无数据库，无Node.js

* **Business**: 管理后台需轻量级，不引入重型框架

* **Dependencies**: PHP 7.4+, Apache + .htaccess, 文件读写权限

## Assumptions

* 共享主机支持PHP 7.4及以上版本

* 共享主机允许PHP Session

* 共享主机允许文件上传（至少2MB）

* 共享主机支持.htaccess URL重写

* 网站将部署在Apache服务器上

## Open Questions

* [ ] 共享主机的PHP版本是多少？

  我不知道有没有安装这个，使用的是Godaddy的共享主机，使用的是cpanel管理，我需要怎么操作？

* [ ] 共享主机的upload\_max\_filesize限制是多少？

  我不知道

* [x] 管理后台的登录账号密码是否需要支持多用户？

* [ ] 表单提交后是否需要发送邮件通知？

  不需要

* [x] 律师页面是否需要SEO meta标签自动生成？

