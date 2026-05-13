# MySQL 数据库迁移方案

## 一、当前方案分析

### 1.1 当前存储结构

| 数据类型 | 存储方式 | 文件路径 |
|----------|----------|----------|
| 律师数据 | JSON文件 | `data/lawyers.json` |
| 表单提交 | JSON文件 | `data/submissions/{form_id}.json` |

### 1.2 当前方案的局限性

| 问题 | 说明 |
|------|------|
| 查询性能 | 大数据量时需要读取整个JSON文件 |
| 复杂查询 | 不支持SQL查询、JOIN等 |
| 并发访问 | 多个请求同时写入可能导致数据损坏 |
| 数据完整性 | 无事务支持 |
| 扩展性 | JSON文件大小增长后性能下降 |

---

## 二、MySQL迁移优势

| 优势 | 说明 |
|------|------|
| 查询性能 | SQL索引加速查询 |
| 复杂查询 | 支持WHERE、ORDER BY、JOIN、GROUP BY |
| 并发控制 | 数据库锁机制 |
| 事务支持 | ACID事务保证数据完整性 |
| 扩展性 | 支持百万级数据 |
| 备份恢复 | MySQL自带备份工具 |

---

## 三、数据库设计

### 3.1 数据库表结构

#### 表1: `lawyers` - 律师信息

| 字段名 | 类型 | 约束 | 说明 |
|--------|------|------|------|
| id | INT | PRIMARY KEY AUTO_INCREMENT | 主键 |
| name | VARCHAR(255) | NOT NULL | 律师姓名 |
| slug | VARCHAR(255) | UNIQUE NOT NULL | URL路径标识 |
| title | VARCHAR(255) | | 职位头衔 |
| bio | TEXT | | 简介 |
| image | VARCHAR(500) | | 图片路径 |
| image_type | ENUM('local','url') | DEFAULT 'local' | 图片类型 |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP | 创建时间 |
| updated_at | DATETIME | ON UPDATE CURRENT_TIMESTAMP | 更新时间 |

#### 表2: `form_submissions` - 表单提交记录

| 字段名 | 类型 | 约束 | 说明 |
|--------|------|------|------|
| id | INT | PRIMARY KEY AUTO_INCREMENT | 主键 |
| submission_id | VARCHAR(50) | UNIQUE NOT NULL | 唯一提交ID |
| form_id | VARCHAR(20) | NOT NULL | 表单ID |
| form_name | VARCHAR(255) | | 表单名称 |
| data | LONGTEXT | NOT NULL | 表单数据(JSON格式) |
| ip | VARCHAR(50) | | 提交者IP |
| user_agent | VARCHAR(500) | | 浏览器UA |
| submitted_at | DATETIME | DEFAULT CURRENT_TIMESTAMP | 提交时间 |

#### 表3: `forms` - 表单配置（可选）

| 字段名 | 类型 | 约束 | 说明 |
|--------|------|------|------|
| id | VARCHAR(20) | PRIMARY KEY | 表单ID |
| name | VARCHAR(255) | NOT NULL | 表单名称 |
| honeypot_field | VARCHAR(100) | | 蜜罐字段名 |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP | 创建时间 |

### 3.2 索引设计

| 表名 | 索引字段 | 索引类型 | 说明 |
|------|----------|----------|------|
| lawyers | slug | UNIQUE | 加速URL查询 |
| form_submissions | form_id | INDEX | 加速表单筛选 |
| form_submissions | submitted_at | INDEX | 加速时间排序 |
| form_submissions | submission_id | UNIQUE | 唯一标识 |

---

## 四、迁移步骤

### 4.1 准备工作

1. **创建数据库和用户**（在cPanel中操作）
   - 创建数据库：`bergpc_db`
   - 创建用户：`bergpc_user`
   - 授予权限：`GRANT ALL ON bergpc_db.* TO 'bergpc_user'@'localhost'`

2. **获取数据库连接信息**
   - 主机：通常为 `localhost` 或 `127.0.0.1`
   - 数据库名：`bergpc_db`
   - 用户名：`bergpc_user`
   - 密码：用户设置的密码

### 4.2 创建数据库配置文件

修改 `admin/config.php`，添加数据库连接配置：

```php
// 数据库配置
define('DB_HOST', 'localhost');
define('DB_NAME', 'bergpc_db');
define('DB_USER', 'bergpc_user');
define('DB_PASSWORD', 'your_database_password');
define('DB_CHARSET', 'utf8mb4');
```

