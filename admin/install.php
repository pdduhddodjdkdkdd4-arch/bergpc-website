<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

try {
    $db = Database::getInstance()->getConnection();

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
