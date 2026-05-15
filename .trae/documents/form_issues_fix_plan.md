# Forms 问题修复计划

## 问题分析

### 1. 删除功能报错
**问题**：`forms.php:2085 Uncaught TypeError: Cannot set properties of null (setting 'value')`

**原因**：代码中还在尝试设置已删除的 `deleteIds` input 元素的值

### 2. 数据统计不一致
**问题**：删除数据后，`index.php` 和 `forms.php` 还在统计旧表 `form_submissions` 的数据

**原因**：`index.php` 和 `forms.php` 还在使用旧表查询数据

### 3. 查询/筛选功能问题
**问题**：点击 Filter 按钮后页面刷新，但查询结果不对，URL 参数没有起到作用

**原因**：
- 服务端代码还在处理旧表查询
- 我们需要让 AG Grid 的筛选生效，而不是通过 URL 参数

### 4. 悬浮提示框样式问题
**问题**：表格悬浮提示框背景白色，字体也是白色系，看不清

**原因**：缺少 AG Grid 提示框的样式配置

---

## 修复计划

### 任务 1：修复删除功能报错
**文件**：`admin/forms.php`、`admin-x7k9m/forms.php`

**步骤**：
- 移除对已删除的 `deleteIds` input 元素的引用
- 清理 deleteSingle 和 deleteSelected 中不需要的代码

---

### 任务 2：统一数据统计使用新表
**文件**：`admin/index.php`、`admin/forms.php`、`admin-x7k9m/forms.php`、`admin-x7k9m/index.php`

**步骤**：
- 修改 `index.php` 查询 `form_submissions_new` 表获取统计和最近数据
- 修改 `forms.php` 顶部的统计数据使用新表
- 查询数据时需要关联 `form_field_values` 表获取姓名和邮箱字段

---

### 任务 3：修复查询/筛选功能
**文件**：`admin/forms.php`、`admin-x7k9m/forms.php`

**步骤**：
- 移除 filter 表单的 submit 行为，不刷新页面
- 让搜索输入框直接在 AG Grid 中筛选
- 让 form_id 下拉框选择后直接筛选
- 使用 AG Grid 的 filterModel 或 api.setFilterModel

---

### 任务 4：修复悬浮提示框样式
**文件**：`admin/forms.php`、`admin-x7k9m/forms.php`

**步骤**：
- 添加 AG Grid tooltip 的样式
- 确保提示框字体颜色为深色，背景为浅色

---

## 修改的文件清单
1. `admin/index.php` - 更新统计查询为新表
2. `admin/forms.php` - 修复删除、查询、样式
3. `admin-x7k9m/index.php` - 同步修改
4. `admin-x7k9m/forms.php` - 同步修改
