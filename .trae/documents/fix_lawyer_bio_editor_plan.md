# 修复律师后台编辑页面 Bio 编辑器问题

## 问题分析

用户反馈在后台修改律师职称后，Bio（描述）字段出现以下问题：
1. 输入字符会被立即删除（退格）
2. 空格正常，但字符输入异常
3. 打开编辑表单时，内容短暂显示后立即消失

## 根本原因

在 `admin-x7k9m/lawyers.php` 文件的 `showEditForm()` 函数中，直接操作 Quill 编辑器的 `root.innerHTML`：

```javascript
window.bioQuill.root.innerHTML = lawyer.bio || '';
```

这种方式违反了 Quill 的设计原则。Quill 使用内部 Delta 数据模型来管理内容，直接修改 DOM 会导致：
- 内部模型与 DOM 不同步
- 输入事件处理异常（Quill 认为内容被外部修改，会重置）
- 内容显示后被 Quill 内部机制清理

## 解决方案

使用 Quill 官方 API 来设置内容，具体有两种方式：

### 方案 A（推荐）：使用 clipboard.dangerouslyPasteHTML()

```javascript
if (window.bioQuill) {
    window.bioQuill.setContents([]); // 先清空
    window.bioQuill.clipboard.dangerouslyPasteHTML(0, lawyer.bio || '');
}
```

### 方案 B：使用 setContents()（需要转换 HTML 为 Delta）

需要额外的转换步骤，不推荐用于 HTML 内容。

## 修改计划

1. 修改 `lawyers.php` 文件中的 `showEditForm()` 函数（第367-368行）
2. 将直接操作 `root.innerHTML` 改为使用 Quill API

## 风险评估

- 低风险：仅修改内容设置方式，不影响其他功能
- 向后兼容：使用 Quill 官方 API，保证兼容性

## 验证步骤

1. 打开后台律师管理页面
2. 点击编辑按钮打开编辑表单
3. 验证 Bio 内容正确显示
4. 尝试输入字符，验证输入正常
5. 保存修改，验证数据正确保存