<?php
require_once __DIR__ . '/db.php';
require_once dirname(__DIR__) . '/admin/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$formId = $_POST['form_id'] ?? '';
$honeypot = $_POST['wpforms']['fields'][3] ?? '';

if (!empty($honeypot)) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Thank you for your submission!']);
    exit;
}

if (empty($formId) || !isset(FORM_CONFIGS[$formId])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid form ID']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $submissionId = uniqid('wpf_', true);
    $formConfig = FORM_CONFIGS[$formId];
    $formName = $formConfig['name'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $submittedAt = date('Y-m-d H:i:s');
    
    $data = [];
    foreach ($_POST as $key => $value) {
        if ($key !== 'form_id' && $key !== 'action') {
            $data[$key] = $value;
        }
    }
    
    $stmtOriginal = $db->prepare("
        INSERT INTO form_submissions 
        (submission_id, form_id, form_name, data, ip, user_agent, submitted_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtOriginal->execute([
        $submissionId,
        $formId,
        $formName,
        json_encode($data),
        $ip,
        $userAgent,
        $submittedAt
    ]);
    
    $newTableError = null;
    try {
        saveToNewTables($db, $submissionId, $formId, $formName, $data, $ip, $userAgent, $submittedAt);
    } catch (Throwable $e) {
        $newTableError = $e->getMessage();
        error_log("Dual write - New table error for {$submissionId}: " . $e->getMessage());
    }
    
    http_response_code(200);
    $response = ['success' => true, 'message' => 'Thank you for your submission!'];
    
    if ($newTableError !== null) {
        $response['warning'] = 'Submission saved but encountered an issue with enhanced storage';
    }
    
    echo json_encode($response);
    exit;
    
} catch (Throwable $e) {
    error_log("Form submission error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
    exit;
}

function saveToNewTables($db, $submissionId, $formId, $formName, $data, $ip, $userAgent, $submittedAt) {
    $stmtNewSub = $db->prepare("
        INSERT INTO form_submissions_new 
        (submission_id, form_id, form_name, ip, user_agent, submitted_at)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmtNewSub->execute([
        $submissionId,
        $formId,
        $formName,
        $ip,
        $userAgent,
        $submittedAt
    ]);
    $newSubId = $db->lastInsertId();
    
    $formConfig = FORM_CONFIGS[$formId] ?? [];
    $processedFields = processFormData($data, $formId, $formConfig);
    
    $stmtFieldValue = $db->prepare("
        INSERT INTO form_field_values 
        (submission_id, field_label, field_value)
        VALUES (?, ?, ?)
    ");
    
    foreach ($processedFields as $fieldLabel => $fieldValue) {
        if ($fieldValue !== null && $fieldValue !== '') {
            $stmtFieldValue->execute([$newSubId, $fieldLabel, $fieldValue]);
        }
    }
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
