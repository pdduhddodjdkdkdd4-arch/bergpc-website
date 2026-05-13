# 替换社交媒体图标为联系方式 - Product Requirement Document

## Overview
- **Summary**: 将所有页脚中的社交媒体图标（YouTube、Facebook、LinkedIn、X）替换为邮箱和联系电话
- **Purpose**: 简化页脚联系信息，移除社交媒体链接，改为直接的邮箱和电话联系方式
- **Target Users**: 网站访客和潜在客户

## Goals
- 删除所有47个页面中的社交媒体图标链接（YouTube、Facebook、LinkedIn、X）
- 替换为邮箱联系方式：info@bergpc.com
- 替换为电话联系方式：(713) 526-0200
- 保持页脚布局美观，使用图标+文字的方式显示联系方式

## Non-Goals (Out of Scope)
- 不修改其他页脚内容（导航菜单、地址等）
- 不添加任何新的社交媒体链接
- 不修改网站其他部分

## Background & Context
- 网站当前在页脚显示4个社交媒体图标：YouTube、Facebook、LinkedIn、X
- 替换为更直接的联系方式，方便用户联系律所
- 邮箱：info@bergpc.com
- 电话：(713) 526-0200

## Functional Requirements
- **FR-1**: 删除所有页面的页脚社交媒体图标区域
- **FR-2**: 在相同位置添加邮箱联系方式（带邮箱图标）
- **FR-3**: 添加联系电话联系方式（带电话图标）
- **FR-4**: 保持原有的HTML结构和CSS样式

## Non-Functional Requirements
- **NFR-1**: 布局保持美观，使用图标+文字形式
- **NFR-2**: 链接功能正常（mailto: 和 tel:）
- **NFR-3**: 响应式布局保持一致

## Constraints
- **Technical**: 
  - 47个HTML页面需要修改
  - 使用本地SVG图标
  - 保持与现有样式兼容
- **Dependencies**: 无

## Assumptions
- 所有页面使用相同的页脚结构
- 邮箱和电话信息准确无误

## Acceptance Criteria

### AC-1: 社交媒体图标已删除
- **Given**: 47个HTML页面
- **When**: 修改完成后
- **Then**: 所有页面不再包含YouTube、Facebook、LinkedIn、X的社交媒体链接
- **Verification**: `programmatic`

### AC-2: 邮箱联系方式已添加
- **Given**: 47个HTML页面
- **When**: 修改完成后
- **Then**: 所有页面显示邮箱 info@bergpc.com（带mailto链接）
- **Verification**: `programmatic`

### AC-3: 联系电话已添加
- **Given**: 47个HTML页面
- **When**: 修改完成后
- **Then**: 所有页面显示电话 (713) 526-0200（带tel链接）
- **Verification**: `programmatic`

### AC-4: 布局美观
- **Given**: 修改后的页面
- **When**: 查看页脚
- **Then**: 邮箱和电话以图标+文字形式清晰显示
- **Verification**: `human-judgment`

## Open Questions
- [ ] 用户是否需要同时显示邮箱和电话？
- [ ] 是否需要添加营业时间信息？
