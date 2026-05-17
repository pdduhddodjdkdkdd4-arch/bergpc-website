# 隐藏 Testimonials 菜单计划

## 概述
用户要求隐藏所有页面导航栏中的 Testimonials 菜单（包含桌面端和移动端/汉堡栏），采用注释方式而非删除。

## 问题分析
通过搜索发现，Testimonials 菜单存在于多个页面文件中，每个文件通常包含 2-3 个位置：
- 桌面端导航菜单 (menu-item-testimonials-desktop)
- 移动端汉堡菜单 (menu-item-testimonials-mobile)

## 修改范围
用户明确提到的文件：
- `disclaimer/index.htm` - 包含 3 处 Testimonials 菜单（第 4622-4625, 4895-4898, 4902-4905 行）

## 修改方法
使用 HTML 注释 `<!-- ... -->` 将整个 `<li>` 元素包裹起来，实现隐藏效果。

## 步骤
1. 注释 `disclaimer/index.htm` 中的第一处 Testimonials 菜单（桌面端）
2. 注释 `disclaimer/index.htm` 中的第二处 Testimonials 菜单（桌面端）
3. 注释 `disclaimer/index.htm` 中的第三处 Testimonials 菜单（移动端）

## 风险评估
- 低风险操作，仅添加注释，不会影响其他功能
- 可随时恢复，只需移除注释即可

## 验证
修改后页面加载时不应显示 Testimonials 菜单项