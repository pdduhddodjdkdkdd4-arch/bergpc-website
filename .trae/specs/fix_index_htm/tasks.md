# 修复 index.htm - The Implementation Plan

## [ ] Task 1: 删除 Google Tag Manager
- **Priority**: P0
- **Depends On**: None
- **Description**: 
  - 从 index.htm 删除头部的 GTM script 标签（从 &lt;!-- Google Tag Manager --&gt; 到 &lt;!-- End Google Tag Manager --&gt;）
  - 从 index.htm 删除 body 中的 GTM noscript 标签（从 &lt;!-- Google Tag Manager (noscript) --&gt; 到 &lt;!-- End Google Tag Manager (noscript) --&gt;）
- **Acceptance Criteria Addressed**: AC-1
- **Test Requirements**:
  - `programmatic` TR-1.1: 搜索确认文件中无 Google Tag Manager 相关内容
- **Notes**: 只修改 index.htm

## [ ] Task 2: 删除搜索模块
- **Priority**: P0
- **Depends On**: None
- **Description**: 
  - 从 index.htm 删除搜索模块（从 &lt;div class="ast-builder-layout-element ast-flex site-header-focus-item ast-header-search" 开始到对应的闭合标签）
- **Acceptance Criteria Addressed**: AC-2
- **Test Requirements**:
  - `programmatic` TR-2.1: 搜索确认文件中无搜索模块相关内容
- **Notes**: 只修改 index.htm

## [ ] Task 3: 验证修改结果
- **Priority**: P1
- **Depends On**: Task 1, Task 2
- **Description**: 
  - 验证所有要求的内容都已删除
  - 验证其他内容保持完整
- **Acceptance Criteria Addressed**: AC-3
- **Test Requirements**:
  - `human-judgement` TR-3.1: 检查文件完整性
