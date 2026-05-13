# 页脚联系方式替换 - The Implementation Plan

## [ ] Task 1: 先修复 index.htm 插入邮箱和电话
- **Priority**: P0
- **Depends On**: None
- **Description**: 
  - 在 index.htm 的注释位置插入邮箱和电话
  - 邮箱一行，电话一行，都带SVG图标
- **Acceptance Criteria Addressed**: AC-2, AC-3
- **Test Requirements**:
  - human-judgement TR-1.1: 验证邮箱和电话显示正常
- **Notes**: 先测试单个页面确保正确

## [ ] Task 2: 分析需要替换的目标代码
- **Priority**: P0
- **Depends On**: Task 1
- **Description**: 
  - 确定要查找和替换的完整社交媒体代码块
  - 准备替换后的完整代码（注释社交媒体 + 新联系方式）
- **Acceptance Criteria Addressed**: AC-1, AC-2, AC-3
- **Test Requirements**:
  - programmatic TR-2.1: 精确匹配要替换的代码
- **Notes**: 从 index.htm 中提取正确的模板

## [ ] Task 3: 创建批量替换脚本
- **Priority**: P0
- **Depends On**: Task 2
- **Description**: 
  - 编写Python脚本处理所有46个HTML文件
  - 精确匹配和替换社交媒体区域
- **Acceptance Criteria Addressed**: AC-1, AC-2, AC-3
- **Test Requirements**:
  - programmatic TR-3.1: 脚本能正确处理所有文件
- **Notes**: 确保不影响 index.htm（已手动修改）

## [ ] Task 4: 执行批量替换
- **Priority**: P0
- **Depends On**: Task 3
- **Description**: 
  - 运行脚本修改所有页面
  - 跳过 index.htm（已手动修改）
- **Acceptance Criteria Addressed**: AC-1, AC-2, AC-3
- **Test Requirements**:
  - programmatic TR-4.1: 所有页面都成功修改

## [ ] Task 5: 验证修改结果
- **Priority**: P1
- **Depends On**: Task 4
- **Description**: 
  - 检查多个页面验证修改正确
  - 确认布局和链接功能正常
- **Acceptance Criteria Addressed**: AC-4
- **Test Requirements**:
  - programmatic TR-5.1: 验证邮箱和电话链接
  - human-judgement TR-5.2: 检查视觉效果
