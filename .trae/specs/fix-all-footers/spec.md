# 修复所有页脚 - Product Requirement Document

## Overview
- **Summary**: 修复所有页面的页脚，在指定位置插入邮箱和电话联系方式，替换原来的社交媒体图标区域
- **Purpose**: 统一修改所有页面的页脚，提供直接的联系方式（邮箱和电话），保持布局一致
- **Target Users**: 网站访客和潜在客户

## Goals
- 在 47 个 index.htm 页面的页脚区域替换社交媒体图标
- 插入邮箱：info@bergpc.com（带 SVG 图标和 mailto 链接）
- 插入电话：281 857-6666（带 SVG 图标和 tel 链接）
- 保持原有的注释结构，一行显示一个联系方式
- 确保所有页面的修改保持一致

## Non-Goals (Out of Scope)
- 不修改页脚的其他部分（导航菜单、地址等）
- 不删除原有的社交媒体代码（保持注释状态）
- 不修改网站的其他部分

## Background & Context
- 用户选中了 index.htm 的第 5765-5946 行（页脚区域）
- 第 5787 行有注释：`<!-- 插入图标+邮箱和图标+电话号码，一行显示一个 -->`
- 原有的社交媒体图标区域已经被注释掉
- 需要按照示例修复所有页面

## Functional Requirements
- **FR-1**: 在指定位置删除注释占位符
- **FR-2**: 插入邮箱联系方式（带 SVG 图标和 mailto 链接）
- **FR-3**: 插入电话联系方式（带 SVG 图标和 tel 链接）
- **FR-4**: 使用 flex 布局，一行显示一个联系方式
- **FR-5**: 修改所有 47 个 index.htm 页面

## Non-Functional Requirements
- **NFR-1**: 布局保持美观，使用 inline-flex 和垂直布局
- **NFR-2**: 链接功能正常（mailto: 和 tel:）
- **NFR-3**: 保持与现有样式兼容

## Constraints
- **Technical**: 47 个 HTML 页面需要修改
- **Dependencies**: 无

## Assumptions
- 所有页面使用相同的页脚结构
- 邮箱和电话信息准确无误
- 所有页面都有相同的注释占位符

## Acceptance Criteria

### AC-1: 注释占位符已删除
- **Given**: 47 个 HTML 页面
- **When**: 修改完成后
- **Then**: 不再包含 `<!-- 插入图标+邮箱和图标+电话号码，一行显示一个 -->`
- **Verification**: `programmatic`

### AC-2: 邮箱联系方式已添加
- **Given**: 47 个 HTML 页面
- **When**: 修改完成后
- **Then**: 所有页面显示邮箱 info@bergpc.com（带 SVG 图标和 mailto 链接）
- **Verification**: `programmatic`

### AC-3: 联系电话已添加
- **Given**: 47 个 HTML 页面
- **When**: 修改完成后
- **Then**: 所有页面显示电话 281 857-6666（带 SVG 图标和 tel 链接）
- **Verification**: `programmatic`

### AC-4: 布局正确
- **Given**: 修改后的页面
- **When**: 查看页脚
- **Then**: 邮箱和电话各占一行，布局美观
- **Verification**: `human-judgment`

## Open Questions
- 无
