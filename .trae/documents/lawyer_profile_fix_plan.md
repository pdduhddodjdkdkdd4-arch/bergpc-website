# 律师资料页面修复计划

## 问题分析

用户反馈 `https://bergpcc.com/lawyers/profile.php?slug=fantasy` 页面渲染错误，主要问题：

1. **资源文件引用错误**：模板使用随机生成的页面ID（如8883）引用Elementor CSS文件，但这些文件实际上不存在
   - 错误示例：`GET https://bergpcc.com/wp-content/uploads/elementor/css/post-8883.css 404 (Not Found)`

2. **相对路径问题**：模板使用 `../../wp-content/` 路径，但 `profile.php` 位于 `/lawyers/` 目录，应该使用 `../wp-content/`

3. **页面结构不一致**：动态生成的页面与静态参考页面 `lawyers/tracy-moberg/index.htm` 布局不一致

## 修复方案

### 方案一：使用固定页面ID

将模板中的页面ID固定为参考页面的ID（3759），这样可以复用已存在的Elementor CSS文件。

### 方案二：直接复制参考页面内容

以参考页面为基础，动态替换律师信息，确保布局完全一致。

## 实施步骤

### 步骤1：修改模板文件

修改 `admin/templates/lawyer_profile.tpl`：
- 将所有 `{{LAWYER_PAGE_ID}}` 替换为固定值 `3759`
- 确保所有资源路径正确

### 步骤2：修改 profile.php

修改 `lawyers/profile.php`：
- 移除随机页面ID生成逻辑
- 确保路径替换正确
- 添加必要的资源引用

### 步骤3：验证修复

测试多个律师页面，确保：
- 页眉和页脚样式正确
- 内容布局与参考页面一致
- 所有资源文件正确加载

## 文件修改

| 文件 | 修改内容 |
|------|----------|
| `admin/templates/lawyer_profile.tpl` | 替换动态页面ID为固定值3759 |
| `lawyers/profile.php` | 移除随机ID生成，修复路径替换逻辑 |

## 风险评估

- **低风险**：使用固定页面ID可能导致样式不完全匹配特定律师页面，但参考页面已包含通用布局样式
- **中等风险**：需要确保所有资源路径正确，避免404错误

## 预期结果

修复后，动态律师页面将：
1. 正确加载所有CSS和JS资源文件
2. 页眉和页脚样式与系统一致
3. 内容布局与参考页面完全一致
4. 动态显示正确的律师信息