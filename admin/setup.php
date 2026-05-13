<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

echo '<html><head><meta charset="utf-8"><title>Database Setup</title>';
echo '<style>body{font-family:sans-serif;max-width:800px;margin:40px auto;padding:0 20px}';
echo '.success{color:#065f46;background:#d1fae5;padding:12px;border-radius:8px;margin:8px 0}';
echo '.error{color:#991b1b;background:#fee2e2;padding:12px;border-radius:8px;margin:8px 0}';
echo '.info{color:#1e40af;background:#dbeafe;padding:12px;border-radius:8px;margin:8px 0}';
echo 'h1{color:#1e3a5f}h2{color:#334155;border-bottom:1px solid #e2e8f0;padding-bottom:8px}</style></head><body>';
echo '<h1>🗄️ Database Setup</h1>';

try {
    $db = Database::getInstance()->getConnection();
    echo '<div class="success">✅ 数据库连接成功！</div>';

    echo '<h2>Step 1: 创建数据表</h2>';

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
    echo '<div class="success">✅ lawyers 表创建成功</div>';

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
    echo '<div class="success">✅ form_submissions 表创建成功</div>';

    $db->exec("CREATE TABLE IF NOT EXISTS forms (
        id VARCHAR(20) PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        honeypot_field VARCHAR(100),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo '<div class="success">✅ forms 表创建成功</div>';

    echo '<h2>Step 2: 迁移律师数据</h2>';
    $lawyersFile = DATA_DIR . '/lawyers.json';
    if (file_exists($lawyersFile)) {
        $lawyers = json_decode(file_get_contents($lawyersFile), true);
        $count = 0;
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
            $count++;
        }
        echo "<div class=\"success\">✅ 律师数据迁移完成！共 {$count} 条记录</div>";
    } else {
        echo '<div class="info">ℹ️ 律师数据文件不存在，跳过迁移</div>';
    }

    echo '<h2>Step 3: 迁移表单提交数据</h2>';
    $submissionsDir = SUBMISSIONS_DIR;
    if (is_dir($submissionsDir)) {
        $totalSubs = 0;
        foreach (glob($submissionsDir . '/*.json') as $file) {
            $formId = basename($file, '.json');
            $submissions = json_decode(file_get_contents($file), true);
            if (!empty($submissions) && is_array($submissions)) {
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
                    $totalSubs++;
                }
            }
        }
        echo "<div class=\"success\">✅ 表单提交数据迁移完成！共 {$totalSubs} 条记录</div>";
    } else {
        echo '<div class="info">ℹ️ 表单提交目录不存在，跳过迁移</div>';
    }

    echo '<h2>Step 4: 迁移表单配置</h2>';
    $stmt = $db->prepare("INSERT IGNORE INTO forms (id, name, honeypot_field) VALUES (?, ?, ?)");
    $formCount = 0;
    foreach (FORM_CONFIGS as $id => $config) {
        $stmt->execute([$id, $config['name'], $config['honeypot']]);
        $formCount++;
    }
    echo "<div class=\"success\">✅ 表单配置迁移完成！共 {$formCount} 个表单</div>";

    echo '<h2>验证</h2>';
    $stmt = $db->query("SELECT COUNT(*) FROM lawyers");
    $lawyerCount = $stmt->fetchColumn();
    $stmt = $db->query("SELECT COUNT(*) FROM form_submissions");
    $subCount = $stmt->fetchColumn();
    $stmt = $db->query("SELECT COUNT(*) FROM forms");
    $formCount = $stmt->fetchColumn();
    
    echo '<div class="info">';
    echo "📊 lawyers 表: {$lawyerCount} 条记录<br>";
    echo "📊 form_submissions 表: {$subCount} 条记录<br>";
    echo "📊 forms 表: {$formCount} 条记录";
    echo '</div>';

    echo '<h2 style="color:#065f46">🎉 数据库设置完成！</h2>';
    echo '<p>请删除此文件以确保安全：<code>' . __FILE__ . '</code></p>';

} catch(PDOException $e) {
    echo '<div class="error">❌ 错误: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

echo '</body></html>';
