* [x] Checkpoint 1: 管理后台隐藏入口生效 - 访问 /admin/ 返回404，访问隐藏路径显示登录页

* [x] Checkpoint 2: 登录认证正确工作 - 正确密码登录成功，错误密码登录失败，未登录重定向

* [x] Checkpoint 3: 管理后台导航完整 - 包含仪表盘、表单管理、律师管理三个入口

* [x] Checkpoint 4: 表单提交API可用 - 提交数据正确存储到JSON文件，蜜罐字段触发丢弃

* [x] Checkpoint 5: 表单管理界面功能完整 - 列表、筛选、分页、删除、下载全部/勾选、刷新

* [x] Checkpoint 6: 表单删除有二次确认 - 删除操作弹出确认对话框

* [x] Checkpoint 7: 律师CRUD功能完整 - 新增、编辑、删除律师数据正确写入JSON

* [x] Checkpoint 8: 律师图片上传与删除正确 - 上传图片保存到指定目录，删除律师时图片被清理

* [x] Checkpoint 9: 律师新增/删除/编辑有二次确认 - 操作前弹出确认对话框

* [x] Checkpoint 10: 律师个人页面模板化生成 - 新增律师后生成正确的个人页面，编辑后更新，删除后移除

* [ ] Checkpoint 11: 生成的律师页面视觉一致 - 与现有律师页面（如tracy-moberg）视觉效果一致（需部署后人工验证）

* [x] Checkpoint 12: 律师列表页动态化 - lawyers/index.php正确读取JSON渲染卡片，增删律师后列表自动更新

* [x] Checkpoint 13: 前端表单改造完成 - 所有WPForms表单action指向新API，UI不变

* [x] Checkpoint 14: CSRF防护生效 - 非法表单提交被阻止

* [x] Checkpoint 15: XSS防护生效 - 输出内容正确转义

* [x] Checkpoint 16: 文件上传安全 - 非图片文件被拒绝，超大文件被拒绝

* [x] Checkpoint 17: data目录不可直接访问 - 通过URL访问data/返回403

* [x] Checkpoint 18: 管理路径可配置 - 修改config后旧路径不可访问，新路径可访问

