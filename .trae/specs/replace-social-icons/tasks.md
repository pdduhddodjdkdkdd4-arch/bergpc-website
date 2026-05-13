# 替换社交媒体图标为联系方式 - The Implementation Plan

## [ ] Task 1: 分析当前页脚社交媒体模块结构
- **Priority**: P0
- **Depends On**: None
- **Description**: 
  - 分析当前页脚社交媒体模块的完整HTML结构
  - 确定需要删除和替换的范围
  - 识别目标元素：ast-footer-social-1-wrap
- **Acceptance Criteria Addressed**: AC-1
- **Test Requirements**:
  - `human-judgement` TR-1.1: 确认所有47个页面使用相同的结构
- **Notes**: 确认YouTube、Facebook、LinkedIn、X四个社交媒体链接需要删除

## [ ] Task 2: 识别并收集邮箱和电话信息
- **Priority**: P0
- **Depends On**: None
- **Description**: 
  - 确认邮箱：info@bergpc.com
  - 确认电话：(713) 526-0200
  - 准备SVG图标代码
- **Acceptance Criteria Addressed**: AC-2, AC-3
- **Test Requirements**:
  - `human-judgement` TR-2.1: 确认联系方式准确
- **Notes**: 电话链接使用 tel:713-526-0200

## [ ] Task 3: 编写替换脚本
- **Priority**: P0
- **Depends On**: Task 1, Task 2
- **Description**: 
  - 创建Python脚本批量处理所有HTML文件
  - 定位 ast-footer-social-1-wrap 区域
  - 删除社交媒体链接
  - 替换为邮箱和电话联系方式（带图标）
- **Acceptance Criteria Addressed**: AC-1, AC-2, AC-3
- **Test Requirements**:
  - `programmatic` TR-3.1: 脚本能正确处理所有47个文件
- **Notes**: 使用正则表达式精确定位和替换

## [ ] Task 4: 执行替换操作
- **Priority**: P0
- **Depends On**: Task 3
- **Description**: 
  - 运行脚本处理所有47个HTML文件
  - 确保每个文件都被正确修改
- **Acceptance Criteria Addressed**: AC-1, AC-2, AC-3
- **Test Requirements**:
  - `programmatic` TR-4.1: 所有47个文件都已修改
- **Notes**: 执行前先备份

## [ ] Task 5: 验证修改结果
- **Priority**: P1
- **Depends On**: Task 4
- **Description**: 
  - 搜索确认所有社交媒体链接已删除
  - 确认邮箱和电话已添加
  - 检查HTML结构完整性
- **Acceptance Criteria Addressed**: AC-4
- **Test Requirements**:
  - `programmatic` TR-5.1: 搜索验证无社交媒体链接
  - `programmatic` TR-5.2: 搜索验证包含邮箱和电话
  - `human-judgement` TR-5.3: 检查页面布局美观性
