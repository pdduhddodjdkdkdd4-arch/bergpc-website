<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET' && isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'list':
                handleList($db);
                break;
            case 'detail':
                handleDetail($db);
                break;
            case 'export':
                handleExport($db);
                break;
            case 'forms':
                handleForms($db);
                break;
            default:
                sendResponse(false, 'Invalid action', null, 400);
        }
    } elseif ($method === 'GET') {
        handleList($db);
    } elseif ($method === 'POST') {
        handleDelete($db);
    } else {
        sendResponse(false, 'Method not allowed', null, 405);
    }
} catch (Exception $e) {
    sendResponse(false, 'Server error: ' . $e->getMessage(), null, 500);
}

function handleList($db) {
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, min(100, intval($_GET['per_page']))) : 20;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $formId = isset($_GET['form_id']) ? trim($_GET['form_id']) : '';
    $dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
    $dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
    $sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'submitted_at';
    $order = isset($_GET['order']) && in_array(strtolower($_GET['order']), ['asc', 'desc']) 
        ? strtoupper($_GET['order']) : 'DESC';
    
    $allowedSortFields = ['id', 'form_id', 'form_name', 'submitted_at', 'created_at', 'ip'];
    if (!in_array($sort, $allowedSortFields)) {
        $sort = 'submitted_at';
    }
    
    $offset = ($page - 1) * $perPage;
    
    $where = [];
    $params = [];
    
    if ($formId !== '') {
        $where[] = 'fs.form_id = ?';
        $params[] = $formId;
    }
    
    if ($dateFrom !== '') {
        $where[] = 'fs.submitted_at >= ?';
        $params[] = $dateFrom . ' 00:00:00';
    }
    
    if ($dateTo !== '') {
        $where[] = 'fs.submitted_at <= ?';
        $params[] = $dateTo . ' 23:59:59';
    }
    
    if ($search !== '') {
        $searchParam = '%' . $search . '%';
        $where[] = '(
            EXISTS (SELECT 1 FROM form_field_values ffv1 
                    JOIN form_fields ff1 ON ffv1.field_id = ff1.id 
                    WHERE ffv1.submission_id = fs.id 
                    AND ff1.field_label IN ("First Name", "Last Name", "Name") 
                    AND ffv1.field_value LIKE ?) 
            OR EXISTS (SELECT 1 FROM form_field_values ffv2 
                      JOIN form_fields ff2 ON ffv2.field_id = ff2.id 
                      WHERE ffv2.submission_id = fs.id 
                      AND ff2.field_label = "Email" 
                      AND ffv2.field_value LIKE ?)
            OR EXISTS (SELECT 1 FROM form_field_values ffv3 
                      JOIN form_fields ff3 ON ffv3.field_id = ff3.id 
                      WHERE ffv3.submission_id = fs.id 
                      AND ff3.field_label = "Phone" 
                      AND ffv3.field_value LIKE ?)
        )';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $countSql = "SELECT COUNT(DISTINCT fs.id) as total 
                 FROM form_submissions_new fs 
                 $whereClause";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $dataSql = "SELECT DISTINCT fs.id, fs.submission_id, fs.form_id, fs.form_name, 
                       fs.ip, fs.user_agent, fs.submitted_at, fs.created_at
                FROM form_submissions_new fs
                LEFT JOIN form_field_values ffv ON fs.id = ffv.submission_id
                LEFT JOIN form_fields ff ON ffv.field_id = ff.id
                $whereClause
                ORDER BY fs.$sort $order
                LIMIT $perPage OFFSET $offset";
    
    $dataStmt = $db->prepare($dataSql);
    $dataStmt->execute($params);
    $submissions = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $enrichedData = [];
    foreach ($submissions as $submission) {
        $fieldValues = getFieldValuesForSubmission($db, $submission['id']);
        $enrichedData[] = [
            'id' => $submission['id'],
            'submission_id' => $submission['submission_id'],
            'form_id' => $submission['form_id'],
            'form_name' => $submission['form_name'],
            'ip' => $submission['ip'],
            'submitted_at' => $submission['submitted_at'],
            'created_at' => $submission['created_at'],
            'fields' => $fieldValues
        ];
    }
    
    $totalPages = ceil($totalCount / $perPage);
    
    sendResponse(true, 'Success', $enrichedData, 200, [
        'total' => $totalCount,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => $totalPages
    ]);
}

function handleDetail($db) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        sendResponse(false, 'Invalid submission ID', null, 400);
    }
    
    $sql = "SELECT id, submission_id, form_id, form_name, ip, user_agent, submitted_at, created_at
            FROM form_submissions_new 
            WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$submission) {
        sendResponse(false, 'Submission not found', null, 404);
    }
    
    $fieldValues = getFieldValuesForSubmission($db, $submission['id']);
    
    $enrichedData = [
        'id' => $submission['id'],
        'submission_id' => $submission['submission_id'],
        'form_id' => $submission['form_id'],
        'form_name' => $submission['form_name'],
        'ip' => $submission['ip'],
        'user_agent' => $submission['user_agent'],
        'submitted_at' => $submission['submitted_at'],
        'created_at' => $submission['created_at'],
        'fields' => $fieldValues
    ];
    
    sendResponse(true, 'Success', $enrichedData, 200);
}

