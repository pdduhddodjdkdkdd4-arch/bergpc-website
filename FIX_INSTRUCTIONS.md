# 修复查询参数问题 - 最简单方案

## 问题
`admin-x7k9m/` URL 带查询参数访问失败，因为 Apache mod_rewrite 与查询参数一起工作不可靠。

## 解决方案

### 方案 A：直接复制目录（推荐，最可靠）⭐⭐⭐

在服务器上执行以下操作：

```bash
# 进入网站根目录
cd /home/yo8jscsedlal/public_html/bergppc.com

# 复制 admin 目录为 admin-x7k9m
cp -r admin admin-x7k9m

# (可选) 确认已复制
ls -la
```

### 方案 B：重命名（如果不想保留原 admin 目录）

```bash
cd /home/yo8jscsedlal/public_html/bergppc.com
mv admin admin-x7k9m
```

### 修改 .htaccess（如果使用方案 A 或 B）

如果选择方案 A 或 B，请更新 `.htaccess`，移除路由规则：

```apache
# 只保留安全规则，移除 admin-x7k9m 路由
RewriteEngine On

# Remove index.htm from URLs (301 redirect to clean URL)
RewriteCond %{THE_REQUEST} /index\.htm\s [NC]
RewriteRule ^(.*)index\.htm$ /$1 [R=301,L]

# Protect data directory - no direct access
RewriteRule ^data/ - [F,L]

# Protect admin templates
RewriteRule ^admin/templates/ - [F,L]

# Block common admin paths (return 404)
RewriteRule ^admin/?$ - [R=404,L]
RewriteRule ^wp-admin/$ - [R=404,L]
RewriteRule ^administrator/?$ - [R=404,L]

# Allow wp-admin/admin-ajax.php for WPForms token
RewriteCond %{REQUEST_URI} !^/wp-admin/admin-ajax\.php$
RewriteRule ^wp-admin/ - [R=404,L]

# API routes
RewriteRule ^api/submit\.php$ api/submit.php [L]

# Lawyers: dynamic profile routing
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^lawyers/([a-z0-9-]+)/?$ lawyers/profile.php?slug=$1 [L,QSA]

DirectoryIndex index.php index.htm index.html
```

---

## 快速开始（最简单的方式）

如果你有 cPanel 或文件管理器：

1. 打开文件管理器，进入 `public_html/bergppc.com/`
2. 右键点击 `admin` 文件夹 → 复制
3. 粘贴并重命名为 `admin-x7k9m`
4. 更新 `.htaccess`，移除 admin 路由规则

完成后访问:
- `https://bergppc.com/admin-x7k9m/simple-test.php?test=1&param=hello`
- `https://bergppc.com/admin-x7k9m/forms.php`

---

## 需要上传的文件（在操作前）

先确保上传这些文件：
- admin/forms.php
- admin/forms-api.php
- admin/simple-test.php
- admin/config.php (已更新)

然后再执行目录复制操作！
