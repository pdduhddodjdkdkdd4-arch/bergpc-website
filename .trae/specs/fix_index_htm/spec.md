# 修复 index.htm - Product Requirement Document

## Overview
- **Summary**: 修复被恢复的 index.htm 文件，重新删除搜索模块和 Google Tag Manager 代码
- **Purpose**: 将 index.htm 恢复到与其他文件一致的状态，删除不需要的谷歌相关代码
- **Target Users**: 网站管理员

## Goals
- 从 index.htm 删除 Google Tag Manager 代码
- 从 index.htm 删除搜索模块
- 保持其他内容完整

## Non-Goals (Out of Scope)
- 不修改其他文件（其他文件已经是正确的）
- 不添加任何新功能

## Background & Context
- index.htm 文件被意外恢复，包含了之前已删除的搜索模块和 GTM
- 其他所有 46 个 HTML 文件已经是正确的状态，不需要修改

## Functional Requirements
- **FR-1**: 删除 index.htm 中的 Google Tag Manager 代码
- **FR-2**: 删除 index.htm 中的搜索模块

## Non-Functional Requirements
- **NFR-1**: 保持其他 HTML 内容完整
- **NFR-2**: 不影响网站功能

## Constraints
- **Technical**: 只修改 index.htm 一个文件
- **Dependencies**: 无

## Assumptions
- 其他文件保持当前正确状态不变

## Acceptance Criteria

### AC-1: GTM 已删除
- **Given**: index.htm 文件
- **When**: 删除操作完成后
- **Then**: 文件中不再包含 Google Tag Manager 相关代码
- **Verification**: `programmatic`

### AC-2: 搜索模块已删除
- **Given**: index.htm 文件
- **When**: 删除操作完成后
- **Then**: 文件中不再包含搜索模块代码
- **Verification**: `programmatic`

### AC-3: 其他内容完整
- **Given**: index.htm 文件
- **When**: 删除操作完成后
- **Then**: 其他所有 HTML 内容保持完整
- **Verification**: `human-judgment`
