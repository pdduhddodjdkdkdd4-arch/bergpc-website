# 修复登录问题 + 删除表单问题

## 问题一：登录返回 Invalid credentials

### 根因分析
`database/add_admin_users.sql` 中的密码哈希是错误的：

```sql
INSERT INTO admin_users (username, password_hash) VALUES
('bergpc_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
```

这个哈希 `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi` 是字符串 `"password"` 的 bcrypt 哈希，**不是** `"admin123"` 的哈希。

### 修复方案
1. 生成 `admin123` 的正确 bcrypt 哈希
2. 更新 `database/add_admin_users.sql` 中的哈希值
3. 生成一个 `database/fix_admin_password.sql` 供用户在 phpMyAdmin 中执行更新

### 正确的哈希值
`admin123` 的 bcrypt 哈希：`$2y$10$YourCorrectHashHere`

（由于本地没有 PHP 环境，需要在 SQL 文件中使用 PHP 在线工具或 MySQL 的方式生成）

### 备选方案
在 `admin/session.php` 的 `login()` 函数中，数据库验证失败时回退到常量验证。但 `config.php` 中已经移除了 `ADMIN_USERNAME` 和 `ADMIN_PASSWORD_HASH` 常量，所以回退机制也失效了。

**最简方案**：在 `config.php` 中恢复回退常量，同时在 SQL 中修复哈希值。

---

## 问题二：删除表单中的 "paid advertisement" 问题

### 影响范围
只有 1 个页面包含这个问题：
- `practice-areas/crypto-litigation/fraud-recovery/index.htm`

### 需要删除的内容
1. **field_28**（radio）：`Did you find the scammers through any paid advertisement or sponsored social media post?` + Yes/No 选项
2. **field_29**（textarea，条件显示）：`If so, on what platform did you click on the ad?`（当 field_28 选择 Yes 时显示）

### 需要修改的文件
| 文件 | 修改内容 |
|------|----------|
| `practice-areas/crypto-litigation/fraud-recovery/index.htm` | 删除 field_28 和 field_29 的 HTML |

### SQL 是否需要更新？
**不需要**。`form_submissions` 表存储的是 JSON 格式的提交数据，表单字段变更不影响表结构。已提交的历史数据中如果包含 field_28/29 的数据，它们仍然会正常显示。

### 管理页面是否需要更新？
**不需要**。管理页面（`admin/forms.php`）是动态读取 `form_submissions` 表中的 JSON 数据并展示的，不依赖固定的字段定义。

### JS 条件逻辑
页面中可能有 WPForms 的条件逻辑 JS 配置引用了 field_28/29，删除 HTML 后需要同步清理 JS 配置，否则可能导致 JS 报错。

---

## 修改文件清单

| 文件 | 修改内容 |
|------|----------|
| `database/add_admin_users.sql` | 修复密码哈希为 admin123 的正确哈希 |
| `database/fix_admin_password.sql` | 新建，供用户在 phpMyAdmin 中更新密码 |
| `admin/config.php` | 恢复 ADMIN_USERNAME 和 ADMIN_PASSWORD_HASH 常量作为回退 |
| `practice-areas/crypto-litigation/fraud-recovery/index.htm` | 删除 field_28 和 field_29 的 HTML + 清理 JS 条件逻辑 |
