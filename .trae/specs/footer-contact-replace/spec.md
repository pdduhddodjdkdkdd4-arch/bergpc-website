# 页脚联系方式替换 - Product Requirement Document

## Overview
- **Summary**: 将所有页面页脚的社交媒体图标替换为邮箱和电话联系方式，保持原布局结构
- **Purpose**: 简化页脚，提供直接的联系方式，一行显示一个（邮箱在上，电话在下）
- **Target Users**: 网站管理员、访客

## Goals
- 注释掉所有社交媒体图标区域（YouTube、Facebook、LinkedIn、X）
- 插入邮箱和电话联系方式，一行一个
- 保持原始CSS类和布局结构不变
- 确保所有页面一致性

## Non-Goals (Out of Scope)
- 不修改页脚其他部分
- 不删除CSS类，只注释内容
- 不改变响应式布局

## Background & Context
- index.htm 已作为示例修复
- 社交媒体区域被注释，插入邮箱和电话
- 邮箱：info@bergpc.com
- 电话：281 857-6666

## Functional Requirements
- **FR-1**: 注释社交媒体图标区域（不删除）
- **FR-2**: 插入邮箱链接（带mailto和SVG图标）
- **FR-3**: 插入电话链接（带tel和SVG图标）
- **FR-4**: 保持原有容器结构

## Non-Functional Requirements
- **NFR-1**: 邮箱和电话各行显示
- **NFR-2**: 使用inline-flex布局
- **NFR-3**: 保持原CSS类兼容性

## Constraints
- **Technical**: 所有46个HTML页面需修改
- **Dependencies**: 无

## Assumptions
- 所有页面使用相同的页脚结构
- 邮箱和电话信息正确

## Acceptance Criteria

### AC-1: 社交媒体区域已注释
- **Given**: 46个HTML页面
- **When**: 修改完成
- **Then**: 所有社交媒体代码被注释
- **Verification**: programmatic

### AC-2: 邮箱联系方式已添加
- **Given**: 46个HTML页面
- **When**: 修改完成
- **Then**: info@bergpc.com 显示，带SVG图标和mailto链接
- **Verification**: programmatic

### AC-3: 电话联系方式已添加
- **Given**: 46个HTML页面
- **When**: 修改完成
- **Then**: 281 857-6666 显示，带SVG图标和tel链接
- **Verification**: programmatic

### AC-4: 布局保持正确
- **Given**: 修改后的页面
- **When**: 查看页脚
- **Then**: 邮箱和电话各行显示，布局美观
- **Verification**: human-judgment

## Open Questions
- [ ] 是否需要调整颜色或图标大小？