### 4.3 创建数据库连接类

创建 `admin/db.php`：

```php
<?php
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASSWORD);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("数据库连接失败: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}
```

### 4.4 创建数据表

创建 `admin/install.php` 用于初始化表结构：

```php
<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

try {
    $db = Database::getInstance()->getConnection();

    // 创建 lawyers 表
    $db->exec("CREATE TABLE IF NOT EXISTS lawyers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        title VARCHAR(255),
        bio TEXT,
        image VARCHAR(500),
        image_type ENUM('local','url') DEFAULT 'local',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_slug (slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 创建 form_submissions 表
    $db->exec("CREATE TABLE IF NOT EXISTS form_submissions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        submission_id VARCHAR(50) UNIQUE NOT NULL,
        form_id VARCHAR(20) NOT NULL,
        form_name VARCHAR(255),
        data LONGTEXT NOT NULL,
        ip VARCHAR(50),
        user_agent VARCHAR(500),
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_form_id (form_id),
        INDEX idx_submitted_at (submitted_at),
        INDEX idx_submission_id (submission_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 创建 forms 表
    $db->exec("CREATE TABLE IF NOT EXISTS forms (
        id VARCHAR(20) PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        honeypot_field VARCHAR(100),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "数据表创建成功！";
} catch(PDOException $e) {
    die("创建表失败: " . $e->getMessage());
}
```

### 4.5 迁移现有数据

创建 `admin/migrate_data.php`：

```php
<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$db = Database::getInstance()->getConnection();

// 迁移律师数据
$lawyersFile = DATA_DIR . '/lawyers.json';
if (file_exists($lawyersFile)) {
    $lawyers = json_decode(file_get_contents($lawyersFile), true);
    foreach ($lawyers as $lawyer) {
        $stmt = $db->prepare("INSERT INTO lawyers 
            (name, slug, title, bio, image, image_type, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE name=VALUES(name), title=VALUES(title), bio=VALUES(bio), 
                image=VALUES(image), image_type=VALUES(image_type), updated_at=VALUES(updated_at)");
        $stmt->execute([
            $lawyer['name'],
            $lawyer['slug'],
            $lawyer['title'] ?? null,
            $lawyer['bio'],
            $lawyer['image'],
            $lawyer['image_type'] ?? 'local',
            $lawyer['created_at'] ?? date('Y-m-d H:i:s'),
            $lawyer['updated_at'] ?? date('Y-m-d H:i:s')
        ]);
    }
    echo "律师数据迁移完成！\n";
}

// 迁移表单提交数据
$submissionsDir = SUBMISSIONS_DIR;
if (is_dir($submissionsDir)) {
    foreach (glob($submissionsDir . '/*.json') as $file) {
        $formId = basename($file, '.json');
        $submissions = json_decode(file_get_contents($file), true);
        foreach ($submissions as $sub) {
            $stmt = $db->prepare("INSERT IGNORE INTO form_submissions
                (submission_id, form_id, form_name, data, ip, user_agent, submitted_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $sub['id'],
                $sub['form_id'],
                $sub['form_name'],
                json_encode($sub['data']),
                $sub['ip'],
                $sub['user_agent'],
                $sub['submitted_at']
            ]);
        }
    }
    echo "表单提交数据迁移完成！\n";
}

// 迁移表单配置
$stmt = $db->prepare("INSERT IGNORE INTO forms (id, name, honeypot_field) VALUES (?, ?, ?)");
foreach (FORM_CONFIGS as $id => $config) {
    $stmt->execute([$id, $config['name'], $config['honeypot']]);
}
echo "表单配置迁移完成！\n";
```

### 4.6 修改API和管理页面以使用数据库

| 文件 | 修改内容 |
|------|----------|
| `api/submit.php` | 使用PDO插入数据库 |
| `admin/forms.php` | 使用PDO查询数据库 |
| `admin/lawyers.php` | 使用PDO进行CRUD操作 |
| `admin/index.php` | 使用PDO查询统计数据 |
| `lawyers/index.php` | 使用PDO查询律师列表 |

---

## 五、代码修改示例

### 5.1 修改 api/submit.php

