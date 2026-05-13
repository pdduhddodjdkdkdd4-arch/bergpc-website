<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../admin/config.php';
require_once __DIR__ . '/../admin/db.php';

$formId = $_POST['form_id'] ?? '';
if (empty($formId) || !isset(FORM_CONFIGS[$formId])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid form ID']);
    exit;
}

$formConfig = FORM_CONFIGS[$formId];
$honeypotField = $formConfig['honeypot'];

$honeypotValue = '';
if (isset($_POST[$honeypotField])) {
    $honeypotValue = $_POST[$honeypotField];
} elseif (isset($_POST['wpforms']) && isset($_POST['wpforms']['fields']) && isset($_POST['wpforms']['fields'][$honeypotField])) {
    $honeypotValue = $_POST['wpforms']['fields'][$honeypotField];
}

if (!empty($honeypotValue)) {
    echo json_encode(['success' => true, 'message' => 'Thank you for your submission']);
    exit;
}

function flattenArray($array, $prefix = '') {
    $result = [];
    foreach ($array as $key => $value) {
        $newKey = $prefix ? $prefix . '[' . $key . ']' : $key;
        if (is_array($value)) {
            $result = array_merge($result, flattenArray($value, $newKey));
        } else {
            $result[$newKey] = $value;
        }
    }
    return $result;
}

$formData = [];

if (isset($_POST['wpforms']) && is_array($_POST['wpforms'])) {
    $wpformsData = flattenArray($_POST['wpforms'], 'wpforms');
    $formData = array_merge($formData, $wpformsData);
}

foreach ($_POST as $key => $value) {
    if ($key !== 'wpforms') {
        if (is_array($value)) {
            $flatSub = flattenArray($value, $key);
            $formData = array_merge($formData, $flatSub);
        } else {
            $formData[$key] = $value;
        }
    }
}

if (empty($formData)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No form data provided']);
    exit;
}

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
    
    header('Location: /practice-areas/business-litigation-thank-you/');
    exit;
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}