<?php
/**
 * 表单数据迁移脚本 v2.0
 * 从 FORM_CONFIGS 读取字段映射，支持复合字段处理
 */

// 错误报告开启
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('PROJECT_ROOT', dirname(__DIR__));

echo "=" . str_repeat("=", 60) . "\n";
echo "表单数据迁移脚本 v2.0\n";
echo "=" . str_repeat("=", 60) . "\n\n";

echo "[1/6] 检查文件路径...\n";
echo "PROJECT_ROOT = " . PROJECT_ROOT . "\n";

$configFile = PROJECT_ROOT . '/admin/config.php';
$dbFile = PROJECT_ROOT . '/admin/db.php';

if (!file_exists($configFile)) {
    die("[X] 配置文件不存在: {$configFile}\n");
}
if (!file_exists($dbFile)) {
    die("[X] 数据库文件不存在: {$dbFile}\n");
}

echo "[2/6] 加载配置文件...\n";
require_once $configFile;
require_once $dbFile;

echo "[3/6] 连接数据库...\n";
try {
    $db = Database::getInstance()->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "[OK] 数据库连接成功\n\n";
} catch (Exception $e) {
    die("[X] 数据库连接失败: " . $e->getMessage() . "\n");
}

echo "[4/6] 检查数据表...\n";
$requiredTables = ['form_submissions', 'form_submissions_new', 'form_field_values'];
foreach ($requiredTables as $table) {
    $stmt = $db->query("SHOW TABLES LIKE '{$table}'");
    if (!$stmt->fetch()) {
        die("[X] 数据表不存在: {$table}\n请先运行 migration.sql\n");
    }
    echo "[OK] 表 {$table} 存在\n";
}

echo "\n[5/6] 构建字段配置映射...\n";
$fieldConfigMap = buildFieldConfigMap();
echo "[OK] 已加载 " . count($fieldConfigMap) . " 个表单的字段配置\n\n";

echo "[6/6] 开始数据迁移...\n";
try {
    $countStmt = $db->query("SELECT COUNT(*) FROM form_submissions");
    $total = $countStmt->fetchColumn();
    echo "共有 {$total} 条记录需要迁移\n\n";

    $batchSize = 100;
    $offset = 0;
    $migrated = 0;
    $skipped = 0;
    $errors = 0;

    while ($offset < $total) {
        echo "[" . date('H:i:s') . "] 处理记录 {$offset} - " . min($offset + $batchSize, $total) . "...\n";
        
        $stmt = $db->prepare("SELECT * FROM form_submissions LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $batchSize, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($submissions as $sub) {
            $submissionId = $sub['submission_id'];
            
            $checkStmt = $db->prepare("SELECT id FROM form_submissions_new WHERE submission_id = ?");
            $checkStmt->execute([$submissionId]);
            if ($checkStmt->fetch()) {
                $skipped++;
                continue;
            }

            try {
                $db->beginTransaction();

                $insertSub = $db->prepare("
                    INSERT INTO form_submissions_new 
                    (submission_id, form_id, form_name, ip, user_agent, submitted_at)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $insertSub->execute([
                    $submissionId,
                    $sub['form_id'],
                    $sub['form_name'],
                    $sub['ip'],
                    $sub['user_agent'],
                    $sub['submitted_at']
                ]);
                $newSubId = $db->lastInsertId();

                $data = json_decode($sub['data'], true);
                if (is_array($data)) {
                    $formId = $sub['form_id'];
                    $formConfig = isset($fieldConfigMap[$formId]) ? $fieldConfigMap[$formId] : [];
                    
                    $processedFields = processFormData($data, $formId, $formConfig);
                    
                    foreach ($processedFields as $fieldLabel => $fieldValue) {
                        $insertValue = $db->prepare("
                            INSERT INTO form_field_values 
                            (submission_id, field_label, field_value)
                            VALUES (?, ?, ?)
                        ");
                        $insertValue->execute([$newSubId, $fieldLabel, $fieldValue]);
                    }
                }

                $db->commit();
                $migrated++;

            } catch (Exception $e) {
                $db->rollBack();
                echo "  [!] 迁移记录 {$submissionId} 时出错: " . $e->getMessage() . "\n";
                $errors++;
            }
        }

        $offset += $batchSize;
    }

    echo "\n" . "=" . str_repeat("=", 60) . "\n";
    echo "迁移完成！\n";
    echo "成功迁移: {$migrated} 条\n";
    echo "跳过（已迁移）: {$skipped} 条\n";
    echo "错误: {$errors} 条\n";
    echo "=" . str_repeat("=", 60) . "\n";

} catch (Exception $e) {
    echo "\n[X] 迁移失败: " . $e->getMessage() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

function buildFieldConfigMap() {
    $map = [];
    foreach (FORM_CONFIGS as $formId => $config) {
        $map[$formId] = $config['fields'] ?? [];
    }
    return $map;
}

function processFormData(array $data, $formId, array $formConfig) {
    $result = [];
    
    foreach ($data as $key => $value) {
        if (is_array($value) && empty($value)) {
            continue;
        }
        
        if ($value === '' || $value === null) {
            continue;
        }
        
        $fieldInfo = parseFieldKey($key, $formConfig);
        
        if ($fieldInfo) {
            $label = $fieldInfo['label'];
            $subfield = $fieldInfo['subfield'];
            
            if ($subfield) {
                $fullLabel = "{$label} - {$subfield}";
            } else {
                $fullLabel = $label;
            }
            
            $result[$fullLabel] = normalizeFieldValue($value);
        }
    }
    
    return $result;
}

function parseFieldKey($key, array $formConfig) {
    if (preg_match('/^wpforms\[fields\]\[(\d+)\](?:\[(\w+)\])?$/i', $key, $matches)) {
        $fieldId = $matches[1];
        $subfield = isset($matches[2]) ? $matches[2] : null;
        
        if (isset($formConfig[$fieldId])) {
            $fieldDef = $formConfig[$fieldId];
            
            if (is_string($fieldDef)) {
                return [
                    'label' => $fieldDef,
                    'subfield' => null
                ];
            }
            
            if (is_array($fieldDef) && isset($fieldDef['subfields'])) {
                if ($subfield && isset($fieldDef['subfields'][$subfield])) {
                    return [
                        'label' => $fieldDef['label'] ?? "Field {$fieldId}",
                        'subfield' => $fieldDef['subfields'][$subfield]
                    ];
                } elseif (!$subfield) {
                    return [
                        'label' => $fieldDef['label'] ?? "Field {$fieldId}",
                        'subfield' => null
                    ];
                }
            }
        }
        
        if ($subfield) {
            return [
                'label' => "Field {$fieldId}",
                'subfield' => ucfirst($subfield)
            ];
        }
        
        return [
            'label' => "Field {$fieldId}",
            'subfield' => null
        ];
    }
    
    return null;
}

function normalizeFieldValue($value) {
    if (is_array($value)) {
        $filtered = array_filter($value, function($v) {
            return $v !== '' && $v !== null;
        });
        
        if (empty($filtered)) {
            return null;
        }
        
        if (count($filtered) === 1) {
            $firstValue = reset($filtered);
            return normalizeFieldValue($firstValue);
        }
        
        return implode(', ', $filtered);
    }
    
    if ($value === '' || $value === null) {
        return null;
    }
    
    if (is_bool($value)) {
        return $value ? '1' : '0';
    }
    
    if (is_numeric($value)) {
        return (string)$value;
    }
    
    $value = trim($value);
    
    if ($value === '') {
        return null;
    }
    
    return $value;
}
