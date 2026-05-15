<?php
/**
 * 表单数据 API
 * 提供 AG Grid 所需的数据
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $db = Database::getInstance()->getConnection();
    
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'list':
            getFormList($db);
            break;
        case 'detail':
            getFormDetail($db);
            break;
        case 'forms':
            getFormTypes($db);
            break;
        case 'delete':
            deleteSubmissions($db);
            break;
        case 'export':
            exportCSV($db);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function getFormList($db) {
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = min(100, max(10, intval($_GET['perPage'] ?? 20)));
    $search = trim($_GET['search'] ?? '');
    $formId = trim($_GET['formId'] ?? '');
    $dateFrom = trim($_GET['dateFrom'] ?? '');
    $dateTo = trim($_GET['dateTo'] ?? '');
    
    $offset = ($page - 1) * $perPage;
    
    // 基础查询
    $sql = "SELECT f.* FROM form_submissions_new f WHERE 1=1";
    $params = [];
    
    // 搜索条件
    if ($search !== '') {
        $sql .= " AND (
            EXISTS (SELECT 1 FROM form_field_values v 
                    WHERE v.submission_id = f.id 
                    AND (v.field_label LIKE ? OR v.field_value LIKE ?))
            OR f.form_name LIKE ?
        )";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // 表单类型筛选
    if ($formId !== '') {
        $sql .= " AND f.form_id = ?";
        $params[] = $formId;
    }
    
    // 日期范围
    if ($dateFrom !== '') {
        $sql .= " AND DATE(f.submitted_at) >= ?";
        $params[] = $dateFrom;
    }
    if ($dateTo !== '') {
        $sql .= " AND DATE(f.submitted_at) <= ?";
        $params[] = $dateTo;
    }
    
    // 总数查询
    $countSql = preg_replace('/SELECT f\..* FROM/', 'SELECT COUNT(*) FROM', $sql);
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();
    
    // 数据查询
    $sql .= " ORDER BY f.submitted_at DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 获取每个记录的字段值
    $result = [];
    foreach ($submissions as $sub) {
        $row = [
            'id' => $sub['id'],
            'submission_id' => $sub['submission_id'],
            'form_id' => $sub['form_id'],
            'form_name' => $sub['form_name'],
            'submitted_at' => $sub['submitted_at'],
            'ip' => $sub['ip'],
            'fields' => []
        ];
        
        // 获取该记录的所有字段值
        $fieldStmt = $db->prepare("SELECT field_label, field_value FROM form_field_values WHERE submission_id = ? ORDER BY id");
        $fieldStmt->execute([$sub['id']]);
        $fields = $fieldStmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // 合并字段值到主行
        foreach ($fields as $label => $value) {
            // 转换字段名为安全的 key
            $key = 'field_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $label);
            $row[$key] = $value;
            $row['fields'][$label] = $value;
            
            // 提取常用字段 - 使用兼容的 strpos 替代 str_contains
            if ((strpos($label, 'First Name') !== false || strpos($label, 'Name') !== false) && !isset($row['display_name'])) {
                $row['display_name'] = $value;
            }
            if (strpos($label, 'Email') !== false && !isset($row['display_email'])) {
                $row['display_email'] = $value;
            }
            if (strpos($label, 'Phone') !== false && !isset($row['display_phone'])) {
                $row['display_phone'] = $value;
            }
        }
        
        $result[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $result,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ]
    ]);
}

function getFormDetail($db) {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        return;
    }
    
    // 获取主记录
    $stmt = $db->prepare("SELECT * FROM form_submissions_new WHERE id = ?");
    $stmt->execute([$id]);
    $sub = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sub) {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
        return;
    }
    
    // 获取字段值
    $fieldStmt = $db->prepare("SELECT field_label, field_value FROM form_field_values WHERE submission_id = ? ORDER BY id");
    $fieldStmt->execute([$id]);
    $fields = $fieldStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'submission' => $sub,
            'fields' => $fields
        ]
    ]);
}

function getFormTypes($db) {
    $stmt = $db->query("SELECT DISTINCT form_id, form_name FROM form_submissions_new ORDER BY form_name");
    $forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $forms
    ]);
}

function deleteSubmissions($db) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $ids = $input['ids'] ?? [];
    
    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => 'No IDs provided']);
        return;
    }
    
    $deleted = 0;
    foreach ($ids as $id) {
        $db->beginTransaction();
        
        try {
            // 删除字段值
            $stmt = $db->prepare("DELETE FROM form_field_values WHERE submission_id = ?");
            $stmt->execute([$id]);
            
            // 删除主记录
            $stmt = $db->prepare("DELETE FROM form_submissions_new WHERE id = ?");
            $stmt->execute([$id]);
            
            $db->commit();
            $deleted++;
        } catch (Exception $e) {
            $db->rollBack();
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Deleted {$deleted} records"
    ]);
}

function exportCSV($db) {
    // TODO: 实现 CSV 导出
    echo json_encode(['success' => true, 'message' => 'Export coming soon']);
}
