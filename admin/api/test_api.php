<?php
echo "=== Forms API 测试脚本 ===\n\n";

$baseUrl = 'http://localhost/admin/api/forms_api.php';

echo "测试 1: 获取表单列表\n";
echo "URL: $baseUrl?action=forms\n";
$response = file_get_contents($baseUrl . '?action=forms');
$result = json_decode($response, true);
echo "响应: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "测试 2: 获取表单数据列表（第一页，每页5条）\n";
echo "URL: $baseUrl?page=1&per_page=5\n";
$response = file_get_contents($baseUrl . '?page=1&per_page=5');
$result = json_decode($response, true);
echo "响应: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "测试 3: 搜索功能测试\n";
echo "URL: $baseUrl?search=test&per_page=5\n";
$response = file_get_contents($baseUrl . '?search=test&per_page=5');
$result = json_decode($response, true);
echo "响应: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "测试 4: 表单类型筛选\n";
echo "URL: $baseUrl?form_id=2528&per_page=5\n";
$response = file_get_contents($baseUrl . '?form_id=2528&per_page=5');
$result = json_decode($response, true);
echo "响应: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "测试 5: 日期范围筛选\n";
echo "URL: $baseUrl?date_from=2024-01-01&date_to=2025-12-31&per_page=5\n";
$response = file_get_contents($baseUrl . '?date_from=2024-01-01&date_to=2025-12-31&per_page=5');
$result = json_decode($response, true);
echo "响应: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "测试 6: 排序功能测试\n";
echo "URL: $baseUrl?sort=submitted_at&order=desc&per_page=5\n";
$response = file_get_contents($baseUrl . '?sort=submitted_at&order=desc&per_page=5');
$result = json_decode($response, true);
echo "响应: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

if (isset($result['data'][0]['id'])) {
    $firstId = $result['data'][0]['id'];
    echo "测试 7: 获取单条记录详情\n";
    echo "URL: $baseUrl?action=detail&id=$firstId\n";
    $response = file_get_contents($baseUrl . '?action=detail&id=' . $firstId);
    $result = json_decode($response, true);
    echo "响应: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
}

echo "=== 测试完成 ===\n";
