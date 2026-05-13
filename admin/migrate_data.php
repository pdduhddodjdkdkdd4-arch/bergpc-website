<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$db = Database::getInstance()->getConnection();

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
} else {
    echo "律师数据文件不存在，跳过迁移\n";
}

$submissionsDir = SUBMISSIONS_DIR;
if (is_dir($submissionsDir)) {
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
            }
        }
    }
    echo "表单提交数据迁移完成！\n";
} else {
    echo "表单提交目录不存在，跳过迁移\n";
}

$stmt = $db->prepare("INSERT IGNORE INTO forms (id, name, honeypot_field) VALUES (?, ?, ?)");
foreach (FORM_CONFIGS as $id => $config) {
    $stmt->execute([$id, $config['name'], $config['honeypot']]);
}
echo "表单配置迁移完成！\n";
