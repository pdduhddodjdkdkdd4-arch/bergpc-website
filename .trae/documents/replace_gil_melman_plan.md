# 替换Gil Melman律师信息计划

## 📋 目标
将网站上所有与"Gil Melman"相关的内容替换为新律师信息，保持网站结构完整。

---

## 🔍 现状分析

### 包含Gil Melman的文件
1. **`lawyers/gil-melman/index.htm`** - Gil Melman的个人介绍页面
2. **`lawyers/index.htm`** - 律师团队列表页面
3. **`practice-areas/crypto-blockchain-business-transactions/index.htm`** - 加密货币业务实践页面
4. **`accepting-cryptocurrency-as-a-form-of-payment-in-your-business/index.htm`** - 接受加密货币支付页面
5. **`practice-areas/crypto-litigation/index.htm`** - 加密货币诉讼页面
6. **Feed文件** - RSS feed文件
7. **备份文件** - 所有对应的*.backup文件

---

## 📁 需要准备的新律师资料

### 1️⃣ 基础信息
- **新律师姓名**（例如：John Smith）
- **URL-safe姓名**（例如：john-smith）
- **职位/头衔**（例如：Partner, Attorney at Law等）
- **简介/专业描述**

### 2️⃣ 媒体资源
- **主照片**（高清照片，多种尺寸：300x200, 768x512, 1024x683, 1536x1024, 2048x1365, 原尺寸）
  - 建议路径：`wp-content/uploads/[年份]/[月份]/[文件名].jpg`
- **可选照片**（如果有其他照片需要展示）

### 3️⃣ 页面内容
- **个人介绍页完整内容**（HTML格式）
- **团队列表页卡片内容**

---

## 🛠️ 实施计划（分阶段）

### 阶段1：准备工作
1. ✅ 创建新律师的个人页面目录
2. 准备所有图片资源
3. 创建新的个人介绍页面（基于gil-melman页面模板）

### 阶段2：文件替换
1. 在`lawyers/index.htm`中替换Gil Melman的卡片
2. 在`practice-areas/crypto-blockchain-business-transactions/index.htm`中替换引用
3. 在`accepting-cryptocurrency-as-a-form-of-payment-in-your-business/index.htm`中替换引用
4. 在`practice-areas/crypto-litigation/index.htm`中替换引用
5. 更新所有feed文件（如果需要）

### 阶段3：资源管理
1. 将新律师照片上传到`wp-content/uploads`目录
2. 更新所有图片引用路径
3. 验证所有资源可访问

### 阶段4：验证与测试
1. 检查所有链接是否正确
2. 检查所有图片是否正常加载
3. 检查页面结构是否完整
4. 验证回归测试（确保没有破坏其他内容）

---

## 📋 详细任务清单

### 任务1：创建新律师页面结构
1. 创建目录 `lawyers/[新-url-name]/`
2. 复制 `lawyers/gil-melman/index.htm` 作为模板
3. 修改模板中的所有内容为新律师信息
4. 验证页面结构完整性

### 任务2：替换律师列表页面
1. 在 `lawyers/index.htm` 中找到Gil Melman的卡片区域
2. 替换姓名、职位、链接、图片
3. 保持HTML结构不变

### 任务3：替换实践领域页面
1. 在 `practice-areas/crypto-blockchain-business-transactions/index.htm` 中替换所有Gil Melman引用
2. 在 `accepting-cryptocurrency-as-a-form-of-payment-in-your-business/index.htm` 中替换
3. 在 `practice-areas/crypto-litigation/index.htm` 中替换

### 任务4：更新媒体资源
1. 将新律师照片放入 `wp-content/uploads/[年份]/[月份]/`
2. 更新所有图片引用路径
3. 确保图片尺寸正确

### 任务5：备份与验证
1. **备份所有修改前的文件**
2. 逐页验证修改
3. 进行回归测试

---

## 🔄 推荐工作流（使用Python脚本）

为了确保准确无误，我将编写自动化脚本来：
1. 扫描所有包含"Gil Melman"或"gil-melman"的文件
2. 创建修改前备份
3. 执行智能替换
4. 生成替换报告

### 脚本功能
- ✅ 自动备份
- ✅ 文本替换（姓名、URL、路径）
- ✅ 报告生成
- ✅ 可验证的变更

---

## ⚠️ 风险与注意事项

### 注意事项
1. **URL大小写问题**：保持URL命名一致性
2. **相对路径**：确保所有相对路径正确计算
3. **图片尺寸**：确保所有尺寸版本都有
4. **Feed文件**：feed文件的修改可能需要额外验证

### 回滚计划
- ✅ 所有修改前都会备份
- ✅ 可以随时从备份恢复

---

## ✅ 验收标准

1. 所有"Gil Melman"引用都已替换
2. 所有"gil-melman"URL引用都已替换
3. 新律师页面完整且可访问
4. 所有图片正常显示
5. 所有链接跳转正确
6. 页面布局和功能没有破坏
7. 可以通过离线方式完全访问

---

## 📝 下一步行动

**请用户提供：**
1. 新律师的完整姓名
2. 新律师的URL安全名称（例如：john-smith）
3. 新律师的职位信息
4. 新律师的个人介绍内容
5. 新律师的照片文件（或者我可以保留原照片作为占位符）

**确认后，我将开始执行自动化替换脚本！**
