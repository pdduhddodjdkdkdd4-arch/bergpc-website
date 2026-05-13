<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

$action = $_REQUEST['action'] ?? '';

if ($action === 'wpforms_submit') {
    $formId = $_REQUEST['form_id'] ?? $_REQUEST['wpforms']['id'] ?? '';
    $token = bin2hex(random_bytes(16));
    $time = time();
    echo json_encode([
        'success' => true,
        'data' => [
            'token' => $token,
            'time' => $time,
            'form_id' => $formId,
        ]
    ]);
    exit;
}

echo json_encode(['success' => true, 'data' => []]);
