# 网站谷歌相关代码 - 详细分析与归类

## 概述
经全面搜索分析，网站中包含以下几类谷歌相关代码和引入资源，涉及 **47个HTML页面**。

---

## 📊 分类总览

| 类别 | 描述 | 影响页面数量 | 可删除性建议 |
|------|------|-------------|------------|
| 1 | Google Tag Manager (GTM) | 47页 | ⚠️ 需确认 |
| 2 | Google Fonts (本地引入) | 47页 | ✅ 可考虑 |
| 3 | Google reCAPTCHA | 47页 | ⚠️ 需确认 |
| 4 | Google Maps | 1页 (联系页) | ⚠️ 需确认 |
| 5 | 插件相关引用 | 多个插件 | ✅ 不建议删 |

---

## 📋 详细分类分析

---

### 🔴 类别 1: Google Tag Manager (GTM)

**描述**: Google Tag Manager 追踪和分析脚本

**代码示例**:
```html
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-5SZQ7SF4');</script>
<!-- End Google Tag Manager -->

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5SZQ7SF4" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
```

**影响文件** (47页):
- [index.htm](file:///d:/JZ/bergpc.com/bergpc.com/index.htm#L104-L110)
- [contact/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/contact/index.htm#L97-L103)
- [blog/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/blog/index.htm)
- [lawyers/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/lawyers/index.htm)
- [lawyers/geoffrey-berg/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/lawyers/geoffrey-berg/index.htm)
- [practice-areas/business-litigation/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/practice-areas/business-litigation/index.htm)
- [practice-areas/crypto-litigation/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/practice-areas/crypto-litigation/index.htm)
- 以及所有其他40个HTML页面...

**特点**:
- GTM容器ID: `GTM-5SZQ7SF4`
- 每个页面头部和body都有
- 用于网站分析和广告追踪

**位置**:
- 头部: HTML head 部分
- NoScript: body 开始处

---

### 🟠 类别 2: Google Fonts (本地引入)

**描述**: Google Fonts 字体库，已本地化存储

**代码示例**:
```html
<link rel='stylesheet' id='astra-google-fonts-css' href='wp-content/astra-local-fonts/astra-local-fonts.css?ver=4.13.1' media='all'>
<link rel='stylesheet' id='elementor-gf-local-roboto-css' href='wp-content/uploads/elementor/google-fonts/css/roboto.css?ver=1743612499' media='all'>
<link rel='stylesheet' id='elementor-gf-local-robotoslab-css' href='wp-content/uploads/elementor/google-fonts/css/robotoslab.css?ver=1743612529' media='all'>
```

**影响文件** (47页):
- 所有HTML页面都有这3个link标签

**特点**:
- 使用的字体: Roboto, Roboto Slab
- **注意**: 这些是**本地**存储的字体文件，**不是**从googleapis.com加载
- 本地文件位置:
  - `wp-content/astra-local-fonts/`
  - `wp-content/uploads/elementor/google-fonts/`

**位置**: HTML head 部分

---

### 🟡 类别 3: Google reCAPTCHA

**描述**: Google reCAPTCHA 验证码，用于表单防垃圾信息

**代码示例**:
```javascript
var wpformsElementorVars = {"captcha_provider":"recaptcha","recaptcha_type":"v2"};
var wpforms_settings = {...,"val_recaptcha_fail_msg":"Google reCAPTCHA verification failed, please try again later.",...}
```

**影响文件** (47页):
- 所有HTML页面都有这个JS变量

**特点**:
- 使用的是 reCAPTCHA v2
- 集成在 WPForms 插件中
- 用于联系表单等表单的验证

**相关文件**:
- 表单验证可能涉及: `wp-content/plugins/wpforms/` 插件文件

---

### 🔵 类别 4: Google Maps

**描述**: Google Maps 地图嵌入，显示律所位置

**代码示例**:
```html
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d6929.127924396223!2d-95.43411591199101!3d29.73238741083355!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8640bf7cf01a1f53%3A0xdee68a741e36baa7!2sBerg%20Plummer%20%26%20Johnson%2C%20LLP!5e0!3m2!1sen!2sus!4v1738607240070!5m2!1sen!2sus" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
```

**影响文件** (1页):
- [contact/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/contact/index.htm#L461) - 联系我们页面

**特点**:
- 显示律所位置: Berg Plummer & Johnson, LLP
- 坐标: 29.7323874, -95.4341159 (休斯顿地区)
- 仅在联系页面出现

---

### 🟢 类别 5: 插件相关引用 (不建议删除)

**描述**: WordPress 插件内部的谷歌相关引用，属于第三方库

**相关文件**:
- `wp-content/plugins/elementor-pro/assets/js/elements-handlers.min.js`
- `wp-content/plugins/wpforms/assets/lib/jquery.timepicker/jquery.timepicker.min.js`
- `wp-content/plugins/wpforms/assets/lib/mailcheck.min.js`
- `wp-content/plugins/astra-sites/inc/lib/onboarding/assets/dist/template-preview/main.js`
- `wp-content/plugins/elementor/assets/css/frontend.min.css`
- `wp-content/plugins/elementor/assets/css/widget-social-icons.min.css`
- 等等...

**特点**:
- 这些是第三方插件库中的引用
- 不是直接的网站功能代码
- **不建议删除**，可能导致插件功能异常

---

## 📁 完整HTML页面清单 (47页)

所有包含谷歌相关内容的HTML页面:

1. [index.htm](file:///d:/JZ/bergpc.com/bergpc.com/index.htm)
2. [contact/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/contact/index.htm)
3. [blog/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/blog/index.htm)
4. [blog/page/2/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/blog/page/2/index.htm)
5. [blog/page/3/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/blog/page/3/index.htm)
6. [lawyers/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/lawyers/index.htm)
7. [lawyers/geoffrey-berg/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/lawyers/geoffrey-berg/index.htm)
8. [lawyers/gil-melman/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/lawyers/gil-melman/index.htm)
9. [lawyers/james-c-plummer/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/lawyers/james-c-plummer/index.htm)
10. [lawyers/kathryn-e-nelson/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/lawyers/kathryn-e-nelson/index.htm)
11. [lawyers/tracy-moebes/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/lawyers/tracy-moebes/index.htm)
12. [practice-areas/business-litigation/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/practice-areas/business-litigation/index.htm)
13. [practice-areas/business-litigation/breach-of-contract/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/practice-areas/business-litigation/breach-of-contract/index.htm)
14. [practice-areas/business-litigation/business-divorce/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/practice-areas/business-litigation/business-divorce/index.htm)
15. [practice-areas/business-litigation/non-compete-disputes/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/practice-areas/business-litigation/non-compete-disputes/index.htm)
16. [practice-areas/business-litigation/partnership-disputes/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/practice-areas/business-litigation/partnership-disputes/index.htm)
17. [practice-areas/crypto-blockchain-business-transactions/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/practice-areas/crypto-blockchain-business-transactions/index.htm)
18. [practice-areas/crypto-litigation/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/practice-areas/crypto-litigation/index.htm)
19. [practice-areas/crypto-litigation/2025-coinbase-data-breach/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/practice-areas/crypto-litigation/2025-coinbase-data-breach/index.htm)
20. [practice-areas/crypto-litigation/2025-meta-crypto-scam-ads-investigation/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/practice-areas/crypto-litigation/2025-meta-crypto-scam-ads-investigation/index.htm)
21. [practice-areas/crypto-litigation/fraud-recovery/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/practice-areas/crypto-litigation/fraud-recovery/index.htm)
22. [category/business-litigation/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/category/business-litigation/index.htm)
23. [category/business-litigation/page/2/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/category/business-litigation/page/2/index.htm)
24. [category/crypto-litigation/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/category/crypto-litigation/index.htm)
25. [category/news/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/category/news/index.htm)
26. [author/gberg/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/author/gberg/index.htm)
27. [author/gberg/page/2/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/author/gberg/page/2/index.htm)
28. [author/gberg/page/3/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/author/gberg/page/3/index.htm)
29. [privacy-policy/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/privacy-policy/index.htm)
30. [disclaimer/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/disclaimer/index.htm)
31. [podcast/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/podcast/index.htm)
32. [15-billion-in-bitcoin-seized/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/15-billion-in-bitcoin-seized/index.htm)
33. [accepting-cryptocurrency-as-a-form-of-payment-in-your-business/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/accepting-cryptocurrency-as-a-form-of-payment-in-your-business/index.htm)
34. [are-non-compete-agreements-enforceable-in-texas/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/are-non-compete-agreements-enforceable-in-texas/index.htm)
35. [company-uses-my-intellectual-property-without-permission/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/company-uses-my-intellectual-property-without-permission/index.htm)
36. [geoffrey-berg-included-in-the-best-lawyers-in-america/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/geoffrey-berg-included-in-the-best-lawyers-in-america/index.htm)
37. [getting-your-share-of-the-15-billion-bitcoin-seizure/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/getting-your-share-of-the-15-billion-bitcoin-seizure/index.htm)
38. [grounds-for-suing-a-business-partner/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/grounds-for-suing-a-business-partner/index.htm)
39. [how-is-stolen-crypto-tracked/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/how-is-stolen-crypto-tracked/index.htm)
40. [how-to-handle-disputes-in-a-partnership/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/how-to-handle-disputes-in-a-partnership/index.htm)
41. [how-to-recover-cryptocurrency-with-the-right-lawyer-in-court/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/how-to-recover-cryptocurrency-with-the-right-lawyer-in-court/index.htm)
42. [how-to-spot-and-avoid-crypto-lawyer-scams/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/how-to-spot-and-avoid-crypto-lawyer-scams/index.htm)
43. [meta-earns-billions-from-victims-lured-into-crypto-scams-by-facebook-ads/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/meta-earns-billions-from-victims-lured-into-crypto-scams-by-facebook-ads/index.htm)
44. [the-slavery-behind-crypto-scams/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/the-slavery-behind-crypto-scams/index.htm)
45. [what-happens-when-dissolving-a-partnership/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/what-happens-when-dissolving-a-partnership/index.htm)
46. [what-is-material-breach-of-contract-in-texas/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/what-is-material-breach-of-contract-in-texas/index.htm)
47. [wp-json/index.htm](file:///d:/JZ/bergpc.com/bergpc.com/wp-json/index.htm)

---

## 💡 删除决策参考

### 建议删除的内容
| 类别 | 原因 |
|------|------|
| Google Tag Manager | 如果不需要网站分析和广告追踪 |
| Google Maps | 如果不需要在联系页面显示地图 |

### 建议保留的内容
| 类别 | 原因 |
|------|------|
| Google Fonts | 已本地化，不影响隐私，删除会影响网站外观 |
| Google reCAPTCHA | 防止表单垃圾信息，建议保留 |
| 插件引用 | 删除可能导致插件功能异常 |

---

## ⚠️ 注意事项

1. **Google Fonts**: 虽然叫"Google Fonts"，但这些是**本地存储**的文件，不会向Google发送请求，可以安全保留
2. **reCAPTCHA**: 删除需要考虑替代方案，否则表单可能被垃圾信息攻击
3. **GTM**: 删除将失去网站数据分析功能
4. **Maps**: 删除需要用其他地图服务替代，或用文字地址替代

---

请确认您希望删除哪些类别的谷歌相关内容？
