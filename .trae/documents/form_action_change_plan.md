# 表单跳转修改计划

## 需求分析

用户要求表单提交后直接跳转到 `/contact/index.htm` 页面，不需要创建 PHP 处理页面，表单数据不需要后端处理。

### 当前状态

检查了 5 个表单文件，发现它们的 `action` 已经是 `/contact/`：

| 文件路径 | 表单ID | 当前action |
|---------|---------|-----------|
| [podcast/index.htm](file:///d:\JZ\bergpc.com\bergpc.com\podcast\index.htm#L5899-5994) | 3778 | `/contact/` |
| [lawyers/index.php](file:///d:\JZ\bergpc.com\bergpc.com\lawyers\index.php#L5749-5845) | 3778 | `/contact/` |
| [lawyers/index.htm](file:///d:\JZ\bergpc.com\bergpc.com\lawyers\index.htm#L5786-5882) | 3778 | `/contact/` |
| [index.htm](file:///d:\JZ\bergpc.com\bergpc.com\index.htm#L6070-6167) | 3778 | `/contact/` |
| [testimonials/index.htm](file:///d:\JZ\bergpc.com\bergpc.com\testimonials\index.htm#L6709-6807) | 3778 | `/contact/` |

## 修改方案

由于用户要求直接跳转而不需要处理数据，当前的 action `/contact/` 已经可以工作（服务器会自动找到 index.htm）。

**需要确认的修改：**
1. 将 action 从 `/contact/` 改为 `/contact/index.htm`（可选，但更明确）
2. 确保表单验证在前端完成

## 修改步骤

1. 更新所有表单的 action 属性为 `/contact/index.htm`
2. 确保前端验证脚本存在

## 涉及文件

1. `podcast/index.htm` - 修改表单 action
2. `lawyers/index.php` - 修改表单 action
3. `lawyers/index.htm` - 修改表单 action
4. `index.htm` - 修改表单 action
5. `testimonials/index.htm` - 修改表单 action
