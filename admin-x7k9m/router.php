<?php
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

preg_match('#/admin-x7k9m/(.*)#', $requestUri, $matches);
$path = isset($matches[1]) ? $matches[1] : 'index.php';

if (empty($path) || substr($path, -1) === '/') {
    $path .= 'index.php';
}

$targetFile = __DIR__ . '/../admin/' . $path;

if (file_exists($targetFile) && is_file($targetFile)) {
    chdir(dirname($targetFile));
    require $targetFile;
} else {
    http_response_code(404);
    echo '404 Not Found';
}
