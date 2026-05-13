# 修复网站外部链接与资源 - Product Requirement Document

## Overview
- **Summary**: 分析并修复网站中使用线上 bergpc.com 地址的链接和资源引用，将其转换为本地引用，缺失的资源进行下载
- **Purpose**: 确保网站可以完全在本地离线访问，所有链接和资源都正确指向本地文件
- **Target Users**: 网站维护者和开发者

## Goals
1. 扫描所有HTML文件，找出所有 bergpc.com 的链接和资源引用
2. 检查本地是否已有对应的文件或资源
3. 将可用的本地资源链接转换为相对路径引用
4. 列出缺失的资源，询问是否下载
5. 下载缺失的资源并更新引用

## Non-Goals (Out of Scope)
- 修改第三方外部资源（如 Gravatar, YouTube, 社交媒体等）
- 修复不在 bergpc.com 域下的链接
- 改变网站内容的功能或设计

## Background & Context
当前网站是从 bergpc.com 爬取的，但仍然有大量链接和资源引用指向线上地址。需要：
1. 分析现有文件结构
2. 识别所有需要修复的引用
3. 分批处理修复

## Functional Requirements
- **FR-1**: 扫描所有HTML文件找出 bergpc.com 引用
- **FR-2**: 检查本地是否有对应的页面或资源
- **FR-3**: 将链接转换为相对路径引用
- **FR-4**: 列出缺失的资源清单
- **FR-5**: 下载缺失的资源并更新引用

## Non-Functional Requirements
- **NFR-1**: 保持原有的文件结构
- **NFR-2**: 不破坏现有的HTML结构
- **NFR-3**: 所有资源下载后保持原文件名和结构

## Constraints
- **Technical**: 使用Python进行处理
- **Business**: 需要用户确认后才下载缺失资源
- **Dependencies**: 需要网络连接来下载缺失资源

## Assumptions
1. 本地文件结构与线上网站结构一致
2. 缺失的资源可以通过HTTP请求正常获取
3. 相对路径转换可以正确工作

## Acceptance Criteria

### AC-1: 完整扫描报告
- **Given**: 所有HTML文件已就绪
- **When**: 执行扫描脚本
- **Then**: 生成完整的 bergpc.com 引用清单
- **Verification**: programmatic
- **Notes**: 包含引用类型（链接、图片、CSS、JS等）

### AC-2: 本地资源检查
- **Given**: 有完整的引用清单
- **When**: 执行检查脚本
- **Then**: 区分哪些引用已有本地文件，哪些缺失
- **Verification**: programmatic
- **Notes**: 生成两份清单：可用和缺失

### AC-3: 链接正确转换
- **Given**: 有可用的本地文件
- **When**: 执行转换脚本
- **Then**: 链接被转换为正确的相对路径
- **Verification**: programmatic + human-judgment
- **Notes**: 相对路径要正确计算

### AC-4: 缺失资源清单
- **Given**: 检查完成
- **When**: 输出缺失资源列表
- **Then**: 清单包含URL、本地路径、资源类型
- **Verification**: human-judgment

### AC-5: 资源下载
- **Given**: 用户确认下载
- **When**: 执行下载脚本
- **Then**: 资源被正确下载到对应目录，引用被更新
- **Verification**: programmatic

## Open Questions
- [ ] 是否包括metadata中的链接（如og:url等）进行修改？
- [ ] 下载时是否需要处理重定向？
