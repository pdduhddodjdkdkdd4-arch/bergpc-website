# Plan: Extract Inline Resources from HTML File

## Summary

将 `d:\JZ\bergpc.com\bergpc.com\lawyers\lisa-dowlen-lisa-autry\index.html` 中的内联资源（图片、CSS、JS）拆分成独立文件，减小主 HTML 文件大小并更新引用。

## Repository Research Conclusion

**文件分析：**
- 文件大小：约 3.16MB
- 内容类型：由 SingleFile 保存的网页归档
- 包含资源类型：
  1. 多个 `<style>` 块（CSS 样式和 base64 字体）
  2. `data:image/png;base64,` 内联图片（如第9行的 `--sf-img-14`）
  3. 可能包含内联 JavaScript

## Proposed Changes

### 1. 创建目录结构
```
lawyers/lisa-dowlen-lisa-autry/
├── index.html          # 更新后的主 HTML 文件
├── styles/             # CSS 文件目录
│   ├── style-1.css
│   ├── style-2.css
│   └── ...
├── images/             # 图片文件目录
│   ├── img-1.png
│   ├── img-2.png
│   └── ...
└── scripts/            # JavaScript 文件目录（如需要）
    └── script-1.js
```

### 2. 提取步骤

#### 步骤1：提取内联图片（data:image/...;base64）
- 解析所有 `data:image/` URL
- 解码 base64 内容并保存为独立图片文件
- 生成唯一文件名（如 `sf-img-14.png`）

#### 步骤2：提取内联 CSS（<style> 块）
- 将每个 `<style>` 块提取到独立 `.css` 文件
- 更新 HTML 中的引用为 `<link rel="stylesheet" href="styles/style-N.css">`

#### 步骤3：提取内联 JS（<script> 块）
- 如果存在内联脚本，提取到独立 `.js` 文件
- 更新 HTML 中的引用为 `<script src="scripts/script-N.js"></script>`

## Files and Modules to Be Modified

- [index.html](file:///d:\JZ\bergpc.com\bergpc.com\lawyers\lisa-dowlen-lisa-autry\index.html) - 主文件

## New Files to Be Created

- `styles/style-*.css` - 提取的 CSS 文件
- `images/img-*.png/jpg/webp` - 提取的图片文件
- `scripts/script-*.js` - 提取的 JavaScript 文件（如需要）

## Verification Steps

1. 运行提取脚本
2. 验证生成的目录结构和文件
3. 在浏览器中打开更新后的 `index.html`
4. 检查页面是否正常显示，所有资源是否正确加载

## Risk Handling

- **编码问题**：确保所有字符编码正确处理
- **路径引用**：确保相对路径正确指向资源文件
- **资源完整性**：验证提取的文件内容与原始内联内容一致
