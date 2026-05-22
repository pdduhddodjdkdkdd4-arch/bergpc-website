# 富文本编辑器改造计划 - Lawyers Bio 字段

## 现状分析

### 1. 管理后台 (`admin-x7k9m/lawyers.php`)
- Bio 输入使用普通 `<textarea>` (第 311 行)
- 提交时直接存储 `$_POST['bio']` 到数据库

### 2. 静态页面生成 (`admin-x7k9m/generate_lawyer_page.php`)
- 第 27 行: `$bioHtml = '<p>' . nl2br(htmlspecialchars($lawyer['bio'])) . '</p>';`
- 将纯文本 bio 转换为 HTML 段落

### 3. 动态页面显示 (`lawyers/profile.php`)
- 第 66 行: `$bioHtml = '<p>' . nl2br(htmlspecialchars($lawyer['bio'])) . '</p>';`
- 与静态生成逻辑相同

### 4. 模板文件 (`admin-x7k9m/templates/lawyer_profile.tpl`)
- 第 5375 行: `{{LAWYER_BIO}}` 占位符

---

## 改造方案

### 选用 Quill.js 富文本编辑器
- 轻量级、易集成
- 支持图片上传、链接
- 可禁用视频和表情
- 开源免费

### 修改文件清单

#### 1. `admin-x7k9m/header.php`
- 在 `</head>` 前添加 Quill.js CSS 和 JS 引用
- Quill CDN: `https://cdn.quilljs.com/1.3.7/quill.snow.css` 和 `quill.snow.js`

#### 2. `admin-x7k9m/lawyers.php`
- 将 `<textarea name="bio" id="lawyerBio" required></textarea>` 替换为:
  ```html
  <div id="bioEditor" style="height: 200px;"></div>
  <input type="hidden" name="bio" id="lawyerBio">
  ```
- 添加 Quill 初始化 JS 代码
- 配置 Quill: 启用链接和图片，禁用视频和表情
- 添加图片上传处理器 (上传到 `wp-content/uploads/lawyers/` 目录)

#### 3. `admin-x7k9m/generate_lawyer_page.php`
- 修改第 27 行: 直接使用 `$lawyer['bio']` 而非转换
  ```php
  $bioHtml = $lawyer['bio'];  // bio 已经是 HTML 格式
  ```

#### 4. `lawyers/profile.php`
- 修改第 66 行: 同上
  ```php
  $bioHtml = $lawyer['bio'];  // bio 已经是 HTML 格式
  ```

---

## 详细实现

### Step 1: 在 header.php 添加 Quill 引用
```html
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.js"></script>
```

### Step 2: 修改 lawyers.php 表单
将:
```html
<div class="form-group">
    <label>Bio *</label>
    <textarea name="bio" id="lawyerBio" required></textarea>
</div>
```

改为:
```html
<div class="form-group">
    <label>Bio *</label>
    <div id="bioEditor"></div>
    <input type="hidden" name="bio" id="lawyerBio" required>
</div>
```

### Step 3: 添加 Quill 初始化 JS
```javascript
document.addEventListener('DOMContentLoaded', function() {
    var quill = new Quill('#bioEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                ['link'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['image'],
                ['clean']
            ]
        }
    });

    // Sync content to hidden input before submit
    var form = document.getElementById('lawyerForm');
    form.onsubmit = function() {
        document.getElementById('lawyerBio').value = quill.root.innerHTML;
    };

    // Load existing content in edit mode
    var existingBio = document.getElementById('lawyerBio').value;
    if (existingBio) {
        quill.root.innerHTML = existingBio;
    }
});
```

### Step 4: 图片上传处理
在 Quill 的 image handler 中:
1. 将图片转为 base64 或
2. 通过 XHR 上传到服务器保存到 `wp-content/uploads/lawyers/`
3. 返回图片 URL

采用方案2，保持与现有图片上传逻辑一致。

### Step 5: 更新 display 代码
- `generate_lawyer_page.php` 第 27 行
- `lawyers/profile.php` 第 66 行

---

## 验证步骤

1. 访问 `admin-x7k9m/lawyers.php`
2. 点击 "Add Lawyer" 或 "Edit"
3. 验证 Bio 字段显示为富文本编辑器
4. 测试: 加粗、斜体、链接、图片上传
5. 保存后验证展示页面正确显示富文本内容
6. 验证编辑时能正确回显之前保存的富文本内容

---

## 注意事项

1. **XSS 防护**: 富文本内容存储后，展示时需要考虑 XSS。当前方案在 admin 端使用 Quill 编辑，输出时仍然使用 `htmlspecialchars()` 的问题需要处理。由于 bio 字段本身是用户输入的富文本，展示时需要更谨慎处理。

   **解决方案**: 在模板中使用 `{{{LAWYER_BIO}}}` (三重大括号) 或在 PHP 中使用 `htmlspecialchars_decode()` 后再输出，但需要确保内容来源可信。

2. **向后兼容**: 已有的纯文本 bio 数据需要能正常显示

3. **图片路径**: 上传的图片使用相对路径，与现有 lawyer 图片逻辑保持一致
