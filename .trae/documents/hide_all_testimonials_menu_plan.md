# 隐藏所有页面 Testimonials 菜单计划

## 概述
用户要求将 Testimonials 菜单的注释操作辐射到所有页面，采用 HTML 注释方式隐藏，不删除代码。

## 问题分析
通过搜索发现，共有约 35 个 HTML 文件包含 Testimonials 菜单，每个文件通常包含 2-3 个位置：
- 桌面端导航菜单 (menu-item-testimonials-desktop)
- 移动端汉堡菜单 (menu-item-testimonials-mobile)

## 修改范围
所有包含 Testimonials 菜单的文件：
1. privacy-policy/index.htm - 3处
2. disclaimer/index.htm - 已完成，3处
3. blog/index.htm - 2处
4. accepting-cryptocurrency-as-a-form-of-payment-in-your-business/index.htm - 3处
5. contact/index.htm - 3处
6. 15-billion-in-bitcoin-seized/index.htm - 3处
7. what-is-material-breach-of-contract-in-texas/index.htm - 3处
8. crypto-scam-fund-recovery-guide-digital-asset-investigation-and-recovery-process-analysis/index.htm - 3处
9. the-slavery-behind-crypto-scams/index.htm - 3处
10. practice-areas/business-litigation/business-divorce/index.htm - 3处
11. practice-areas/business-litigation/breach-of-contract/index.htm - 3处
12. practice-areas/business-litigation/non-compete-disputes/index.htm - 3处
13. practice-areas/business-litigation/partnership-disputes/index.htm - 3处
14. practice-areas/business-litigation/index.htm - 3处
15. practice-areas/business-litigation-thank-you/index.htm - 3处
16. practice-areas/business-litigation-thank-you/index.php - 3处
17. practice-areas/crypto-blockchain-business-transactions/index.htm - 3处
18. practice-areas/crypto-litigation/2025-coinbase-data-breach/index.htm - 2处
19. practice-areas/crypto-litigation/2025-meta-crypto-scam-ads-investigation/index.htm - 3处
20. practice-areas/crypto-litigation/fraud-recovery/index.htm - 3处
21. practice-areas/crypto-litigation/index.htm - 3处
22. category/business-litigation/index.htm - 3处
23. category/business-litigation/page/2/index.htm - 3处
24. category/crypto-litigation/index.htm - 3处
25. what-happens-when-dissolving-a-partnership/index.htm - 3处
26. category/news/index.htm - 3处
27. meta-earns-billions-from-victims-lured-into-crypto-scams-by-facebook-ads/index.htm - 3处
28. podcast/index.htm - 3处
29. author/gberg/page/2/index.htm - 3处
30. author/gberg/page/3/index.htm - 3处
31. getting-your-share-of-the-15-billion-bitcoin-seizure/index.htm - 3处
32. author/gberg/index.htm - 3处
33. are-non-compete-agreements-enforceable-in-texas/index.htm - 3处
34. grounds-for-suing-a-business-partner/index.htm - 3处

## 修改方法
使用 sed 命令批量替换，将 Testimonials 菜单项用 HTML 注释包裹。

## 步骤
1. 使用 PowerShell 的 sed 或正则替换命令批量处理所有 .htm 和 .php 文件
2. 替换模式：将 `<li id="menu-item-testimonials-(desktop|mobile)" ...>...Testimonials...</li>` 包裹在注释中

## 风险评估
- 低风险操作，仅添加注释
- 使用正则表达式批量处理，效率高
- 可随时恢复，只需移除注释即可

## 验证
修改后搜索确认所有 Testimonials 菜单都已被注释