```php
<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../admin/config.php';
require_once __DIR__ . '/../admin/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$formId = $_POST['form_id'] ?? '';
if (empty($formId) || !isset(FORM_CONFIGS[$formId])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid form ID']);
    exit;
}

// 检查蜜罐
$formConfig = FORM_CONFIGS[$formId];
$honeypotValue = '';
foreach ($_POST as $key => $value) {
    if (strpos($key, $formConfig['honeypot']) !== false) {
        $honeypotValue = $value;
        break;
    }
}
if (!empty($honeypotValue)) {
    echo json_encode(['success' => true, 'message' => 'Thank you']);
    exit;
}

// 收集数据
$formData = [];
foreach ($_POST as $key => $value) {
    if (strpos($key, 'wpforms[fields]') === 0) {
        $formData[$key] = is_array($value) ? implode(', ', $value) : $value;
    }
}

if (empty($formData)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No form data']);
    exit;
}

// 插入数据库
try {
    $db = Database::getInstance()->getConnection();
    $submissionId = uniqid('sub_', true);
    
    $stmt = $db->prepare("INSERT INTO form_submissions
        (submission_id, form_id, form_name, data, ip, user_agent)
        VALUES (?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $submissionId,
        $formId,
        $formConfig['name'],
        json_encode($formData),
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Thank you for your submission']);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
```

### 5.2 修改 admin/lawyers.php（新增律师）

```php
// 新增律师
if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    // ... 图片处理 ...
    
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
    $slug = trim($slug, '-');
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // 检查slug唯一性
        $stmt = $db->prepare("SELECT COUNT(*) FROM lawyers WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetchColumn() > 0) {
            $slug = $slug . '-' . time();
        }
        
        // 插入数据库
        $stmt = $db->prepare("INSERT INTO lawyers
            (name, slug, title, bio, image, image_type)
            VALUES (?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([$name, $slug, $title, $bio, $imagePath, $imageType]);
        
        // 生成页面
        require_once __DIR__ . '/generate_lawyer_page.php';
        generateLawyerPage([
            'id' => $slug,
            'name' => $name,
            'slug' => $slug,
            'title' => $title,
            'image' => $imagePath,
            'image_type' => $imageType,
            'bio' => $bio,
            'created_at' => date('c'),
            'updated_at' => date('c')
        ]);
        
        $message = 'Lawyer added successfully!';
    } catch(PDOException $e) {
        $message = 'Database error: ' . $e->getMessage();
    }
}
```

---

## 六、实施计划

### 6.1 步骤清单

| 步骤 | 任务 | 负责人 | 时间 |
|------|------|--------|------|
| 1 | 在cPanel创建数据库和用户 | 用户 | 5分钟 |
| 2 | 修改config.php添加数据库配置 | 我 | 5分钟 |
| 3 | 创建db.php数据库连接类 | 我 | 10分钟 |
| 4 | 创建install.php初始化表结构 | 我 | 10分钟 |
| 5 | 创建migrate_data.php迁移脚本 | 我 | 10分钟 |
| 6 | 修改api/submit.php | 我 | 15分钟 |
| 7 | 修改admin/forms.php | 我 | 20分钟 |
| 8 | 修改admin/lawyers.php | 我 | 20分钟 |
| 9 | 修改admin/index.php | 我 | 10分钟 |
| 10 | 修改lawyers/index.php | 我 | 15分钟 |
| 11 | 测试验证 | 双方 | 30分钟 |

### 6.2 风险处理

| 风险 | 应对措施 |
|------|----------|
| 数据迁移失败 | 保留JSON文件作为备份，迁移前测试 |
| 数据库连接失败 | 使用PDO异常处理，提供错误信息 |
| 性能问题 | 添加索引，优化查询 |
| 兼容性问题 | 保持API接口不变，前端无需修改 |

---

## 七、需要用户提供的信息

请提供以下数据库连接信息（在cPanel的MySQL数据库管理中获取）：

1. **数据库主机**：通常为 `localhost`
2. **数据库名称**：如 `bergpc_db`
3. **数据库用户名**：如 `bergpc_user`
4. **数据库密码**：用户设置的密码

---

## 八、总结

将JSON文件存储迁移到MySQL数据库是一个很好的改进，可以提供更好的性能、查询能力和数据完整性。迁移过程保持向后兼容，前端代码无需修改，只需更新后端API和管理页面。

如需开始迁移，请提供数据库连接信息。
