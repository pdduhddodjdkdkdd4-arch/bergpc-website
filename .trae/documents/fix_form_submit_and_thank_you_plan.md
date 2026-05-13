# 修复表单提交和感谢页面的计划

## 问题分析

1. **表单提交问题**：
   - `/api/submit.php` 目前返回 JSON 响应而不是重定向到感谢页面
   - 用户希望提交成功后跳转到 `/practice-areas/business-litigation-thank-you/` 页面
   - 表单中有 `form_id="4296"` 隐藏字段，在 `FORM_CONFIGS` 中已配置

2. **感谢页面**：已经存在于 `practice-areas/business-litigation-thank-you/index.htm`

## 修复方案

### 1. 修改 `api/submit.php`
- 保持现有的表单验证和数据库存储逻辑
- 成功保存数据后，添加重定向到感谢页面的功能
- 修改响应方式：错误时返回 JSON，成功时重定向

### 2. 检查并确保感谢页面的完整性
- 检查 `practice-areas/business-litigation-thank-you/index.htm` 是否完整
- 确保使用与其他页面相同的页眉和页脚

## 执行步骤

1. 修改 `api/submit.php`：
   - 在成功保存数据后，移除 JSON 响应
   - 改为使用 `header()` 重定向到 `/practice-areas/business-litigation-thank-you/`

2. 测试修复：
   - 验证表单提交后是否正确跳转
   - 验证数据是否仍然正确保存到数据库

## 文件修改列表

- `api/submit.php`
