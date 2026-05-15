<?php
/**
 * 目录复制辅助脚本
 * 访问此脚本会复制 admin/ 目录为 admin-x7k9m/
 * 之后删除此文件！
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>目录修复工具</h1>";

$sourceDir = __DIR__; // 当前目录是 admin/
$targetDir = dirname(__DIR__) . '/admin-x7k9m';

echo "<p>源目录: <code>" . htmlspecialchars($sourceDir) . "</code></p>";
echo "<p>目标目录: <code>" . htmlspecialchars($targetDir) . "</code></p>";

// 检查目标是否已存在
if (is_dir($targetDir)) {
    echo "<p style='color: orange;'>⚠️ 目标目录已存在！</p>";
    echo "<p>是否删除并重新复制？ <a href='?action=replace'>是的，替换</a> | <a href='?'>取消</a></p>";
    
    if (isset($_GET['action']) && $_GET['action'] === 'replace') {
        deleteDirectory($targetDir);
        echo "<p>已删除旧目录。</p>";
        copyDirectory($sourceDir, $targetDir);
    }
} else {
    // 复制
    copyDirectory($sourceDir, $targetDir);
}

echo "<hr>";
echo "<h2>下一步</h2>";
echo "<ol>";
echo "<li>删除此文件: <code>admin/fix_directory.php</code></li>";
echo "<li>使用简化版 .htaccess (见项目根目录 .htaccess.simple)</li>";
echo "<li>测试访问: <a href='../admin-x7k9m/simple-test.php?test=1'>admin-x7k9m/simple-test.php?test=1</a></li>";
echo "</ol>";

/**
 * 递归复制目录
 */
function copyDirectory($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    echo "<p>开始复制...</p><ul>";
    
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                echo "<li>目录: $file/</li>";
                copyDirectory($src . '/' . $file, $dst . '/' . $file);
            } else {
                echo "<li>文件: $file</li>";
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    
    closedir($dir);
    echo "</ul><p style='color: green;'>✅ 复制完成！</p>";
}

/**
 * 递归删除目录
 */
function deleteDirectory($dir) {
    if (!is_dir($dir)) return;
    
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            if (is_dir($dir . '/' . $file)) {
                deleteDirectory($dir . '/' . $file);
            } else {
                unlink($dir . '/' . $file);
            }
        }
    }
    rmdir($dir);
}