function handleDelete($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['ids']) || !is_array($input['ids']) || empty($input['ids'])) {
        sendResponse(false, 'Invalid IDs', null, 400);
    }
    
    $ids = array_map('intval', $input['ids']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $db->beginTransaction();
    try {
        $deleteValues = $db->prepare("DELETE FROM form_field_values WHERE submission_id IN ($placeholders)");
        $deleteValues->execute($ids);
        
        $deleteSubmissions = $db->prepare("DELETE FROM form_submissions_new WHERE id IN ($placeholders)");
        $deleteSubmissions->execute($ids);
        
        $db->commit();
        
        $deletedCount = $deleteSubmissions->rowCount();
        sendResponse(true, "Successfully deleted $deletedCount record(s)", ['deleted' => $deletedCount], 200);
    } catch (Exception $e) {
        $db->rollBack();
        sendResponse(false, 'Failed to delete: ' . $e->getMessage(), null, 500);
    }
}

function handleExport($db) {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $formId = isset($_GET['form_id']) ? trim($_GET['form_id']) : '';
    $dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
    $dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
    
    $where = [];
    $params = [];
    
    if ($formId !== '') {
        $where[] = 'fs.form_id = ?';
        $params[] = $formId;
    }
    
    if ($dateFrom !== '') {
        $where[] = 'fs.submitted_at >= ?';
        $params[] = $dateFrom . ' 00:00:00';
    }
    
    if ($dateTo !== '') {
        $where[] = 'fs.submitted_at <= ?';
        $params[] = $dateTo . ' 23:59:59';
    }
    
    if ($search !== '') {
        $searchParam = '%' . $search . '%';
        $where[] = '(
            EXISTS (SELECT 1 FROM form_field_values ffv1 
                    JOIN form_fields ff1 ON ffv1.field_id = ff1.id 
                    WHERE ffv1.submission_id = fs.id 
                    AND ff1.field_label IN ("First Name", "Last Name", "Name") 
                    AND ffv1.field_value LIKE ?) 
            OR EXISTS (SELECT 1 FROM form_field_values ffv2 
                      JOIN form_fields ff2 ON ffv2.field_id = ff2.id 
                      WHERE ffv2.submission_id = fs.id 
                      AND ff2.field_label = "Email" 
                      AND ffv2.field_value LIKE ?)
            OR EXISTS (SELECT 1 FROM form_field_values ffv3 
                      JOIN form_fields ff3 ON ffv3.field_id = ff3.id 
                      WHERE ffv3.submission_id = fs.id 
                      AND ff3.field_label = "Phone" 
                      AND ffv3.field_value LIKE ?)
        )';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "SELECT fs.id, fs.submission_id, fs.form_id, fs.form_name, 
                   fs.ip, fs.submitted_at
            FROM form_submissions_new fs
            $whereClause
            ORDER BY fs.submitted_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $headers = ['ID', 'Submission ID', 'Form ID', 'Form Name', 'IP', 'Submitted At'];
    $allFieldLabels = [];
    
    foreach ($submissions as $submission) {
        $fieldValues = getFieldValuesForSubmission($db, $submission['id']);
        foreach (array_keys($fieldValues) as $label) {
            if (!in_array($label, $headers)) {
                $allFieldLabels[] = $label;
            }
        }
    }
    
    $headers = array_merge($headers, $allFieldLabels);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=form_submissions_' . date('Y-m-d_His') . '.csv');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, $headers);
    
    foreach ($submissions as $submission) {
        $fieldValues = getFieldValuesForSubmission($db, $submission['id']);
        $row = [
            $submission['id'],
            $submission['submission_id'],
            $submission['form_id'],
            $submission['form_name'],
            $submission['ip'],
            $submission['submitted_at']
        ];
        
        foreach ($allFieldLabels as $label) {
            $row[] = isset($fieldValues[$label]) ? $fieldValues[$label] : '';
        }
        
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

function handleForms($db) {
    $sql = "SELECT DISTINCT form_id, form_name 
            FROM form_submissions_new 
            ORDER BY form_name";
    $stmt = $db->query($sql);
    $forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse(true, 'Success', $forms, 200);
}

function getFieldValuesForSubmission($db, $submissionId) {
    $sql = "SELECT ff.field_label, ffv.field_value
            FROM form_field_values ffv
            JOIN form_fields ff ON ffv.field_id = ff.id
            WHERE ffv.submission_id = ?
            ORDER BY ff.display_order, ff.id";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$submissionId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $fields = [];
    foreach ($results as $row) {
        $fields[$row['field_label']] = $row['field_value'];
    }
    
    return $fields;
}

function sendResponse($success, $message, $data = null, $statusCode = 200, $pagination = null) {
    http_response_code($statusCode);
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if ($pagination !== null) {
        $response['pagination'] = $pagination;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
