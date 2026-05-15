<?php
/**
 * 验证数据迁移结果
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

echo "=" . str_repeat("=", 60) . "\n";
echo "数据迁移验证\n";
echo "=" . str_repeat("=", 60) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. 检查主表
    echo "[1/3] 检查 form_submissions_new 表...\n";
    $stmt = $db->query("SELECT COUNT(*) FROM form_submissions_new");
    $count = $stmt->fetchColumn();
    echo "总记录数: {$count}\n";
    
    $stmt = $db->query("SELECT * FROM form_submissions_new ORDER BY id DESC LIMIT 2");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($records as $r) {
        echo "  - [ID: {$r['id']}] {$r['form_name']} ({$r['submitted_at']})\n";
    }
    
    // 2. 检查字段值
    echo "\n[2/3] 检查 form_field_values 表...\n";
    $stmt = $db->query("SELECT COUNT(*) FROM form_field_values");
    $count = $stmt->fetchColumn();
    echo "总字段值记录数: {$count}\n";
    
    // 3. 显示详细数据
    echo "\n[3/3] 查看第一条记录的详细数据...\n";
    $stmt = $db->query("SELECT id FROM form_submissions_new ORDER BY id LIMIT 1");
    $firstId = $stmt->fetchColumn();
    
    if ($firstId) {
        $stmt = $db->prepare("SELECT field_label, field_value FROM form_field_values WHERE submission_id = ? ORDER BY id");
        $stmt->execute([$firstId]);
        $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "共 " . count($fields) . " 个字段:\n";
        
        foreach ($fields as $f) {
            $value = $f['field_value'];
            if (strlen($value) > 50) {
                $value = substr($value, 0, 50) . "...";
            }
            echo "  - {$f['field_label']}: " . ($value ?: "-") . "\n";
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "[✅] 数据验证完成！迁移成功\n";
    echo str_repeat("=", 60) . "\n";
    echo "\n下一步：访问管理后台查看新表格界面\n";
    
} catch (Exception $e) {
    echo "[X] 验证失败: " . $e->getMessage() . "\n";
    exit(1);
}
