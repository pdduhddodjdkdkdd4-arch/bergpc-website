# In-Depth Analysis and Fix Plan for Delete Button Error

## Repository Research Conclusion

我找到了问题的根源！对比 Edit 按钮（正常工作）和 Delete 按钮（报错）的实现差异：

**Edit 按钮（Line 252） - WORKING**
```php
<button onclick='showEditForm(<?php echo json_encode($lawyer, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>)' class="btn btn-secondary btn-sm">Edit</button>
```
- 使用 `JSON_HEX_*` 标志：`JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT`
- `onclick` 属性使用**单引号** `'`
- `json_encode()` 会将单引号编码为 `\u0027`，配合单引号包裹的 `onclick` 属性很安全

**Delete 按钮（当前 Line 257） - BROKEN**
```php
<button onclick="confirmDelete(<?php echo json_encode($lawyer['id']); ?>, <?php echo json_encode($lawyer['name']); ?>)" class="btn btn-danger btn-sm">Delete</button>
```
- `json_encode()` **缺少** `JSON_HEX_*` 标志
- `onclick` 属性使用**双引号** `"`
- 如果律师姓名包含单引号 `'` 和特殊字符组合，会导致 JavaScript 语法错误

## Problem Root Cause

问题在于两个关键差异：
1. 缺少 `JSON_HEX_*` 标志（特别是 `JSON_HEX_APOS`）
2. `onclick` 使用双引号而不是单引号

## Files and Modules to Be Modified

- [lawyers.php](file:///d:\JZ\bergpc.com\bergpc.com\admin-x7k9m\lawyers.php#L257)

## Steps for Modifications

修改 Delete 按钮，使其与 Edit 按钮使用相同的安全模式：
1. 给 `json_encode()` 添加 `JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT` 标志
2. 将 `onclick` 属性的引号从双引号改为单引号

## Proposed Code Change

**Before:**
```php
<button onclick="confirmDelete(<?php echo json_encode($lawyer['id']); ?>, <?php echo json_encode($lawyer['name']); ?>)" class="btn btn-danger btn-sm">Delete</button>
```

**After:**
```php
<button onclick='confirmDelete(<?php echo json_encode($lawyer['id'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>, <?php echo json_encode($lawyer['name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>)' class="btn btn-danger btn-sm">Delete</button>
```

## Verification Steps

1. 访问律师管理页面
2. 找到有单引号姓名的律师（如 "Lisa Dowlen 'Lisa' Autry"）
3. 点击 Delete 按钮 - 模态框应该正常显示，不报错
4. 同样测试 Edit 按钮确保功能不受影响
