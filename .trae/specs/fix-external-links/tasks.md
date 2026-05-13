# 修复外部链接与资源 - The Implementation Plan (Decomposed and Prioritized Task List)

## [x] Task 1: 扫描所有HTML文件找出 bergpc.com 引用
- **Priority**: P0
- **Depends On**: None
- **Description**: 
  - 遍历所有 index.htm 文件
  - 提取所有 https?://bergpc.com 引用
  - 按资源类型分类（链接、图片、CSS、JS、字体等）
  - 生成扫描报告
- **Acceptance Criteria Addressed**: AC-1
- **Test Requirements**:
  - programmatic: 生成完整的引用清单
  - human-judgement: 检查清单的完整性和准确性
- **Notes**: 需要处理HTML中的各种属性

## [x] Task 2: 检查本地是否有对应文件
- **Priority**: P0
- **Depends On**: Task 1
- **Description**: 
  - 对每个引用计算本地路径
  - 检查本地文件是否存在
  - 区分可用和缺失的资源
- **Acceptance Criteria Addressed**: AC-2
- **Test Requirements**:
  - programmatic: 生成两份清单
  - human-judgement: 检查分类准确性
- **Notes**: 正确处理 URL 到本地路径的转换

## [ ] Task 3: 将可用的链接转换为相对路径
- **Priority**: P0
- **Depends On**: Task 2
- **Description**: 
  - 计算正确的相对路径
  - 更新HTML文件中的引用
  - 保持HTML结构不变
- **Acceptance Criteria Addressed**: AC-3
- **Test Requirements**:
  - programmatic: 验证链接格式
  - human-judgement: 抽查几个页面的链接
- **Notes**: 从不同深度目录计算相对路径

## [x] Task 4: 生成缺失资源清单
- **Priority**: P0
- **Depends On**: Task 2
- **Description**: 
  - 整理缺失的资源
  - 包含完整URL、本地路径、资源类型
  - 统计下载预估大小
- **Acceptance Criteria Addressed**: AC-4
- **Test Requirements**:
  - human-judgement: 检查清单清晰完整
- **Notes**: 供用户确认后再下载

## [x] Task 5: 下载缺失的资源并更新引用
- **Priority**: P1
- **Depends On**: 用户确认 Task 4
- **Description**: 
  - 创建需要的目录结构
  - 下载资源到正确位置
  - 更新HTML中的引用
- **Acceptance Criteria Addressed**: AC-5
- **Test Requirements**:
  - programmatic: 验证文件存在且可访问
  - human-judgement: 抽查下载的资源
- **Notes**: 需要处理下载错误和重试
