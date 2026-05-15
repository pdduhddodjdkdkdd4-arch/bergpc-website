<?php
/**
 * 表单数据迁移脚本
 * 将 form_submissions 表中的数据迁移到新表结构
 */

// 定义项目根目录
define('PROJECT_ROOT', dirname(__DIR__, 3));

// 包含数据库配置
require_once PROJECT_ROOT . '/admin/config.php';
require_once PROJECT_ROOT . '/admin/db.php';

echo "开始数据迁移...\n";

try {
    $db = Database::getInstance()->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 首先获取所有表单字段定义，用于快速查找
    $fieldMap = [];
    $stmt = $db->query("SELECT id, form_id, field_key FROM form_fields");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $formId = $row['form_id'];
        $fieldKey = $row['field_key'];
        if (!isset($fieldMap[$formId])) {
            $fieldMap[$formId] = [];
        }
        $fieldMap[$formId][$fieldKey] = $row['id'];
    }
    echo "已加载 " . count($fieldMap) . " 个表单的字段定义\n";

    // 获取需要迁移的记录数
    $countStmt = $db->query("SELECT COUNT(*) FROM form_submissions");
    $total = $countStmt->fetchColumn();
    echo "共有 {$total} 条记录需要迁移\n";

    // 分批处理数据
    $batchSize = 100;
    $offset = 0;
    $migrated = 0;
    $skipped = 0;

    while ($offset < $total) {
        echo "处理记录 {$offset} - " . min($offset + $batchSize, $total) . "...\n";
        
        $stmt = $db->prepare("SELECT * FROM form_submissions LIMIT ? OFFSET ?");
        $stmt->execute([$batchSize, $offset]);
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($submissions as $sub) {
            // 检查是否已经迁移过
            $checkStmt = $db->prepare("SELECT id FROM form_submissions_new WHERE submission_id = ?");
            $checkStmt->execute([$sub['submission_id']]);
            if ($checkStmt->fetch()) {
                $skipped++;
                continue;
            }

            try {
                $db->beginTransaction();

                // 1. 插入主表记录
                $insertSub = $db->prepare("
                    INSERT INTO form_submissions_new 
                    (submission_id, form_id, form_name, ip, user_agent, submitted_at)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $insertSub->execute([
                    $sub['submission_id'],
                    $sub['form_id'],
                    $sub['form_name'],
                    $sub['ip'],
                    $sub['user_agent'],
                    $sub['submitted_at']
                ]);
                $newSubId = $db->lastInsertId();

                // 2. 解析 JSON 数据并插入字段值
                $data = json_decode($sub['data'], true);
                if (is_array($data)) {
                    $formId = $sub['form_id'];
                    
                    foreach ($data as $fieldKey => $fieldValue) {
                        // 查找字段定义
                        $fieldId = null;
                        if (isset($fieldMap[$formId][$fieldKey])) {
                            $fieldId = $fieldMap[$formId][$fieldKey];
                        } else {
                            // 如果没有字段定义，尝试创建一个通用的
                            $fieldLabel = $fieldKey;
                            // 尝试从已知的字段映射中获取
                            $fieldLabel = getFieldLabel($fieldKey);
                            
                            $insertField = $db->prepare("
                                INSERT INTO form_fields 
                                (form_id, field_key, field_label, field_type, display_order)
                                VALUES (?, ?, ?, 'text', 999)
                            ");
                            $insertField->execute([$formId, $fieldKey, $fieldLabel]);
                            $fieldId = $db->lastInsertId();
                            
                            // 更新字段映射
                            if (!isset($fieldMap[$formId])) {
                                $fieldMap[$formId] = [];
                            }
                            $fieldMap[$formId][$fieldKey] = $fieldId;
                        }

                        // 处理字段值
                        $processedValue = processFieldValue($fieldValue);

                        // 插入字段值
                        if ($fieldId) {
                            $insertValue = $db->prepare("
                                INSERT INTO form_field_values 
                                (submission_id, field_id, field_value)
                                VALUES (?, ?, ?)
                            ");
                            $insertValue->execute([$newSubId, $fieldId, $processedValue]);
                        }
                    }
                }

                $db->commit();
                $migrated++;

            } catch (Exception $e) {
                $db->rollBack();
                echo "迁移记录 {$sub['submission_id']} 时出错: " . $e->getMessage() . "\n";
                $skipped++;
            }
        }

        $offset += $batchSize;
    }

    echo "\n迁移完成！\n";
    echo "成功迁移: {$migrated} 条\n";
    echo "跳过: {$skipped} 条\n";

} catch (Exception $e) {
    echo "迁移失败: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * 获取字段标签
 */
function getFieldLabel($fieldKey) {
    // 常见字段映射
    $commonLabels = [
        'wpforms[fields][1]' => 'Email',
        'wpforms[fields][2]' => 'Case Details',
        'wpforms[fields][3]' => 'Honeypot',
        'wpforms[fields][4]' => 'Additional Field',
        'wpforms[fields][5]' => 'Additional Field 2',
        'wpforms[fields][9]' => 'Disclaimer Agreement',
        'wpforms[fields][13][first]' => 'First Name',
        'wpforms[fields][13][last]' => 'Last Name',
        'wpforms[fields][17]' => 'Current Occupation',
        'wpforms[fields][18]' => 'Additional Information',
        'wpforms[fields][19][first]' => 'First Name',
        'wpforms[fields][19][last]' => 'Last Name',
        'wpforms[fields][19]' => 'Amount Lost',
        'wpforms[fields][20]' => 'Additional Information 2',
        'wpforms[fields][21]' => 'Last Transaction Date',
        'wpforms[fields][22]' => 'Additional Notes',
        'wpforms[fields][25]' => 'Phone',
        'wpforms[fields][26][address1]' => 'Address Line 1',
        'wpforms[fields][26][city]' => 'City',
        'wpforms[fields][26][state]' => 'State/Province',
        'wpforms[fields][26][country]' => 'Country',
        'wpforms[fields][27]' => 'General Disclaimer',
        'wpforms[fields][29]' => 'Question',
        'wpforms[fields][30]' => 'Additional Question',
        'wpforms[fields][31]' => 'Additional Question 2',
        'wpforms[fields][32]' => 'Date Field',
        'wpforms[fields][33]' => 'Date Field 2',
        'wpforms[fields][34]' => 'Date Field 3',
        'wpforms[id]' => 'Form ID',
        'wpforms[post_id]' => 'Post ID',
        'wpforms[submit]' => 'Submit',
        'wpforms[token]' => 'Token',
        'form_id' => 'Form ID',
        'page_title' => 'Page Title',
        'page_url' => 'Page URL',
        'url_referer' => 'Referer URL',
        'page_id' => 'Page ID',
        'alt_s' => 'Alt S',
        'bhmrqc1587' => 'Honeypot Field',
        'start_timestamp' => 'Start Timestamp',
        'end_timestamp' => 'End Timestamp'
    ];
    
    return $commonLabels[$fieldKey] ?? $fieldKey;
}

/**
 * 处理字段值
 */
function processFieldValue($value) {
    if (is_array($value)) {
        // 如果是数组，用逗号连接
        return implode(', ', array_filter($value));
    }
    if ($value === null || $value === '') {
        return null; // 使用 null 表示空值
    }
    return $value;
}
