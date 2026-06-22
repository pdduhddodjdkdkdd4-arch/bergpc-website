# Messenger 自动跳转与后台设置功能 - 检查清单

## 数据库
- [ ] `site_settings` 表已创建
- [ ] 初始 Messenger 配置数据已插入

## API
- [ ] `api/settings.php` GET 端点返回正确的 JSON 格式
- [ ] API 包含 `pageId` 和 `enabled` 字段
- [ ] POST 方法正确保存配置到数据库

## 后台设置页面
- [ ] `/admin-x7k9m/settings.php` 页面可访问
- [ ] Messenger Page ID 输入框显示当前值
- [ ] 启用/禁用开关功能正常
- [ ] 保存按钮正确调用 API
- [ ] 保存成功后显示提示信息
- [ ] 后台导航菜单包含 Settings 链接

## MessengerManager 类
- [ ] `loadSettings()` 方法正确从 API 加载配置
- [ ] `DEFAULT_PAGE_ID` 使用 API 返回的值
- [ ] `enabled` 状态正确控制跳转行为
- [ ] `buildMessage()` 方法生成"问题：答案"格式文本

## 表单数据缓存
- [ ] 表单提交前数据缓存到 localStorage
- [ ] localStorage key 为 `formData`
- [ ] 数据格式为 `{ fieldLabel: fieldValue }`
- [ ] 字段标签使用友好名称
- [ ] 每次提交覆盖旧数据

## 感谢页面
- [ ] 感谢页面加载时从 localStorage 读取数据
- [ ] 自动调用 `messengerManager.openMessenger()`
- [ ] Messenger 打开后预填充正确文本
- [ ] 禁用状态时不自动跳转

## 整体流程测试
- [ ] 后台修改 Page ID 后前端使用新值
- [ ] 表单提交 → 缓存 → 感谢页面 → Messenger 跳转完整流程正常
- [ ] iOS 设备跳转 Messenger App 正常
- [ ] Android 设备跳转 Messenger App 正常
- [ ] Web 浏览器打开 Messenger 网页版正常