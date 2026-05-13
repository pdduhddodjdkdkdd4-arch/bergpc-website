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
foreach ($_POST as $key => $value) {
    if (strpos($key, $honeypotField) !== false || $key === $honeypotField) {
        $honeypotValue = $value;
        break;
    }
}
if (!empty($honeypotValue)) {
    echo json_encode(['success' => true, 'message' => 'Thank you for your submission']);
    exit;
}

$formData = [];
foreach ($_POST as $key => $value) {
    if (strpos($key, 'wpforms[fields]') === 0) {
        $formData[$key] = is_array($value) ? implode(', ', $value) : $value;
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
    
    echo json_encode(['success' => true, 'message' => 'Thank you for your submission']);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
