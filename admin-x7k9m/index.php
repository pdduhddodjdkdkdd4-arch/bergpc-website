<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$pageTitle = 'Dashboard';
require_once __DIR__ . '/header.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // 使用新表统计
    $stmt = $db->query("SELECT COUNT(*) FROM form_submissions_new");
    $submissionsCount = $stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(DISTINCT form_id) FROM form_submissions_new");
    $formTypes = $stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(*) FROM lawyers");
    $lawyersCount = $stmt->fetchColumn();
    
    // 获取最近提交的数据
    $stmt = $db->query("SELECT * FROM form_submissions_new ORDER BY submitted_at DESC LIMIT 5");
    $recentSubmissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 查询字段值数据
    $submissionIds = array_column($recentSubmissions, 'id');
    if (!empty($submissionIds)) {
        $placeholders = implode(',', array_fill(0, count($submissionIds), '?'));
        $stmt = $db->prepare("SELECT submission_id, field_label, field_value FROM form_field_values WHERE submission_id IN ($placeholders)");
        $stmt->execute($submissionIds);
        $fieldValues = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 重新组织字段值
        $fieldMap = [];
        foreach ($fieldValues as $fv) {
            if (!isset($fieldMap[$fv['submission_id']])) {
                $fieldMap[$fv['submission_id']] = [];
            }
            $fieldMap[$fv['submission_id']][$fv['field_label']] = $fv['field_value'];
        }
        
        // 将字段值添加到 submissions 中
        foreach ($recentSubmissions as &$sub) {
            $sub['data'] = $fieldMap[$sub['id']] ?? [];
        }
    }
} catch(Throwable $e) {
    $submissionsCount = 0;
    $formTypes = 0;
    $lawyersCount = 0;
    $recentSubmissions = [];
    $message = 'Database error: ' . $e->getMessage();
    $messageType = 'error';
}
?>

<?php if (isset($message)): ?>
<div style="background: <?php echo $messageType === 'success' ? '#064e3b' : '#7f1d1d'; ?>; color: <?php echo $messageType === 'success' ? '#34d399' : '#fca5a5'; ?>; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo $submissionsCount; ?></div>
        <div class="stat-label">Total Submissions</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $formTypes; ?></div>
        <div class="stat-label">Active Forms</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $lawyersCount; ?></div>
        <div class="stat-label">Team Members</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Recent Submissions</h2>
        <a href="forms.php" class="btn btn-primary btn-sm">View All</a>
    </div>
    <?php if (empty($recentSubmissions)): ?>
        <div class="empty-state">
            <p>No submissions yet</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Form</th>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentSubmissions as $sub): ?>
                <tr>
                    <td><?php echo date('M j, Y g:i A', strtotime($sub['submitted_at'])); ?></td>
                    <td><span class="badge badge-blue"><?php echo htmlspecialchars($sub['form_name']); ?></span></td>
                    <td>
                        <?php 
                        // 从字段数据中获取姓名
                        $firstName = '';
                        $lastName = '';
                        $email = 'N/A';
                        foreach ($sub['data'] as $key => $value) {
                            if (strpos($key, 'first') !== false || strpos($key, 'First Name') !== false) {
                                $firstName = $value;
                            }
                            if (strpos($key, 'last') !== false || strpos($key, 'Last Name') !== false) {
                                $lastName = $value;
                            }
                            if (strpos($key, 'email') !== false || strpos($key, 'Email') !== false) {
                                $email = $value;
                            }
                        }
                        echo htmlspecialchars(trim($firstName . ' ' . $lastName) ?: 'N/A');
                        ?>
                    </td>
                    <td>
                        <?php 
                        $email = 'N/A';
                        foreach ($sub['data'] as $key => $value) {
                            if (strpos(strtolower($key), 'email') !== false) {
                                $email = $value;
                                break;
                            }
                        }
                        echo htmlspecialchars($email);
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
