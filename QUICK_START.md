# 🚀 快速解决查询参数问题

## 问题
`admin-x7k9m/` 带查询参数访问失败

## 解决方案：复制 admin/ → admin-x7k9m/

### 只需 3 步！

---

## 📋 第一步：上传文件

确保以下文件已上传到服务器：

| 文件 | 位置 |
|------|------|
| `admin/forms.php` | 已简化 |
| `admin/forms-api.php` | API |
| `admin/simple-test.php` | 测试页面 |
| `admin/fix_directory.php` | 复制工具 |
| `admin/config.php` | 已更新 |
| `.htaccess.simple` | 根目录 |

---

## 🔧 第二步：运行复制工具

在浏览器中访问：
```
https://bergpcc.com/admin-x7k9m/fix_directory.php
```

这个工具会自动将 `admin/` 复制为 `admin-x7k9m/`

---

## ⚙️ 第三步：更新 .htaccess

将根目录的 `.htaccess` 替换为 `.htaccess.simple` 的内容

---

## ✅ 完成！

测试：
1. 访问 `https://bergpcc.com/admin-x7k9m/simple-test.php?test=1&param=hello` - 应该正常显示
2. 访问 `https://bergpcc.com/admin-x7k9m/forms.php` - AG Grid 表格应该正常工作

---

## 📁 目录结构（最终）

```
bergpcc.com/
├── admin/              ← 保留，供 api/submit.php 引用
├── admin-x7k9m/        ← 新的，用于访问
└── .htaccess           ← 简化版
```

---

## 💡 为什么这样有效？

- admin/ 内部文件都用 `__DIR__` 相对路径，复制后自动工作
- 保留原 admin/ 供 api/submit.php 引用
- 完全避免 mod_rewrite 查询参数问题！
