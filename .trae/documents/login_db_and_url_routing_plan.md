# 登录数据库化 + URL 路由优化方案

## 一、登录逻辑改为数据库验证

### 现状分析
- 当前用户名和密码硬编码在 `admin/config.php` 中
- 密码使用 `password_hash()` 加密存储（bcrypt）
- `admin/session.php` 的 `login()` 函数直接比对常量

### 修改方案

#### 1. 创建 `admin_users` 表（生成 SQL 文件）

```sql
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO admin_users (username, password_hash) VALUES
('bergpc_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
```

#### 2. 修改 `admin/session.php` 的 `login()` 函数

- 从数据库查询用户名匹配的记录
- 使用 `password_verify()` 验证密码哈希
- 数据库连接失败时回退到常量验证（向后兼容）

#### 3. 修改 `admin/config.php`

- 移除 `ADMIN_USERNAME` 和 `ADMIN_PASSWORD_HASH` 常量
- 保留注释说明用户信息已迁移到数据库

#### 4. 生成 `database/add_admin_users.sql`

- 用户自行导入到 phpMyAdmin

### 安全增强
- 登录失败时统一返回 "Invalid credentials"，不区分用户名错误还是密码错误
- 保持现有的 session 安全机制（超时、CSRF、regenerate_id）

---

## 二、URL 路由优化（去除 index.htm 后缀）

### 现状分析
- 当前 URL 格式：`https://bergppc.com/lawyers/index.htm`
- 期望 URL 格式：`https://bergppc.com/lawyers/`
- GoDaddy 共享主机使用 nginx 前端代理 + Apache 后端
- `.htaccess` 已在根目录，但缺少 URL 重写规则去除 `index.htm`
- HTML 文件中有约 480+ 处链接包含 `index.htm` 后缀

### 修改方案

#### 方案选择：双层策略

**层1：`.htaccess` 重写规则（服务端）**
在根目录 `.htaccess` 中添加规则，将带 `index.htm` 的 URL 301 重定向到干净 URL：

```apache
# Remove index.htm from URLs
RewriteCond %{THE_REQUEST} /index\.htm\s [NC]
RewriteRule ^(.*)index\.htm$ /$1 [R=301,L]
```

**层2：HTML 链接替换（客户端）**
将所有 HTML 文件中的 `index.htm` 链接替换为目录路径：
- `href="../../index.htm"` → `href="../../"`
- `href="index.htm"` → `href="./"`
- `href="/lawyers/index.htm"` → `href="/lawyers/"`

> 注意：需要排除 CSS/JS 资源引用中的 `index.htm`（实际上不存在这种情况，因为资源文件不会命名为 index.htm）

#### 特殊处理
- `<link rel="canonical">` 中的 `index.htm` 也需要替换
- `<link rel="alternate">` RSS feed 链接中的 `index.htm` 需要替换
- `<link rel="shortlink">` 中的 `index.htm` 需要替换

#### 关于 `.htaccess` 是否生效
- GoDaddy 共享主机使用 Apache 处理 PHP 请求，`.htaccess` 对 PHP 文件有效
- nginx 代理层可能不处理 `.htaccess` 的 RewriteRule
- 但 HTML 链接替换是客户端层面的，不依赖 `.htaccess`
- 双层策略确保：即使 `.htaccess` 不生效，用户点击链接也是干净 URL

---

## 三、需要修改的文件清单

| 文件 | 修改内容 |
|------|----------|
| `admin/session.php` | `login()` 函数改为数据库查询 |
| `admin/config.php` | 移除硬编码的用户名密码常量 |
| `database/add_admin_users.sql` | 新建 SQL 文件 |
| `.htaccess` | 添加 index.htm 重定向规则 |
| 所有 `.htm` 文件（约 10+ 个） | 替换链接中的 index.htm |

## 四、风险与注意事项

1. **数据库连接失败**：`login()` 函数保留常量回退机制，确保数据库不可用时仍可登录
2. **301 重定向缓存**：浏览器会缓存 301 重定向，测试时可用 302 临时重定向
3. **SEO 影响**：301 重定向对 SEO 友好，不会丢失页面权重
4. **链接替换范围**：只替换 `href` 和 `src` 属性中的 `index.htm`，不替换 JS 字符串中的内容
