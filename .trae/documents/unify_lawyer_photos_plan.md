# 统一律师照片引用计划

## 问题分析

当前HTML文件中引用了多个尺寸的tomas-francisco-tijerina照片：
- `tomas-francisco-tijerina-300x200.jpg`
- `tomas-francisco-tijerina-768x512.jpg`
- `tomas-francisco-tijerina-1024x683.jpg`
- `tomas-francisco-tijerina-1536x1024.jpg`
- `tomas-francisco-tijerina-2048x1365.jpg`
- `tomas-francisco-tijerina-scaled.jpg`

但实际只有一个文件存在：`tomas-francisco-tijerina.jpg`

## 解决方案

将所有图片引用统一指向单一文件 `tomas-francisco-tijerina.jpg`，移除srcset属性以避免浏览器尝试加载不存在的尺寸。

## 实施步骤

1. 扫描所有包含tomas-francisco-tijerina图片引用的文件
2. 将所有带尺寸后缀的图片名替换为基础文件名
3. 移除或简化srcset属性
4. 更新相关的width和height属性

## 要修改的文件

根据搜索结果，需要修改以下文件：
- `lawyers/tomas-francisco-tijerina/index.htm`
- `lawyers/index.htm`
- `lawyers/gil-melman/index.htm`
- `practice-areas/crypto-blockchain-business-transactions/index.htm`
- `accepting-cryptocurrency-as-a-form-of-payment-in-your-business/index.htm`
- `feed/index.htm`
- `category/crypto-litigation/feed/index.htm`
- `author/gberg/feed/index.htm`

## 替换规则

1. `tomas-francisco-tijerina-\d+x\d+\.jpg` → `tomas-francisco-tijerina.jpg`
2. `tomas-francisco-tijerina-scaled\.jpg` → `tomas-francisco-tijerina.jpg`
3. 移除或简化srcset属性

## 风险与回滚

- 修改前备份所有文件
- 如果出现问题，从备份恢复
