# 表单提交查询问题修复计划

## 问题分析

`https://bergppc.com/admin-x7k9m/forms.php` 页面查询不到数据库中已有的3条数据。

### 可能的原因

1. **PDO LIMIT/OFFSET 参数绑定问题**：
   - 在 forms.php 第 138-140 行：
     ```php
     $sql .= " ORDER BY submitted_at DESC LIMIT ? OFFSET ?";
     $params[] = $perPage;
     $params[] = $offset;
     ```
   - `$perPage` 和 `$offset` 虽然是整数，但在 PDO 中绑定时可能被当作字符串处理
   - MySQL 的 LIMIT 和 OFFSET 需要明确的整数参数

2. **PDO 配置问题**：
   - db.php 第 10 行设置了 `PDO::ATTR_EMULATE_PREPARES, true`
   - 这可能导致参数绑定时类型不准确

## 修复方案

### 方案一：显式绑定整数参数（推荐）

在执行查询时，显式使用 `bindValue()` 并指定 `PDO::PARAM_INT` 类型：

```php
$stmt = $db->prepare($sql);
// 绑定其他参数
foreach ($params as $index => $param) {
    if ($index < count($params) - 2) { // 不是最后两个参数
        $stmt->bindValue($index + 1, $param);
    }
}
// 显式绑定 LIMIT 和 OFFSET 为整数
$stmt->bindValue(count($params) - 1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(count($params), $offset, PDO::PARAM_INT);
$stmt->execute();
```

### 方案二：直接内联整数参数

将 LIMIT 和 OFFSET 直接嵌入 SQL 语句（需要先确保是整数）：

```php
$perPage = intval($perPage);
$offset = intval($offset);
$sql .= " ORDER BY submitted_at DESC LIMIT $perPage OFFSET $offset";
```

### 方案三：移除 LIMIT/OFFSET 进行测试

临时移除分页逻辑，测试是否能查询到数据，以确认问题是否出在分页逻辑。

## 实施步骤

### 步骤1：修改 forms.php 文件

找到查询执行部分（约第 142-144 行），修改为：

```php
$stmt = $db->prepare($sql);

// 先绑定除 LIMIT 和 OFFSET 之外的所有参数
$paramCount = count($params);
for ($i = 0; $i < $paramCount - 2; $i++) {
    $stmt->bindValue($i + 1, $params[$i]);
}

// 显式绑定 LIMIT 和 OFFSET 为整数类型
$stmt->bindValue($paramCount - 1, intval($params[$paramCount - 2]), PDO::PARAM_INT);
$stmt->bindValue($paramCount, intval($params[$paramCount - 1]), PDO::PARAM_INT);

$stmt->execute();
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

### 步骤2：添加调试信息（可选）

在查询前添加调试输出，帮助确认参数是否正确：

```php
error_log("SQL: $sql");
error_log("Params: " . print_r($params, true));
```

### 步骤3：测试修复

访问表单提交页面，确认能够查询到数据。

## 文件修改

| 文件 | 修改内容 |
|------|----------|
| `admin/forms.php` | 修复 PDO 参数绑定，添加显式的整数类型绑定 |

## 风险评估

- **低风险**：仅修改查询执行逻辑，不影响其他功能
- **无需数据库修改**：不需要修改数据库表结构

## 预期结果

修复后，`forms.php` 页面将能够正确查询并显示数据库中的所有表单提交记录。