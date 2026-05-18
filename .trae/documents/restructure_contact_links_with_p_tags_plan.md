# 使用 <p> 标签重构页脚联系信息计划

## 📋 目标

将所有页面页脚中的邮箱和电话链接从 `div` 包裹改为 `p` 标签包裹，与地址信息风格保持一致。

---

## 🔍 当前分析

### 需要修改的结构

**当前代码：**
```html
<div class="footer-contact-info"
    style="display: flex; flex-direction: column; gap: 8px;">

    <a href="mailto:info@bergpclawfirms.com"
        style="display: inline-flex; align-items: center; gap: 8px; color: inherit; text-decoration: none;">
        <svg>...</svg>
        <span>info@bergpclawfirms.com</span>
    </a>

    <a href="tel:+12818020190"
        style="display: inline-flex; align-items: center; gap: 8px; color: inherit; text-decoration: none;">
        <svg>...</svg>
        <span>2818020190</span>
    </a>

</div>
```

**期望代码：**
```html
<p>
    <a href="mailto:info@bergpclawfirms.com"
        style="display: inline-flex; align-items: center; gap: 8px; color: inherit; text-decoration: none;">
        <svg>...</svg>
        <span>info@bergpclawfirms.com</span>
    </a>
</p>

<p>
    <a href="tel:+12818020190"
        style="display: inline-flex; align-items: center; gap: 8px; color: inherit; text-decoration: none;">
        <svg>...</svg>
        <span>2818020190</span>
    </a>
</p>
```

---

## 🛠️ 实施步骤

### 步骤 1：扫描所有包含 footer-contact-info 的页面
- 查找所有使用 `footer-contact-info` 类的 HTML 文件
- 统计需要修改的文件数量

### 步骤 2：创建批量修改脚本
```python
# 替换规则：
# 1. 将 <div class="footer-contact-info" ...> 移除
# 2. 将 </div> 闭合标签移除
# 3. 将内部的每个 <a>...</a> 用 <p>...</p> 包裹
```

### 步骤 3：执行修改
- 备份所有要修改的文件
- 运行脚本批量修改
- 验证修改结果

### 步骤 4：验证与归零测试
1. 验证 HTML 结构正确性
2. 验证所有链接仍然有效
3. 确认样式与地址段落一致
4. 检查是否有遗漏的文件

---

## 📁 需要修改的文件

预计包含 `footer-contact-info` 的文件：
- `lawyers/index.htm`
- `lawyers/geoffrey-berg/index.htm`
- `lawyers/gil-melman/index.htm`
- `lawyers/james-c-plummer/index.htm`
- `lawyers/kathryn-e-nelson/index.htm`
- `lawyers/tomas-francisco-tijerina/index.htm`
- `lawyers/tracy-moebes/index.htm`
- `lawyers/tracy-moberg/index.htm`
- 以及其他包含此结构的页面（预计约48个）

---

## 🔄 替换规则

| 原内容 | 替换为 |
|--------|--------|
| `<div class="footer-contact-info"` | `<p>` |
| `style="display: flex; flex-direction: column; gap: 8px;">` | (移除) |
| `</div>` (footer-contact-info 闭合) | `</p>` |
| 内部的 `<a>...</a>` | `<a>...</a>` (保持不变，但已包裹在 p 中) |

---

## ⚠️ 风险与注意事项

1. **HTML 结构完整性**：确保嵌套关系正确
2. **样式一致性**：移除 div 的 flex 样式，使用 p 的默认块级元素行为
3. **备份**：所有修改前必须备份
4. **验证**：修改后检查所有链接是否正常工作

### 回滚计划
- 所有修改前备份为 `*.backup_p_tags`
- 如有问题，从备份恢复

---

## ✅ 验收标准

1. 所有页面的 footer 联系信息都使用 `<p>` 标签包裹
2. HTML 结构完整，无未闭合标签
3. 所有 mailto 和 tel 链接功能正常
4. 样式风格与地址段落一致
5. 所有相关页面已更新（无遗漏）

---

## 📝 归零测试验证清单

- [ ] 检查 lawyers/index.htm 的页脚结构
- [ ] 检查 geoffrey-berg/index.htm 的页脚结构
- [ ] 检查所有律师个人页面
- [ ] 验证所有邮件链接可点击
- [ ] 验证所有电话链接可点击
- [ ] 确认无 HTML 语法错误
