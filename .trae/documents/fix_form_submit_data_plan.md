# 修复表单提交和管理后台显示的计划

## 问题分析

### 问题 1：表单提交返回 "No form data provided"

**根本原因**：PHP 自动将 `wpforms[fields][13][first]` 这样的 POST 键名解析为嵌套数组：

```php
$_POST = [
    'wpforms' => [
        'fields' => [
            13 => ['first' => 'John', 'last' => 'Doe'],
            1 => 'email@example.com',
        ]
    ],
    'form_id' => '2528',
]
```

但 `submit.php` 第 39-43 行在 `$_POST` 顶层查找以 `wpforms[fields]` 开头的键名，这永远不会匹配，因为顶层键只是 `wpforms`。

同样，蜜罐检测（第 27-32 行）也无法工作，因为它也在 `$_POST` 顶层查找。

### 问题 2：管理后台字段显示

`forms.php` 中硬编码了几个字段键名来提取 Name/Email/Phone，但如果提交修复后数据能正确存储，现有的键名格式 `wpforms[fields][13][first]` 应该能正确匹配。

### 问题 3：统一全站表单

用户要求所有表单都按照 fraud-recovery 页面（form 2528）的字段对齐。但不同表单有不同用途：

* **律师页面表单（3600/3778）**：只有 Name/Email/Disclaimer，是简单的联系方式表单

* **业务诉讼表单（4296）**：Name/Email/Phone/Address/Message

* **加密货币表单（2528/3952/4147）**：最完整的表单，20+ 字段

**建议方案**：不修改 HTML 表单结构（风险大、工作量大），而是修复 PHP 后端使其能正确接收和存储所有表单数据，同时改进管理后台展示所有提交的字段。

## 修复方案

### 1. 修复 `api/submit.php`（核心修复）

* 重写表单数据提取逻辑，从 `$_POST['wpforms']['fields']` 嵌套数组中提取数据

* 将嵌套数组扁平化为 `wpforms[fields][13][first]` 格式的键名，保持与管理后台的兼容性

* 修复蜜罐检测逻辑，从嵌套数组中检测

* 修复重定向：根据表单类型跳转到不同的感谢页面

### 2. 改进 `admin/forms.php`

* 在表格中增加 Phone 列

* 改进详情弹窗，将字段 ID 映射为可读的标签名

* CSV 导出增加更多字段列

### 3. 不修改 HTML 表单

* 后台表单不修复。

* 律师页面不需要 20+ 字段的长表单

* 修复后端数据处理即可正确收集和展示所有表单数据

## 执行步骤

1. 重写 `api/submit.php` 的数据提取逻辑
2. 改进 `admin/forms.php` 的字段展示
3. 提交代码

## 文件修改列表

* `api/submit.php` - 重写数据提取逻辑

* `admin/forms.php` - 改进字段展示

