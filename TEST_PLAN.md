# 表单数据表格重构项目 - 测试计划与调试指南

**项目名称**: 表单数据表格重构  
**版本**: 2.0  
**创建日期**: 2026-05-15  
**最后更新**: 2026-05-15

---

## 目录

1. [项目概述](#项目概述)
2. [测试环境准备](#测试环境准备)
3. [单元测试](#单元测试)
4. [集成测试](#集成测试)
5. [功能测试](#功能测试)
6. [性能测试](#性能测试)
7. [安全测试](#安全测试)
8. [浏览器兼容性测试](#浏览器兼容性测试)
9. [移动端测试](#移动端测试)
10. [已知问题与解决方案](#已知问题与解决方案)
11. [部署检查清单](#部署检查清单)
12. [回滚方案](#回滚方案)
13. [测试执行建议](#测试执行建议)
14. [调试工具与技巧](#调试工具与技巧)

---

## 项目概述

### 系统架构

本项目包含以下核心组件：

1. **前端界面** (`admin/forms.php`)
   - AG Grid 表格展示
   - 列配置管理
   - 数据筛选和搜索
   - CSV 导出功能
   - 响应式设计

2. **API接口** (`admin/api/forms_api.php`)
   - RESTful API 设计
   - 支持分页、筛选、排序
   - 数据导出功能
   - 批量删除功能

3. **数据迁移** (`admin/migrate_data.php`)
   - 从旧表迁移到新表结构
   - 支持多种字段类型处理
   - 批量处理机制

4. **数据库结构**
   - `form_submissions` - 原始数据表
   - `form_submissions_new` - 新数据结构
   - `form_fields` - 字段定义表
   - `form_field_values` - 字段值表

### 技术栈

- **前端**: HTML5, CSS3, JavaScript (ES6+)
- **UI框架**: AG Grid Community/Enterprise 31.0.0
- **后端**: PHP 8.x
- **数据库**: MySQL 8.x
- **安全**: CSRF Token, XSS 防护, SQL 预处理

---

## 测试环境准备

### 1. 本地环境要求

```bash
# PHP 版本
PHP >= 8.0

# MySQL 版本
MySQL >= 8.0

# 必需扩展
- PDO
- PDO_MYSQL
- JSON
- MBSTRING

# Node.js (可选，用于构建工具)
Node.js >= 16.0
npm >= 8.0
```

### 2. 数据库准备

```sql
-- 创建测试数据库
CREATE DATABASE bergpcc_test DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 使用测试数据库
USE bergpcc_test;

-- 创建表结构（从生产环境导入）
-- 确保表结构与生产环境一致
```

### 3. 测试数据准备

创建以下测试数据：

```sql
-- 测试用表单配置
INSERT INTO form_submissions (submission_id, form_id, form_name, data, ip, submitted_at)
VALUES
('TEST001', '2528', 'Crypto Fraud & Recovery', 
 '{"wpforms[fields][1]":"test@example.com","wpforms[fields][13][first]":"John","wpforms[fields][13][last]":"Doe","wpforms[fields][25]":"1234567890"}',
 '192.168.1.1', NOW()),
('TEST002', '4147', 'Meta Crypto Scam Ads Investigation',
 '{"wpforms[fields][1]":"admin@test.com","wpforms[fields][13][first]":"Admin","wpforms[fields][13][last]":"User"}',
 '192.168.1.2', NOW()),
('TEST003', '3952', 'Coinbase Data Breach',
 '{"wpforms[fields][1]":"breach@test.com","wpforms[fields][19][first]":"Jane","wpforms[fields][19][last]":"Smith"}',
 '192.168.1.3', NOW());
```

### 4. 配置文件

```php
// 测试环境配置
define('DB_HOST', 'localhost');
define('DB_NAME', 'bergpcc_test');
define('DB_USER', 'test_user');
define('DB_PASSWORD', 'test_password');
```

---

## 单元测试

### 1. 数据库操作测试

#### 1.1 连接测试

```php
// test_db_connection.php
function testDatabaseConnection() {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT 1");
        $result = $stmt->fetch();
        
        assert($result !== false, "Database connection failed");
        echo "✓ Database connection successful\n";
        return true;
    } catch (Exception $e) {
        echo "✗ Database connection failed: " . $e->getMessage() . "\n";
        return false;
    }
}
```

#### 1.2 CRUD 操作测试

```php
// test_crud_operations.php

// 测试创建
function testCreateSubmission() {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        INSERT INTO form_submissions_new 
        (submission_id, form_id, form_name, ip, submitted_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([
        'TEST_UNIT_' . time(),
        '2528',
        'Test Form',
        '127.0.0.1'
    ]);
    
    assert($result === true, "Create operation failed");
    assert($db->lastInsertId() > 0, "Last insert ID not returned");
    
    return $db->lastInsertId();
}

// 测试读取
function testReadSubmission($id) {
    $stmt = $db->prepare("SELECT * FROM form_submissions_new WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    assert($result !== false, "Read operation failed");
    return $result;
}

// 测试更新
function testUpdateSubmission($id) {
    $stmt = $db->prepare("UPDATE form_submissions_new SET form_name = ? WHERE id = ?");
    $result = $stmt->execute(['Updated Form', $id]);
    
    assert($result === true, "Update operation failed");
    return $result;
}

// 测试删除
function testDeleteSubmission($id) {
    $stmt = $db->prepare("DELETE FROM form_submissions_new WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    assert($result === true, "Delete operation failed");
    return $result;
}
```

#### 1.3 字段值存储测试

```php
// test_field_values.php

function testFieldValuesStorage() {
    $db = Database::getInstance()->getConnection();
    
    // 1. 创建提交记录
    $stmt = $db->prepare("
        INSERT INTO form_submissions_new 
        (submission_id, form_id, form_name, submitted_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute(['TEST_FIELDS_' . time(), '2528', 'Field Test']);
    $submissionId = $db->lastInsertId();
    
    // 2. 插入字段值
    $testFields = [
        ['label' => 'Email', 'value' => 'test@example.com'],
        ['label' => 'First Name', 'value' => 'John'],
        ['label' => 'Last Name', 'value' => 'Doe'],
        ['label' => 'Phone', 'value' => '1234567890'],
        ['label' => 'Disclaimer Acceptance', 'value' => '1']
    ];
    
    foreach ($testFields as $field) {
        $stmt = $db->prepare("
            INSERT INTO form_field_values (submission_id, field_label, field_value)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$submissionId, $field['label'], $field['value']]);
    }
    
    // 3. 验证字段值
    $stmt = $db->prepare("
        SELECT field_label, field_value 
        FROM form_field_values 
        WHERE submission_id = ?
    ");
    $stmt->execute([$submissionId]);
    $storedFields = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    foreach ($testFields as $field) {
        assert(
            isset($storedFields[$field['label']]) && 
            $storedFields[$field['label']] === $field['value'],
            "Field value mismatch: {$field['label']}"
        );
    }
    
    echo "✓ Field values storage test passed\n";
    
    return $submissionId;
}
```

### 2. API 测试

#### 2.1 列表接口测试

```php
// test_api_list.php

function testApiListEndpoint() {
    $baseUrl = 'http://localhost/admin/api/forms_api.php';
    
    // 测试基本列表请求
    $url = $baseUrl . '?action=list';
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    assert($data['success'] === true, "List API failed");
    assert(isset($data['data']), "Data key not found");
    assert(isset($data['pagination']), "Pagination not found");
    
    // 测试分页
    $url = $baseUrl . '?action=list&page=1&per_page=10';
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    assert($data['pagination']['page'] === 1, "Page parameter not working");
    assert($data['pagination']['per_page'] === 10, "Per page parameter not working");
    
    // 测试搜索
    $url = $baseUrl . '?action=list&search=john';
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    assert(is_array($data['data']), "Search results should be array");
    
    // 测试表单筛选
    $url = $baseUrl . '?action=list&form_id=2528';
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    assert(is_array($data['data']), "Form filter results should be array");
    
    echo "✓ API List endpoint tests passed\n";
}
```

#### 2.2 详情接口测试

```php
// test_api_detail.php

function testApiDetailEndpoint() {
    $baseUrl = 'http://localhost/admin/api/forms_api.php';
    
    // 测试获取存在的记录
    $url = $baseUrl . '?action=detail&id=1';
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    assert($data['success'] === true, "Detail API failed");
    assert(isset($data['data']['fields']), "Fields not included");
    
    // 测试获取不存在的记录
    $url = $baseUrl . '?action=detail&id=999999';
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    assert($data['success'] === false, "Should return error for non-existent record");
    assert($data['message'] === 'Submission not found', "Error message incorrect");
    
    // 测试无效 ID
    $url = $baseUrl . '?action=detail&id=invalid';
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    assert($data['success'] === false, "Should return error for invalid ID");
    
    echo "✓ API Detail endpoint tests passed\n";
}
```

#### 2.3 删除接口测试

```php
// test_api_delete.php

function testApiDeleteEndpoint() {
    $baseUrl = 'http://localhost/admin/api/forms_api.php';
    
    // 1. 创建测试数据
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        INSERT INTO form_submissions_new (submission_id, form_id, form_name, submitted_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute(['TEST_DEL_' . time(), '2528', 'Delete Test']);
    $testId = $db->lastInsertId();
    
    // 2. 测试删除单个记录
    $ch = curl_init($baseUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['ids' => [$testId]]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    assert($data['success'] === true, "Delete operation failed");
    
    // 3. 验证记录已删除
    $stmt = $db->prepare("SELECT * FROM form_submissions_new WHERE id = ?");
    $stmt->execute([$testId]);
    $result = $stmt->fetch();
    assert($result === false, "Record should be deleted");
    
    // 4. 测试批量删除
    $stmt->execute(['TEST_BATCH1_' . time(), '2528', 'Batch Test 1']);
    $id1 = $db->lastInsertId();
    $stmt->execute(['TEST_BATCH2_' . time(), '2528', 'Batch Test 2']);
    $id2 = $db->lastInsertId();
    
    $ch = curl_init($baseUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['ids' => [$id1, $id2]]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    assert($data['success'] === true, "Batch delete failed");
    
    echo "✓ API Delete endpoint tests passed\n";
}
```

#### 2.4 导出接口测试

```php
// test_api_export.php

function testApiExportEndpoint() {
    $baseUrl = 'http://localhost/admin/api/forms_api.php';
    
    // 测试 CSV 导出
    $url = $baseUrl . '?action=export&form_id=2528';
    
    // 注意：这个测试需要捕获实际的 HTTP 响应
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $response = curl_exec($ch);
    
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    curl_close($ch);
    
    // 验证 CSV 头
    assert(
        strpos($headers, 'Content-Type: text/csv') !== false,
        "Content-Type should be text/csv"
    );
    assert(
        strpos($headers, 'Content-Disposition: attachment') !== false,
        "Should have attachment disposition"
    );
    
    // 验证 CSV 内容
    $lines = explode("\n", trim($body));
    assert(count($lines) > 1, "CSV should have header and data");
    
    echo "✓ API Export endpoint tests passed\n";
}
```

### 3. 前端功能测试

#### 3.1 AG Grid 初始化测试

```javascript
// test_ag_grid_init.js

describe('AG Grid Initialization', () => {
    it('should initialize grid with correct options', () => {
        const gridDiv = document.querySelector('#myGrid');
        const gridApi = gridDiv.agGrid.getApi();
        
        // 验证列定义存在
        assert(gridApi.getColumnDefs().length > 0);
        
        // 验证行数据加载
        assert(gridApi.getRowCount() > 0);
        
        // 验证默认配置
        assert(gridApi.getGridOption('pagination') === true);
        assert(gridApi.getGridOption('rowSelection') === 'multiple');
    });
    
    it('should have correct column configuration', () => {
        const columnDefs = gridApi.getColumnDefs();
        
        // 验证复选框列
        assert(columnDefs[0].headerCheckboxSelection === true);
        
        // 验证基础列
        const baseColumns = ['submission_id', 'form_name', 'submitted_at', 'display_name', 'display_email'];
        baseColumns.forEach(col => {
            const found = columnDefs.find(c => c.field === col);
            assert(found !== undefined, `Column ${col} not found`);
        });
        
        // 验证操作列
        const actionCol = columnDefs.find(c => c.headerName === 'Actions');
        assert(actionCol !== undefined);
    });
});
```

#### 3.2 单元格渲染器测试

```javascript
// test_cell_renderers.js

describe('Cell Renderers', () => {
    it('should format Yes/No fields correctly', () => {
        const yesNoFields = [
            { key: 'disclaimer', value: '1', expected: 'Yes' },
            { key: 'disclaimer', value: '0', expected: 'No' },
            { key: 'disclaimer', value: 'yes', expected: 'Yes' },
            { key: 'disclaimer', value: 'no', expected: 'No' }
        ];
        
        yesNoFields.forEach(testCase => {
            const result = isYesNoField(testCase.key);
            assert(result === true);
        });
    });
    
    it('should truncate long text', () => {
        const longText = 'A'.repeat(150);
        const truncated = truncateText(longText, 100);
        
        assert(truncated.length === 100);
        assert(truncated.endsWith('...'));
    });
    
    it('should handle empty values', () => {
        const emptyValues = [null, undefined, '', '  '];
        
        emptyValues.forEach(value => {
            const result = formatValue(value);
            assert(result === '-');
        });
    });
    
    it('should format dates correctly', () => {
        const dateStr = '2026-05-15 14:30:00';
        const formatted = formatDate(dateStr);
        
        // 验证格式包含日期和时间
        assert(formatted.includes('2026'));
        assert(formatted.includes('May'));
        assert(formatted.includes('15'));
    });
});
```

---

## 集成测试

### 1. 前端与后端集成

#### 1.1 数据流测试

```javascript
// test_data_flow.js

describe('Data Flow Integration', () => {
    it('should load data from PHP into grid', () => {
        // 验证 PHP 传递的数据
        const phpData = <?php echo $submissionsJson; ?>;
        
        assert(Array.isArray(phpData));
        assert(phpData.length > 0);
        
        // 验证数据结构
        phpData.forEach(sub => {
            assert(sub.hasOwnProperty('submission_id'));
            assert(sub.hasOwnProperty('form_name'));
            assert(sub.hasOwnProperty('submitted_at'));
            assert(sub.hasOwnProperty('data'));
        });
        
        // 验证网格数据与 PHP 数据一致
        const gridData = gridApi.getRowData();
        assert(gridData.length === phpData.length);
    });
    
    it('should handle pagination correctly', () => {
        const initialCount = gridApi.getRowCount();
        
        // 改变每页大小
        gridApi.setGridOption('paginationPageSize', 10);
        assert(gridApi.getDisplayedRowCount() <= 10);
        
        // 改变页面
        gridApi.goToPage(2);
        // 验证页面改变
    });
    
    it('should sync grid selection with UI', () => {
        // 选择几行
        gridApi.selectIndex(0);
        gridApi.selectIndex(1);
        gridApi.selectIndex(2);
        
        // 验证选择状态
        const selectedRows = gridApi.getSelectedRows();
        assert(selectedRows.length === 3);
        
        // 验证全选复选框状态
        const selectAllCheckbox = document.getElementById('selectAll');
        assert(selectAllCheckbox.indeterminate === true);
    });
});
```

#### 1.2 搜索和筛选集成

```javascript
// test_search_filter.js

describe('Search and Filter Integration', () => {
    it('should filter data based on search input', () => {
        const searchInput = document.querySelector('input[name="search"]');
        
        // 输入搜索词
        searchInput.value = 'john';
        searchInput.dispatchEvent(new Event('input'));
        
        // 提交表单
        searchInput.form.submit();
        
        // 验证 PHP 端收到搜索参数
        // window.location.search 应该包含 search=john
    });
    
    it('should filter by form type', () => {
        const formSelect = document.querySelector('select[name="form_id"]');
        
        formSelect.value = '2528';
        formSelect.dispatchEvent(new Event('change'));
        
        // 验证筛选结果
        const gridData = gridApi.getRowData();
        gridData.forEach(row => {
            assert(row.form_id === '2528');
        });
    });
    
    it('should combine multiple filters', () => {
        // 同时应用搜索和表单筛选
        const searchInput = document.querySelector('input[name="search"]');
        const formSelect = document.querySelector('select[name="form_id"]');
        
        searchInput.value = 'test';
        formSelect.value = '2528';
        
        searchInput.form.submit();
        
        // 验证两个筛选条件都生效
    });
});
```

### 2. CSV 导出集成测试

```javascript
// test_csv_export.js

describe('CSV Export Integration', () => {
    it('should export all visible data', () => {
        const csvForm = document.getElementById('csvForm');
        
        // 模拟提交
        csvForm.querySelector('[name="csv_form_id"]').value = '2528';
        
        // 监听表单提交
        let submitted = false;
        csvForm.addEventListener('submit', (e) => {
            submitted = true;
            e.preventDefault();
        });
        
        // 触发下载
        downloadAll();
        
        assert(submitted === true);
        assert(csvForm.querySelector('[name="action"]').value === 'download_csv');
    });
    
    it('should export selected rows only', () => {
        // 选择特定行
        gridApi.selectIndex(0);
        gridApi.selectIndex(2);
        
        // 触发下载
        downloadSelected();
        
        // 验证提交的数据
        const csvIds = document.getElementById('csvIds');
        const ids = JSON.parse(csvIds.value);
        assert(ids.length === 2);
    });
});
```

### 3. 列配置管理集成

```javascript
// test_column_config.js

describe('Column Configuration Integration', () => {
    it('should save column state to localStorage', () => {
        // 移动列
        gridApi.moveColumn('display_name', 0);
        
        // 触发保存
        saveColumnState();
        
        // 验证 localStorage
        const savedState = localStorage.getItem(COLUMN_STATE_KEY);
        const parsed = JSON.parse(savedState);
        
        assert(parsed.columnState !== undefined);
        assert(Array.isArray(parsed.columnState));
    });
    
    it('should restore column state on page load', () => {
        // 设置特定的列状态
        const testState = {
            columnState: [
                { colId: 'display_name', width: 200 },
                { colId: 'display_email', width: 250 }
            ],
            sidebarOpen: true
        };
        localStorage.setItem(COLUMN_STATE_KEY, JSON.stringify(testState));
        
        // 重新加载页面
        location.reload();
        
        // 验证列状态已恢复
        const currentState = gridApi.getColumnState();
        // 验证列宽等属性
    });
    
    it('should export and import column config', () => {
        // 导出配置
        exportColumnConfig();
        
        // 验证下载的文件
        const downloads = document.querySelectorAll('a[download]');
        assert(downloads.length > 0);
        
        // 导入配置
        const input = document.querySelector('input[type="file"]');
        // 模拟文件导入
    });
});
```

---

## 功能测试

### 1. AG Grid 功能测试

#### 1.1 表格显示功能

```gherkin
# features/grid_display.feature

Feature: AG Grid Display
  Scenario: Display form submissions in grid
    Given I am on the form submissions page
    Then I should see the AG Grid table
    And the grid should contain submission data
    And the grid should have pagination enabled
    And the grid should have column sorting enabled

  Scenario: Verify column headers
    Given the grid is loaded
    Then I should see columns:
      | Column Name      |
      | ID               |
      | Form             |
      | Date             |
      | Name             |
      | Email            |
      | Actions          |
    And all columns should be sortable
    And all columns should be resizable
```

#### 1.2 排序和筛选功能

```gherkin
# features/sorting_filtering.feature

Feature: Sorting and Filtering
  Scenario: Sort by column
    Given I am viewing the form submissions grid
    When I click on the "Date" column header
    Then the grid should sort by date ascending
    When I click on the "Date" column header again
    Then the grid should sort by date descending

  Scenario: Filter by text
    Given I am viewing the form submissions grid
    When I type "john" in the Email filter
    Then only rows with "john" in email should be shown
    And the row count should update accordingly

  Scenario: Filter by number
    Given I am viewing the form submissions grid
    When I filter the ID column with value "1"
    Then only the row with ID 1 should be shown
```

#### 1.3 选择和批量操作

```gherkin
# features/selection.feature

Feature: Row Selection and Batch Operations
  Scenario: Select single row
    Given I am viewing the form submissions grid
    When I click the checkbox in a row
    Then the row should be selected
    And the selection count should update

  Scenario: Select multiple rows
    Given I am viewing the form submissions grid
    When I click the header checkbox
    Then all rows should be selected
    When I click the header checkbox again
    Then all rows should be deselected

  Scenario: Delete selected rows
    Given I have selected 3 rows
    When I click the "Delete Selected" button
    Then a confirmation dialog should appear
    When I confirm the deletion
    Then the selected rows should be removed from the grid
    And a success message should be displayed
```

### 2. 表单筛选功能测试

#### 2.1 搜索功能

```gherkin
# features/search.feature

Feature: Search Functionality
  Scenario: Search by name
    Given I am on the form submissions page
    When I enter "John Doe" in the search box
    And I click the Filter button
    Then I should see submissions matching "John Doe"
    And the URL should contain the search parameter

  Scenario: Search by email
    Given I am on the form submissions page
    When I enter "example.com" in the search box
    And I click the Filter button
    Then I should see submissions with emails containing "example.com"

  Scenario: Clear search
    Given I have performed a search
    When I click the "Clear" button
    Then the search box should be empty
    And all submissions should be shown
```

#### 2.2 表单类型筛选

```gherkin
# features/form_filter.feature

Feature: Form Type Filter
  Scenario: Filter by specific form
    Given I am on the form submissions page
    When I select "Crypto Fraud & Recovery" from the form dropdown
    And I click the Filter button
    Then only submissions from "Crypto Fraud & Recovery" should be shown

  Scenario: Show all forms
    Given I have filtered by a specific form
    When I select "All Forms" from the dropdown
    And I click the Filter button
    Then submissions from all forms should be shown
```

### 3. CSV 导出功能测试

```gherkin
# features/csv_export.feature

Feature: CSV Export
  Scenario: Export all submissions
    Given I am on the form submissions page
    When I click "Download All CSV"
    Then a CSV file should be downloaded
    And the file should be named with the current date
    And the file should contain all visible submissions

  Scenario: Export selected submissions
    Given I have selected 3 submissions
    When I click "Download Selected"
    Then a CSV file should be downloaded
    And the file should contain only the selected submissions

  Scenario: Export with form filter
    Given I have filtered by "Crypto Fraud & Recovery"
    When I click "Download All CSV"
    Then the exported CSV should contain only submissions from that form
```

### 4. 详情查看功能测试

```gherkin
# features/detail_view.feature

Feature: Submission Detail View
  Scenario: View submission details
    Given I am viewing the form submissions grid
    When I click the "View" button on a row
    Then a modal should open
    And the modal should show submission details
    And the modal should show all form fields

  Scenario: View includes metadata
    Given I am viewing submission details
    Then I should see:
      | Field          |
      | ID             |
      | Form Name      |
      | Submitted Date |
      | IP Address     |
    And the form data should be grouped by category

  Scenario: Close detail modal
    Given the detail modal is open
    When I click the close button
    Or I press Escape
    Then the modal should close
```

---

## 性能测试

### 1. 页面加载性能

#### 1.1 首屏加载时间

```bash
# 使用 Lighthouse 测试
lighthouse http://localhost/admin/forms.php \
  --output=json \
  --output-path=./reports/performance.json \
  --only-categories=performance

# 关键指标
# - First Contentful Paint (FCP) < 1.5s
# - Largest Contentful Paint (LCP) < 2.5s
# - Time to Interactive (TTI) < 3.5s
```

#### 1.2 AG Grid 加载时间

```javascript
// test_grid_performance.js

describe('AG Grid Performance', () => {
    it('should load grid within acceptable time', () => {
        const startTime = performance.now();
        
        // 初始化网格
        new agGrid.Grid(gridDiv, gridOptions());
        
        const endTime = performance.now();
        const loadTime = endTime - startTime;
        
        console.log(`Grid load time: ${loadTime}ms`);
        
        // 断言：网格加载时间应小于 500ms
        assert(loadTime < 500, `Grid took ${loadTime}ms to load`);
    });
    
    it('should handle large datasets efficiently', () => {
        // 生成大量测试数据
        const largeDataset = Array.from({ length: 10000 }, (_, i) => ({
            submission_id: i,
            form_name: `Form ${i % 10}`,
            submitted_at: new Date().toISOString(),
            data: { email: `user${i}@test.com` }
        }));
        
        const startTime = performance.now();
        gridApi.setGridOption('rowData', largeDataset);
        const endTime = performance.now();
        
        console.log(`Set ${largeDataset.length} rows in ${endTime - startTime}ms`);
        
        // 虚拟滚动应该使大数据量保持流畅
        assert(endTime - startTime < 1000);
    });
});
```

### 2. API 响应时间

#### 2.1 列表接口性能

```bash
# 使用 Apache Bench 测试
ab -n 100 -c 10 "http://localhost/admin/api/forms_api.php?action=list&per_page=20"

# 性能目标
# - 平均响应时间 < 200ms
# - 95th percentile < 500ms
# - 错误率 < 1%
```

```php
// test_api_performance.php

function testApiPerformance() {
    $iterations = 100;
    $times = [];
    
    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        
        // 执行 API 请求
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM form_submissions_new LIMIT 20");
        $stmt->execute();
        $stmt->fetchAll();
        
        $times[] = (microtime(true) - $start) * 1000;
    }
    
    $avg = array_sum($times) / count($times);
    $max = max($times);
    $min = min($times);
    
    echo "API Performance:\n";
    echo "Average: {$avg}ms\n";
    echo "Min: {$min}ms\n";
    echo "Max: {$max}ms\n";
    
    // 断言平均响应时间
    assert($avg < 200, "Average response time should be < 200ms");
}
```

#### 2.2 分页性能

```php
// test_pagination_performance.php

function testPaginationPerformance() {
    $pageSizes = [10, 20, 50, 100];
    
    foreach ($pageSizes as $size) {
        $start = microtime(true);
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM form_submissions_new LIMIT ? OFFSET 0");
        $stmt->execute([$size]);
        $results = $stmt->fetchAll();
        
        $time = (microtime(true) - $start) * 1000;
        
        echo "Page size {$size}: {$time}ms\n";
        
        // 断言：不同页面大小的响应时间应该相近
        assert($time < 300, "Pagination query took too long");
    }
}
```

### 3. 数据库性能

#### 3.1 索引检查

```sql
-- 检查现有索引
SHOW INDEX FROM form_submissions_new;

-- 应该存在的索引
-- - PRIMARY KEY (id)
-- - INDEX idx_submission_id (submission_id)
-- - INDEX idx_form_id (form_id)
-- - INDEX idx_submitted_at (submitted_at)
-- - INDEX idx_form_id_submitted (form_id, submitted_at)
```

#### 3.2 查询性能分析

```sql
-- 启用查询分析
SET profiling = 1;

-- 执行查询
SELECT * FROM form_submissions_new 
WHERE form_id = '2528' 
ORDER BY submitted_at DESC 
LIMIT 20;

-- 查看查询计划
EXPLAIN SELECT * FROM form_submissions_new 
WHERE form_id = '2528' 
ORDER BY submitted_at DESC 
LIMIT 20;

-- 查看性能分析结果
SHOW PROFILES;
```

---

## 安全测试

### 1. XSS 防护测试

#### 1.1 存储型 XSS

```bash
# test_xss_stored.sh

# 准备 XSS 测试数据
XSS_PAYLOAD='<script>alert("XSS")</script>'

# 通过表单提交 XSS
curl -X POST "http://localhost/admin/api/forms_api.php" \
  -H "Content-Type: application/json" \
  -d '{
    "form_id": "2528",
    "data": {
      "email": "'$XSS_PAYLOAD'",
      "name": "<img src=x onerror=alert(1)>"
    }
  }'

# 访问页面，检查是否弹出 alert
# 使用 Selenium 进行自动化测试
```

```php
// test_xss_sanitization.php

function testXSSSanitization() {
    $testCases = [
        '<script>alert("XSS")</script>',
        '<img src=x onerror=alert(1)>',
        'javascript:alert("XSS")',
        '<svg onload=alert("XSS")>',
        '{{constructor.constructor("alert(1)")()}}'
    ];
    
    foreach ($testCases as $payload) {
        // 在视图中渲染
        $escaped = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');
        
        // 验证特殊字符被转义
        assert(
            strpos($escaped, '<script>') === false,
            "Script tag should be escaped"
        );
        assert(
            strpos($escaped, '<img') === false,
            "Img tag should be escaped"
        );
    }
    
    echo "✓ XSS sanitization tests passed\n";
}
```

#### 1.2 反射型 XSS

```javascript
// test_reflected_xss.js

describe('Reflected XSS Protection', () => {
    it('should escape search input in URL', () => {
        const searchValue = '<script>alert("XSS")</script>';
        const encoded = encodeURIComponent(searchValue);
        
        // 导航到搜索 URL
        window.location.href = `?search=${encoded}`;
        
        // 重新加载后，检查输入框的值
        const searchInput = document.querySelector('input[name="search"]');
        const inputValue = searchInput.value;
        
        // 验证值被正确转义
        assert(inputValue.includes('&lt;'));
        assert(inputValue.includes('&gt;'));
    });
    
    it('should not execute reflected scripts', () => {
        let alertCalled = false;
        window.alert = () => { alertCalled = true; };
        
        const maliciousUrl = '?search=<script>alert("XSS")</script>';
        window.location.href = maliciousUrl;
        
        // 重新加载页面
        location.reload();
        
        // 验证 alert 未被调用
        assert(alertCalled === false, "XSS script should not execute");
    });
});
```

### 2. CSRF 防护测试

#### 2.1 CSRF Token 验证

```php
// test_csrf_protection.php

function testCSRFProtection() {
    // 1. 生成有效的 CSRF token
    $validToken = generateCsrfToken();
    
    // 2. 测试有效 token
    $result = verifyCsrfToken($validToken);
    assert($result === true, "Valid CSRF token should pass");
    
    // 3. 测试无效 token
    $result = verifyCsrfToken('invalid_token');
    assert($result === false, "Invalid CSRF token should fail");
    
    // 4. 测试空 token
    $result = verifyCsrfToken('');
    assert($result === false, "Empty CSRF token should fail");
    
    // 5. 测试过期 token (如果实现了过期时间)
    $expiredToken = generateCsrfToken(time() - 7200); // 2 hours ago
    $result = verifyCsrfToken($expiredToken);
    assert($result === false, "Expired CSRF token should fail");
    
    echo "✓ CSRF protection tests passed\n";
}
```

#### 2.2 CSRF 攻击模拟

```bash
# test_csrf_attack.sh

# 模拟 CSRF 攻击
curl -X POST "http://localhost/admin/api/forms_api.php" \
  -H "Content-Type: application/json" \
  -d '{"ids": [1, 2, 3]}'

# 应该返回 403 Forbidden 或错误信息
```

### 3. SQL 注入防护测试

#### 3.1 参数化查询验证

```php
// test_sql_injection.php

function testSQLInjectionProtection() {
    $testPayloads = [
        "' OR '1'='1",
        "'; DROP TABLE form_submissions; --",
        "1; DELETE FROM form_submissions WHERE 1=1",
        "UNION SELECT * FROM users--",
        "1' AND '1'='1",
        "<script>alert('XSS')</script>"
    ];
    
    $db = Database::getInstance()->getConnection();
    
    foreach ($testPayloads as $payload) {
        // 测试搜索功能
        $stmt = $db->prepare("
            SELECT * FROM form_submissions_new 
            WHERE form_name LIKE ? 
            LIMIT 10
        ");
        $stmt->execute(['%' . $payload . '%']);
        $results = $stmt->fetchAll();
        
        // 验证不会返回意外的数据
        // 如果使用了正确的参数化查询，这些 payload 应该是安全的
        
        echo "Payload '{$payload}' handled safely\n";
    }
    
    echo "✓ SQL injection protection tests passed\n";
}
```

#### 3.2 LIKE 查询转义

```php
// test_like_escape.php

function testLikeQueryEscape() {
    $db = Database::getInstance()->getConnection();
    
    // 测试包含通配符的输入
    $testInput = "test%_value"; // 包含 LIKE 通配符
    
    // 正确转义 LIKE 通配符
    $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $testInput);
    
    $stmt = $db->prepare("
        SELECT * FROM form_submissions_new 
        WHERE form_name LIKE ? 
        LIMIT 10
    ");
    $stmt->execute(['%' . $escaped . '%']);
    $results = $stmt->fetchAll();
    
    // 验证结果不包含意外匹配
    echo "✓ LIKE query escape test passed\n";
}
```

### 4. 访问控制测试

#### 4.1 认证检查

```php
// test_authentication.php

function testAuthenticationRequired() {
    // 模拟未登录请求
    session_unset();
    
    ob_start();
    include 'admin/forms.php';
    $content = ob_get_clean();
    
    // 应该重定向到登录页或显示登录表单
    assert(
        strpos($content, 'login') !== false || 
        headers_sent() === false,
        "Should require authentication"
    );
}

function testSessionTimeout() {
    // 设置会话超时
    $_SESSION['last_activity'] = time() - 4000; // 超过 1 小时
    
    // 访问受保护页面
    include 'admin/auth.php';
    
    // 应该清除会话并重定向
    assert(session_status() === PHP_SESSION_NONE);
}
```

---

## 浏览器兼容性测试

### 1. 桌面浏览器测试

#### 1.1 Chrome

```javascript
// 最低支持版本: Chrome 90+
const chromeTests = {
    'Grid Rendering': () => {
        const gridDiv = document.querySelector('#myGrid');
        const gridApi = gridDiv.agGrid.getApi();
        assert(gridApi !== undefined);
    },
    'Column Resize': () => {
        // 测试列拖拽调整大小
        const col = gridApi.getColumn('display_name');
        assert(col !== undefined);
    },
    'Virtual Scrolling': () => {
        // 验证虚拟滚动正常工作
        const renderedRows = document.querySelectorAll('.ag-row');
        assert(renderedRows.length < 100); // 不应该渲染所有行
    }
};
```

#### 1.2 Firefox

```javascript
// 最低支持版本: Firefox 88+
const firefoxTests = {
    'Grid Rendering': () => {
        // Firefox 特定的测试
        assert(typeof gridApi !== 'undefined');
    },
    'CSS Grid Layout': () => {
        // 测试 CSS Grid 布局
        const computedStyle = window.getComputedStyle(gridDiv);
        assert(computedStyle.display === 'grid' || computedStyle.display === 'block');
    }
};
```

#### 1.3 Safari

```javascript
// 最低支持版本: Safari 14+
const safariTests = {
    'Grid Rendering': () => {
        // Safari 特定的测试
    },
    'Touch Events': () => {
        // 测试触摸事件（如果有）
    }
};
```

#### 1.4 Edge

```javascript
// 最低支持版本: Edge 90+
const edgeTests = {
    'Grid Rendering': () => {
        // Edge 特定的测试
    }
};
```

### 2. 响应式设计测试

#### 2.1 断点测试

```css
/* 断点定义 */
/* - Mobile: < 480px */
/* - Tablet: 480px - 768px */
/* - Desktop: > 768px */
```

```javascript
// test_responsive.js

describe('Responsive Design', () => {
    const breakpoints = [
        { width: 375, height: 667, name: 'Mobile (iPhone SE)' },
        { width: 768, height: 1024, name: 'Tablet (iPad)' },
        { width: 1280, height: 800, name: 'Desktop' },
        { width: 1920, height: 1080, name: 'Large Desktop' }
    ];
    
    breakpoints.forEach(bp => {
        it(`should display correctly at ${bp.name}`, () => {
            // 调整窗口大小
            window.resizeTo(bp.width, bp.height);
            
            // 验证布局适应
            const gridContainer = document.querySelector('#myGrid');
            const containerWidth = gridContainer.offsetWidth;
            
            // 验证容器宽度符合预期
            assert(containerWidth <= bp.width);
            
            // 验证工具栏按钮文本显示
            const btnText = document.querySelector('.btn-text');
            if (bp.width < 768) {
                assert(window.getComputedStyle(btnText).display === 'none');
            } else {
                assert(window.getComputedStyle(btnText).display !== 'none');
            }
        });
    });
});
```

### 3. 打印样式测试

```css
/* test_print.css */

@media print {
    .toolbar,
    .sidebar,
    .loading-overlay {
        display: none !important;
    }
    
    .ag-theme-alpine {
        width: 100% !important;
        height: auto !important;
    }
    
    .ag-row {
        break-inside: avoid;
    }
}
```

```javascript
// test_print.js

describe('Print Functionality', () => {
    it('should prepare grid for printing', () => {
        // 触发打印预览
        window.print();
        
        // 验证工具栏被隐藏
        const toolbar = document.querySelector('.toolbar');
        const toolbarDisplay = window.getComputedStyle(toolbar).display;
        assert(toolbarDisplay === 'none');
    });
});
```

---

## 移动端测试

### 1. iOS 设备测试

```javascript
// test_ios_devices.js

const iosDevices = [
    { name: 'iPhone 12', width: 390, height: 844 },
    { name: 'iPhone SE', width: 375, height: 667 },
    { name: 'iPad Mini', width: 768, height: 1024 }
];

describe('iOS Compatibility', () => {
    iosDevices.forEach(device => {
        it(`should work on ${device.name}`, () => {
            // 使用 Chrome DevTools 模拟设备
            // 验证触摸交互
            const gridElement = document.querySelector('#myGrid');
            
            // 模拟触摸滚动
            const touchEvent = new TouchEvent('touchmove', {
                touches: [new Touch({
                    identifier: 0,
                    target: gridElement,
                    clientX: 100,
                    clientY: 100
                })]
            });
            gridElement.dispatchEvent(touchEvent);
            
            // 验证滚动行为
        });
    });
});
```

### 2. Android 设备测试

```javascript
// test_android_devices.js

const androidDevices = [
    { name: 'Pixel 5', width: 393, height: 851 },
    { name: 'Samsung Galaxy S21', width: 360, height: 800 },
    { name: 'Samsung Galaxy Tab S7', width: 800, height: 1280 }
];

describe('Android Compatibility', () => {
    androidDevices.forEach(device => {
        it(`should work on ${device.name}`, () => {
            // 模拟设备
            window.resizeTo(device.width, device.height);
            
            // 验证触摸事件
            // 验证滚动行为
        });
    });
});
```

### 3. 触摸交互测试

```javascript
// test_touch_interactions.js

describe('Touch Interactions', () => {
    it('should handle touch scroll', () => {
        const gridElement = document.querySelector('#myGrid');
        
        let startY = 0;
        let currentY = 0;
        
        // 模拟触摸开始
        gridElement.dispatchEvent(new TouchEvent('touchstart', {
            touches: [new Touch({ clientY: 100 })]
        }));
        
        // 模拟触摸移动
        gridElement.dispatchEvent(new TouchEvent('touchmove', {
            touches: [new Touch({ clientY: 150 })]
        }));
        
        // 模拟触摸结束
        gridElement.dispatchEvent(new TouchEvent('touchend', {
            changedTouches: [new Touch({ clientY: 200 })]
        }));
        
        // 验证滚动位置已更新
    });
    
    it('should handle long press for context menu', () => {
        // 测试长按行为（如果实现）
    });
    
    it('should handle swipe to delete', () => {
        // 测试滑动手势删除（如果实现）
    });
});
```

---

## 已知问题与解决方案

### 问题 1: AG Grid Enterprise 许可证警告

**问题描述**:
```
AG Grid Enterprise License Warning: 
AG Grid Enterprise v31.0.0 is valid for development and internal use only.
```

**影响**: 在开发环境中显示警告信息

**解决方案**:
1. 如果用于生产环境，需要购买 AG Grid Enterprise 许可证
2. 对于开发/测试目的，可以在 `gridOptions()` 中设置许可证密钥
3. 或者考虑使用 AG Grid Community 版本（功能受限但免费）

**临时方案**:
```javascript
agGrid.LicenseManager.setLicenseKey('your_license_key');
```

### 问题 2: 大量数据加载性能

**问题描述**: 当 `form_submissions` 表中有超过 10,000 条记录时，页面加载变慢

**根本原因**:
- 所有数据在页面加载时一次性获取
- AG Grid 虽然有虚拟滚动，但初始数据量大时仍影响性能

**解决方案**:
1. 实现服务器端分页（推荐）
2. 使用 API 动态加载数据
3. 限制初始加载的记录数

**实施步骤**:
```javascript
// 修改 gridOptions 使用服务器端分页
const gridOptions = {
    // ...其他配置
    pagination: true,
    paginationPageSize: 20,
    cacheBlockSize: 20,
    maxBlocksInCache: 10,
    rowModelType: 'serverSide', // 启用服务器端模式
};
```

### 问题 3: 列状态持久化冲突

**问题描述**: 多个浏览器标签页共享同一个 localStorage，可能导致列状态冲突

**解决方案**:
1. 在保存列状态时添加标签页标识
2. 或者在页面加载时检查并合并状态

```javascript
const COLUMN_STATE_KEY = 'forms_grid_column_state_v2';
const TAB_ID = 'tab_' + Math.random().toString(36).substr(2, 9);

function saveColumnState() {
    const savedState = localStorage.getItem(COLUMN_STATE_KEY);
    let stateObj = savedState ? JSON.parse(savedState) : {};
    
    // 添加标签页特定的状态
    stateObj[TAB_ID] = {
        columnState: gridApi.getColumnState(),
        timestamp: Date.now()
    };
    
    localStorage.setItem(COLUMN_STATE_KEY, JSON.stringify(stateObj));
}
```

### 问题 4: CSV 导出编码问题

**问题描述**: 导出的 CSV 文件在某些 Excel 版本中显示乱码

**根本原因**: UTF-8 编码的 BOM（Byte Order Mark）未正确添加

**解决方案**:
```php
// 在输出 CSV 之前添加 BOM
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($output, $headers);
```

### 问题 5: 日期筛选器格式问题

**问题描述**: AG Grid 的日期筛选器在某些浏览器中无法正确解析日期

**解决方案**:
```javascript
// 使用自定义日期过滤
const columnDefs = [
    {
        field: 'submitted_at',
        filter: 'agDateColumnFilter',
        filterParams: {
            comparator: function(filterDate, cellValue) {
                if (cellValue == null) return -1;
                const cellDate = new Date(cellValue);
                
                if (cellDate < filterDate) return -1;
                if (cellDate > filterDate) return 1;
                return 0;
            }
        }
    }
];
```

### 问题 6: 数据迁移脚本处理复杂字段

**问题描述**: 迁移脚本在处理某些复杂 JSON 结构时可能丢失数据

**已知影响字段**:
- 嵌套数组（如 `wpforms[fields][26]` 地址字段）
- 多值字段（如复选框）

**解决方案**:
1. 审查迁移后的数据完整性
2. 对于复杂字段，手动验证数据
3. 如有需要，修改 `normalizeFieldValue()` 函数

### 问题 7: CSRF Token 过期问题

**问题描述**: 用户长时间停留在表单页面，CSRF token 过期后提交失败

**解决方案**:
1. 在表单页面添加 token 刷新机制
2. 或者延长 token 有效期

```javascript
// 在提交前检查 token 有效性
async function submitWithTokenRefresh(form) {
    const token = form.querySelector('[name="csrf_token"]');
    
    // 检查 token 是否即将过期（例如 5 分钟内）
    const tokenAge = Date.now() - token.dataset.generated;
    if (tokenAge > CSRF_TOKEN_LIFETIME - 300000) {
        // 获取新 token
        const response = await fetch('/admin/api/refresh_csrf.php');
        const data = await response.json();
        token.value = data.token;
        token.dataset.generated = Date.now();
    }
    
    form.submit();
}
```

### 问题 8: 移动端触摸滚动不流畅

**问题描述**: 在移动设备上滚动 AG Grid 时不够流畅

**解决方案**:
```css
.ag-theme-alpine {
    -webkit-overflow-scrolling: touch;
    overflow-y: auto;
}

.ag-body-viewport {
    overflow: auto;
    -webkit-overflow-scrolling: touch;
}
```

---

## 部署检查清单

### 部署前检查

#### 1. 代码审查

- [ ] 所有 PHP 文件语法检查通过
- [ ] 所有 JavaScript 文件无语法错误
- [ ] 所有 CSS 文件无语法错误
- [ ] 敏感信息（如数据库密码）已配置为环境变量
- [ ] 所有硬编码的 URL 已更新为实际域名
- [ ] CSRF 密钥已更改
- [ ] 管理员密码已更改

#### 2. 数据库检查

- [ ] 数据库迁移脚本已准备
- [ ] 数据库备份已创建
- [ ] 新表结构已验证
- [ ] 索引已正确创建
- [ ] 权限已正确设置
- [ ] 数据迁移测试已完成

#### 3. 文件检查

```
admin/
├── forms.php (✓ 已更新)
├── api/
│   └── forms_api.php (✓ 已创建)
├── config.php (✓ 已验证)
├── db.php (✓ 已验证)
├── header.php
├── footer.php
├── migrate_data.php (✓ 已创建)
└── css/ (如有必要)

需要上传的文件:
1. admin/forms.php
2. admin/api/forms_api.php
3. admin/migrate_data.php
```

#### 4. 配置检查

```php
// config.php 配置检查清单
- DB_HOST: 68.178.239.252
- DB_NAME: bergpcc
- DB_USER: Klinevargas
- DB_PASSWORD: [已设置]
- ADMIN_USERNAME: bergpc_admin
- ADMIN_FALLBACK_PASSWORD: admin123
- FORM_CONFIGS: 8 个表单配置
```

#### 5. 依赖检查

- [ ] AG Grid CDN 可访问
- [ ] 所有 CDN 资源已验证
- [ ] 字体加载正常

### 部署执行

#### 步骤 1: 创建数据库备份

```bash
# 使用 mysqldump 备份
mysqldump -h 68.178.239.252 -u Klinevargas -p bergpcc > backup_$(date +%Y%m%d_%H%M%S).sql

# 备份表结构
mysqldump -h 68.178.239.252 -u Klinevargas -p bergpcc --no-data > structure_$(date +%Y%m%d_%H%M%S).sql
```

#### 步骤 2: 创建新表

```sql
-- 创建 form_submissions_new 表
CREATE TABLE form_submissions_new (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id VARCHAR(255) NOT NULL UNIQUE,
    form_id VARCHAR(50) NOT NULL,
    form_name VARCHAR(255) NOT NULL,
    ip VARCHAR(45),
    user_agent TEXT,
    submitted_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_form_id (form_id),
    INDEX idx_submitted_at (submitted_at),
    INDEX idx_form_submitted (form_id, submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 创建 form_fields 表
CREATE TABLE form_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_id VARCHAR(50) NOT NULL,
    field_key VARCHAR(255) NOT NULL,
    field_label VARCHAR(255) NOT NULL,
    field_type VARCHAR(50) DEFAULT 'text',
    display_order INT DEFAULT 999,
    INDEX idx_form_id (form_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 创建 form_field_values 表
CREATE TABLE form_field_values (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    field_id INT NOT NULL,
    field_value TEXT,
    FOREIGN KEY (submission_id) REFERENCES form_submissions_new(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES form_fields(id) ON DELETE CASCADE,
    INDEX idx_submission_id (submission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### 步骤 3: 上传文件

```bash
# 上传核心文件
scp admin/forms.php user@server:/path/to/admin/
scp -r admin/api/ user@server:/path/to/admin/
scp admin/migrate_data.php user@server:/path/to/admin/
```

#### 步骤 4: 运行数据迁移

```bash
# SSH 到服务器
ssh user@server

# 进入管理目录
cd /path/to/admin

# 运行迁移脚本
php migrate_data.php

# 预期输出:
# ============================================================
# 表单数据迁移脚本 v2.0
# ============================================================
# 已加载 8 个表单的字段配置
# 共有 XXX 条记录需要迁移
# [14:30:00] 处理记录 0 - 100...
# ...
# 迁移完成！
# 成功迁移: XXX 条
# 跳过（已迁移）: XXX 条
# 错误: XXX 条
```

#### 步骤 5: 验证迁移

```sql
-- 检查记录数
SELECT 
    (SELECT COUNT(*) FROM form_submissions) AS original_count,
    (SELECT COUNT(*) FROM form_submissions_new) AS new_count;

-- 检查数据完整性
SELECT COUNT(*) as total,
       SUM(CASE WHEN EXISTS (SELECT 1 FROM form_field_values WHERE submission_id = fs.id) THEN 1 ELSE 0 END) as with_fields
FROM form_submissions_new fs;
```

### 部署后检查

#### 1. 功能验证

- [ ] 主页可访问
- [ ] 登录功能正常
- [ ] 表单提交列表显示
- [ ] AG Grid 正常渲染
- [ ] 搜索功能正常
- [ ] 筛选功能正常
- [ ] 分页功能正常
- [ ] 详情查看正常
- [ ] 删除功能正常
- [ ] CSV 导出正常

#### 2. 性能验证

- [ ] 页面加载时间 < 3 秒
- [ ] API 响应时间 < 500ms
- [ ] 无 JavaScript 控制台错误
- [ ] 无 PHP 错误日志

#### 3. 安全验证

- [ ] CSRF 保护生效
- [ ] XSS 防护生效
- [ ] SQL 注入防护生效
- [ ] 访问控制正常

---

## 回滚方案

### 快速回滚步骤

#### 方案 1: 文件级别回滚

```bash
#!/bin/bash
# rollback.sh

# 1. 停止网站（可选）
# systemctl stop apache2

# 2. 恢复旧文件
cp /path/to/backup/forms.php /path/to/admin/forms.php
rm -rf /path/to/admin/api/
cp -r /path/to/backup/api /path/to/admin/

# 3. 恢复数据库（如果需要）
mysql -h 68.178.239.252 -u Klinevargas -p bergpcc < /path/to/backup/database.sql

# 4. 重启网站（可选）
# systemctl start apache2

echo "回滚完成！"
```

#### 方案 2: 数据库回滚

```sql
-- 如果只需要回滚数据库更改

-- 1. 保留新表作为备份
RENAME TABLE form_submissions_new TO form_submissions_new_backup;

-- 2. 恢复原始表
RENAME TABLE form_submissions TO form_submissions_old;
RENAME TABLE form_submissions_backup TO form_submissions;

-- 3. 如果需要，完全删除新表
-- DROP TABLE form_submissions_new_backup;
-- DROP TABLE form_field_values;
-- DROP TABLE form_fields;
```

### 分阶段回滚

#### 阶段 1: 紧急回滚（0-5 分钟）

```bash
# 立即恢复文件
git checkout HEAD~1 -- admin/forms.php
git checkout HEAD~1 -- admin/api/
```

#### 阶段 2: 数据库回滚（5-15 分钟）

```sql
-- 创建临时表保存当前数据
CREATE TABLE form_submissions_temp AS SELECT * FROM form_submissions_new;

-- 恢复原始表
TRUNCATE TABLE form_submissions;
INSERT INTO form_submissions 
SELECT submission_id, form_id, form_name, data, ip, user_agent, submitted_at 
FROM form_submissions_backup;

-- 验证恢复
SELECT COUNT(*) FROM form_submissions;
```

#### 阶段 3: 数据修复（15-30 分钟）

如果问题仅影响部分数据：

```sql
-- 识别问题记录
SELECT * FROM form_submissions_new 
WHERE submitted_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
AND /* 其他问题条件 */;

-- 手动修复或删除问题记录
DELETE FROM form_submissions_new WHERE /* 问题条件 */;

-- 从备份恢复问题记录
INSERT INTO form_submissions_new 
SELECT * FROM form_submissions_backup 
WHERE /* 问题条件 */;
```

### 回滚检查清单

- [ ] 旧版本文件已恢复
- [ ] 数据库已恢复到原始状态
- [ ] 旧版本功能正常
- [ ] 无数据丢失
- [ ] 监控告警已确认解决

---

## 测试执行建议

### 测试执行顺序

1. **单元测试** → 首先验证各组件独立工作
2. **集成测试** → 验证组件之间的协作
3. **功能测试** → 验证完整业务流程
4. **性能测试** → 验证系统在负载下的表现
5. **安全测试** → 验证安全性
6. **兼容性测试** → 验证跨平台兼容性

### 测试环境配置

```yaml
# testing_environment.yml
environment:
  name: staging
  url: https://staging.bergppc.com
  database:
    host: staging-db.bergppc.com
    name: bergpcc_staging
  
  test_data:
    total_submissions: 10000
    forms_count: 8
    users_count: 50
  
  timeouts:
    api_response: 500ms
    page_load: 3000ms
    grid_render: 500ms
```

### 关键测试用例优先级

#### P0 - 必须通过（阻塞发布）

1. 基础功能
   - [ ] 页面加载无错误
   - [ ] 数据正确显示
   - [ ] 分页正常工作
   - [ ] 删除功能正常

2. 安全
   - [ ] CSRF 保护有效
   - [ ] XSS 防护有效
   - [ ] SQL 注入防护有效

#### P1 - 重要（影响用户体验）

1. 功能完整性
   - [ ] 搜索功能正常
   - [ ] 筛选功能正常
   - [ ] CSV 导出正常
   - [ ] 列配置保存/恢复正常

2. 性能
   - [ ] 页面加载 < 3s
   - [ ] API 响应 < 500ms
   - [ ] 滚动流畅

#### P2 - 优化（可后续改进）

1. 兼容性
   - [ ] 移动端功能完整
   - [ ] 响应式布局正常
   - [ ] 触摸交互流畅

2. 用户体验
   - [ ] 动画流畅
   - [ ] 提示信息清晰
   - [ ] 错误处理友好

### 测试报告模板

```markdown
# 测试报告 - [日期]

## 执行摘要
- 总测试用例: XX
- 通过: XX (XX%)
- 失败: XX (XX%)
- 阻塞: XX

## 测试结果详情

### P0 测试结果
| 用例 | 状态 | 备注 |
|------|------|------|
| 页面加载 | ✓ 通过 | |
| 数据显示 | ✓ 通过 | |
| ... | ... | ... |

### 问题列表
| ID | 描述 | 严重性 | 状态 |
|----|------|--------|------|
| BUG-001 | 描述 | 高 | 已修复 |

## 建议
- 可以发布
- 需要修复 P0 问题后发布
```

---

## 调试工具与技巧

### 1. 浏览器开发者工具

#### Chrome DevTools

```javascript
// 在控制台中调试 AG Grid

// 获取网格 API
const gridApi = document.querySelector('#myGrid').agGrid.getApi();

// 打印所有行数据
console.table(gridApi.getRowData());

// 打印列定义
console.log('Columns:', gridApi.getColumnDefs());

// 打印选中行
console.log('Selected:', gridApi.getSelectedRows());

// 导出网格状态
console.log('State:', JSON.stringify(gridApi.getColumnState(), null, 2));

// 手动刷新数据
gridApi.refreshCells();
gridApi.redrawRows();
```

### 2. PHP 调试

#### Xdebug 配置

```ini
[xdebug]
zend_extension=xdebug.so
xdebug.mode=develop,debug
xdebug.start_with_request=trigger
xdebug.client_host=localhost
xdebug.client_port=9000
xdebug.log_level=0
```

#### 断点调试

```php
// 在关键位置添加断点
if ($debug_mode) {
    file_put_contents('/tmp/debug.log', 
        date('Y-m-d H:i:s') . ' - ' . 
        json_encode(['sql' => $sql, 'params' => $params]) . "\n", 
        FILE_APPEND);
}

// 查看日志
tail -f /tmp/debug.log
```

### 3. 网络请求调试

#### API 请求测试

```bash
# 测试列表 API
curl -v "http://localhost/admin/api/forms_api.php?action=list&page=1&per_page=10"

# 测试详情 API
curl -v "http://localhost/admin/api/forms_api.php?action=detail&id=1"

# 测试删除 API
curl -X POST "http://localhost/admin/api/forms_api.php" \
  -H "Content-Type: application/json" \
  -d '{"ids": [1, 2]}'

# 测试导出 API
curl -v "http://localhost/admin/api/forms_api.php?action=export&form_id=2528" \
  -o export.csv
```

### 4. 数据库调试

#### 查看查询执行计划

```sql
-- 启用查询分析
SET profiling = 1;

-- 执行查询
SELECT * FROM form_submissions_new 
WHERE form_id = '2528' 
ORDER BY submitted_at DESC 
LIMIT 20;

-- 查看执行时间
SHOW PROFILES;

-- 查看详细的执行计划
EXPLAIN SELECT * FROM form_submissions_new 
WHERE form_id = '2528' 
ORDER BY submitted_at DESC 
LIMIT 20;
```

#### 调试迁移脚本

```bash
# 以调试模式运行迁移
php -d xdebug.remote_autostart=1 migrate_data.php

# 或添加详细输出
php -v migrate_data.php 2>&1 | tee migration.log
```

### 5. 常见问题快速诊断

```bash
#!/bin/bash
# diagnostic.sh

echo "=== System Diagnostics ==="

echo -e "\n[1] PHP Version"
php -v

echo -e "\n[2] Required PHP Extensions"
php -m | grep -E "pdo|mysql|json|mbstring"

echo -e "\n[3] Database Connection"
php -r "
try {
    \$db = Database::getInstance()->getConnection();
    echo '✓ Database connected successfully\n';
    echo 'Server version: ' . \$db->getAttribute(PDO::ATTR_SERVER_VERSION);
} catch (Exception \$e) {
    echo '✗ Connection failed: ' . \$e->getMessage();
}
"

echo -e "\n[4] Table Existence"
php -r "
\$db = Database::getInstance()->getConnection();
\$tables = ['form_submissions', 'form_submissions_new', 'form_fields', 'form_field_values'];
foreach (\$tables as \$table) {
    \$stmt = \$db->query(\"SHOW TABLES LIKE '\$table'\");
    if (\$stmt->fetch()) {
        echo \"✓ \$table exists\n\";
    } else {
        echo \"✗ \$table missing\n\";
    }
}
"

echo -e "\n[5] File Permissions"
ls -lh admin/forms.php admin/api/forms_api.php admin/migrate_data.php

echo -e "\n[6] Recent Error Logs"
tail -n 20 /var/log/apache2/error.log 2>/dev/null || \
tail -n 20 /var/log/nginx/error.log 2>/dev/null || \
echo "Unable to read error logs"

echo -e "\n=== Diagnostics Complete ==="
```

---

## 附录

### A. API 端点参考

| 端点 | 方法 | 参数 | 描述 |
|------|------|------|------|
| `/api/forms_api.php?action=list` | GET | page, per_page, search, form_id, date_from, date_to | 获取提交列表 |
| `/api/forms_api.php?action=detail` | GET | id | 获取提交详情 |
| `/api/forms_api.php?action=export` | GET | form_id, search, date_from, date_to | 导出 CSV |
| `/api/forms_api.php?action=forms` | GET | - | 获取表单列表 |
| `/api/forms_api.php` | POST | ids (JSON) | 删除提交 |

### B. 数据库表结构

```sql
-- form_submissions (原始表 - 保留用于兼容)
form_id: VARCHAR(50)
submission_id: VARCHAR(255)
form_name: VARCHAR(255)
data: JSON
ip: VARCHAR(45)
user_agent: TEXT
submitted_at: DATETIME

-- form_submissions_new (新表)
id: INT AUTO_INCREMENT PRIMARY KEY
submission_id: VARCHAR(255) UNIQUE
form_id: VARCHAR(50)
form_name: VARCHAR(255)
ip: VARCHAR(45)
user_agent: TEXT
submitted_at: DATETIME
created_at: DATETIME

-- form_fields
id: INT AUTO_INCREMENT PRIMARY KEY
form_id: VARCHAR(50)
field_key: VARCHAR(255)
field_label: VARCHAR(255)
field_type: VARCHAR(50)
display_order: INT

-- form_field_values
id: INT AUTO_INCREMENT PRIMARY KEY
submission_id: INT (FK)
field_id: INT (FK)
field_value: TEXT
```

### C. 常用命令参考

```bash
# 数据库操作
mysqldump -h host -u user -p database > backup.sql
mysql -h host -u user -p database < backup.sql

# 文件操作
scp local/file user@host:/path/to/file
rsync -avz local/ user@host:/path/to/

# 服务操作
sudo systemctl restart apache2
sudo systemctl restart php-fpm
sudo systemctl status apache2

# 日志查看
tail -f /var/log/apache2/error.log
grep "error" /var/log/apache2/access.log
```

---

**文档版本**: 1.0  
**维护者**: 开发团队  
**下次审查**: 2026-06-15
