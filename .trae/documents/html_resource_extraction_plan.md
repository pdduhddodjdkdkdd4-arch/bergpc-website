# HTML资源提取计划

## 项目概述
处理 `lawyers/lisa-dowlen-lisa-autry/index.html` 文件，将内联CSS和Base64图片提取为独立文件，减小主HTML文件体积。

## 目标文件
- 源文件：`lawyers/lisa-dowlen-lisa-autry/index.html` (约3MB+)
- 目标：提取CSS到 `styles.css`，提取图片到 `image-*.{png,jpg,jpeg,svg,webp}`

## 初步分析结果

### CSS块识别
根据已读取的文件内容，发现以下`<style>`标签：
1. 第5行：包含大型内联样式（包含Base64 PNG图片引用）
2. 第9-10行：Font Awesome 6.4.2 CSS
3. 第14-18行：jQuery UI CSS
4. 第19行：PT Sans字体定义（Base64 WOFF2）
5. 第22行：Font Awesome 4.3.0 CSS（Base64 WOFF2）
6. 第23行：remodal-overlay样式
7. 第24行：remodal主题样式
8. 第25行：loading动画样式
9. 第26-28行：IE条件注释中的CSS链接（保留，不提取）
10. 第32行：PT Sans字体定义（重复）
11. 第35行：Font Awesome 4.3.0 CSS（重复）
12. 第36行：Google翻译小部件样式

### 图片资源识别
1. 第5行：`data:image/png;base64,...` (sf-img-14)
2. 第317行：Google Translate图标 `data:image/png;base64,...`
3. 第359行：律师照片 `data:image/jpeg;base64,...`
4. 第36行：Google翻译按钮图标 `data:image/png;base64,...`

### 风险评估
- 发现IE条件注释（第26-28行）：保留原样，不提取
- 发现iframe中的内联内容：保留原样，不提取
- 发现多个重复的字体CSS：提取时去重

## 提取步骤

### 步骤1：提取CSS样式
1. 收集所有`<style>`标签内容（排除条件注释内的）
2. 合并去重后写入 `styles.css`
3. 在HTML中替换为 `<link rel="stylesheet" href="styles.css">`

### 步骤2：提取Base64图片
1. 解析所有`data:image/`格式的图片
2. 按类型命名为 `image-1.png`, `image-2.jpg`, `image-3.png` 等
3. 将Base64解码并保存为独立文件
4. 更新HTML中对应的`<img>`标签或CSS中的`url()`引用

### 步骤3：更新HTML文件
1. 移除所有内联`<style>`标签
2. 添加`<link>`引用styles.css
3. 替换所有Base64图片引用为文件引用
4. 确保HTML结构完整

### 步骤4：测试验证
1. 检查HTML标签是否完全闭合
2. 验证CSS样式是否正确应用
3. 验证图片是否正常显示
4. 对比提取前后的渲染效果

## 输出文件清单
- `index.html` - 优化后的HTML文件
- `styles.css` - 提取的CSS样式文件
- `image-1.png` - 提取的图片1
- `image-2.jpg` - 提取的图片2
- `image-3.png` - 提取的图片3

## 注意事项
1. 保留条件注释中的CSS引用
2. 保留iframe内的内联内容
3. 不修改JavaScript动态生成的内容
4. 确保所有HTML标签正确闭合
5. 保持CSS选择器顺序和完整性

## 风险处理机制
- 遇到JavaScript动态生成的CSS：保留不提取
- 遇到条件注释：保留原样
- 遇到内联事件处理程序：保留不提取
- 遇到iframe/shadow DOM：保留不提取
- 不确定的代码块：暂停并询问用户